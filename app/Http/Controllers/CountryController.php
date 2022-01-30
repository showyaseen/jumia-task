<?php

namespace App\Http\Controllers;

use App\Http\Resources\CountryCodeResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function list(Request $request) {
        return CountryCodeResource::collection(Customer::distinctCountryCodes()->get());
    }
}
