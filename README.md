# Phone Numbers

## Description

Create a single page application that uses the database provided (SQLite 3) to list and
categorize country phone numbers.
Phone numbers should be categorized by country, state (valid or not valid), country code and
number.
The page should render a list of all phone numbers available in the DB. It should be possible to
filter by country and state. Pagination is an extra.

#### Problems
1. Some information not exsit in the database such country, country_code, state and formatted phone_num.
2. SQLite not support `regexp` function so we will not able to perfrom queries based on regexp to filter phone numbers and cannot extract related info such country_code or validate phone number using regexp query conditions.

## Proposed Solution
1. create user defined functions into SQLite to support `regexp` conditions and formating functions like extract country_code from phone number and other related usages.
3. using SQLite user defined functions we can perform query filters on database side so retrive only matched and formatted information.
2. define configuration file contain information about each country ex. country_name, regexp rules for country phone numbers so this config file can be used in SQLite user defined functions to get country name from code or validate specific country phone numbers.

## Solution Components

#### 1. Countries configuration file
using the configuration we can define regexp needed to parse any phone number extract country_code we can also validate the phone using regexp based on country defiend rules or get country name from country_code.

```php
<?php

return [
    'countries' => [
        237 => [
            'regexp' => '\(237\)\ ?[2368]\d{7,8}$',
            'country_name' => 'Cameroon',
            'country_code' => '+237'
        ],
        251 => [
            'regexp' => '\(251\)\ ?[1-59]\d{8}$',
            'country_name' => 'Ethiopia',
            'country_code' => '+251'
        ],
        212 => [
            'regexp' => '\(212\)\ ?[5-9]\d{8}$',
            'country_name' => 'Morocco',
            'country_code' => '+212'
        ],
        258 => [
            'regexp' => '\(258\)\ ?[28]\d{7,8}$',
            'country_name' => 'Mozambique',
            'country_code' => '+258'
        ],
        256 => [
            'regexp' => '\(256\)\ ?\d{9}$',
            'country_name' => 'Uganda',
            'country_code' => '+256'
        ],
    ],
    'phone_parts_regexp' => '^\(([0-9]{2,3})\)[ ](\w{7,11})$'
];
```

#### 2. Add user defined functions using laravel service provider

follwoing `SQLiteFunctionServiceProvider.php` file define our needed user defined functions as follwoing:
1. `regexp` function that can used by query where conditions to filter records based on spesiffic country regexp rule and it takes form of `SELECT * FROM customer WHERE phone REGEXP \(237\)\ ?[2368]\d{7,8}$` the query will retrive only Cameron matched phone numbers. 
2. `COUNTRY_CODE()` function which can extract country_code from phone column using regexp defined into configuration file, this will allow query format like `SELECT COUNTRY_CODE(phone) FROM customer;` this sholud return `212` from given phone number `(212) 123456789` also this function is used in our project to filter company in form like: `SELECT * FROM customer WHERE COUNTRY_CODE(phone) = '212'` so this will return only numbers in Morocco country.
3. `PHONE_NUM()` function to extract phone number without country code will allow query format like `SELECT PHONE_NUM(phone) FROM customer;` this sholud return `123456789` from given phone number `(212) 123456789`.
4. `PHONE_STATE()` it can take phone number and using the apprpriate regexp from confgiuration file and return `OK` or `NOK`, the typical usegae: `SELECT PHONE_STATE(phone) FROM customer;`.

so combine all these function to retrive country_code, state, phone_num for specific country SQLite can now support query in form of `SELECT COUNTRY_CODE(phone), PHONE_STATE(phone), PHONE_NUM(phone) FROM customer where COUNTRY_CODE(phone) = '256' `.

Follwoing php code will register and define these SQLite function when our app bootstrap, Laravel load all Service Providers defined in `app.php` Service Provider array one by one execute there `register` and `boot` functions. 


