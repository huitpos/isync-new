<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\User;

class UpdateProductAbbreviation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //php artisan products:update-abbreviation
    protected $signature = 'products:update-abbreviation {--company-id= : Update products for a specific company only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product abbreviation based on product name without vowels, max 25 chars, first letter uppercase';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update product abbreviations...');

        $query = Product::query()
            ->whereNotNull('abbreviation');

        // If company ID is provided, filter by company
        if ($this->option('company-id')) {
            $query->where('company_id', $this->option('company-id'));
            $this->info('Filtering products for company ID: ' . $this->option('company-id'));
        }

        // Get total count for progress bar
        $totalProducts = $query->count();

        if ($totalProducts === 0) {
            $this->warn('No products found to update.');
            return;
        }

        $this->info("Found {$totalProducts} products to update.");

        $progressBar = $this->output->createProgressBar($totalProducts);
        $progressBar->start();

        $updatedCount = 0;
        $skippedCount = 0;
        $batchSize = 1000;

        // Process products in batches
        $query->chunk($batchSize, function ($products) use (&$updatedCount, &$skippedCount, $progressBar) {
            foreach ($products as $product) {
                $originalAbbreviation = $product->abbreviation;
                $newAbbreviation = $this->generateAbbreviation($product->name);

                if ($originalAbbreviation !== $newAbbreviation) {
                    $product->update(['abbreviation' => $newAbbreviation]);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();

        $this->newLine(2);
        $this->info("Update completed!");
        $this->info("Total products processed: {$totalProducts}");
        $this->info("Products updated: {$updatedCount}");
        $this->info("Products skipped (no change): {$skippedCount}");
    }

    /**
     * Generate abbreviation from product name
     * - Remove vowels (a, e, i, o, u)
     * - First letter of each word uppercase, rest lowercase
     * - Retain spaces between words
     * - Maximum 25 characters
     *
     * @param string $name
     * @return string
     */
    private function generateAbbreviation(string $name): string
    {
        // Remove or convert non-ASCII characters first
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        
        // Convert to lowercase
        $name = strtolower($name);
        
        // Remove any remaining non-printable or problematic characters
        $name = preg_replace('/[^\x20-\x7E]/', '', $name);
        
        // Split into words
        $words = preg_split('/\s+/', trim($name));
        
        $abbreviatedWords = [];
        
        foreach ($words as $word) {
            if (empty($word)) continue;
            
            // Remove all vowels (a, e, i, o, u) from the entire word
            $abbreviatedWord = '';
            
            for ($i = 0; $i < strlen($word); $i++) {
                $char = $word[$i];
                
                // Remove vowels from all positions
                if (!in_array($char, ['a', 'e', 'i', 'o', 'u'])) {
                    $abbreviatedWord .= $char;
                }
            }
            
            // Only add to result if there are remaining characters after vowel removal
            if (!empty($abbreviatedWord)) {
                // Capitalize the first letter of the abbreviated word
                $abbreviatedWords[] = ucfirst($abbreviatedWord);
            }
        }
        
        // Join all words with spaces
        $result = implode(' ', $abbreviatedWords);
        
        // Limit to 25 characters and trim any trailing spaces
        return trim(substr($result, 0, 25));
    }
}