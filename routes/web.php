<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\EtudiantsParFiliereController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\EmploiDuTempsController; // <-- Ton nouveau contrôleur importé ici
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

/* =========================================================================
    ===== 1. ACCUEIL & AUTHENTIFICATION (Pour tous les acteurs) =====
   ========================================================================= */
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ===== UTILS & LOGS ===== */
Route::get('/debug-login', [AuthController::class, 'showDebug'])->name('debug.login');
Route::get('/voir-log', function () {
    $log = storage_path('logs/laravel.log');
    if (!file_exists($log)) return 'Aucun log trouvé.';
    return '<pre style="background:#1a1a1a;color:#00ff00;padding:20px;font-size:12px;">'
        . htmlspecialchars(substr(file_get_contents($log), -3000)) . '</pre>';
});


/* =========================================================================
    ===== 2. ESPACE ADMINISTRATEUR (Gestionnaire principal du système) =====
   ========================================================================= */
Route::middleware(['auth', 'role:administrateur'])
    ->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

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

    Route::get('/classes', [AdminController::class, 'indexClasses'])->name('classes.index');
    Route::get('/classes/create', [AdminController::class, 'createClasse'])->name('classes.create');
    Route::post('/classes', [AdminController::class, 'storeClasse'])->name('classes.store');
    Route::delete('/classes/{classe}', [AdminController::class, 'destroyClasse'])->name('classes.destroy');

    Route::get('/matieres', [AdminController::class, 'indexMatieres'])->name('matieres.index');
    Route::post('/matieres', [AdminController::class, 'storeMatiere'])->name('matieres.store');

    Route::get('/salles', [AdminController::class, 'indexSalles'])->name('salles.index');
    Route::post('/salles', [AdminController::class, 'storeSalle'])->name('salles.store');

    Route::get('/departements', [DepartementController::class, 'index'])->name('departements.index');
    Route::post('/departements', [DepartementController::class, 'store'])->name('departements.store');
    Route::get('/departements/{departement}/filieres', [DepartementController::class, 'showFilieres'])->name('departements.filieres');
    Route::post('/departements/{departement}/filieres', [DepartementController::class, 'storeFiliere'])->name('departements.filieres.store');
    Route::get('/departements/{departement}/filieres/{filiere}/classes', [DepartementController::class, 'showClasses'])->name('departements.classes');
    Route::get('/departements/{departement}/filieres/{filiere}/classes/{classe}/matieres', [DepartementController::class, 'showMatieres'])->name('departements.matieres');
    Route::post('/departements/{departement}/filieres/{filiere}/classes/{classe}/ues', [DepartementController::class, 'storeUE'])->name('departements.ues.store');
    Route::post('/departements/{departement}/filieres/{filiere}/classes/{classe}/ues/{ue}/matieres', [DepartementController::class, 'storeMatiereUE'])->name('departements.ues.matieres.store');

    Route::get('/etudiants-filiere', [EtudiantsParFiliereController::class, 'index'])->name('etudiants-filiere.index');
    Route::get('/etudiants-filiere/{filiere}/classes', [EtudiantsParFiliereController::class, 'showClasses'])->name('etudiants-filiere.classes');
    Route::get('/etudiants-filiere/{filiere}/classes/{classe}/etudiants', [EtudiantsParFiliereController::class, 'showEtudiants'])->name('etudiants-filiere.etudiants');

    Route::prefix('api')->group(function () {
        Route::get('/departements/{departement}/filieres', [InscriptionController::class, 'filieresParDepartement']);
        Route::get('/filieres/{filiere}/classes', [InscriptionController::class, 'classesParFiliere']);
    });
    
    Route::get('/rapports/pdf', [AdminController::class, 'genererRapportPDF'])->name('rapports.pdf');
});


/* =========================================================================
    ===== 3. ESPACE ENSEIGNANT (Responsable du cours) =====
   ========================================================================= */
