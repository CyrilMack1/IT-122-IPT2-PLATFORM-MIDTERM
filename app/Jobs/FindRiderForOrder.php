<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\RiderSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FindRiderForOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes max

    public function __construct(public Order $order) {}

    public function handle(RiderSearchService $service): void
    {
        $service->findRider($this->order->fresh(['restaurant']));
    }
}