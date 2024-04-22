<?php

namespace App\Exports\Excel\GDPR;

use App\Models\Ticket;
use App\Exports\Excel\GDPR\TicketChatHistoryExport;
use App\Repositories\TicketRepository;
use App\Traits\FileSystemsCloudTrait;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SupportTicketsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;
    use FileSystemsCloudTrait;

    public function __construct(int $userId, string $tmpExportGdprDirectory)
    {
        $this->userId = $userId;
        $this->tmpExportGdprDirectory = $tmpExportGdprDirectory;
    }

    public function query()
    {
        return Ticket::with('status')->where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'ticket_status',
            'subject',
            'created_at',
            'updated_at'

        ];
    }

    public function map($row): array
    {
        $storagePath = TicketRepository::getThumbnailS3StoragePath($row->id);
        $ticketAssets = $this->getFiles($storagePath);
        foreach ($ticketAssets as $asset) {
            $this->getFile($this->tmpExportGdprDirectory, $asset);
        }

        (new TicketChatHistoryExport(
            $row->id,
            $row->subject,
            $row->created_at,
        ))->store($this->tmpExportGdprDirectory . '/ticket_' . $row->id . '_chat_history.pdf');

        return [
            $row->id,
            $row->status->name,
            $row->subject,
            $row->created_at,
            $row->updated_at
        ];
    }
}
