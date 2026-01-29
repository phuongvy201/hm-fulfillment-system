<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateOnHoldOrdersToPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-on-hold-to-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update orders from on_hold status to pending status after 1 hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneHourAgo = Carbon::now()->subHour();
        
        // Find all orders with status 'on_hold' that were created more than 1 hour ago
        $orders = Order::where('status', 'on_hold')
            ->where('created_at', '<=', $oneHourAgo)
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $order->update(['status' => 'pending']);
            $count++;
            
            Log::info('Order status updated from on_hold to pending', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at,
                'updated_at' => now(),
            ]);
        }

        if ($count > 0) {
            $this->info("Updated {$count} order(s) from 'on_hold' to 'pending' status.");
        } else {
            $this->info('No orders found to update.');
        }

        return Command::SUCCESS;
    }
}
