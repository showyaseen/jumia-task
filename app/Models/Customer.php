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
