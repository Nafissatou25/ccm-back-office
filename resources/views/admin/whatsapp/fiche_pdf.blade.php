<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #2D2961; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { color: #2D2961; font-size: 16px; margin: 4px 0; }
        .header p { margin: 2px 0; font-size: 11px; color: #666; }
        .ref { background: #2D2961; color: white; padding: 8px 16px; border-radius: 4px; display: inline-block; font-size: 14px; font-weight: bold; }
        .section { margin: 16px 0; }
        .section h2 { font-size: 13px; color: #2D2961; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 8px; border-bottom: 1px solid #f0f0f0; }
        td:first-child { font-weight: bold; width: 40%; color: #555; }
        .description-box { background: #f8f8f8; border-left: 3px solid #2D2961; padding: 10px; margin: 8px 0; border-radius: 2px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
<div class="header">
    <h1>SOCADEL — Délégation Régionale Extrême-Nord</h1>
    <p>Fiche de réclamation — Canal WhatsApp</p>
    <br>
    <span class="ref">{{ $ref }}</span>
    <p style="margin-top:8px;">Générée le {{ $date }}</p>
</div>

<div class="section">
    <h2>Informations du client</h2>
    <table>
        <tr><td>Nom complet</td><td>{{ $wr->full_name }}</td></tr>
        <tr><td>N° contrat</td><td>{{ $wr->contract_number ?? 'Non fourni' }}</td></tr>
        <tr><td>Téléphone de contact</td><td>{{ $wr->contact_phone }}</td></tr>
        <tr><td>Numéro WhatsApp</td><td>{{ $wr->wa_phone }}</td></tr>
        <tr><td>Point de repère</td><td>{{ $wr->location_hint }}</td></tr>
    </table>
</div>

<div class="section">
    <h2>Description du problème</h2>
    <div class="description-box">{{ $wr->description }}</div>
</div>

<div class="section">
    <h2>Métadonnées</h2>
    <table>
        <tr><td>Canal de réception</td><td><span style="background:#f6c23e;padding:3px 8px;border-radius:3px;">WhatsApp</span></td></tr>
        <tr><td>Date de soumission</td><td>{{ $wr->created_at->format('d/m/Y à H:i') }}</td></tr>
        <tr><td>Statut</td><td>{{ $wr->status }}</td></tr>
    </table>
</div>

<div class="footer">
    SOCADEL — Société Camerounaise d'Électricité · Délégation Régionale Extrême-Nord<br>
    Document généré automatiquement par le système CCM
</div>
</body>
</html>