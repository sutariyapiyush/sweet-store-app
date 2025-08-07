<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'address',
        'gst_number',
        'pan_number',
        'contact_person',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the raw materials supplied by this seller
     */
    public function rawMaterials()
    {
        return $this->hasMany(RawMaterial::class);
    }

    /**
     * Scope to get only active sellers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted GST number
     */
    public function getFormattedGstNumberAttribute()
    {
        if (!$this->gst_number) {
            return null;
        }

        // Format GST number as XX-XXXXX-XXXXX-XXX
        return substr($this->gst_number, 0, 2) . '-' .
               substr($this->gst_number, 2, 5) . '-' .
               substr($this->gst_number, 7, 5) . '-' .
               substr($this->gst_number, 12, 3);
    }

    /**
     * Validate GST number format
     */
    public static function isValidGstNumber($gstNumber)
    {
        // GST number should be 15 characters: 2 state code + 10 PAN + 1 entity number + 1 default 'Z' + 1 check digit
        return preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstNumber);
    }
}
