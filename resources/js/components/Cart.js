import React, { Component } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";

// ---- AXIOS SETUP (CSRF + Global error handler) ----
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
  axios.defaults.headers.common["X-CSRF-TOKEN"] = token.getAttribute("content");
}
axios.interceptors.response.use(
  (res) => res,
  (error) => {
    const status = error?.response?.status;
    const message =
      error?.response?.data?.message ||
      error?.message ||
      "Something went wrong";
    if (status === 401) {
      Swal.fire("Session expired", "Please login again.", "error");
      // window.location.reload();
    } else if (status === 419) {
      Swal.fire("Security error", "CSRF token mismatch.", "error");
    } else {
      Swal.fire("Error", message, "error");
    }
    return Promise.reject(error);
  }
);

class Cart extends Component {
  constructor(props) {
    super(props);
    this.state = {
      // data
      cart: [],
      products: [],
      categories: [],
      selectedCategory: null,
      selectedSubcategory: null,

      // product search/pagination
      search: "",
      currentPage: 1,
      totalPages: 1,

      // barcode & customer
      barcode: "",
      customer_id: "",
      extraDiscount: 0,

      // customers lazy search (no full preload)
      customerQuery: "",
      customerOptions: [],
      customerLoading: false,
      showCustomerDropdown: false,

      // UI states
      error: null,
      layout: "grid",
      showLayoutDropdown: false,
      categoryScrollPosition: 0,
      subcategoryScrollPosition: 0,

      // loading/locks
      loadingCart: false,
      loadingProducts: false,
      loadingCategories: false,
      scanningBarcode: false,
      submittingOrder: false,
      changingQtyIds: {}, // { [product_id]: true }

      // internals for debounce/cancellation
      _productSearchTimer: null,
    };

    // binds
    this.loadCart = this.loadCart.bind(this);
    this.loadProducts = this.loadProducts.bind(this);
    this.loadCategories = this.loadCategories.bind(this);
    this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
    this.handleScanBarcode = this.handleScanBarcode.bind(this);
    this.addProductToCart = this.addProductToCart.bind(this);
    this.handleChangeQty = this.handleChangeQty.bind(this);
    this.handleClickDelete = this.handleClickDelete.bind(this);
    this.handleEmptyCart = this.handleEmptyCart.bind(this);
    this.handleChangeSearch = this.handleChangeSearch.bind(this);
    this.handleSeach = this.handleSeach.bind(this);
    this.handlePageChange = this.handlePageChange.bind(this);
    this.handleCategoryClick = this.handleCategoryClick.bind(this);
    this.handleSubcategoryClick = this.handleSubcategoryClick.bind(this);
    this.scrollCategories = this.scrollCategories.bind(this);
    this.scrollSubcategories = this.scrollSubcategories.bind(this);
    this.toggleLayout = this.toggleLayout.bind(this);
    this.getSubtotal = this.getSubtotal.bind(this);
    this.getTotal = this.getTotal.bind(this);
    this.handleChangeExtraDiscount = this.handleChangeExtraDiscount.bind(this);
    this.handleClickSubmit = this.handleClickSubmit.bind(this);
    this.setCustomerId = this.setCustomerId.bind(this);
    this.onChangeCustomerQuery = this.onChangeCustomerQuery.bind(this);
    this.selectCustomer = this.selectCustomer.bind(this);

    // axios cancel tokens
    this.productsCancelSource = null;
    this.customersCancelSource = null;
  }

  componentDidMount() {
    this.loadCart();
    this.loadProducts();
    this.loadCategories();
    // we DO NOT pre-load all customers to avoid heavy payloads
  }

  componentWillUnmount() {
    if (this.productsCancelSource) this.productsCancelSource.cancel();
    if (this.customersCancelSource) this.customersCancelSource.cancel();
    if (this.state._productSearchTimer) clearTimeout(this.state._productSearchTimer);
  }

  // ------------------- LOADERS -------------------
  async loadCart() {
    try {
      this.setState({ loadingCart: true });
      const res = await axios.get("/admin/cart");
      this.setState({ cart: res.data, error: null });
    } catch (err) {
      this.setState({
        error: "Failed to load cart. Please check your connection or login status.",
      });
    } finally {
      this.setState({ loadingCart: false });
    }
  }

