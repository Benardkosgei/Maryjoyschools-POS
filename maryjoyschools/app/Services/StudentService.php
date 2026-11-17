<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class StudentService
{
    /**
     * Generate next student ID
     */
    public function generateStudentId(): string
    {
        $prefix = config('student.student_id_prefix');
        $lastStudent = Client::whereNotNull('student_id')
            ->where('student_id', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastStudent ? 
            (int)substr($lastStudent->student_id, strlen($prefix)) + 1 : 1;
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate next admission number
     */
    public function generateAdmissionNumber(): string
    {
        $prefix = config('student.admission_number_prefix') . date('Y');
        $lastAdmission = Client::whereNotNull('admission_number')
            ->where('admission_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastAdmission ? 
            (int)substr($lastAdmission->admission_number, strlen($prefix)) + 1 : 1;
        
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Check if student can make a purchase
     */
    public function canStudentPurchase(Client $student, float $amount): array
    {
        $availableCredit = $student->credit_limit + $student->account_balance;
        $canPurchase = $amount <= $availableCredit;
        
        return [
            'can_purchase' => $canPurchase,
            'available_credit' => $availableCredit,
            'requested_amount' => $amount,
            'shortfall' => $canPurchase ? 0 : $amount - $availableCredit
        ];
    }

    /**
     * Update student account balance
     */
    public function updateAccountBalance(Client $student, float $amount, string $type, string $description = null): bool
    {
        try {
            DB::beginTransaction();
            
            $oldBalance = $student->account_balance;
            
            if ($type === 'credit') {
                $student->account_balance += $amount;
            } elseif ($type === 'debit') {
                // Check if negative balance is allowed
                if (!config('student.negative_balance_allowed') && 
                    ($student->account_balance - $amount) < 0) {
                    throw new \Exception('Insufficient balance. Negative balance not allowed.');
                }
                $student->account_balance -= $amount;
            }
            
            $student->save();
            
            // Log the transaction (you might want to create a separate transactions table)
            \Log::info("Student account balance updated", [
                'student_id' => $student->student_id,
                'old_balance' => $oldBalance,
                'new_balance' => $student->account_balance,
                'amount' => $amount,
                'type' => $type,
                'description' => $description
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error("Failed to update student account balance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get student statistics
     */
    public function getStudentStatistics(): array
    {
        return [
            'total_students' => Client::whereNotNull('student_id')->count(),
            'active_students' => Client::whereNotNull('student_id')->where('status', 'Active')->count(),
            'inactive_students' => Client::whereNotNull('student_id')->where('status', 'Inactive')->count(),
            'graduated_students' => Client::whereNotNull('student_id')->where('status', 'Graduated')->count(),
            'transferred_students' => Client::whereNotNull('student_id')->where('status', 'Transferred')->count(),
            'students_by_class' => Client::whereNotNull('student_id')
                ->where('status', 'Active')
                ->groupBy('class_grade')
                ->selectRaw('class_grade, count(*) as count')
                ->pluck('count', 'class_grade'),
            'students_by_house' => Client::whereNotNull('student_id')
                ->where('status', 'Active')
                ->whereNotNull('house')
                ->groupBy('house')
                ->selectRaw('house, count(*) as count')
                ->pluck('count', 'house'),
            'total_account_balance' => Client::whereNotNull('student_id')->sum('account_balance'),
            'average_account_balance' => Client::whereNotNull('student_id')->avg('account_balance'),
            'students_with_negative_balance' => Client::whereNotNull('student_id')
                ->where('account_balance', '<', 0)->count(),
            'students_with_low_balance' => Client::whereNotNull('student_id')
                ->where('account_balance', '<', config('student.low_balance_threshold'))
                ->where('account_balance', '>=', 0)->count(),
        ];
    }

    /**
     * Bulk update student status
     */
    public function bulkUpdateStatus(array $studentIds, string $status): int
    {
        return Client::whereIn('id', $studentIds)
            ->whereNotNull('student_id')
            ->update(['status' => $status]);
    }

    /**
     * Get students with low balance
     */
    public function getStudentsWithLowBalance(): \Illuminate\Database\Eloquent\Collection
    {
        return Client::whereNotNull('student_id')
            ->where('status', 'Active')
            ->where('account_balance', '<', config('student.low_balance_threshold'))
            ->where('account_balance', '>=', 0)
            ->orderBy('account_balance')
            ->get();
    }

    /**
     * Get students with negative balance
     */
    public function getStudentsWithNegativeBalance(): \Illuminate\Database\Eloquent\Collection
    {
        return Client::whereNotNull('student_id')
            ->where('status', 'Active')
            ->where('account_balance', '<', 0)
            ->orderBy('account_balance')
            ->get();
    }
}