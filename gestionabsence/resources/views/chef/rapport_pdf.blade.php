<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Global des Absences</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #1e3a8a;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border: 1px solid #d1d5db;
        }
        td {
            padding: 10px;
            border: 1px solid #d1d5db;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-center { text-align: center; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-success { color: #16a34a; font-weight: bold; }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">UFR SATIC — Université Alioune Diop</h1>
        <div class="subtitle">Rapport Global des Absences Pédagogiques (Généré le {{ date('d/m/Y') }})</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Classe / Filière</th>
                <th class="text-center">Total Pointages</th>
                <th class="text-center">Présences</th>
                <th class="text-center">Absences</th>
                <th class="text-center">Taux d'absentéisme</th>
            </tr>
        </thead>
        <tbody>
            @foreach($classes as $classe)
                <tr>
                    <td style="font-weight: bold;">
                        {{ $classe->filiere->nom ?? 'Filière' }} - {{ $classe->niveau->nom ?? '' }}
                    </td>
                    <td class="text-center">{{ $classe->total }}</td>
                    <td class="text-center text-success">{{ $classe->presents }}</td>
                    <td class="text-center text-danger">{{ $classe->absents }}</td>
                    <td class="text-center">
                        <span class="badge">{{ $classe->taux }}%</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>