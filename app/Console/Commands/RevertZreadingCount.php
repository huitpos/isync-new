<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\EndOfDay;
use App\Models\CutOff;
use App\Models\Order;
use App\Models\ProductCountLog;
use App\Models\BranchProduct;
use Illuminate\Support\Facades\DB;
class RevertZreadingCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:revert-zreading-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $branchId = 10;
        $endOfDayControl = EndOfDay::where('id', 2800)->orderBy('end_of_day_id', 'desc')->first();

        $endOfDays = EndOfDay::where('branch_id', $branchId)
            ->where('id', '>', $endOfDayControl->id)
            ->get();

        $processedProducts = [];
        foreach ($endOfDays as $endOfDay) {
            $this->info("Reverting zreading count for end of day: " . $endOfDay->treg . " - " . $endOfDay->end_of_day_id);

            if ($endOfDay->treg < $endOfDayControl->treg) {
                $logs = ProductCountLog::where('object_id', $endOfDay->end_of_day_id)
                    ->where('branch_id', $branchId)
                    ->where('object_type', 'end_of_days')
                    ->get();

                foreach ($logs as $log) {
                    if (in_array($log->product_id, $processedProducts)) {
                        continue;
                    }

                    $processedProducts[] = $log->product_id;

                    $latestCorrectLog = ProductCountLog::where('branch_id', $branchId)
                        ->where('product_id', $log->product_id)
                        ->where('created_at', '<', '2026-10-28 00:00:00')
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!$latestCorrectLog) {
                        $qty = 0;
                    } else {
                        $qty = $latestCorrectLog->new_quantity;
                    }

                    $updateQuery = "UPDATE branch_product SET stock = ? WHERE branch_id = ? AND product_id = ?";
                    $updateParams = [$qty, $branchId, $log->product_id];

                    DB::update($updateQuery, $updateParams);

                    $this->info("Reverted zreading count for product ID: " . $log->product_id . " to " . $qty);
                }
            }
        }
    }
}
