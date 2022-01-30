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
