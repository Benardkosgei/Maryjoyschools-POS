<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'code', 'adresse', 'email', 'phone', 'country', 'city', 'tax_number',
        // Student-specific fields
        'student_id', 'admission_number', 'class_grade', 'stream', 'house', 
        'date_of_birth', 'gender', 'parent_guardian_name', 'parent_guardian_phone', 
        'parent_guardian_email', 'emergency_contact', 'account_balance', 
        'credit_limit', 'status', 'notes'
    ];

    protected $casts = [
        'code' => 'integer',
        'date_of_birth' => 'date',
        'account_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    /**
     * Get the student's full name with class information
     */
    public function getFullStudentInfoAttribute()
    {
        $info = $this->name;
        if ($this->class_grade) {
            $info .= ' - ' . $this->class_grade;
            if ($this->stream) {
                $info .= $this->stream;
            }
        }
        return $info;
    }

    /**
     * Get the student's age
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    /**
     * Check if student can make a purchase based on credit limit
     */
    public function canPurchase($amount)
    {
        $availableCredit = $this->credit_limit + $this->account_balance;
        return $amount <= $availableCredit;
    }

    /**
     * Get available credit for the student
     */
    public function getAvailableCreditAttribute()
    {
        return $this->credit_limit + $this->account_balance;
    }

    /**
     * Scope for active students only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope for students by class
     */
    public function scopeByClass($query, $class)
    {
        return $query->where('class_grade', $class);
    }

    /**
     * Relationships
     */
    public function sales()
    {
        return $this->hasMany(\App\Models\Sale::class, 'client_id');
    }

    public function saleReturns()
    {
        return $this->hasMany(\App\Models\SaleReturn::class, 'client_id');
    }

    public function quotations()
    {
        return $this->hasMany(\App\Models\Quotation::class, 'client_id');
    }

    public function paymentSales()
    {
        return $this->hasManyThrough(\App\Models\PaymentSale::class, \App\Models\Sale::class, 'client_id', 'sale_id');
    }
}
