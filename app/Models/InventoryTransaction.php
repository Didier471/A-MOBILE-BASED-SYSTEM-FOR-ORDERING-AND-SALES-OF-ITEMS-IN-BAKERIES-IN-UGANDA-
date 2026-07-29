<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'remarks',
        'created_by',
    ];

    /**
     * The product involved in this inventory transaction.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The user who performed this inventory transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}