Route::middleware(['auth', 'role:professeur'])
    ->prefix('professeur')->name('professeur.')->group(function () {
    
    Route::get('/dashboard', [ProfesseurController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/mon-emploi-du-temps', [ProfesseurController::class, 'monEmploiDuTemps'])->name('emploi-du-temps');
    Route::post('/edt/{edt}/demarrer', [ProfesseurController::class, 'demarrerDepuisEDT'])->name('edt.demarrer');
    Route::get('/etudiants', [ProfesseurController::class, 'mesEtudiants'])->name('etudiants');
    
    Route::get('/absences', [ProfesseurController::class, 'absencesClasse'])->name('absences');
    Route::patch('/absence/{absence}/modifier', [ProfesseurController::class, 'modifierAbsence'])->name('absence.modifier');

    Route::post('/cours/{cours}/pointage', [ProfesseurController::class, 'declarerPointage'])->name('pointage');
    
    Route::get('/cours/{cours}/rapport-pdf', [ProfesseurController::class, 'genererRapportPDF'])->name('rapport.pdf');
});


/* =========================================================================
    ===== 4. ESPACE CHEF DE SERVICE PÉDAGOGIQUE (Superviseur SATIC) =====
   ========================================================================= */
Route::middleware(['auth', 'role:chef_service'])
    ->prefix('chef')->name('chef.')->group(function () {
    
    Route::get('/dashboard', [ChefServiceController::class, 'dashboard'])->name('dashboard');
    Route::get('/alertes', [ChefServiceController::class, 'alertes'])->name('alertes');
    
    Route::get('/emploi-du-temps', [ChefServiceController::class, 'emploiDuTemps'])->name('edt.index');
    Route::get('/emploi-du-temps/{classe}', [ChefServiceController::class, 'edtClasse'])->name('edt.classe');
    Route::post('/emploi-du-temps/{classe}', [ChefServiceController::class, 'storeEDT'])->name('edt.store');
    Route::delete('/emploi-du-temps/creneau/{idEDT}', [ChefServiceController::class, 'destroyEDT'])->name('edt.destroy');
    Route::put('/emploi-du-temps/creneau/{idEDT}', [ChefServiceController::class, 'updateEDT'])->name('edt.update');
    
    // 🆕 NOUVELLES ROUTES : Affichage de l'EDT et insertion sécurisée anti-doublon
    Route::get('/classes/{idClasse}/emploi-du-temps', [EmploiDuTempsController::class, 'show'])->name('emploi_du_temps.show');
    Route::post('/emploi-du-temps/store', [EmploiDuTempsController::class, 'store'])->name('emploi_du_temps.store');

    Route::get('/salles', [ChefServiceController::class, 'salles'])->name('salles');
    
    Route::get('/rapport', [ChefServiceController::class, 'rapportGlobal'])->name('rapport');
    Route::get('/rapport/pdf', [ChefServiceController::class, 'genererRapportPDF'])->name('rapport.pdf');
    
    Route::get('/etudiants-filiere', [EtudiantsParFiliereController::class, 'index'])->name('etudiants-filiere.index');
    Route::get('/etudiants-filiere/{filiere}/classes', [EtudiantsParFiliereController::class, 'showClasses'])->name('etudiants-filiere.classes');
    Route::get('/etudiants-filiere/{filiere}/classes/{classe}/etudiants', [EtudiantsParFiliereController::class, 'showEtudiants'])->name('etudiants-filiere.etudiants');

    // Rapports transmis à chaque fin de cours
    Route::get('/rapports-cours', [ChefServiceController::class, 'rapportsCours'])->name('rapports-cours.index');
    Route::get('/rapports-cours/{cours}/pdf', [ChefServiceController::class, 'rapportCoursPDF'])->name('rapports-cours.pdf');
    Route::patch('/rapports-cours/{rapport}/lu', [ChefServiceController::class, 'marquerLu'])->name('rapports-cours.lu');
});


/* =========================================================================
    ===== 5. ESPACE ÉTUDIANT (Acteur principal) =====
   ========================================================================= */
Route::middleware(['auth', 'role:etudiant'])
    ->prefix('etudiant')->name('etudiant.')->group(function () {
    
    Route::get('/dashboard', [EtudiantController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/absences', [EtudiantController::class, 'mesAbsences'])->name('absences');
    Route::get('/cours', [EtudiantController::class, 'mesCours'])->name('cours');
    
    Route::get('/mon-emploi-du-temps', [EtudiantController::class, 'monEmploiDuTemps'])->name('emploi-du-temps');
});


/* =========================================================================
    ===== 6. GESTION DES COURS (Cycle de vie - Démarrer / Terminer) =====
   ========================================================================= */
Route::middleware(['auth', 'role:administrateur,professeur'])->group(function () {
    Route::post('cours/{cours}/demarrer', [CoursController::class, 'demarrer'])->name('cours.demarrer');
    Route::post('cours/{cours}/terminer', [CoursController::class, 'terminer'])->name('cours.terminer');
    
    Route::resource('cours', CoursController::class);
});


/* =========================================================================
    ===== 7. GESTION DES ABSENCES (Validation & Justification) =====
   ========================================================================= */
Route::middleware('auth')->prefix('absences')->name('absences.')->group(function () {
    Route::get('/', [AbsenceController::class, 'index'])->name('index');
    Route::get('/cours/{cours}/feuille', [AbsenceController::class, 'feuille'])->name('feuille');
    Route::post('/cours/{cours}/enregistrer', [AbsenceController::class, 'enregistrer'])->name('enregistrer');
    Route::get('/statistiques', [AbsenceController::class, 'statistiques'])->name('statistiques');
    
    Route::get('/rapport', [AbsenceController::class, 'rapport'])->name('rapport');

    Route::middleware('role:administrateur,chef_service')->group(function () {
        Route::patch('/{absence}/valider', [AbsenceController::class, 'valider'])->name('valider');
        Route::patch('/{absence}/justifier', [AbsenceController::class, 'justifier'])->name('justifier');
    });
});


/* =========================================================================
    ===== 8. RECONNAISSANCE FACIALE / BIOMÉTRIE & API REST (Python) =====
   ========================================================================= */
Route::middleware('auth')->prefix('biometrie')->name('biometrie.')->group(function () {
    Route::get('/', [BiometrieController::class, 'index'])->name('index');

    Route::middleware('role:administrateur,chef_service')->group(function () {
        Route::get('/etudiant/{etudiant}/enregistrer', [BiometrieController::class, 'enregistrer'])->name('enregistrer');
        Route::post('/etudiant/{etudiant}/sauvegarder', [BiometrieController::class, 'sauvegarder'])->name('sauvegarder');
    });

    Route::middleware('role:professeur,administrateur')->group(function () {
        Route::get('/cours/{cours}/pointage', [BiometrieController::class, 'pointage'])->name('pointage');
        Route::post('/cours/{cours}/verifier', [BiometrieController::class, 'verifierVisage'])->name('verifier');
        Route::post('/cours/{cours}/traiter', [BiometrieController::class, 'traiterPointage'])->name('traiter');
    });
});

// Envoi automatique des présences en temps réel par le script Python (API REST)
Route::post('/api/pointage/valider', [PointageController::class, 'validerPresence'])->name('api.pointage.valider');