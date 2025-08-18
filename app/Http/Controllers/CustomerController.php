<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        if (request()->wantsJson()) {
            return response(Customer::all());
        }
        $customers = Customer::latest()->paginate(10);
        return view('customers.index')->with('customers', $customers);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(CustomerStoreRequest $request)
    {
        $customer = Customer::create([
            'first_name'  => $request->first_name,
            'last_name'   => $request->last_name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'address'     => $request->address,
            'user_id'     => $request->user()->id,
            'cnic'        => $request->cnic,
            'ntn_number'  => $request->ntn_number,
            'fbr_number'  => $request->fbr_number,
        ]);

        if (!$customer) {
            return redirect()->back()->with('error', 'Sorry, something went wrong while creating the customer.');
        }

        return redirect()->route('customers.index')->with('success', 'Success! New customer has been added.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->first_name  = $request->first_name;
        $customer->last_name   = $request->last_name;
        $customer->email       = $request->email;
        $customer->phone       = $request->phone;
        $customer->address     = $request->address;
        $customer->cnic        = $request->cnic;
        $customer->ntn_number  = $request->ntn_number;
        $customer->fbr_number  = $request->fbr_number;

        if (!$customer->save()) {
            return redirect()->back()->with('error', 'Sorry, something went wrong while updating the customer.');
        }

        return redirect()->route('customers.index')->with('success', 'Success! The customer has been updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
