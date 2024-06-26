<?php

namespace App\Console\Commands;

use App\Repositories\AF\AfAdvertRepository;
use Illuminate\Console\Command;

class DeactivateExpiredAdverts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deactivate:adverts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate expired adverts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    /**
     * @var AfAdvertRepository
     */
    private $afAdvertRepository;

    /**
     * AfAdvertRepository constructor.
     */
    public function __construct(AfAdvertRepository $afAdvertRepository)
    {
        parent::__construct();
        $this->afAdvertRepository = $afAdvertRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->afAdvertRepository->deactivateExpiredAdverts();

        $this->info('Deactivating expired adverts');
    }
}
