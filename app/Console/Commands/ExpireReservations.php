<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:expire-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libera y expira automáticamente las reservas de stock vencidas';

    /**
     * Execute the console command.
     */
    public function handle(InventoryService $inventoryService): int
    {
        $this->info('Buscando reservas vencidas para expirar...');

        $count = $inventoryService->expireOverdueReservations();

        if ($count > 0) {
            $this->info("Se han expirado {$count} reservas y se liberó el stock correspondiente.");
        } else {
            $this->info('No hay reservas vencidas pendientes de expiración.');
        }

        return Command::SUCCESS;
    }
}
