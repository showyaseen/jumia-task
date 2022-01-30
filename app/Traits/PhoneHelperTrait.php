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
