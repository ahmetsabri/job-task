<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function lastAddedItem()
    {
        return $this->hasOne(CartItem::class)->ofMany('created_at');
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
