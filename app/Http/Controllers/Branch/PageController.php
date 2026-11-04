<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\BranchRepositoryInterface;

use Carbon\Carbon;

use App\Repositories\Interfaces\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    protected $companyRepository;

    public function __construct(CompanyRepositoryInterface $companyRepository) {
        $this->companyRepository = $companyRepository;
    }

    public function dashboard(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');
        $filters = $this->dashboardFilters($request);

        return view('branch.dashboard', [
            'company' => $company,
            'branch' => $branch,
            'branchId' => $branch->id,
            'selectedRangeParam' => $filters['selectedRangeParam'],
            'startDateParam' => $filters['startDateParam'],
            'endDateParam' => $filters['endDateParam'],
        ]);
    }

    public function dashboardData(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');
        $filters = $this->dashboardFilters($request);

        $branchId = $branch->id;
        $startDate = $filters['startDate'];
        $endDate = $filters['endDate'];

        $netAmount = $this->companyRepository->getTransactionNetSales($company->id, $startDate, $endDate, $branchId);
        $grossAmount = $this->companyRepository->getTransactionGrossSales($company->id, $startDate, $endDate, $branchId);
        $transactionCount = $this->companyRepository->getTransactionCount($company->id, $startDate, $endDate, $branchId);
        $costAmount = $this->companyRepository->getTransactionCostAmount($company->id, $startDate, $endDate, $branchId);

        $transactions = DB::table('transactional_db.transactions')
            ->select(
                DB::raw('DATE_FORMAT(transactions.completed_at, "%Y-%m") as `year_month`'),
                DB::raw('branches.name as branch'),
                DB::raw('SUM(gross_sales) as total_sales')
            )
            ->join('branches', function ($join) use ($company, $branchId) {
                $join->on('transactions.branch_id', '=', 'branches.id')
                     ->where('branches.company_id', '=', $company->id);

                if ($branchId) {
                    $join->where('branches.id', '=', $branchId);
                }
            })
            ->where('transactions.is_complete', true)
            ->where('transactions.is_void', false)
            ->whereBetween('transactions.completed_at', [$startDate, $endDate])
            ->groupBy('year_month', 'branch')
            ->orderBy('year_month')
            ->get();

        // Initialize arrays to track totals by department and item
        $departmentSales = [];
        $itemSales = [];
        $finalData = [];
        $branches = [];
        $salesData = [];

        foreach ($transactions as $transaction) {
            $yearMonth = $transaction->year_month;
            $branch = $transaction->branch;

            if (!isset($salesData[$yearMonth])) {
                $salesData[$yearMonth] = ['year_month' => $yearMonth];
            }

            if (!isset($salesData[$yearMonth])) {
                $salesData[$yearMonth] = [];
            }

            if (!isset($salesData[$yearMonth][$branch])) {
                $salesData[$yearMonth][$branch] = 0;
            }

            $salesData[$yearMonth][$branch] += $transaction->total_sales;

            if (!in_array($transaction->branch, $branches)) {
                $branches[] = $transaction->branch;
            }
        }

        $finalData = [];
        foreach ($salesData as $yearMonth => $sales) {
            $row = [$sales['year_month']];

            foreach ($branches as $branch) {
                $row[] = $sales[$branch] ?? 0;
            }

            $finalData[] = $row;
        }

        $orders = DB::table('transactional_db.orders')
            ->select(
                DB::raw('departments.id as department_id'),
                DB::raw('orders.product_id as product_id'),
                DB::raw('departments.name as department_name'),
                DB::raw('products.name as product_name'),
                DB::raw('SUM(orders.qty) as qty')
            )
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->join('departments', 'products.department_id', '=', 'departments.id')
            ->join('branches', function ($join) use ($company, $branchId) {
                $join->on('orders.branch_id', '=', 'branches.id')
                     ->where('branches.company_id', '=', $company->id);

                if ($branchId) {
                    $join->where('branches.id', '=', $branchId);
                }
            })
            ->where('orders.is_completed', true)
            ->where('orders.is_void', false)
            ->whereBetween('orders.completed_at', [$startDate, $endDate])
            ->groupBy('departments.id', 'products.id')
            ->get();

        $payments = DB::table('transactional_db.transactions')
            ->select(
                DB::raw('payments.payment_type_name as payment_type'),
                DB::raw('count(*) as qty')
            )
            ->join('branches', function ($join) use ($company, $branchId) {
                $join->on('transactions.branch_id', '=', 'branches.id')
                     ->where('branches.company_id', '=', $company->id);

                if ($branchId) {
                    $join->where('branches.id', '=', $branchId);
                }
            })
            ->join('transactional_db.payments', function ($join) use ($company, $branchId) {
                $join->on('transactions.transaction_id', '=', 'payments.transaction_id')
                    ->on('transactions.branch_id', '=', 'payments.branch_id')
                    ->on('transactions.pos_machine_id', '=', 'payments.pos_machine_id')
                    ->where('branches.company_id', '=', $company->id);

                if ($branchId) {
                    $join->where('branches.id', '=', $branchId);
                }
            })
            ->where('transactions.is_complete', true)
            ->where('transactions.is_void', false)
            ->whereBetween('transactions.completed_at', [$startDate, $endDate])
            ->groupBy('payments.payment_type_name')
            ->get();

        $paymentTypeSales = [];
        foreach ($payments as $payment) {
            $paymentTypeSales[] = [$payment->payment_type, $payment->qty];
        }

        foreach ($orders as $order) {
            if (!isset($departmentSales[$order->department_name])) {
                $departmentSales[$order->department_name] = 0;
            }

            $departmentSales[$order->department_name] += $order->qty;

            if (!isset($itemSales[$order->product_name])) {
                $itemSales[$order->product_name] = 0;
            }
            $itemSales[$order->product_name] += $order->qty;
        }

        arsort($departmentSales);

        $top20 = array_slice($departmentSales, 0, 20, true);

        $total = array_sum($departmentSales);
        $top20Total = array_sum($top20);
        $othersTotal = $total - $top20Total;

        if ($othersTotal > 0) {
            $top20['Others'] = $othersTotal;
        }

        // Prepare data for the view
        $departmentSales = [];
        foreach ($top20 as $item => $value) {
            $departmentSales[] = [$item, $value];
        }

        arsort($itemSales);

        $top20 = array_slice($itemSales, 0, 20, true);

        $total = array_sum($itemSales);
        $top20Total = array_sum($top20);
        $othersTotal = $total - $top20Total;

        // Prepare data for the view
        $itemSales = [];
        foreach ($top20 as $item => $value) {
            $itemSales[] = [$item, $value];
        }

        return response()->json([
            'selectedRange' => $filters['selectedRangeParam'],
            'range' => [
                'transactionCount' => $transactionCount,
                'grossAmount' => number_format($grossAmount, 2),
                'netAmount' => number_format($netAmount, 2),
                'profit' => number_format($grossAmount - $costAmount, 2),
            ],
            'branches' => $branches,
            'salesData' => $this->numericChartRows($finalData),
            'departmentSales' => $this->numericChartRows($departmentSales),
            'itemSales' => $this->numericChartRows($itemSales),
            'paymentTypeSales' => $this->numericChartRows($paymentTypeSales),
        ]);
    }

    private function dashboardFilters(Request $request): array
    {
        $selectedRangeParam = $request->input('selectedRange', 'Year to Date');
        $startDateParam = $request->input('startDate');
        $endDateParam = $request->input('endDate');
        $dateParam = $request->input('date_range');

        $startDate = Carbon::now()->startOfYear()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');

        if ($dateParam && str_contains($dateParam, ' - ')) {
            [$rawStart, $rawEnd] = explode(' - ', $dateParam, 2);
            $startDate = Carbon::parse(trim($rawStart))->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse(trim($rawEnd))->format('Y-m-d 23:59:59');
        } elseif ($startDateParam && $endDateParam) {
            $startDate = Carbon::parse($startDateParam)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDateParam)->format('Y-m-d 23:59:59');
        }

        return [
            'selectedRangeParam' => $selectedRangeParam,
            'startDateParam' => $startDateParam,
            'endDateParam' => $endDateParam,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    private function numericChartRows(array $rows): array
    {
        return array_map(function ($row) {
            foreach ($row as $index => $value) {
                if ($index === 0) {
                    continue;
                }

                $row[$index] = (float) $value;
            }

            return $row;
        }, $rows);
    }
}
