<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
        h1 { font-size: 16px; text-align: center; color: #0B1F33; margin-bottom: 5px; }
        .sous-titre { text-align: center; color: #6b7280; font-size: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background: #0B1F33; color: #fff; }
        th { padding: 7px 10px; text-align: left; font-size: 10px; }
        td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) { background: #f9fafb; }
        .badge { padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .present  { background: #d1fae5; color: #065f46; }
        .absent   { background: #fee2e2; color: #991b1b; }
        .retard   { background: #fef3c7; color: #92400e; }
        .justifie { background: #dbeafe; color: #1e40af; }
        .total { margin-top: 15px; font-size: 10px; color: #6b7280; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <h1>UFR SATIC — Rapport des Absences</h1>
    <div class="sous-titre">Généré le {{ now()->format('d/m/Y à H:i') }}</div>

    @if($absences->isEmpty())
        <p style="text-align:center;color:#6b7280;">Aucune absence enregistrée.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Étudiant</th>
                <th>Cours</th>
                <th>Matière</th>
                <th>Classe</th>
                <th>Date</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
        @foreach($absences as $i => $absence)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>
                {{ $absence->etudiant->user->prenom ?? '—' }}
                {{ $absence->etudiant->user->nom ?? '' }}
            </td>
            <td>{{ $absence->cours->matiere->nomMatiere ?? '—' }}</td>
            <td>{{ $absence->cours->matiere->codeUE ?? '—' }}</td>
            <td>{{ $absence->cours->classe->nom ?? '—' }}</td>
            <td>{{ \Carbon\Carbon::parse($absence->date)->format('d/m/Y') }}</td>
            <td>
                <span class="badge {{ $absence->statut }}">
                    {{ ucfirst($absence->statut) }}
                </span>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <div class="total">
        Total : {{ $absences->count() }} enregistrement(s) |
        Absents : {{ $absences->where('statut', 'absent')->count() }} |
        Présents : {{ $absences->where('statut', 'present')->count() }} |
        Retards : {{ $absences->where('statut', 'retard')->count() }} |
        Justifiés : {{ $absences->where('statut', 'justifie')->count() }}
    </div>
    @endif

    <div class="footer">
        Système de Gestion des Absences — UFR SATIC, Université Alioune Diop de Bambey
    </div>
</body>
</html>