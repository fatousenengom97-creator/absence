<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    AdminController,
    AbsenceController,
    BiometrieController,
    ProfesseurController,
    EtudiantController,
    ChefServiceController,
    ClasseController,
    DepartementController,
    InscriptionController,
    EtudiantsParFiliereController
};

// 🛠️ IMPORTATION EXPLICITE POUR ÉVITER LES CONFLITS D'ALIAS
use App\Http\Controllers\Admin\MatiereController;
use App\Http\Controllers\Admin\CoursController as AdminCoursController; 
use App\Http\Controllers\CoursController;


/*
|--------------------------------------------------------------------------
| ===== ACCUEIL / AUTHENTIFICATION =====
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ===== DEBUG (À supprimer avant la soutenance) =====
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| ===== ESPACE ADMINISTRATEUR =====
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:administrateur'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Étudiants par filière
    Route::get('/etudiants-filiere', [EtudiantsParFiliereController::class, 'index'])->name('etudiants-filiere.index');
    Route::get('/etudiants-filiere/{filiere}/classes', [EtudiantsParFiliereController::class, 'showClasses'])->name('etudiants-filiere.classes');
    Route::get('/etudiants-filiere/{filiere}/classes/{classe}/etudiants', [EtudiantsParFiliereController::class, 'showEtudiants'])->name('etudiants-filiere.etudiants');

    // Gestion des Utilisateurs
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
    // ==========================================
    // Classes (Gérées par ClasseController à la racine)
    // ==========================================
    Route::get('/classes', [\App\Http\Controllers\ClasseController::class, 'index'])->name('classes.index');
    
    // ⚡ ROUTE D'API FILTRAGE : Celle appelée par ton JavaScript (fetch)
    Route::get('/classes/filter', [\App\Http\Controllers\ClasseController::class, 'getClassesByCriteria'])->name('classes.filter');
    
    // Actions de simulation sur le bon contrôleur
    Route::get('/classes/{id}/edit', [\App\Http\Controllers\ClasseController::class, 'edit'])->name('classes.edit');
    Route::post('/classes/store', [\App\Http\Controllers\ClasseController::class, 'store'])->name('classes.store');
    Route::post('/classes/{id}/update', [\App\Http\Controllers\ClasseController::class, 'update'])->name('classes.update');
    Route::post('/classes/{id}/delete', [\App\Http\Controllers\ClasseController::class, 'destroy'])->name('classes.delete');
    // Matières
    Route::get('/matieres', [MatiereController::class, 'index'])->name('matieres.index');
    Route::post('/matieres', [MatiereController::class, 'store'])->name('matieres.store');
    Route::delete('/matieres/{id}', [MatiereController::class, 'destroy'])->name('matieres.destroy');

    // Salles
    Route::get('/salles', [AdminController::class, 'indexSalles'])->name('salles.index');
    Route::post('/salles', [AdminController::class, 'storeSalle'])->name('salles.store');

    // Vues simples de l'Admin
    Route::get('/statistiques', function () { return view('admin.statistiques.index'); })->name('statistiques.index');
    
    // Reconnaissance faciale de l'Admin
    Route::get('/reconnaissance-faciale', function () { return view('admin.biometrie.index'); })->name('biometrie.index');

    // 🗓️ Planification et gestion des cours (ADMIN) - Utilise le contrôleur Admin dédié
    Route::get('/cours', [AdminCoursController::class, 'index'])->name('cours.index');
    Route::post('/cours/store', [AdminCoursController::class, 'store'])->name('cours.store');
    // 🚀 AJOUTE CETTE LIGNE ICI :
Route::get('/cours/matieres-par-classe/{classe_id}', [AdminCoursController::class, 'getMatieresParClasse'])->name('cours.matieres');

    // ⚡ API Cascade Spécifique au Planificateur de cours
    Route::prefix('api/cours')->name('api.cours.')->group(function () {
        Route::get('/classes/{idClasse}/matieres', [AdminCoursController::class, 'getMatieresParClasse'])->name('matieres');
    });

    // Structure Académique (Départements / Filières / Cursus)
    Route::get('/departements', [DepartementController::class, 'index'])->name('departements.index');
    Route::post('/departements', [DepartementController::class, 'store'])->name('departements.store');
    Route::get('/departements/{departement}/filieres', [DepartementController::class, 'showFilieres'])->name('departements.filieres');
    Route::post('/departements/{departement}/filieres', [DepartementController::class, 'storeFiliere'])->name('departements.filieres.store');
    Route::get('/departements/{departement}/filieres/{filiere}/classes', [DepartementController::class, 'showClasses'])->name('departements.classes');
    Route::get('/departements/{departement}/filieres/{filiere}/classes/{classe}/matieres', [DepartementController::class, 'showMatieres'])->name('departements.matieres');
    Route::post('/departements/{departement}/filieres/{filiere}/classes/{classe}/ues', [DepartementController::class, 'storeUE'])->name('departements.ues.store');
    Route::post('/departements/{departement}/filieres/{filiere}/classes/{classe}/ues/{ue}/matieres', [DepartementController::class, 'storeMatiereUE'])->name('departements.ues.matieres.store');

    // API cascade générale (inscription)
    Route::prefix('api')->group(function () {
        Route::get('/departements/{departement}/filieres', [InscriptionController::class, 'filieresParDepartement']);
        Route::get('/filieres/{filiere}/classes', [InscriptionController::class, 'classesParFiliere']);
    });
});

/*
|--------------------------------------------------------------------------
| ===== ESPACE PROFESSEUR =====
|--------------------------------------------------------------------------
*/
// --- ROUTES COMMUNES (Admin, Prof, Étudiant) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/cours', [CoursController::class, 'index'])->name('cours.index');
    Route::post('/cours/store', [CoursController::class, 'store'])->name('cours.store');
    // ... tes autres routes globales pour les cours
});

