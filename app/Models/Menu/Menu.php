<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable =['name','description','category','image','is_available','price','created_at','updated_at'];
}
