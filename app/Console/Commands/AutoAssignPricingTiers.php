<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PricingTierService;

class AutoAssignPricingTiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pricing-tiers:auto-assign {--reset : Reset all tiers to default before assigning}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động phân hạng users dựa trên số đơn hàng';

    /**
     * Execute the console command.
     */
    public function handle(PricingTierService $service)
    {
        if ($this->option('reset')) {
            $this->info('Đang reset tất cả tiers về mặc định...');
            $service->resetAllTiers();
            $this->info('✓ Đã reset xong');
        }

        $this->info('Đang tự động phân hạng cho tất cả users...');
        $service->autoAssignAllUsers();
        $this->info('✓ Hoàn thành!');
    }
}