  async loadProducts(search = "", categoryId = null, page = 1) {
    try {
      if (this.productsCancelSource) this.productsCancelSource.cancel("New request initiated");
      this.productsCancelSource = axios.CancelToken.source();
      this.setState({ loadingProducts: true });

      const params = new URLSearchParams();
      params.set("for", "cart");
      if (search) params.set("search", search);
      if (categoryId) params.set("category_id", categoryId);
      params.set("page", page);

      const url = `/admin/products?${params.toString()}`;
      const res = await axios.get(url, { cancelToken: this.productsCancelSource.token });
      const { data, current_page, last_page } = res.data;

      this.setState({
        products: data || [],
        currentPage: current_page || 1,
        totalPages: last_page || 1,
        error: null,
      });
    } catch (err) {
      if (!axios.isCancel(err)) {
        this.setState({
          error: "Failed to load products. Please check your connection or login status.",
        });
      }
    } finally {
      this.setState({ loadingProducts: false });
    }
  }

  async loadCategories() {
    try {
      this.setState({ loadingCategories: true });
      const res = await axios.get("/admin/categories/pos");
      const categories = res.data || [];
      this.setState({ categories, error: null });
      if (categories.length === 0) {
        this.setState({
          error: "No categories available. Please add categories in the admin panel.",
        });
      }
    } catch (err) {
      this.setState({
        error: "Failed to load categories. Please check your connection or login status.",
      });
    } finally {
      this.setState({ loadingCategories: false });
    }
  }

  // ------------------- BARCODE -------------------
  handleOnChangeBarcode(e) {
    this.setState({ barcode: e.target.value });
  }

  async handleScanBarcode(e) {
    e.preventDefault();
    const { barcode, scanningBarcode } = this.state;
    if (!barcode || scanningBarcode) return;

    try {
      this.setState({ scanningBarcode: true });
      await axios.post("/admin/cart", { barcode });
      await this.loadCart();
      this.setState({ barcode: "" });
    } catch (err) {
      // error handled by interceptor
    } finally {
      this.setState({ scanningBarcode: false });
    }
  }

  // ------------------- PRODUCTS → CART -------------------
  async addProductToCart(product) {
  try {
    if (product.barcode) {
      await axios.post("/admin/cart", { barcode: product.barcode });
    } else {
      await axios.post("/admin/cart", { product_id: product.id });
    }
    await this.loadCart();
  } catch (err) {
    // handled by interceptor
  }
}

  // ------------------- CART CHANGES -------------------
  async handleChangeQty(product_id, qty) {
    qty = Math.max(1, Number(qty) || 1);
    const prevCart = this.state.cart.map((c) => ({ ...c, pivot: { ...c.pivot } }));

    // Optimistic UI with rollback if failed
    const nextCart = this.state.cart.map((c) => {
      if (c.id === product_id) {
        return { ...c, pivot: { ...c.pivot, quantity: qty } };
      }
      return c;
    });

    this.setState((s) => ({
      cart: nextCart,
      changingQtyIds: { ...s.changingQtyIds, [product_id]: true },
    }));

    try {
      await axios.post("/admin/cart/change-qty", { product_id, quantity: qty });
      // (optional) refresh cart in case backend adjusted price/discount
      await this.loadCart();
    } catch (err) {
      // rollback on error
      this.setState({ cart: prevCart });
    } finally {
      this.setState((s) => {
        const copy = { ...s.changingQtyIds };
        delete copy[product_id];
        return { changingQtyIds: copy };
      });
    }
  }

  async handleClickDelete(product_id) {
    const prevCart = this.state.cart;
    const nextCart = prevCart.filter((c) => c.id !== product_id);
    this.setState({ cart: nextCart });

    try {
      await axios.post("/admin/cart/delete", { product_id, _method: "DELETE" });
      // ensure sync
      await this.loadCart();
    } catch (err) {
      this.setState({ cart: prevCart }); // rollback
    }
  }

  async handleEmptyCart() {
    const prevCart = this.state.cart;
    this.setState({ cart: [] });
    try {
      await axios.post("/admin/cart/empty", { _method: "DELETE" });
      await this.loadCart();
    } catch (err) {
      this.setState({ cart: prevCart });
    }
  }

