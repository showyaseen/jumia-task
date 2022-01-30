# Phone Numbers

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

this setting from docker-composeyaml file if there some conflicts with your local network just adjust settings on theat file.

please contact me if have any issue run the task project.

## TASK PROBLEM
#### Task Description
Create a single page application that uses the database provided (SQLite 3) to list and
categorize country phone numbers.
Phone numbers should be categorized by country, state (valid or not valid), country code and
number.
The page should render a list of all phone numbers available in the DB. It should be possible to
filter by country and state. Pagination is an extra.

#### Task Problems
1. the information needed to be rendered on the frontend is not exist on the database such country, country_code, state and formatted phone_num.
2. SQLite not support `regexp` function which is the key feature in this task since categorize phone and extract related info such country_code or validate phone number is depend on using regex conditions.

## Proposed Solution
1. create user defined functions into SQLite to support nessaccery operation needed to applied into phone number such `regex` funtion and formating function like extract country_code from phone number so we are able to provide the information needed to show.
3. using SQLite user defined functions we can perform our rules on database side so retrive only matched and formatted informations.
2. define configuration file contain information about each country ex. country_name, regex rules for country phone numbers so this config file can be used in sqlite user defined functions to get country name from code or validate specific country phone numbers.

## Solution Components

#### 1. Countries configuration file
using the configuration we can define regex needed to parse any phone number extract country_code we can also validate the phone using regex based on country defiend rules or get country name from country_code.

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
1. `regex` function that can used by query where conditions to filter records based on spesiffic country regex rule and it takes form of `coulmn regex pattern`. 
2. `country_code()` function which can extract country_code from phone column using regex defined into configuration file.
3. `phone_num()` function to extract phone number without country code.
4. `phone_state()` it can take phone number and using the apprpriate regex from confgiuration file and return `OK` or `NOK`.

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
        if (empty(config('phone_configuration.phone_parts_regexp'))) {
            throw new Exception('phone extract parts configuration is not defiend');
        }
        mb_regex_encoding('UTF-8');
        mb_ereg(config('phone_configuration.phone_parts_regexp'), $phone, $matches);
        return $matches[1] ?? null;
    }

    private function phoneNumFunction($phone)
    {
        if (empty(config('phone_configuration.phone_parts_regexp'))) {
            throw new Exception('phone extract parts configuration is not defiend');
        }
        mb_regex_encoding('UTF-8');
        mb_ereg(config('phone_configuration.phone_parts_regexp'), $phone, $matches);
        return $matches[2] ?? null;
    }

    private function phoneStateFunction($phone)
    {
        $country_code = $this->countryCodeFunction($phone);
        $country_regex = config('phone_configuration.countries')[$country_code]['regexp'] ?? null;
        mb_regex_encoding('UTF-8');
        if ($country_regex && mb_ereg($country_regex, $phone)) {
            return "OK";
        }
        return "NOK";
    }
}
```
#### 3. Phone Helper Trait

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

#### 4. ApplyFilterTrait

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

#### 5. CustomerController

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

#### 6. CountryController 
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


## THANKS 

Thanks for reaching this point :) please feel free to contact me to disucss any thing realted to the implementation above.


 Author
- [Yaseen Taha](https://github.com/showyaseen)
- showyaseen@hotmail.com