<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'dob',
        'job_title',
        'department',
        'role',
        'employee_id',
        'joining_date',
        'status',
        'address',
        'emergency_name',
        'emergency_phone',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            // Get current year
            $year = date('Y');

            // Find last employee ID for this year
            $lastEmployee = self::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            if ($lastEmployee && $lastEmployee->employee_id) {
                // Extract the number from EMP-2025-0001
                $lastNumber = intval(substr($lastEmployee->employee_id, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = "0001";
            }

            // Generate employee_id
            $employee->employee_id = "EMP-{$year}-{$newNumber}";
        });
    }
}
