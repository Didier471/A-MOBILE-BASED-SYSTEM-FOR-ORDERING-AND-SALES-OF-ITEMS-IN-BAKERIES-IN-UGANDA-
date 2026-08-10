<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'customer_id',
        'delivery_address',
        'recipient_name',
        'recipient_phone',
        'delivery_fee',
        'status',
        'assigned_to',
        'scheduled_at',
        'delivered_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Sale Relationship
    |--------------------------------------------------------------------------
    */

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Customer Relationship
    |--------------------------------------------------------------------------
    */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Assigned User Relationship
    |--------------------------------------------------------------------------
    */

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /*
    |--------------------------------------------------------------------------
    | Created By Relationship
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}