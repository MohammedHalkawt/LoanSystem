<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'car_name',
        'model_year',
        'overall_price',
        'basic_price',
        'upfront_payment',
        'purchase_date',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'overall_price' => 'decimal:2',
        'basic_price'   => 'decimal:2',
        'upfront_payment' => 'decimal:2',
    ];

    /**
     * Get the customer that owns the purchase.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the car associated with the purchase.
     */
    public function car(): HasOne
    {
        return $this->hasOne(Car::class);
    }
}