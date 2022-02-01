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
    'phone_parts_regexp' => '^\(([0-9]{2,3})\)[ ](\w{2,11})$'
];