  // ------------------- PRODUCTS SEARCH/PAGE -------------------
  handleChangeSearch(e) {
    const search = e.target.value;
    const { selectedCategory, selectedSubcategory, _productSearchTimer } = this.state;

    if (_productSearchTimer) clearTimeout(_productSearchTimer);
    this.setState({
      search,
      selectedCategory: null,
      selectedSubcategory: null,
      currentPage: 1,
      categoryScrollPosition: 0,
      _productSearchTimer: setTimeout(() => {
        const categoryId = null;
        this.loadProducts(search, categoryId, 1);
      }, 300),
    });
  }

  handleSeach(e) {
    if (e.keyCode === 13) {
      const { search } = this.state;
      if (this.state._productSearchTimer) clearTimeout(this.state._productSearchTimer);
      this.loadProducts(search, null, 1);
    }
  }

  handlePageChange(page) {
    const { search, selectedCategory, selectedSubcategory } = this.state;
    const categoryId = selectedSubcategory
      ? selectedSubcategory.id
      : selectedCategory
      ? selectedCategory.id
      : null;
    this.setState({ currentPage: page });
    this.loadProducts(search, categoryId, page);
  }

  handleCategoryClick(category) {
    this.setState({
      selectedCategory: category,
      selectedSubcategory: null,
      search: "",
      currentPage: 1,
      subcategoryScrollPosition: 0,
    });
    this.loadProducts("", category.id, 1);
  }

  handleSubcategoryClick(subcategory) {
    this.setState({ selectedSubcategory: subcategory, currentPage: 1 });
    this.loadProducts("", subcategory.id, 1);
  }

  // ------------------- SCROLLERS -------------------
  scrollCategories(direction) {
    const container = document.getElementById("category-carousel");
    if (container) {
      const scrollAmount = direction === "left" ? -200 : 200;
      container.scrollBy({ left: scrollAmount, behavior: "smooth" });
      this.setState((prev) => ({
        categoryScrollPosition: prev.categoryScrollPosition + scrollAmount,
      }));
    }
  }

  scrollSubcategories(direction) {
    const container = document.getElementById("subcategory-carousel");
    if (container) {
      const scrollAmount = direction === "left" ? -200 : 200;
      container.scrollBy({ left: scrollAmount, behavior: "smooth" });
      this.setState((prev) => ({
        subcategoryScrollPosition: prev.subcategoryScrollPosition + scrollAmount,
      }));
    }
  }

  // ------------------- CUSTOMERS (AJAX SEARCH) -------------------
  async searchCustomers(query) {
    if (this.customersCancelSource) this.customersCancelSource.cancel("New customer search");
    this.customersCancelSource = axios.CancelToken.source();
    this.setState({ customerLoading: true });

    try {
      const res = await axios.get(`/admin/customers?search=${encodeURIComponent(query)}`, {
        cancelToken: this.customersCancelSource.token,
      });
      const customers = Array.isArray(res.data?.data) ? res.data.data : res.data || [];
      this.setState({
        customerOptions: customers,
        showCustomerDropdown: true,
      });
    } catch (err) {
      // handled by interceptor
    } finally {
      this.setState({ customerLoading: false });
    }
  }

  onChangeCustomerQuery(e) {
    const query = e.target.value;
    this.setState({ customerQuery: query });
    if (query.length >= 2) {
      // small debounce
      if (this._customerTimer) clearTimeout(this._customerTimer);
      this._customerTimer = setTimeout(() => this.searchCustomers(query), 250);
    } else {
      if (this._customerTimer) clearTimeout(this._customerTimer);
      this.setState({ customerOptions: [], showCustomerDropdown: false });
    }
  }

  setCustomerId(e) {
    this.setState({ customer_id: e.target.value });
  }

  selectCustomer(customer) {
    this.setState({
      customer_id: customer.id,
      customerQuery: `${customer.first_name || ""} ${customer.last_name || ""}`.trim(),
      showCustomerDropdown: false,
    });
  }

  // ------------------- TOTALS -------------------
  handleChangeExtraDiscount(e) {
    const value = parseFloat(e.target.value) || 0;
    this.setState({ extraDiscount: Math.min(Math.max(value, 0), 100) });
  }

  getSubtotal(cart) {
    return sum(
      cart.map(
        (c) => c.price * (1 - (c.discount_percentage || 0) / 100) * c.pivot.quantity
      )
    );
  }

  getTotal() {
    const subtotal = this.getSubtotal(this.state.cart);
    const discount = subtotal * (this.state.extraDiscount / 100);
    return subtotal - discount;
  }