```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\SQLiteConnection;
use DB;
use Exception;

class SQLiteFunctionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (DB::Connection() instanceof SQLiteConnection) {
            $this->addSQLiteFunctions(DB::connection());
        }
    }

    private function addSQLiteFunctions(SQLiteConnection $connection)
    {
        $pdo = $connection->getPdo();

        // add sqlite functions
        $pdo->sqliteCreateFunction('REGEXP', function($pattern, $value) {
            return $this->regExpFunction($pattern, $value);
        });

        $pdo->sqliteCreateFunction('COUNTRY_CODE', function($phone) {
            return $this->countryCodeFunction($phone);
        });

        $pdo->sqliteCreateFunction('PHONE_NUM', function($phone) {
            return $this->phoneNumFunction($phone);
        });

        $pdo->sqliteCreateFunction('PHONE_STATE', function($phone) {
            return $this->phoneStateFunction($phone);
        });  
    }

    private function regExpFunction($pattern, $value)
    {
        return (false !== mb_ereg($pattern, $value)) ? 1 : 0;
    }

    private function countryCodeFunction($phone)
    {
        $phoneParts = $this->extractPhoneParts($phone);
        return $phoneParts[1] ?? null;
    }

    private function phoneNumFunction($phone)
    {
        $phoneParts = $this->extractPhoneParts($phone);
        return $phoneParts[2] ?? null;
    }

    private function phoneStateFunction($phone)
    {
        $country_code = $this->countryCodeFunction($phone);
        $country_regex = config('phone_configuration.countries')[$country_code]['regexp'] ?? null;

        if ($country_regex && mb_ereg($country_regex, $phone)) {
            return "OK";
        }
        return "NOK";
    }

    private function extractPhoneParts($phone) {
        if (empty(config('phone_configuration.phone_parts_regexp'))) {
            throw new Exception('phone extract parts configuration is not defiend');
        }
        
        mb_ereg(config('phone_configuration.phone_parts_regexp'), $phone, $matches);
        return $matches;
    }
}
```
#### 3. Customer Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PhoneHelperTrait;
use App\Traits\ApplyFilterTrait;

class Customer extends Model
{
    use PhoneHelperTrait;
    use ApplyFilterTrait;
    use HasFactory;

    protected $table = 'customer';

}
```
#### 4. Phone Helper Trait

```php
<?php

namespace App\Traits;

trait PhoneHelperTrait
{

    public function getCountryCodeAttribute()
    {
        return config('phone_configuration.countries')[$this->attributes['country_code']]['country_code'] ?? null;
    }

    public function getCountryAttribute()
    {
        return config('phone_configuration.countries')[$this->attributes['country_code']]['country_name'] ?? null;
    }

    public function scopeSelectPhoneCategories($query)
    {
        $query->selectRaw('COUNTRY_CODE(`phone`) as `country_code`')
            ->selectRaw('PHONE_NUM(`phone`) as `phone_num`')
            ->selectRaw('PHONE_STATE(`phone`) as `phone_state`');
    }

    public function scopeDistinctCountryCodes($query)
    {
        $query->selectRaw('COUNTRY_CODE(`phone`) as `country_code`')->groupBy('country_code');
    }
}

```

#### 5. ApplyFilterTrait

```php
<?php

namespace App\Traits;

trait ApplyFilterTrait
{
    private $db_filters = [
        'country_code' => 'COUNTRY_CODE(phone)',
        'state' => 'PHONE_STATE(phone)',
    ];
    
    public function scopeApplyFilter($query, $column, $value)
    {
        $db_filter = $this->db_filters[$column] ?? $column;
        if(!empty($db_filter) && !empty($value)) {
            $query->whereRaw("$db_filter = ?", [$value]);
        }
    }
}

```

### 6. Api Routes
```php
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('customers', 'App\Http\Controllers\CustomerController@index');
Route::get('countries-list', 'App\Http\Controllers\CountryController@list');
```

#### 7. CustomerController

```php
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

```

#### 8. CountryController 
```php
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

```

## Frontend Components
#### 1. App.vue

```vue
<template>
  <div class="container">
      <b-card
      img-src="https://group.jumia.com/_nuxt/img/j-group.67a6140.svg"
      img-top
      header-tag="header"
      title="Phone Numbers"
      class="jumia_img mt-4"
    >
      <search-fields  :countries="countries" ></search-fields>
      <customer-list :customers="customers"></customer-list>
      <pagination align="center" :data="customersPagination" @pagination-change-page="getCustomerList"></pagination>    
      </b-card>
  </div>
