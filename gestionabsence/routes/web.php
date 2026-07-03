<?php

use App\Http\Controllers\EtudiantsParFiliereController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\DepartementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\Admin\MatiereController;
use App\Http\Controllers\{
    AuthController,
    AdminController,
    CoursController,
    AbsenceController,
    BiometrieController,
    ProfesseurController,
    EtudiantController,
    ChefServiceController,
};
 
/* ===== ACCUEIL / AUTH ===== */
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ===== DEBUG (supprimer avant soutenance) ===== */
Route::get('/debug-login', [AuthController::class, 'showDebug'])->name('debug.login');

Route::get('/voir-log', function () {
    $log = storage_path('logs/laravel.log');
    if (!file_exists($log)) {
        return 'Aucun log trouvé — aucune erreur encore.';
    }
    $contenu = file_get_contents($log);
    $extrait = substr($contenu, -3000);
    return '<pre style="background:#1a1a1a;color:#00ff00;padding:20px;font-size:12px;">'
        . htmlspecialchars($extrait) . '</pre>';
});

Route::get('/test-login', function () {
    $user = \App\Models\User::where('email', 'admin@satic.edu')->first();

    if (!$user) {
        return '<h2 style="color:red">❌ Utilisateur introuvable !<br>Lance : php artisan db:seed</h2>';
    }

    $motDePasseOk = \Illuminate\Support\Facades\Hash::check('password', $user->password);

    echo '<h3>Résultat du diagnostic :</h3>';
    echo '<p>Utilisateur : ' . $user->prenom . ' ' . $user->nom . '</p>';
    echo '<p>Rôle : ' . $user->role . '</p>';
    echo '<p>Mot de passe OK : ' . ($motDePasseOk ? '✅ OUI' : '❌ NON') . '</p>';
    echo '<p>Compte actif : ' . ($user->is_active ? '✅ OUI' : '❌ NON') . '</p>';

    if (\Illuminate\Support\Facades\Auth::attempt([
        'email'    => 'admin@satic.edu',
        'password' => 'password'
    ])) {
        echo '<p style="color:green;font-size:1.2em">✅ CONNEXION RÉUSSIE</p>';
        \Illuminate\Support\Facades\Auth::logout();
    } else {
        echo '<p style="color:red;font-size:1.2em">❌ CONNEXION ÉCHOUÉE</p>';
    }
});

/* ===== ADMIN ===== */
Route::middleware(['auth', 'role:administrateur'])
    ->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Étudiants par filière
    Route::get('/etudiants-filiere', [EtudiantsParFiliereController::class, 'index'])->name('etudiants-filiere.index');
    Route::get('/etudiants-filiere/{filiere}/classes', [EtudiantsParFiliereController::class, 'showClasses'])->name('etudiants-filiere.classes');
    Route::get('/etudiants-filiere/{filiere}/classes/{classe}/etudiants', [EtudiantsParFiliereController::class, 'showEtudiants'])->name('etudiants-filiere.etudiants');

    // Utilisateurs
    Route::get('/users', [AdminController::class, 'indexUsers'])->name('users.index');
    Route::get('/users/etudiants', [AdminController::class, 'indexEtudiants'])->name('users.etudiants');
    Route::get('/users/professeurs', [AdminController::class, 'indexProfesseurs'])->name('users.professeurs');
    Route::get('/users/administrateurs', [AdminController::class, 'indexAdministrateurs'])->name('users.administrateurs');
    Route::get('/users/chefs-service', [AdminController::class, 'indexChefsService'])->name('users.chefs-service');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

    // Classes
    Route::get('/classes', [ClasseController::class, 'index'])->name('classes.index');
    Route::get('/classes/create', [AdminController::class, 'createClasse'])->name('classes.create');
    Route::post('/classes', [AdminController::class, 'storeClasse'])->name('classes.store');
    Route::delete('/classes/{classe}', [AdminController::class, 'destroyClasse'])->name('classes.destroy');

    // Gestion exclusive des Matières (Correctement préfixées en admin.matieres.*)
    Route::get('/matieres', [MatiereController::class, 'index'])->name('matieres.index');
    Route::post('/matieres', [MatiereController::class, 'store'])->name('matieres.store');
    Route::delete('/matieres/{id}', [MatiereController::class, 'destroy'])->name('matieres.destroy');

    // INTÉGRATION SÉCURISÉE : Chargement direct de la vue dans l'espace Admin (Correction 403)
    Route::get('/cours', function () {
        return view('admin.cours.index');
    })->name('cours.index');
    
    // NOUVEAU : Route Statistiques dédiée à l'Admin
    Route::get('/statistiques', function () {
        return view('admin.statistiques.index'); // Ajuste le chemin de ta vue si nécessaire
    })->name('statistiques.index');

    // NOUVEAU : Route Reconnaissance Faciale dédiée à l'Admin
    Route::get('/reconnaissance-faciale', function () {
        return view('admin.biometrie.index'); // Ajuste le chemin de ta vue si nécessaire
    })->name('biometrie.index');

    // Salles
    Route::get('/salles', [AdminController::class, 'indexSalles'])->name('salles.index');
    Route::post('/salles', [AdminController::class, 'storeSalle'])->name('salles.store');

    // Départements
    Route::get('/departements', [DepartementController::class, 'index'])->name('departements.index');
    Route::post('/departements', [DepartementController::class, 'store'])->name('departements.store');
    Route::get('/departements/{departement}/filieres', [DepartementController::class, 'showFilieres'])->name('departements.filieres');
    Route::post('/departements/{departement}/filieres', [DepartementController::class, 'storeFiliere'])->name('departements.filieres.store');
    Route::get('/departements/{departement}/filieres/{filiere}/classes', [DepartementController::class, 'showClasses'])->name('departements.classes');
    Route::get('/departements/{departement}/filieres/{filiere}/classes/{classe}/matieres', [DepartementController::class, 'showMatieres'])->name('departements.matieres');
    Route::post('/departements/{departement}/filieres/{filiere}/classes/{classe}/ues', [DepartementController::class, 'storeUE'])->name('departements.ues.store');
    Route::post('/departements/{departement}/filieres/{filiere}/classes/{classe}/ues/{ue}/matieres', [DepartementController::class, 'storeMatiereUE'])->name('departements.ues.matieres.store');

    // API cascade (département -> filière -> classe)
    Route::prefix('api')->group(function () {
        Route::get('/departements/{departement}/filieres', [InscriptionController::class, 'filieresParDepartement']);
        Route::get('/filieres/{filiere}/classes', [InscriptionController::class, 'classesParFiliere']);
    });
});

