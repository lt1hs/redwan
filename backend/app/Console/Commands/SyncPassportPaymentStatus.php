<?php

namespace App\Console\Commands;

use App\Models\Passport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPassportPaymentStatus extends Command
{
    protected $signature = 'passports:sync-payment-status';
    protected $description = 'Sync old payment status values with new ones';

    public function handle()
    {
        $this->info('Syncing payment status values...');
        
        // Map old values to new values
        $paymentStatusMap = [
            'تم' => 'paid',
            'لم يتم' => 'pending',
            'يدفع لاحقا' => 'pending',
            'في انتظار الدفع' => 'pending',
            'ملغي' => 'cancelled',
            'قيد الانجاز' => 'pending',
            'جاهز للاستلام' => 'pending',
            'تم تسليمه' => 'paid',
        ];
        
        // Get count of passports
        $count = Passport::count();
        $this->info("Found {$count} passports to update");
        
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();
        
        try {
            // Start a transaction
            DB::beginTransaction();
            
            // Get all passports
            Passport::chunk(100, function ($passports) use ($paymentStatusMap, $progressBar) {
                foreach ($passports as $passport) {
                    // Skip if payment_status is already one of the new values
                    if (in_array($passport->payment_status, ['pending', 'paid', 'cancelled'])) {
                        $progressBar->advance();
                        continue;
                    }
                    
                    // Get the new value
                    $newStatus = $paymentStatusMap[$passport->payment_status] ?? 'pending';
                    
                    // Update the passport
                    $passport->payment_status = $newStatus;
                    $passport->save();
                    
                    $progressBar->advance();
                }
            });
            
            // Commit the transaction
            DB::commit();
            
            $progressBar->finish();
            $this->newLine();
            $this->info('Payment status values synced successfully!');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Roll back the transaction
            DB::rollBack();
            
            $this->error('Error syncing payment status values: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
} 