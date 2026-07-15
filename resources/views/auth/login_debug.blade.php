<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Debug Login - SATIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
<div class="row justify-content-center">
<div class="col-md-7">

    <h4 class="mb-4 text-primary">🔍 Page de diagnostic</h4>

    {{-- Erreurs de login --}}
    @if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $e)
            <p class="mb-0">❌ {{ $e }}</p>
        @endforeach
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif

    {{-- Formulaire test --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Test de connexion</div>
        <div class="card-body">
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', 'admin@satic.edu') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" value="password">
                </div>
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
        </div>
    </div>

    {{-- Infos système --}}
    <div class="card">
        <div class="card-header fw-bold">État du système</div>
        <div class="card-body">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td>APP_KEY</td>
                        <td>
                            @if(config('app.key'))
                                <span class="text-success fw-bold">✅ Définie</span>
                            @else
                                <span class="text-danger fw-bold">❌ MANQUANTE → php artisan key:generate</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Base de données</td>
                        <td>
                            @try
                                @php \DB::connection()->getPdo(); @endphp
                                <span class="text-success fw-bold">✅ Connectée</span>
                            @catch(\Exception $e)
                                <span class="text-danger fw-bold">❌ {{ $e->getMessage() }}</span>
                            @endtry
                        </td>
                    </tr>
                    <tr>
                        <td>Utilisateurs en base</td>
                        <td>
                            @try
                                @php $count = \App\Models\User::count(); @endphp
                                @if($count > 0)
                                    <span class="text-success fw-bold">✅ {{ $count }} utilisateurs</span>
                                @else
                                    <span class="text-danger fw-bold">❌ Table vide → php artisan db:seed</span>
                                @endif
                            @catch(\Exception $e)
                                <span class="text-danger fw-bold">❌ Table inexistante → php artisan migrate</span>
                            @endtry
                        </td>
                    </tr>
                    <tr>
                        <td>Session driver</td>
                        <td><code>{{ config('session.driver') }}</code></td>
                    </tr>
                    <tr>
                        <td>APP_ENV</td>
                        <td><code>{{ config('app.env') }}</code></td>
                    </tr>
                    <tr>
                        <td>APP_URL</td>
                        <td><code>{{ config('app.url') }}</code></td>
                    </tr>
                </tbody>
            </table>

            {{-- Liste des utilisateurs si disponible --}}
            @try
                @php $users = \App\Models\User::select('nom','prenom','email','role')->take(10)->get(); @endphp
                @if($users->count() > 0)
                <hr>
                <p class="fw-bold mb-2">Comptes disponibles :</p>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Nom</th><th>Email</th><th>Rôle</th></tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td>{{ $u->prenom }} {{ $u->nom }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge bg-secondary">{{ $u->role }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <small class="text-muted">Mot de passe par défaut : <code>password</code></small>
                @endif
            @catch(\Exception $e)
            @endtry
        </div>
    </div>

</div>
</div>
</div>
</body>
</html>