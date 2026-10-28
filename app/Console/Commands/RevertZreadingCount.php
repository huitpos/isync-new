<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\EndOfDay;
use App\Models\CutOff;
use App\Models\Order;
use App\Models\ProductCountLog;
use App\Models\BranchProduct;

class RevertZreadingCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:revert-zreading-count {--branch-id= : Revert zreading count for a specific branch only}';

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
        $endOfDayControl = EndOfDay::where('id', 2800)->orderBy('end_of_day_id', 'desc')->first();

        $endOfDays = EndOfDay::where('branch_id', $this->option('branch-id'))
            ->where('id', '>', $endOfDayControl->id)
            ->get();

        foreach ($endOfDays as $endOfDay) {
            if ($endOfDay->treg > $endOfDayControl->treg) {
                $logs = ProductCountLog::where('object_id', $endOfDay->end_of_day_id)
                    ->where('branch_id', $this->option('branch-id'))
                    ->where('object_type', 'end_of_days')
                    ->get();

                foreach ($logs as $log) {
                    $branchProduct = BranchProduct::where('branch_id', $this->option('branch-id'))
                        ->where('product_id', $log->product_id)
                        ->first();

                    $branchProduct->stock = $branchProduct->stock - ($log->new_quantity - $log->old_quantity);

                    dd($branchProduct);

                    $branchProduct->save();
                }
            }
        }
    }
}
