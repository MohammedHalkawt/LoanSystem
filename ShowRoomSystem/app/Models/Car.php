<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'customer_id',
        'name',
        'model_year',
        'status',
        'drive_folder_id',
        'purchase_receipt_file_id',
    ];

    protected $casts = [
        'model_year' => 'integer',
    ];

    /**
     * Get the purchase that created this car.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the customer who owns this car.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rentPayments()
    {
        return $this->hasMany(RentPayment::class);
    }

    public function getDriveLinkAttribute()
    {
        if (!$this->drive_folder_id) {
            return null;
        }

        return "https://drive.google.com/drive/folders/{$this->drive_folder_id}";
    }
}
