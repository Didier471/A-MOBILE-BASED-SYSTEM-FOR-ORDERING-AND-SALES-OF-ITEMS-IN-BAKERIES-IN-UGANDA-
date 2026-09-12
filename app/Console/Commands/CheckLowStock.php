<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature = 'inventory:check-low-stock';

    protected $description = 'Notify Admin and Procurement Staff about products at or below reorder level';

    public function handle(): int
    {
        $lowStock = Product::whereColumn('stock_quantity', '<=', 'reorder_level')
            ->orderBy('stock_quantity')
            ->get();

        if ($lowStock->isEmpty()) {
            $this->info('No low-stock products right now.');
            return self::SUCCESS;
        }

        $recipients = User::role(['Admin', 'Procurement Staff'])->get();

        if ($recipients->isEmpty()) {
            $this->warn('Found low-stock products but no Admin/Procurement Staff users to notify.');
            return self::SUCCESS;
        }

        foreach ($recipients as $recipient) {
            $recipient->notify(new LowStockAlert($lowStock));
        }

        $this->info("Notified {$recipients->count()} user(s) about {$lowStock->count()} low-stock product(s).");

        return self::SUCCESS;
    }
}