</template>

<script>
import axios from "axios";
import pagination from 'laravel-vue-pagination'
import CustomerList from "./components/CustomerList.vue";
import SearchFields from "./components/SearchFields.vue";
export default {
  components: {
    CustomerList,
    SearchFields,
    pagination
  },
  data() {
    return {
      filters: {},
      customers: [],
      countries: [],
      customersPagination: {}
    };
  },
  created() {
    this.getCustomerList();
    this.getCountriesList();

    this.$root.$on('filterBy', (filterBy) => {
      this.filters[filterBy.by] = filterBy.value;
      this.getCustomerList();
    })
  },
  methods: {
    getCustomerList(page = 1) {
      axios
        .post(`/api/customers?page=${page}`, {filters: this.filters})
        .then((response) => {
          this.customers = response.data.data;
          this.customersPagination = response.data;
        })
        .catch((err) => {
          console.log("some thing wrong happened");
        });
    },
    getCountriesList() {
      axios
        .get(`/api/countries-list`)
        .then((response) => {
          this.countries = response.data.data;
        })
        .catch((err) => {
          console.log("some thing wrong happened");
        });
    }
  }
};
</script>
```


#### 2. CustomerList.vue

```vue
<template>
  <div>
    <b-row class="mt-4">
      <b-col>
        <b-table striped hover :items="customers"></b-table>
      </b-col>
    </b-row>
  </div>
</template>

<script>
export default {
  props: {
    customers: Array,
  },
};
</script>
```

#### 3. SearchFields.vue
```vue
<template>
  <div>
    <b-form inline>
      <b-row class="mt-4">
        <b-col cols="3">
          <b-form-select v-model="selectedCountry" :options="countriesList"></b-form-select>
        </b-col>
        <b-col cols="3">
          <b-form-select v-model="selectedState" :options="stateList"></b-form-select>
        </b-col>
      </b-row>
    </b-form>
  </div>
</template>

<script>
export default {
  props: {
    countries: Array,
  },
  watch: {
    countries(countries) {
      this.countriesList = [{ value: null, text: "Please select an option" }];
      countries.forEach((country) =>
        this.countriesList.push({
          value: country.country_code,
          text: country.country_name,
        })
      );
    },
    selectedCountry(country_code) {
      this.filterBy({by: 'country_code', value: country_code});
    },
    selectedState(state) {
      this.filterBy({by: 'state', value: state});
    },
  },
  data() {
    return {
      selectedCountry: null,
      selectedState: null,
      countriesList: [{ value: null, text: "Please select an option" }],
      stateList: [
        { value: null, text: "Please select an option" },
        { value: 'OK', text: "Valid phone numbers" },
        { value: 'NOK', text: "Invalid phone numbers" },
      ],
    };
  },
  methods: {
    filterBy($filter) {
      this.$root.$emit('filterBy', $filter);
    }
  }
};
</script>
```

## Installation

To run this project without issues please use  docker container to build up the project follwoing the steps to uild the project.

go to docker directory and build docker containers:

```bash
cd docker
docker-compose build
```

and after project built successfully start up the containers:

```bash
docker-compose up -d
```

## Build Project
###### Build Laravel & Vuejs 
 build project (Laravel & Vuejs) using follwoing steps.

1. go to app root and run follwoing to build laravel project:

```bash
composer install 
``` 

```bash
php artisan key:gen
```

2. build and install npm pakages:

```bash
npm install
```

```bash
npm run dev
```



## RUN Project 

visit the ip:
http://10.10.0.102/

this setting from docker-compose.yaml file if there some conflicts with your local network just adjust settings on theat file.


## THANKS 

Thanks for reaching this point :) please feel free to contact me to disucss any thing realted to the implementation above.


 Author
- [Yaseen Taha](https://github.com/showyaseen)
- showyaseen@hotmail.com