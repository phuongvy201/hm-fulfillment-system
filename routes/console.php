<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Tự động phân hạng pricing tiers hàng tháng (chạy vào ngày 1 mỗi tháng lúc 00:00)
Schedule::command('pricing-tiers:auto-assign --reset')
    ->monthly()
    ->at('00:00')
    ->description('Reset và tự động phân hạng pricing tiers hàng tháng');
