<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des Absences</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header { text-align: center; border-bottom: 3px solid #1a3a5c; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { color: #1a3a5c; margin: 0; font-size: 16px; }
        .header p { margin: 4px 0; color: #555; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #c2c9d0; color: #5469f0; padding: 7px 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #1259e7; }
        tr:nth-child(even) { background: #f8fafc; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .present  { background: #d1fae5; color: #065f46; }
        .absent   { background: #fee2e2; color: #991b1b; }
        .retard   { background: #fef3c7; color: #d06e23; }
        .justifie { background: #dbeafe; color: #1644da; }
        .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #888; }
        .stats { display: flex; gap: 20px; margin-bottom: 15px; }
        .stat-box { background: #f0f4f8; padding: 8px 14px; border-radius: 6px; border-left: 3px solid #2e86de; }
        .stat-box strong { font-size: 15px; color: #0e66c4; display: block; }
        .stat-box span { font-size: 9px; color: #4b30d6; }
    </style>
</head>
<body>
<div class="header">
    <h2>UFR SATIC — Rapport des Absences</h2>
    <p>Système de Gestion des Absences par Reconnaissance Faciale</p>
    <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
</div>

@php
    $totalAbsences = $absences->where('statut','absent')->count();
    $totalPresences = $absences->where('statut','present')->count();
    $totalJustifies = $absences->where('statut','justifie')->count();
@endphp

<table style="width:auto;margin-bottom:15px;">
    <tr>
        <td style="padding:6px 12px;background:#fee2e2;border-radius:6px;border:none;">
            <strong style="font-size:14px;color:#991b1b;">{{ $totalAbsences }}</strong><br>
            <span style="font-size:9px;color:#555;">Absences</span>
        </td>
        <td width="10"></td>
        <td style="padding:6px 12px;background:#d1fae5;border-radius:6px;border:none;">
            <strong style="font-size:14px;color:#065f46;">{{ $totalPresences }}</strong><br>
            <span style="font-size:9px;">Présences</span>
        </td>
        <td width="10"></td>
        <td style="padding:6px 12px;background:#dbeafe;border-radius:6px;border:none;">
            <strong style="font-size:14px;color:#1e40af;">{{ $totalJustifies }}</strong><br>
            <span style="font-size:9px;">Justifiés</span>
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Étudiant</th>
            <th>Code</th>
            <th>Matière</th>
            <th>Classe</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Facial</th>
            <th>Justification</th>
        </tr>
    </thead>
    <tbody>
    @foreach($absences as $i => $abs)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $abs->etudiant->user->full_name }}</td>
        <td>{{ $abs->etudiant->codePar }}</td>
        <td>{{ $abs->cours->matiere->nomMatiere }}</td>
        <td>{{ $abs->cours->classe->nom }}</td>
        <td>{{ $abs->date->format('d/m/Y') }}</td>
        <td><span class="badge {{ $abs->statut }}">{{ ucfirst($abs->statut) }}</span></td>
        <td style="text-align:center;">{{ $abs->pointage_facial ? '✓' : '—' }}</td>
        <td>{{ $abs->justification ?? '—' }}</td>
    </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    UFR SATIC — {{ now()->format('Y') }} | Total enregistrements : {{ $absences->count() }}
</div>
</body>
</html>