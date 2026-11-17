<?php

namespace App\Http\Controllers\hrm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeSessionController extends Controller
{
    public function attendance_by_employee($id, Request $request)
    {
        // Implementation for employee attendance
        return response()->json(['message' => 'Employee attendance method']);
    }
}