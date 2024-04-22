<?php

namespace App\Exports\Excel\GDPR;

use App\Models\TicketMessage;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketChatHistoryExport implements FromView, WithColumnWidths, WithStyles
{
    use Exportable;

    public function __construct(int $ticketId, string $ticketSubject, string $ticketSubmittedAt)
    {
        $this->ticketId = $ticketId;
        $this->ticketSubject = $ticketSubject;
        $this->ticketSubmittedAt = $ticketSubmittedAt;
    }

    public function view(): View
    {
        return view('exports.ticketChatHistory', [
            'ticketId' => $this->ticketId,
            'ticketSubject' => $this->ticketSubject,
            'createdAt' => $this->ticketSubmittedAt,
            'ticketMessage' => TicketMessage::with('user')->where('ticket_id', $this->ticketId)->get()
        ]);
    }

    public function columnWidths(): array
    {
        return ['A' => 100];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Styling an entire column.
            'A'  => [
                'font' => ['size' => 10]
            ],

            // Styling row
            'A1'  => ['font' => ['bold' => true]],
        ];
    }
}
