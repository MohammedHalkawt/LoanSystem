<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'folder_path',
    ];
    protected $appends = ['google_drive_link'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function rentPayments()
    {
        return $this->hasMany(RentPayment::class);
    }

    public function getGoogleDriveLinkAttribute()
    {
        if (!$this->folder_path) {
            return null;
        }
        return "https://drive.google.com/drive/folders/{$this->folder_path}";
    }
}
