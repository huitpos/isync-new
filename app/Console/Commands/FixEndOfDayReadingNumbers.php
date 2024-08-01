<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\EndOfDay;
use App\Models\CutOff;

class FixEndOfDayReadingNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fix-end-of-day-reading-numbers';

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
        //get all end of days where beg_reading_number and end_reading_number are null
        $endOfDays = EndOfDay::whereNull('beg_reading_number')
            ->whereNull('end_reading_number')
            ->get();

        foreach ($endOfDays as $endOfDay) {
            $endOfDayId = $endOfDay->end_of_day_id;
            $branchId = $endOfDay->branch_id;
            $posMachineId = $endOfDay->pos_machine_id;

            $cutOffs = CutOff::where('branch_id', $branchId)
                ->where('pos_machine_id', $posMachineId)
                ->where('end_of_day_id', $endOfDayId)
                ->get();

            // get the smallest and largest reading number from $cutOffs
            $smallestReadingNumber = $cutOffs->min('reading_number');
            $largestReadingNumber = $cutOffs->max('reading_number');

            $endOfDay->beg_reading_number = $smallestReadingNumber;
            $endOfDay->end_reading_number = $largestReadingNumber;
            $endOfDay->save();
        }
    }
}