  // ------------------- ORDER SUBMIT -------------------
  handleClickSubmit() {
    const total = this.getTotal();
    if (!this.state.cart.length) return;

    this.setState({ submittingOrder: true });

    Swal.fire({
      title: "Received Amount",
      input: "text",
      inputValue: total.toFixed(2),
      showCancelButton: true,
      confirmButtonText: "Send",
      showLoaderOnConfirm: true,
      preConfirm: (amount) => {
        return axios
          .post("/admin/orders", {
            customer_id: this.state.customer_id,
            amount,
            extra_discount: this.state.extraDiscount,
          })
          .then((res) => {
            this.loadCart();
            return res.data;
          })
          .catch((err) => {
            Swal.showValidationMessage(
              err?.response?.data?.message || "Failed to place order"
            );
          });
      },
      allowOutsideClick: () => !Swal.isLoading(),
    }).then((result) => {
      this.setState({ submittingOrder: false });
      if (result.value) {
        Swal.fire("Success", "Order placed successfully!", "success");
      }
    });
  }

  // ------------------- UI HELPERS -------------------
  getCategoryIcon(category) {
    const iconMap = {
      vegetable: "🥕",
      fruits: "🍎",
      meat: "🥩",
      dairy: "🥛",
      drinks: "☕",
      cleaning: "🧽",
      default: "📦",
    };
    const categoryName = (category?.name || "").toLowerCase();
    return iconMap[categoryName] || iconMap.default;
  }

  toggleLayout(layout) {
    this.setState({ layout, showLayoutDropdown: false });
  }

