<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDueReviewsJob;
use Illuminate\Console\Command;

class ScheduleDueReviewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:schedule-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enfileira o processamento de revisões vencidas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ProcessDueReviewsJob::dispatch();
        $this->info('Job de revisões vencidas enviado para a fila.');

        return self::SUCCESS;
    }
}
