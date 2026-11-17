<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Sale;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * Get student dashboard data
     */
    public function getDashboardData(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'viewAny', Client::class);

        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // Basic statistics
        $stats = $this->studentService->getStudentStatistics();

        // Today's sales to students
        $todayStudentSales = Sale::whereHas('client', function($query) {
                $query->whereNotNull('student_id');
            })
            ->whereDate('date', $today)
            ->count();

        $todayStudentSalesAmount = Sale::whereHas('client', function($query) {
                $query->whereNotNull('student_id');
            })
            ->whereDate('date', $today)
            ->sum('GrandTotal');

        // This month's sales to students
        $monthlyStudentSales = Sale::whereHas('client', function($query) {
                $query->whereNotNull('student_id');
            })
            ->where('date', '>=', $thisMonth)
            ->count();

        $monthlyStudentSalesAmount = Sale::whereHas('client', function($query) {
                $query->whereNotNull('student_id');
            })
            ->where('date', '>=', $thisMonth)
            ->sum('GrandTotal');

        // Top spending students this month
        $topStudents = Client::whereNotNull('student_id')
            ->withSum(['sales as total_spent' => function($query) use ($thisMonth) {
                $query->where('date', '>=', $thisMonth);
            }], 'GrandTotal')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'student_id', 'class_grade', 'stream']);

        // Recent transactions
        $recentTransactions = Sale::whereHas('client', function($query) {
                $query->whereNotNull('student_id');
            })
            ->with(['client:id,name,student_id,class_grade,stream'])
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get(['id', 'client_id', 'date', 'GrandTotal', 'payment_statut']);

        // Students with low balance
        $lowBalanceStudents = $this->studentService->getStudentsWithLowBalance();

        // Students with negative balance
        $negativeBalanceStudents = $this->studentService->getStudentsWithNegativeBalance();

        // Sales by class this month
        $salesByClass = Sale::whereHas('client', function($query) {
                $query->whereNotNull('student_id');
            })
            ->where('date', '>=', $thisMonth)
            ->join('clients', 'sales.client_id', '=', 'clients.id')
            ->groupBy('clients.class_grade')
            ->selectRaw('clients.class_grade, COUNT(*) as count, SUM(sales.GrandTotal) as total')
            ->get();

        return response()->json([
            'statistics' => $stats,
            'today' => [
                'sales_count' => $todayStudentSales,
                'sales_amount' => $todayStudentSalesAmount,
            ],
            'monthly' => [
                'sales_count' => $monthlyStudentSales,
                'sales_amount' => $monthlyStudentSalesAmount,
            ],
            'top_students' => $topStudents,
            'recent_transactions' => $recentTransactions,
            'low_balance_students' => $lowBalanceStudents->take(5),
            'negative_balance_students' => $negativeBalanceStudents->take(5),
            'sales_by_class' => $salesByClass,
            'alerts' => [
                'low_balance_count' => $lowBalanceStudents->count(),
                'negative_balance_count' => $negativeBalanceStudents->count(),
            ]
        ]);
    }

    /**
     * Get student sales chart data
     */
    public function getStudentSalesChart(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'viewAny', Client::class);

        $period = $request->get('period', 'month'); // week, month, year
        $startDate = Carbon::now();

        switch ($period) {
            case 'week':
                $startDate = $startDate->startOfWeek();
                $groupBy = 'DATE(date)';
                break;
            case 'year':
                $startDate = $startDate->startOfYear();
                $groupBy = 'MONTH(date)';
                break;
            default: // month
                $startDate = $startDate->startOfMonth();
                $groupBy = 'DATE(date)';
                break;
        }

        $salesData = Sale::whereHas('client', function($query) {
                $query->whereNotNull('student_id');
            })
            ->where('date', '>=', $startDate)
            ->selectRaw("$groupBy as period, COUNT(*) as count, SUM(GrandTotal) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json($salesData);
    }
}