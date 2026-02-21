<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'car_id',
        'amount',
        'payment_date',
        'receipt_path',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount'       => 'decimal:2',
    ];

    /**
     * Get the customer that made the payment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the car this payment is for.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}