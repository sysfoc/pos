import React, { Component } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";

// Set CSRF token for Axios requests
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

class Cart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            customers: [],
            categories: [],
            selectedCategory: null,
            selectedSubcategory: null,
            barcode: "",
            search: "",
            customer_id: "",
            extraDiscount: 0,
            error: null,
            currentPage: 1,
            totalPages: 1,
            categoryScrollPosition: 0,
            subcategoryScrollPosition: 0,
            layout: "grid",
            showLayoutDropdown: false,
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);
        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.setCustomerId = this.setCustomerId.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadCategories = this.loadCategories.bind(this);
        this.handleCategoryClick = this.handleCategoryClick.bind(this);
        this.handleSubcategoryClick = this.handleSubcategoryClick.bind(this);
        this.handlePageChange = this.handlePageChange.bind(this);
        this.scrollCategories = this.scrollCategories.bind(this);
        this.scrollSubcategories = this.scrollSubcategories.bind(this);
        this.toggleLayout = this.toggleLayout.bind(this);
        this.handleChangeExtraDiscount = this.handleChangeExtraDiscount.bind(this);
    }

    componentDidMount() {
        this.loadCart();
        this.loadProducts();
        this.loadCustomers();
        this.loadCategories();
    }

    loadCustomers() {
        axios.get(`/admin/customers`).then(res => {
            const customers = res.data;
            this.setState({ customers });
        }).catch(err => {
            console.error("Error loading customers:", err.response ? err.response.data : err.message);
            this.setState({ error: "Failed to load customers. Please check your connection or login status." });
        });
    }

    loadProducts(search = "", categoryId = null, page = 1) {
        const query = search ? `search=${search}` : "";
        const categoryQuery = categoryId ? `${query ? '&' : ''}category_id=${categoryId}` : "";
        const pageQuery = `${query || categoryQuery ? '&' : ''}page=${page}`;
        const url = `/admin/products?for=cart&${query}${categoryQuery}${pageQuery}`;
        axios.get(url).then(res => {
            const { data, current_page, last_page } = res.data;
            this.setState({
                products: data,
                currentPage: current_page,
                totalPages: last_page,
            });
        }).catch(err => {
            console.error("Error loading products:", err.response ? err.response.data : err.message);
            this.setState({ error: "Failed to load products. Please check your connection or login status." });
        });
    }

    loadCategories() {
        axios.get("/admin/categories/pos").then(res => {
            const categories = res.data;
            this.setState({ categories, error: null });
            if (categories.length === 0) {
                this.setState({ error: "No categories available. Please add categories in the admin panel." });
            }
        }).catch(err => {
            console.error("Error loading categories:", err.response ? err.response.data : err.message);
            this.setState({ error: "Failed to load categories. Please check your connection or login status." });
        });
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

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;
        this.setState({ barcode });
    }

    loadCart() {
        axios.get("/admin/cart").then(res => {
            const cart = res.data;
            this.setState({ cart });
        }).catch(err => {
            console.error("Error loading cart:", err.response ? err.response.data : err.message);
            this.setState({ error: "Failed to load cart. Please check your connection or login status." });
        });
    }

    handleScanBarcode(event) {
        event.preventDefault();
        const { barcode } = this.state;
        if (barcode) {
            axios
                .post("/admin/cart", { barcode })
                .then(res => {
                    this.loadCart();
                    this.setState({ barcode: "" });
                })
                .catch(err => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }

    handleChangeQty(product_id, qty) {
        const cart = this.state.cart.map(c => {
            if (c.id === product_id) {
                c.pivot.quantity = qty;
            }
            return c;
        });

        this.setState({ cart });

        axios
            .post("/admin/cart/change-qty", { product_id, quantity: qty })
            .then(res => {})
            .catch(err => {
                Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    handleChangeExtraDiscount(event) {
        const value = parseFloat(event.target.value) || 0;
        this.setState({ extraDiscount: Math.min(Math.max(value, 0), 100) });
    }

    getSubtotal(cart) {
        return sum(cart.map(c => c.price * (1 - (c.discount_percentage || 0) / 100) * c.pivot.quantity));
    }

    handleClickDelete(product_id) {
        axios
            .post("/admin/cart/delete", { product_id, _method: "DELETE" })
            .then(res => {
                const cart = this.state.cart.filter(c => c.id !== product_id);
                this.setState({ cart });
            });
    }

    handleEmptyCart() {
        axios.post("/admin/cart/empty", { _method: "DELETE" }).then(res => {
            this.setState({ cart: [] });
        });
    }

    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search, selectedCategory: null, selectedSubcategory: null, currentPage: 1, categoryScrollPosition: 0 });
        this.loadProducts(search, null, 1);
    }

    handleSeach(event) {
        if (event.keyCode === 13) {
            this.loadProducts(this.state.search, null, 1);
        }
    }

    handlePageChange(page) {
        const { search, selectedCategory, selectedSubcategory } = this.state;
        const categoryId = selectedSubcategory ? selectedSubcategory.id : selectedCategory ? selectedCategory.id : null;
        this.setState({ currentPage: page });
        this.loadProducts(search, categoryId, page);
    }

    scrollCategories(direction) {
        const container = document.getElementById('category-carousel');
        if (container) {
            const scrollAmount = direction === 'left' ? -200 : 200;
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            this.setState(prevState => ({
                categoryScrollPosition: prevState.categoryScrollPosition + scrollAmount
            }));
        }
    }

    scrollSubcategories(direction) {
        const container = document.getElementById('subcategory-carousel');
        if (container) {
            const scrollAmount = direction === 'left' ? -200 : 200;
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            this.setState(prevState => ({
                subcategoryScrollPosition: prevState.subcategoryScrollPosition + scrollAmount
            }));
        }
    }

    addProductToCart(barcode) {
        let product = this.state.products.find(p => p.barcode === barcode);
        if (product) {
            let cart = this.state.cart.find(c => c.id === product.id);
            if (cart) {
                this.setState({
                    cart: this.state.cart.map(c => {
                        if (c.id === product.id && product.quantity > c.pivot.quantity) {
                            c.pivot.quantity = c.pivot.quantity + 1;
                        }
                        return c;
                    })
                });
            } else if (product.quantity > 0) {
                product = {
                    ...product,
                    pivot: {
                        quantity: 1,
                        product_id: product.id,
                        user_id: 1
                    }
                };
                this.setState({ cart: [...this.state.cart, product] });
            }

            axios
                .post("/admin/cart", { barcode })
                .then(res => {})
                .catch(err => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }

    setCustomerId(event) {
        this.setState({ customer_id: event.target.value });
    }

    handleClickSubmit() {
        const total = this.getTotal();
        Swal.fire({
            title: 'Received Amount',
            input: 'text',
            inputValue: total,
            showCancelButton: true,
            confirmButtonText: 'Send',
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                return axios.post('/admin/orders', { customer_id: this.state.customer_id, amount, extra_discount: this.state.extraDiscount }).then(res => {
                    this.loadCart();
                    return res.data;
                }).catch(err => {
                    Swal.showValidationMessage(err.response.data.message);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.value) {
                // Handle success
            }
        });
    }

    getCategoryIcon(category) {
        const iconMap = {
            'vegetable': '🥕',
            'fruits': '🍎',
            'meat': '🥩',
            'dairy': '🥛',
            'drinks': '☕',
            'cleaning': '🧽',
            'default': '📦'
        };

        const categoryName = category.name.toLowerCase();
        return iconMap[categoryName] || iconMap.default;
    }

    toggleLayout(layout) {
        this.setState({ layout, showLayoutDropdown: false });
    }

    getTotal() {
        const subtotal = this.getSubtotal(this.state.cart);
        const discount = subtotal * (this.state.extraDiscount / 100);
        return subtotal - discount;
    }

    render() {
        const { cart, products, customers, categories, barcode, selectedCategory, selectedSubcategory, error, search, currentPage, totalPages, categoryScrollPosition, subcategoryScrollPosition, layout, showLayoutDropdown, extraDiscount } = this.state;
        const subtotal = this.getSubtotal(cart);
        const discount = subtotal * (extraDiscount / 100);
        const total = subtotal - discount;

        const isCategoryStart = categoryScrollPosition <= 0;
        const isCategoryEnd = categoryScrollPosition >= (categories.length - 4) * 80; // Approximate max scroll
        const isSubcategoryStart = subcategoryScrollPosition <= 0;
        const isSubcategoryEnd = subcategoryScrollPosition >= ((selectedCategory?.subcategories?.length || 0) - 4) * 120; // Approximate max scroll

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
                            <div className="flex space-x-2 mb-4">
                                <button className="px-4 py-2 bg-green-500 text-white rounded-lg text-sm sm:text-base">Categories</button>
                            </div>

                            <div className="relative px-8 sm:px-10">
                                <button
                                    className={`absolute ml-3 left-[-2rem] sm:left-[-2.5rem] top-1/2 -translate-y-1/2 z-10 px-2 py-1 rounded-full text-sm shadow-sm ${
                                        isCategoryStart ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-500 text-white hover:bg-green-600'
                                    }`}
                                    onClick={() => this.scrollCategories('left')}
                                    disabled={isCategoryStart}
                                    aria-label="Previous categories"
                                >
                                    ←
                                </button>
                                <div
                                    id="category-carousel"
                                    className="flex overflow-x-auto scrollbar-hide space-x-3 sm:space-x-4 pb-2"
                                    style={{ scrollSnapType: 'x mandatory' }}
                                >
                                    {categories.map(category => (
                                        <div
                                            key={category.id}
                                            className={`bg-white rounded-lg shadow-sm p-3 sm:p-4 cursor-pointer transition-all flex-shrink-0 w-24 sm:w-28 ${
                                                selectedCategory && selectedCategory.id === category.id ? 'border-2 border-green-500' : 'border border-gray-200 hover:shadow-md'
                                            }`}
                                            onClick={() => this.handleCategoryClick(category)}
                                        >
                                            <div className={`w-12 sm:w-16 h-12 sm:h-16 rounded-full mb-2 sm:mb-3 flex items-center justify-center text-xl sm:text-2xl mx-auto ${
                                                selectedCategory && selectedCategory.id === category.id ? 'bg-green-500 text-white' : 'bg-gray-100'
                                            }`}>
                                                {this.getCategoryIcon(category)}
                                            </div>
                                            <div className="w-full">
                                                <span className="text-xs sm:text-sm font-semibold text-gray-800 truncate text-center block">{category.name}</span>
                                                <div className="text-xs text-gray-400 text-center">+ {category.product_count || 0} items</div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <button
                                    className={`absolute mr-4 right-[-2rem] sm:right-[-2.5rem] top-1/2 -translate-y-1/2 z-10 px-2 py-1 rounded-full text-sm shadow-sm ${
                                        isCategoryEnd ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-500 text-white hover:bg-green-600'
                                    }`}
                                    onClick={() => this.scrollCategories('right')}
                                    disabled={isCategoryEnd}
                                    aria-label="Next categories"
                                >
                                    →
                                </button>
                            </div>

                            {/* Subcategories */}
                            {selectedCategory && selectedCategory.subcategories && selectedCategory.subcategories.length > 0 && (
                                <div className="mb-4 sm:mb-6">
                                    {selectedCategory.subcategories.length > 6 ? (
                                        <div className="relative mt-4 px-8 sm:px-10">
                                            <button
                                                className={`absolute ml-3 left-[-2rem] sm:left-[-2.5rem] top-1/2 -translate-y-1/2 z-10 px-2 py-1 rounded-full text-sm shadow-sm ${
                                                    isSubcategoryStart ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-500 text-white hover:bg-green-600'
                                                }`}
                                                onClick={() => this.scrollSubcategories('left')}
                                                disabled={isSubcategoryStart}
                                                aria-label="Previous subcategories"
                                            >
                                                 ←
                                            </button>
                                            <div
                                                id="subcategory-carousel"
                                                className="flex overflow-x-auto scrollbar-hide space-x-2 sm:space-x-3 pb-2"
                                                style={{ scrollSnapType: 'x mandatory' }}
                                            >
                                                {selectedCategory.subcategories.map(subcategory => (
                                                    <button
                                                        key={subcategory.id}
                                                        className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg whitespace-nowrap transition text-xs sm:text-sm flex-shrink-0 ${
                                                            selectedSubcategory && selectedSubcategory.id === subcategory.id
                                                                ? 'bg-green-500 text-white'
                                                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                                        }`}
                                                        onClick={() => this.handleSubcategoryClick(subcategory)}
                                                    >
                                                        {subcategory.name} ({subcategory.product_count || 0})
                                                    </button>
                                                ))}
                                            </div>
                                            <button
                                                className={`absolute mr-4 right-[-2rem] sm:right-[-2.5rem] top-1/2 -translate-y-1/2 z-10 px-2 py-1 rounded-full text-sm shadow-sm ${
                                                    isSubcategoryEnd ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-500 text-white hover:bg-green-600'
                                                }`}
                                                onClick={() => this.scrollSubcategories('right')}
                                                disabled={isSubcategoryEnd}
                                                aria-label="Next subcategories"
                                            >
                                                →
                                            </button>
                                        </div>
                                    ) : (
                                        <div className="flex overflow-x-auto mt-4 space-x-2 sm:space-x-3 pb-2">
                                            {selectedCategory.subcategories.map(subcategory => (
                                                <button
                                                    key={subcategory.id}
                                                    className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg whitespace-nowrap transition text-xs sm:text-sm ${
                                                        selectedSubcategory && selectedSubcategory.id === subcategory.id
                                                            ? 'bg-green-500 text-white'
                                                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
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
                            <h2 className="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Products</h2>
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
                                    <svg className="w-4 h-4 text-gray-400 absolute left-2 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <div className="relative">
                                    <button
                                        className="px-4 sm:px-5 py-1 sm:py-1.5 bg-gray-800 text-white rounded-lg hover:bg-gray-700 text-xs sm:text-sm"
                                        onClick={() => this.setState({ showLayoutDropdown: !showLayoutDropdown })}
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
                        {layout === "grid" ? (
                            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
                                {products.map(product => (
                                    <div
                                        key={product.id}
                                        className="bg-white rounded-lg p-3 sm:p-4 shadow-sm border hover:shadow-md transition-shadow cursor-pointer flex flex-col items-center min-w-0"
                                        onClick={() => this.addProductToCart(product.barcode)}
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
                                        <h3 className={`font-medium text-xs sm:text-sm mb-1 text-center ${
                                            window.APP && window.APP.warning_quantity > product.quantity
                                                ? "text-red-500"
                                                : "text-gray-800"
                                        } truncate w-full`}>
                                            {product.name}
                                        </h3>
                                        <div className="text-center text-xs sm:text-sm flex flex-wrap justify-center gap-1 sm:gap-2">
                                            <span className="text-green-600 font-semibold">
                                                {window.APP ? window.APP.currency_symbol : '$'}{product.price}
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
                                {products.map(product => (
                                    <div
                                        key={product.id}
                                        className="bg-white rounded-lg p-3 sm:p-4 shadow-sm border hover:shadow-md transition-shadow cursor-pointer flex items-center justify-between"
                                        onClick={() => this.addProductToCart(product.barcode)}
                                    >
                                        <h3 className={`flex-1 font-medium text-xs sm:text-sm ${
                                            window.APP && window.APP.warning_quantity > product.quantity
                                                ? "text-red-500"
                                                : "text-gray-800"
                                        } truncate`}>
                                            {product.name}
                                        </h3>
                                        <div className="flex items-center space-x-4 sm:space-x-6">
                                            <span className="text-green-600 font-semibold text-xs sm:text-sm">
                                                {window.APP ? window.APP.currency_symbol : '$'}{product.price}
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
                                    currentPage === 1 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-500 text-white hover:bg-green-600'
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
                                    currentPage === totalPages ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-500 text-white hover:bg-green-600'
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
                                <span className="bg-green-100 text-green-700 px-2 py-1 rounded text-xs sm:text-sm">Customer</span>
                            </div>
                        </div>

                        {/* Customer Selection */}
                        <div className="mb-4 sm:mb-6">
                            <select
                                className="w-full px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                onChange={this.setCustomerId}
                                value={this.state.customer_id}
                            >
                                <option value="">Add Customer</option>
                                {customers.map(customer => (
                                    <option key={customer.id} value={customer.id}>
                                        {`${customer.first_name} ${customer.last_name}`}
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
                                    className="w-full px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                            </form>
                            <div className="flex space-x-2 mt-2">
                                <button
                                    className="px-3 py-1.5 sm:py-2 text-gray-500 hover:text-gray-700 text-xs sm:text-sm"
                                    onClick={this.handleEmptyCart}
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

                            {cart.map(item => (
                                <div key={item.id} className="flex items-center space-x-3 py-2 border-b text-xs sm:text-sm">
                                    <div className="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden">
                                        {item.image_url ? (
                                            <img
                                                src={item.image_url}
                                                alt={item.name}
                                                className="w-full h-full object-cover rounded-full"
                                            />
                                        ) : (
                                            '📦'
                                        )}
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="font-medium truncate">{item.name}</h4>
                                    </div>
                                    <div className="text-gray-600">
                                        {item.discount_percentage || 0}%
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <button
                                            className="w-5 sm:w-6 h-5 sm:h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100"
                                            onClick={() => this.handleChangeQty(item.id, Math.max(1, item.pivot.quantity - 1))}
                                        >
                                            -
                                        </button>
                                        <span>{item.pivot.quantity}</span>
                                        <button
                                            className="w-5 sm:w-6 h-5 sm:h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100"
                                            onClick={() => this.handleChangeQty(item.id, item.pivot.quantity + 1)}
                                        >
                                            +
                                        </button>
                                    </div>
                                    <div className="text-right">
                                        <div className="font-semibold">
                                            {window.APP ? window.APP.currency_symbol : '$'}{(item.price * (1 - (item.discount_percentage || 0) / 100) * item.pivot.quantity).toFixed(2)}
                                        </div>
                                    </div>
                                    <button
                                        className="text-red-500 hover:text-red-700 mt-1"
                                        onClick={() => this.handleClickDelete(item.id)}
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
                                <span>{window.APP ? window.APP.currency_symbol : '$'}{subtotal.toFixed(2)}</span>
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
                                    <span>{window.APP ? window.APP.currency_symbol : '$'}{total.toFixed(2)}</span>
                                </div>
                            </div>
                        </div>

                        {/* Action Buttons */}
                        <div className="space-y-3">
                            <button
                                className="w-full px-4 py-2 sm:py-3 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition-colors disabled:bg-gray-400 text-xs sm:text-sm"
                                onClick={this.handleEmptyCart}
                                disabled={!cart.length}
                            >
                                Cancel Order
                            </button>
                            <button
                                className="w-full px-4 py-2 sm:py-3 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors disabled:bg-gray-400 text-xs sm:text-sm"
                                onClick={this.handleClickSubmit}
                                disabled={!cart.length}
                            >
                                Place Order
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
