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
1. We have to create user defined functions into SQLite to support nessaccery operation needed to applied into phone number such `regex` funtion and formating function like extract country_code from phone number so we are able to provide the information needed to show.
2. we can define configuration file contain information about each country ex. country_name, regex function for country phone numbers so this config file can be used in sqlite user defined functions to get country name from code or validate specific country phone numbers.

## Solution Components

1. #### countries configuration file
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



 Author

- [Yaseen Taha](https://github.com/showyaseen)
- showyaseen@hotmail.com

## THANKS 
