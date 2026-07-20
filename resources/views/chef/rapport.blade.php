<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Global des Absences</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #374151;
            font-size: 13px;
            line-height: 1.4;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-top: 5px;
        }
        .filter-bar {
            max-width: 1000px;
            margin: 0 auto 16px auto;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filter-bar select {
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 13px;
            min-width: 260px;
        }
        .filter-bar label {
            font-weight: 600;
            font-size: 13px;
            color: #1e3a8a;
        }
        table {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            border-collapse: collapse;
            background: #fff;
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
        tr:nth-child(even) { background-color: #f9fafb; }
        .text-center { text-align: center; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-success { color: #16a34a; font-weight: bold; }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-success { background-color: #dcfce7; color: #14532d; }

        .btn-voir {
            display: inline-block;
            padding: 6px 12px;
            background-color: #1e3a8a;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .btn-voir:hover {
            background-color: #1e40af;
        }

        .section-title {
            max-width: 1000px;
            margin: 24px auto 8px auto;
            font-size: 15px;
            font-weight: bold;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .table-absents th { background-color: #991b1b; }

        .row-hidden { display: none; }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">UFR SATIC — Université Alioune Diop</h1>
        <div class="subtitle">Rapport Global des Absences Pédagogiques (Généré le {{ date('d/m/Y') }})</div>
    </div>

    {{-- Étudiants absents aujourd'hui --}}
    @if($absentsAujourdhui->isNotEmpty())
    <div class="section-title">
        ⚠️ Étudiants absents aujourd'hui — toutes classes ({{ $absentsAujourdhui->count() }})
    </div>
    <table class="table-absents" style="margin-bottom: 24px;">
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Matière</th>
                <th>Classe</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($absentsAujourdhui as $a)
            <tr>
                <td>{{ $a->etudiant->user->prenom ?? '' }} {{ $a->etudiant->user->nom ?? '' }}</td>
                <td>{{ $a->cours->matiere->nomMatiere ?? '—' }}</td>
                <td>{{ $a->cours->classe->nom ?? '—' }}</td>
                <td style="color:#6b7280;">{{ \Carbon\Carbon::parse($a->date)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="filter-bar">
        <label for="filtreClasse">Filtrer par classe :</label>
        <select id="filtreClasse" onchange="filtrerClasse()">
            <option value="tout">Toutes les classes</option>
            @foreach($classes as $classe)
            <option value="classe-{{ $classe->idClasse }}">
                {{ $classe->nom }} ({{ $classe->filiere->nomFiliere ?? '—' }} - {{ $classe->niveau->nom ?? '—' }})
            </option>
            @endforeach
        </select>
    </div>

    <table>
        <thead>
            <tr>
                <th>Classe</th>
                <th>Filière / Niveau</th>
                <th class="text-center">Total Pointages</th>
                <th class="text-center">Présences</th>
                <th class="text-center">Absences</th>
                <th class="text-center">Taux d'absentéisme</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @forelse($classes as $classe)
                <tr class="ligne-classe" data-classe="classe-{{ $classe->idClasse }}">
                    <td style="font-weight: bold;">{{ $classe->nom }}</td>
                    <td>{{ $classe->filiere->nomFiliere ?? '—' }} - {{ $classe->niveau->nom ?? '—' }}</td>
                    <td class="text-center">{{ $classe->total }}</td>
                    <td class="text-center text-success">{{ $classe->presents }}</td>
                    <td class="text-center text-danger">{{ $classe->absents }}</td>
                    <td class="text-center">
                        <span class="badge {{ $classe->taux > 20 ? 'badge-danger' : 'badge-success' }}">
                            {{ $classe->taux }}%
                        </span>
                    </td>
                    <td class="text-center">
                        @if($classe->filiere)
                        <a href="{{ route('chef.etudiants-filiere.etudiants', [$classe->filiere, $classe]) }}" class="btn-voir">
                            Voir les étudiants
                        </a>
                        @else
                        <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #6b7280; padding: 20px;">
                        Aucune donnée d'absence disponible pour le moment.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        function filtrerClasse() {
            const valeur = document.getElementById('filtreClasse').value;
            const lignes = document.querySelectorAll('.ligne-classe');

            lignes.forEach(function (ligne) {
                if (valeur === 'tout' || ligne.dataset.classe === valeur) {
                    ligne.classList.remove('row-hidden');
                } else {
                    ligne.classList.add('row-hidden');
                }
            });
        }
    </script>

</body>
</html>