<?php

namespace App\Console\Commands;

use App\DataObject\AF\BulkImportStatusData;
use App\Exceptions\Quizzes\Imports\IndexImportException;
use App\Models\BulkImportStatus;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class CleanStuckBulkImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:stuck_bulk_imports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean stuck bulk imports';

    private BulkImportStatus $bulkImportStatus;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BulkImportStatus $bulkImportStatus)
    {
        parent::__construct();
        $this->bulkImportStatus = $bulkImportStatus;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stuckBimCount = $this->stuckBimQuery()->count();
        if ($stuckBimCount === 0) {
            return $this->info('No bulk imports stuck');
        }

        $this->handleBimFailing();
        $this->info("Failed $stuckBimCount bulk imports");
    }

    public function handleBimFailing()
    {
        $error = new IndexImportException('Job timed out, please try again',
            99, 0, 'N/A'
        );
        $this->stuckBimQuery()->update([
            'errors' => $error->formatError(),
            'status' => BulkImportStatusData::FAILED,
        ]);
    }

    public function stuckBimQuery()
    {
        return $this->bulkImportStatus
            ->where('updated_at', '<', Carbon::now()->subHours(1))
            ->where('status', BulkImportStatusData::PROCESSING);
    }
}
