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
