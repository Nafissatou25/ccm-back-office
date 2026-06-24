<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappRequest;
use App\Models\Ticket;
use App\Models\Client;
use App\Models\Unit;
use App\Models\Type;
use App\Models\Agency;
use App\Services\SlaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WhatsappRequestController extends Controller
{
    public function index()
    {
        $requests = WhatsappRequest::latest()->get();
        return view('admin.whatsapp.index', compact('requests'));
    }

    public function show(WhatsappRequest $whatsappRequest)
    {
        $units = Unit::orderBy('name')->get();
        $agencies = Agency::orderBy('name')->get();
        return view('admin.whatsapp.show', compact('whatsappRequest', 'units', 'agencies'));
    }

    public function convert(Request $request, WhatsappRequest $whatsappRequest)
    {
        if ($whatsappRequest->status === 'CONVERTED') {
            return back()->withErrors(['msg' => 'Cette demande a déjà été convertie.']);
        }

        $data = $request->validate([
            'unit_id'     => 'required|exists:units,id',
            'type_id'     => 'required|exists:types,id',
            'agency_id'   => 'required|exists:agencies,id',
            'assigned_to' => 'nullable|exists:users,id',
            'is_urgent'   => 'nullable|boolean',
        ]);

        // Créer ou retrouver le client
        $client = Client::firstOrCreate(
            ['phone' => $whatsappRequest->contact_phone],
            [
                'name'            => $whatsappRequest->client_name,
                'firstname'       => $whatsappRequest->client_firstname,
                'phone'           => $whatsappRequest->contact_phone,
                'contract_number' => $whatsappRequest->contract_number,
                'delivery_point'  => $whatsappRequest->location_hint,
            ]
        );

        // Générer la fiche PDF
        $pdfPath = $this->generateFiche($whatsappRequest);

        // Créer le ticket
        $ticket = Ticket::create([
            'unit_id'         => $data['unit_id'],
            'type_id'         => $data['type_id'],
            'agency_id'       => $data['agency_id'],
            'assigned_to'     => $data['assigned_to'] ?? null,
            'client_id'       => $client->id,
            'description'     => $whatsappRequest->description,
            'is_urgent'       => $request->boolean('is_urgent'),
            'status'          => 'OPEN',
            'origin'          => 'WHATSAPP',
            'attachment_path' => $pdfPath,
            'created_by'      => auth()->id(),
        ]);

        // Appliquer SLA
        SlaService::applySla($ticket);
        $ticket->save();

        // Marquer la demande comme convertie
        $whatsappRequest->update([
            'status'       => 'CONVERTED',
            'ticket_id'    => $ticket->id,
            'converted_by' => auth()->id(),
            'converted_at' => now(),
        ]);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', "Ticket #{$ticket->id} créé depuis la demande WhatsApp.");
    }

    private function generateFiche(WhatsappRequest $wr): string
    {
        $pdf = Pdf::loadView('admin.whatsapp.fiche_pdf', [
            'wr'   => $wr,
            'ref'  => '#WA-' . str_pad($wr->id, 4, '0', STR_PAD_LEFT),
            'date' => now()->format('d/m/Y à H:i'),
        ]);

        $filename = 'fiches/wa_' . $wr->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }
}