/* ===== PROFESSEUR ===== */
Route::middleware(['auth', 'role:professeur'])
    ->prefix('professeur')->name('professeur.')->group(function () {
    Route::get('/dashboard', [ProfesseurController::class, 'dashboard'])->name('dashboard');
    Route::get('/etudiants', [ProfesseurController::class, 'mesEtudiants'])->name('etudiants');
    Route::get('/absences', [ProfesseurController::class, 'absencesClasse'])->name('absences');
    Route::post('/cours/{cours}/pointage', [ProfesseurController::class, 'declarerPointage'])->name('pointage');
});

/* ===== ETUDIANT ===== */
Route::middleware(['auth', 'role:etudiant'])
    ->prefix('etudiant')->name('etudiant.')->group(function () {
    Route::get('/dashboard', [EtudiantController::class, 'dashboard'])->name('dashboard');
    Route::get('/absences', [EtudiantController::class, 'mesAbsences'])->name('absences');
    Route::get('/cours', [EtudiantController::class, 'mesCours'])->name('cours');
});

/* ===== CHEF SERVICE ===== */
Route::middleware(['auth', 'role:chef_service'])
    ->prefix('chef')->name('chef.')->group(function () {
    Route::get('/dashboard', [ChefServiceController::class, 'dashboard'])->name('dashboard');
    Route::get('/rapport', [ChefServiceController::class, 'rapportGlobal'])->name('rapport');
    Route::get('/alertes', [ChefServiceController::class, 'alertes'])->name('alertes');
});

/* ===== ESPACE DES COURS (PROFESSEUR UNIQUEMENT) ===== */
Route::middleware(['auth', 'role:professeur'])->group(function () {
    Route::get('cours', [CoursController::class, 'index'])->name('cours.index');
    Route::get('cours/create', [CoursController::class, 'create'])->name('cours.create');
    Route::post('cours', [CoursController::class, 'store'])->name('cours.store');
    Route::get('cours/{cours}', [CoursController::class, 'show'])->name('cours.show');
    Route::get('cours/{cours}/edit', [CoursController::class, 'edit'])->name('cours.edit');
    Route::put('cours/{cours}', [CoursController::class, 'update'])->name('cours.update');
    Route::delete('cours/{cours}', [CoursController::class, 'destroy'])->name('cours.destroy');
});

/* ===== ABSENCES ===== */
Route::middleware('auth')->prefix('absences')->name('absences.')->group(function () {
    Route::get('/', [AbsenceController::class, 'index'])->name('index');
    Route::get('/cours/{cours}/feuille', [AbsenceController::class, 'feuille'])->name('feuille');
    Route::post('/cours/{cours}/enregistrer', [AbsenceController::class, 'enregistrer'])->name('enregistrer');
    Route::patch('/{absence}/valider', [AbsenceController::class, 'valider'])->name('valider');
    Route::patch('/{absence}/justifier', [AbsenceController::class, 'justifier'])->name('justifier');
    Route::get('/rapport', [AbsenceController::class, 'rapport'])->name('rapport');
    Route::get('/statistiques', [AbsenceController::class, 'statistiques'])->name('statistiques');
});

/* ===== BIOMETRIE ===== */
Route::middleware('auth')->prefix('biometrie')->name('biometrie.')->group(function () {
    Route::get('/', [BiometrieController::class, 'index'])->name('index');
    Route::get('/etudiant/{etudiant}/enregistrer', [BiometrieController::class, 'enregistrer'])->name('enregistrer');
    Route::post('/etudiant/{etudiant}/sauvegarder', [BiometrieController::class, 'sauvegarder'])->name('sauvegarder');
    Route::get('/cours/{cours}/pointage', [BiometrieController::class, 'pointage'])->name('pointage');
    Route::post('/cours/{cours}/traiter', [BiometrieController::class, 'traiterPointage'])->name('traiter');
});