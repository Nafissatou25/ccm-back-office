<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class TicketsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnWidths
{
    protected $tickets;
    protected $startDate;
    protected $endDate;
    protected $selectedUnitId;

    public function __construct($tickets, $startDate, $endDate, $selectedUnitId)
    {
        $this->tickets = $tickets;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->selectedUnitId = $selectedUnitId;
    }

    public function collection()
    {
        return $this->tickets;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date création',
            'Client',
            'Unité',
            'Type',
            'Agence',
            'Priorité',
            'Statut',
            'Urgent',
            'Date résolution',
            'Délai SLA (h)',
            'Description',
        ];
    }

    public function map($ticket): array
    {
        $clientName = $ticket->client ? $ticket->client->name . ' ' . $ticket->client->firstname : '—';
        $slaHours = $ticket->created_at && $ticket->resolution_due_at
            ? round($ticket->created_at->diffInHours($ticket->resolution_due_at), 1)
            : null;

        return [
            $ticket->id,
            $ticket->created_at->format('d/m/Y H:i'),
            $clientName,
            $ticket->unit?->name ?? '—',
            $ticket->type?->name ?? '—',
            $ticket->agency?->name ?? '—',
            $ticket->priority ?? '—',
            $ticket->status,
            $ticket->is_urgent ? 'Oui' : 'Non',
            $ticket->resolved_at ? $ticket->resolved_at->format('d/m/Y H:i') : '—',
            $slaHours,
            $ticket->description,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style de la première ligne (en-tête)
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 18,
            'C' => 25,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 12,
            'H' => 15,
            'I' => 10,
            'J' => 18,
            'K' => 14,
            'L' => 40,
        ];
    }
}