  // ------------------- RENDER -------------------
  render() {
    const {
      cart,
      products,
      categories,
      barcode,
      selectedCategory,
      selectedSubcategory,
      error,
      search,
      currentPage,
      totalPages,
      categoryScrollPosition,
      subcategoryScrollPosition,
      layout,
      showLayoutDropdown,
      extraDiscount,
      loadingProducts,
      loadingCart,
      scanningBarcode,
      submittingOrder,
      changingQtyIds,

      // customers ui
      customer_id,
      customerQuery,
      customerOptions,
      customerLoading,
      showCustomerDropdown,
    } = this.state;

    const subtotal = this.getSubtotal(cart);
    const discount = subtotal * (extraDiscount / 100);
    const total = subtotal - discount;

    const isCategoryStart = categoryScrollPosition <= 0;
    const isCategoryEnd = categoryScrollPosition >= (categories.length - 4) * 80;
    const isSubcategoryStart = subcategoryScrollPosition <= 0;
    const isSubcategoryEnd =
      subcategoryScrollPosition >=
      ((selectedCategory?.subcategories?.length || 0) - 4) * 120;

    const currency = (window.APP && window.APP.currency_symbol) || "$";

    return (
      <div className="bg-gray-50 min-h-screen flex flex-col">
        {error && (
          <div className="px-4 sm:px-6 py-2">
            <div className="p-4 bg-red-100 text-red-700 rounded-lg text-sm">
              {error}
            </div>
          </div>
        )}

        <div className="flex flex-1 flex-col md:flex-row">
          {/* Main Content */}
          <div className="flex-1 p-4 sm:p-6 min-w-0 max-w-full">
            {/* Categories Section */}
            <div className="mb-6 sm:mb-8">
              <div className="flex items-center gap-2 mb-4">
                <button className="px-4 py-2 bg-green-500 text-white rounded-lg text-sm sm:text-base">
                  Categories
                </button>
                {this.state.loadingCategories && (
                  <span className="text-xs text-gray-500">Loading...</span>
                )}
              </div>

              <div className="relative px-8 sm:px-10">
                <button
                  className={`absolute ml-3 left-[-2rem] sm:left-[-2.5rem] top-1/2 -translate-y-1/2 z-10 px-2 py-1 rounded-full text-sm shadow-sm ${
                    isCategoryStart
                      ? "bg-gray-300 text-gray-500 cursor-not-allowed"
                      : "bg-green-500 text-white hover:bg-green-600"
                  }`}
                  onClick={() => this.scrollCategories("left")}
                  disabled={isCategoryStart}
                  aria-label="Previous categories"
                >
                  ←
                </button>
                <div
                  id="category-carousel"
                  className="flex overflow-x-auto scrollbar-hide space-x-3 sm:space-x-4 pb-2"
                  style={{ scrollSnapType: "x mandatory" }}
                >
                  {categories.map((category) => (
                    <div
                      key={category.id}
                      className={`bg-white rounded-lg shadow-sm p-3 sm:p-4 cursor-pointer transition-all flex-shrink-0 w-24 sm:w-28 ${
                        selectedCategory && selectedCategory.id === category.id
                          ? "border-2 border-green-500"
                          : "border border-gray-200 hover:shadow-md"
                      }`}
                      onClick={() => this.handleCategoryClick(category)}
                    >
                      <div
                        className={`w-12 sm:w-16 h-12 sm:h-16 rounded-full mb-2 sm:mb-3 flex items-center justify-center text-xl sm:text-2xl mx-auto ${
                          selectedCategory && selectedCategory.id === category.id
                            ? "bg-green-500 text-white"
                            : "bg-gray-100"
                        }`}
                      >
                        {this.getCategoryIcon(category)}
                      </div>
                      <div className="w-full">
                        <span className="text-xs sm:text-sm font-semibold text-gray-800 truncate text-center block">
                          {category.name}
                        </span>
                        <div className="text-xs text-gray-400 text-center">
                          + {category.product_count || 0} items
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
                <button
                  className={`absolute mr-4 right-[-2rem] sm:right-[-2.5rem] top-1/2 -translate-y-1/2 z-10 px-2 py-1 rounded-full text-sm shadow-sm ${
                    isCategoryEnd
                      ? "bg-gray-300 text-gray-500 cursor-not-allowed"
                      : "bg-green-500 text-white hover:bg-green-600"
                  }`}
                  onClick={() => this.scrollCategories("right")}
                  disabled={isCategoryEnd}
                  aria-label="Next categories"
                >
                  →
                </button>
              </div>

              {/* Subcategories */}
              {selectedCategory &&
                selectedCategory.subcategories &&
                selectedCategory.subcategories.length > 0 && (
                  <div className="mb-4 sm:mb-6">
                    {selectedCategory.subcategories.length > 6 ? (
                      <div className="relative mt-4 px-8 sm:px-10">
                        <button
                          className={`absolute ml-3 left-[-2rem] sm:left-[-2.5rem] top-1/2 -translate-y-1/2 z-10 px-2 py-1 rounded-full text-sm shadow-sm ${
                            isSubcategoryStart
                              ? "bg-gray-300 text-gray-500 cursor-not-allowed"
                              : "bg-green-500 text-white hover:bg-green-600"
                          }`}
                          onClick={() => this.scrollSubcategories("left")}
                          disabled={isSubcategoryStart}
                          aria-label="Previous subcategories"
                        >
                          ←
                        </button>
                        <div
                          id="subcategory-carousel"
                          className="flex overflow-x-auto scrollbar-hide space-x-2 sm:space-x-3 pb-2"
                          style={{ scrollSnapType: "x mandatory" }}
                        >
                          {selectedCategory.subcategories.map((subcategory) => (
                            <button
                              key={subcategory.id}
                              className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg whitespace-nowrap transition text-xs sm:text-sm flex-shrink-0 ${
                                selectedSubcategory &&
                                selectedSubcategory.id === subcategory.id
                                  ? "bg-green-500 text-white"
                                  : "bg-gray-200 text-gray-700 hover:bg-gray-300"
                              }`}
                              onClick={() =>
                                this.handleSubcategoryClick(subcategory)
                              }
                            >
                              {subcategory.name} ({subcategory.product_count || 0})
                            </button>
                          ))}
                        </div>
                        <button
                          className={`absolute mr-4 right-[-2rem] sm:right-[-2.5rem] top-1/2 -translate-y-1/2 z-10 px-2 py-1 rounded-full text-sm shadow-sm ${
                            isSubcategoryEnd
                              ? "bg-gray-300 text-gray-500 cursor-not-allowed"
                              : "bg-green-500 text-white hover:bg-green-600"
                          }`}
                          onClick={() => this.scrollSubcategories("right")}
                          disabled={isSubcategoryEnd}
                          aria-label="Next subcategories"
                        >
                          →
                        </button>
                      </div>
                    ) : (
                      <div className="flex overflow-x-auto mt-4 space-x-2 sm:space-x-3 pb-2">
                        {selectedCategory.subcategories.map((subcategory) => (
                          <button
                            key={subcategory.id}
                            className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg whitespace-nowrap transition text-xs sm:text-sm ${
                              selectedSubcategory &&
                              selectedSubcategory.id === subcategory.id
                                ? "bg-green-500 text-white"
                                : "bg-gray-200 text-gray-700 hover:bg-gray-300"
                            }`}
                            onClick={() => this.handleSubcategoryClick(subcategory)}
                          >
                            {subcategory.name} ({subcategory.product_count || 0})
                          </button>
                        ))}
                      </div>
                    )}
                  </div>
                )}
            </div>

            {/* Search Section */}
            <div className="mb-4 sm:mb-6">
              <h2 className="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">
                Products
              </h2>
              <div className="flex items-center space-x-3 sm:space-x-4">
                <div className="relative flex-1 max-w-xs">
                  <input
                    type="text"
                    placeholder="Search by Product Name"
                    className="w-full px-4 sm:px-5 py-1 pl-8 border border-gray-300 rounded-lg text-xs sm:text-sm"
                    value={search}
                    onChange={this.handleChangeSearch}
                    onKeyDown={this.handleSeach}
                  />
                  <svg
                    className="w-4 h-4 text-gray-400 absolute left-2 top-1/2 -translate-y-1/2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    ></path>
                  </svg>
                </div>
                <div className="relative">
                  <button
                    className="px-4 sm:px-5 py-1 sm:py-1.5 bg-gray-800 text-white rounded-lg hover:bg-gray-700 text-xs sm:text-sm"
                    onClick={() =>
                      this.setState({
                        showLayoutDropdown: !showLayoutDropdown,
                      })
                    }
                    aria-label="Toggle layout dropdown"
                  >
                    {layout === "grid" ? "Grid" : "List"}
                  </button>
                  {showLayoutDropdown && (
                    <div className="absolute z-10 mt-2 w-32 bg-white shadow-lg rounded-lg border border-gray-200">
                      <button
                        className="block w-full text-left px-4 py-2 text-xs sm:text-sm hover:bg-gray-100"
                        onClick={() => this.toggleLayout("grid")}
                        aria-label="Select Grid layout"
                      >
                        Grid
                      </button>
                      <button
                        className="block w-full text-left px-4 py-2 text-xs sm:text-sm hover:bg-gray-100"
                        onClick={() => this.toggleLayout("list")}
                        aria-label="Select List layout"
                      >
                        List
                      </button>
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Products Display */}
            {loadingProducts ? (
              <div className="py-10 text-center text-sm text-gray-500">
                Loading products...
              </div>
            ) : products.length === 0 ? (
              <div className="py-10 text-center text-sm text-gray-500">
                No products found.
              </div>
            ) : layout === "grid" ? (
              <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
                {products.map((product) => (
                  <div
                    key={product.id}
                    className="bg-white rounded-lg p-3 sm:p-4 shadow-sm border hover:shadow-md transition-shadow cursor-pointer flex flex-col items-center min-w-0"
                    onClick={() => this.addProductToCart(product)}
                    title="Click to add to cart"
                  >
                    <div className="w-12 sm:w-16 h-12 sm:h-16 bg-gray-100 rounded-full mb-2 sm:mb-3 flex items-center justify-center text-xl sm:text-2xl mx-auto overflow-hidden">
                                            {product.image_url ? (
                                                <img
                                                    src={product.image_url}
                                                    alt={product.name}
                                                    className="w-full h-full object-cover rounded-full"
                                                />
                                            ) : (
                                                '📦'
                                            )}
                                        </div>

                    <h3
                      className={`font-medium text-xs sm:text-sm mb-1 text-center ${
                        window.APP && window.APP.warning_quantity > product.quantity
                          ? "text-red-500"
                          : "text-gray-800"
                      } truncate w-full`}
                    >
                      {product.name}
                    </h3>
                    <div className="text-center text-xs sm:text-sm flex flex-wrap justify-center gap-1 sm:gap-2">
                      <span className="text-green-600 font-semibold">
                        {currency}
                        {product.price}
                      </span>
                    </div>
                    <div className="text-center text-xs text-gray-500 mt-1">
                      Stock: {product.quantity}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="flex flex-col space-y-3">
                {products.map((product) => (
                  <div
                    key={product.id}
                    className="bg-white rounded-lg p-3 sm:p-4 shadow-sm border hover:shadow-md transition-shadow cursor-pointer flex items-center justify-between"
                    onClick={() => this.addProductToCart(product.barcode)}
                    title="Click to add to cart"
                >
                    <h3
                      className={`flex-1 font-medium text-xs sm:text-sm ${
                        window.APP && window.APP.warning_quantity > product.quantity
                          ? "text-red-500"
                          : "text-gray-800"
                      } truncate`}
                    >
                      {product.name}
                    </h3>
                    <div className="flex items-center space-x-4 sm:space-x-6">
                      <span className="text-green-600 font-semibold text-xs sm:text-sm">
                        {currency}
                        {product.price}
                      </span>
                      <span className="text-xs text-gray-500">
                        Stock: {product.quantity}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            )}

            {/* Pagination Controls */}
            <div className="mt-4 sm:mt-6 flex items-center justify-center space-x-2 sm:space-x-4">
              <button
                className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm ${
                  currentPage === 1
                    ? "bg-gray-300 text-gray-500 cursor-not-allowed"
                    : "bg-green-500 text-white hover:bg-green-600"
                }`}
                onClick={() => this.handlePageChange(currentPage - 1)}
                disabled={currentPage === 1}
              >
                Previous
              </button>
              <span className="text-xs sm:text-sm text-gray-600">
                Page {currentPage} of {totalPages}
              </span>
              <button
                className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm ${
                  currentPage === totalPages
                    ? "bg-gray-300 text-gray-500 cursor-not-allowed"
                    : "bg-green-500 text-white hover:bg-green-600"
                }`}
                onClick={() => this.handlePageChange(currentPage + 1)}
                disabled={currentPage === totalPages}
              >
                Next
              </button>
            </div>
          </div>

          {/* Shopping Cart Sidebar */}
          <div className="w-full md:w-96 bg-white border-t md:border-t-0 md:border-l p-4 sm:p-6">
            <div className="flex items-center justify-between mb-4 sm:mb-6">
              <h2 className="text-base sm:text-lg font-semibold">Billing Section</h2>
              <div className="flex items-center space-x-2">
                <span className="bg-green-100 text-green-700 px-2 py-1 rounded text-xs sm:text-sm">
                  Customer
                </span>
              </div>
            </div>

            {/* Customer Search + Select (AJAX) */}
            <div className="mb-4 sm:mb-6">
              <div className="relative">
                <input
                  type="text"
                  value={customerQuery}
                  onChange={this.onChangeCustomerQuery}
                  placeholder="Search customer by name/phone"
                  className="w-full px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                  onFocus={() =>
                    this.setState({ showCustomerDropdown: customerOptions.length > 0 })
                  }
                />
                {customerLoading && (
                  <div className="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400">
                    ...
                  </div>
                )}
                {showCustomerDropdown && (
                  <div className="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow max-h-56 overflow-auto">
                    {customerOptions.length === 0 ? (
                      <div className="px-3 py-2 text-xs text-gray-500">
                        No customers
                      </div>
                    ) : (
                      customerOptions.map((c) => (
                        <button
                          key={c.id}
                          className="w-full text-left px-3 py-2 text-xs sm:text-sm hover:bg-gray-100"
                          onClick={() => this.selectCustomer(c)}
                        >
                          {(c.first_name || "") + " " + (c.last_name || "")}
                        </button>
                      ))
                    )}
                  </div>
                )}
              </div>

              {/* Hidden select for compatibility (optional keep) */}
              <select
                className="w-full mt-2 px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                onChange={this.setCustomerId}
                value={customer_id}
              >
                <option value="">Select Customer (optional)</option>
                {customerOptions.map((c) => (
                  <option key={c.id} value={c.id}>
                    {(c.first_name || "") + " " + (c.last_name || "")}
                  </option>
                ))}
              </select>

              {/* Barcode Input */}
              <form onSubmit={this.handleScanBarcode} className="mt-4">
                <input
                  type="text"
                  value={barcode}
                  onChange={this.handleOnChangeBarcode}
                  placeholder="Scan or enter barcode"
                  className="w-full px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-100"
                  disabled={scanningBarcode}
                />
              </form>
              <div className="flex space-x-2 mt-2">
                <button
                  className="px-3 py-1.5 sm:py-2 text-gray-500 hover:text-gray-700 text-xs sm:text-sm"
                  onClick={this.handleEmptyCart}
                  disabled={loadingCart || !cart.length}
                >
                  Clear Cart
                </button>
              </div>
            </div>

            {/* Cart Items */}
            <div className="space-y-4 mb-4 sm:mb-6">
              <div className="flex items-center justify-between py-2 text-xs sm:text-sm font-medium text-gray-600">
                <span>Item</span>
                <span>Discount %</span>
                <span>QTY</span>
                <span>Price</span>
                <span>Delete</span>
              </div>

              {loadingCart && (
                <div className="text-xs text-gray-500">Loading cart...</div>
              )}

              {cart.map((item) => (
                <div
                  key={item.id}
                  className="flex items-center space-x-3 py-2 border-b text-xs sm:text-sm"
                >
                  <div className="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden">
                    {item.image_url ? (
                      <img
                        src={item.image_url}
                        alt={item.name}
                        className="w-full h-full object-cover rounded-full"
                      />
                    ) : (
                      "📦"
                    )}
                  </div>
                  <div className="flex-1">
                    <h4 className="font-medium truncate">{item.name}</h4>
                  </div>
                  <div className="text-gray-600">{item.discount_percentage || 0}%</div>
                  <div className="flex items-center space-x-2">
                    <button
                      className="w-5 sm:w-6 h-5 sm:h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 disabled:opacity-50"
                      onClick={() =>
                        this.handleChangeQty(
                          item.id,
                          Math.max(1, item.pivot.quantity - 1)
                        )
                      }
                      disabled={!!changingQtyIds[item.id]}
                    >
                      -
                    </button>
                    <span>{item.pivot.quantity}</span>
                    <button
                      className="w-5 sm:w-6 h-5 sm:h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 disabled:opacity-50"
                      onClick={() =>
                        this.handleChangeQty(item.id, item.pivot.quantity + 1)
                      }
                      disabled={!!changingQtyIds[item.id]}
                    >
                      +
                    </button>
                  </div>
                  <div className="text-right">
                    <div className="font-semibold">
                      {currency}
                      {(
                        item.price *
                        (1 - (item.discount_percentage || 0) / 100) *
                        item.pivot.quantity
                      ).toFixed(2)}
                    </div>
                  </div>
                  <button
                    className="text-red-500 hover:text-red-700 mt-1 disabled:opacity-50"
                    onClick={() => this.handleClickDelete(item.id)}
                    disabled={!!changingQtyIds[item.id]}
                    title="Remove item"
                  >
                    🗑️
                  </button>
                </div>
              ))}
            </div>

            {/* Bill Summary */}
            <div className="space-y-2 mb-4 sm:mb-6 text-xs sm:text-sm">
              <div className="flex justify-between">
                <span>Sub total:</span>
                <span>
                  {currency}
                  {subtotal.toFixed(2)}
                </span>
              </div>
              <div className="flex justify-between items-center">
                <span>Extra discount (%):</span>
                <input
                  type="number"
                  min="0"
                  max="100"
                  step="0.01"
                  value={extraDiscount}
                  onChange={this.handleChangeExtraDiscount}
                  className="w-20 px-2 py-1 text-right border border-gray-300 rounded"
                />
              </div>
              <div className="border-t pt-2 mt-2">
                <div className="flex justify-between font-semibold">
                  <span>Total:</span>
                  <span>
                    {currency}
                    {total.toFixed(2)}
                  </span>
                </div>
              </div>
            </div>

            {/* Action Buttons */}
            <div className="space-y-3">
              <button
                className="w-full px-4 py-2 sm:py-3 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition-colors disabled:bg-gray-400 text-xs sm:text-sm"
                onClick={this.handleEmptyCart}
                disabled={!cart.length || loadingCart}
              >
                Cancel Order
              </button>
              <button
                className="w-full px-4 py-2 sm:py-3 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors disabled:bg-gray-400 text-xs sm:text-sm"
                onClick={this.handleClickSubmit}
                disabled={!cart.length || submittingOrder}
              >
                {submittingOrder ? "Placing..." : "Place Order"}
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default Cart;

if (document.getElementById("cart")) {
  ReactDOM.render(<Cart />, document.getElementById("cart"));
}
