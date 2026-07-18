<?php

namespace App\Models\Menu;

use App\Models\Restaurant\Restaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    protected $fillable =['name','description','category','image','is_available','price','restaurant_id','created_at','updated_at'];

    public function restaurant():belongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