// --- EN DESSOUS : TON GROUPE PROFESSEUR ---
Route::middleware(['auth', 'role:professeur'])->prefix('professeur')->name('professeur.')->group(function () {
    Route::get('/dashboard', [ProfesseurController::class, 'dashboard'])->name('dashboard');
    Route::get('/etudiants', [ProfesseurController::class, 'mesEtudiants'])->name('etudiants');
    Route::get('/absences', [ProfesseurController::class, 'absencesClasse'])->name('absences');
    Route::post('/cours/{cours}/pointage', [ProfesseurController::class, 'declarerPointage'])->name('pointage');
});

/*
|--------------------------------------------------------------------------
| ===== ESPACE ETUDIANT =====
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:etudiant'])->prefix('etudiant')->name('etudiant.')->group(function () {
    Route::get('/dashboard', [EtudiantController::class, 'dashboard'])->name('dashboard');
    Route::get('/absences', [EtudiantController::class, 'mesAbsences'])->name('absences');
    Route::get('/cours', [EtudiantController::class, 'mesCours'])->name('cours');
});

/*
|--------------------------------------------------------------------------
| ===== ESPACE CHEF DE SERVICE =====
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:chef_service'])->prefix('chef')->name('chef.')->group(function () {
    Route::get('/dashboard', [ChefServiceController::class, 'dashboard'])->name('dashboard');
    Route::get('/rapport', [ChefServiceController::class, 'rapportGlobal'])->name('rapport');
    Route::get('/alertes', [ChefServiceController::class, 'alertes'])->name('alertes');
});

/*
|--------------------------------------------------------------------------
| ===== MODULES GENERAUX PROTEGES (AUTH) =====
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Gestion des Absences et Feuilles de présence - Utilise le contrôleur global si nécessaire
    Route::prefix('absences')->name('absences.')->group(function () {
        Route::get('/', [AbsenceController::class, 'index'])->name('index');
        Route::get('/cours/{cours}/feuille', [AbsenceController::class, 'feuille'])->name('feuille');
        Route::post('/cours/{cours}/enregistrer', [AbsenceController::class, 'enregistrer'])->name('enregistrer');
        Route::patch('/{absence}/valider', [AbsenceController::class, 'valider'])->name('valider');
        Route::patch('/{absence}/justifier', [AbsenceController::class, 'justifier'])->name('justifier');
        Route::get('/rapport', [AbsenceController::class, 'rapport'])->name('rapport');
        Route::get('/statistiques', [AbsenceController::class, 'statistiques'])->name('statistiques');
    });

    // Traitements Biométriques / Reconnaissance faciale
    Route::prefix('biometrie')->name('biometrie.')->group(function () {
        Route::get('/', [BiometrieController::class, 'index'])->name('index');
        Route::get('/etudiant/{etudiant}/enregistrer', [BiometrieController::class, 'enregistrer'])->name('enregistrer');
        Route::post('/etudiant/{etudiant}/sauvegarder', [BiometrieController::class, 'sauvegarder'])->name('sauvegarder');
        Route::get('/cours/{cours}/pointage', [BiometrieController::class, 'pointage'])->name('pointage');
        Route::post('/cours/{cours}/traiter', [BiometrieController::class, 'traiterPointage'])->name('traiter');
    });
});





// On ajoute 'as' => 'admin.' pour que les routes s'appellent admin.matieres.index, admin.matieres.filter, etc.
Route::middleware(['auth'])->prefix('admin')->as('admin.')->group(function () {

    // 1. Route pour la récupération des filières (accessible via route('admin.filieres.recuperer'))
    Route::get('/filieres/recuperer', [MatiereController::class, 'getFilieresByCriteria'])->name('filieres.recuperer');

    // 2. Route pour le filtrage des matières (accessible via route('admin.matieres.filter'))
    Route::get('/matieres/filter', [MatiereController::class, 'filter'])->name('matieres.filter');

    // 3. Route ressource globale (génère admin.matieres.index, admin.matieres.store, etc.)
    Route::resource('/matieres', MatiereController::class);
    
});