<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Model
{
    use HasFactory;
    protected $fillable = ['name','permit_scan','landline_number','city','street','alley','management_full_name','management_phone'];

}
