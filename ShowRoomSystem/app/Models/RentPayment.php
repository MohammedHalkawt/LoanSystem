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
        'covered_month_from',
        'covered_month_to',
        'months_count',
        'payment_date',
        'receipt_path',
        'receipt_drive_file_id',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount'       => 'decimal:2',
        'months_count' => 'integer',
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

    public function getReceiptDriveLinkAttribute()
    {
        if (!$this->receipt_drive_file_id) {
            return null;
        }

        return "https://drive.google.com/file/d/{$this->receipt_drive_file_id}/view";
    }
}
