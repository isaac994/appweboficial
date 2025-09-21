<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUsersTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:users-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check users table structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando estructura de tabla users...');
        $this->newLine();

        $columns = DB::select('DESCRIBE users');

        $this->table(
            ['Campo', 'Tipo', 'Null', 'Key', 'Default', 'Extra'],
            array_map(function($column) {
                return [
                    $column->Field,
                    $column->Type,
                    $column->Null,
                    $column->Key,
                    $column->Default ?? 'NULL',
                    $column->Extra
                ];
            }, $columns)
        );
    }
}
