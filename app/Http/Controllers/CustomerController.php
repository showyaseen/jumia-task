<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customerQuery = Customer::SelectPhoneCategories();
        
        if(!empty($request->filters)) {
            foreach ($request->filters as $by=>$value) {
                $customerQuery->applyFilter($by, $value);
            }
        }
        
        $customers = $customerQuery->paginate(10);
        return CustomerResource::collection($customers);
    }
}
