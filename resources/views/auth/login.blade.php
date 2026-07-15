<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #0d6efd, #4da3ff);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card {
    width: 400px;
    padding: 30px;
    border-radius: 15px;
}
</style>
</head>

<body>

<div class="card">
<h3 class="text-center">Connexion</h3>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="POST" action="/login">
@csrf

<input type="email" name="email" class="form-control mb-3" placeholder="Email">
<input type="password" name="password" class="form-control mb-3" placeholder="Mot de passe">

<button class="btn btn-primary w-100">Se connecter</button>

</form>
</div>

</body>
</html>