<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Niveau;
use App\Models\Filiere;
use App\Models\Professeur;

class MatiereController extends Controller
{
    /**
     * Page principale : On charge les structures de base pour l'interface
     */
    public function index()
    {
        // On garde ça pour que les menus déroulants affichent quelque chose
        $niveaux = Niveau::all();
        $filieres = Filiere::all(); 
        $professeurs = Professeur::all();

        return view('admin.matieres.index', compact('niveaux', 'filieres', 'professeurs'));
    }

    /**
     * SIMULATION API : Renvoie les filières selon le département sans toucher à la BDD
     */
    public function getFilieresByCriteria(Request $request)
{
    // On récupère le département (par défaut 'Informatique' ou 'Mathématiques' selon tes besoins)
    $dept = $request->query('dept', 'Informatique');

    // Nettoyage de la valeur reçue pour éviter les erreurs de casse ou d'espaces
    $deptKey = trim(strtoupper($dept));

    // Simulation des filières réelles selon le département officiel de l'UFR SATIC
    if ($deptKey === 'INFORMATIQUE' || $deptKey === 'TIC') {
        $filieresSimulees = [
            [
                'codeFiliere' => 'D2A', 
                'nomFiliere' => 'Développement d\'Applications Web et Mobiles (D2A)', 
                'departement' => 'Informatique'
            ],
            [
                'codeFiliere' => 'SRT', 
                'nomFiliere' => 'Systèmes, Réseaux et Télécommunications (SRT)', 
                'departement' => 'Informatique'
            ]
        ];
    } elseif ($deptKey === 'MATH' || $deptKey === 'MATHEMATIQUES' || $deptKey === 'SID') {
        $filieresSimulees = [
            [
                'codeFiliere' => 'SID', 
                'nomFiliere' => 'Statistique et Informatique Décisionnelle (SID)', 
                'departement' => 'Mathématiques'
            ]
        ];
    } elseif ($deptKey === 'PHYSIQUE' || $deptKey === 'PC' || $deptKey === 'PN') {
        $filieresSimulees = [
            [
                'codeFiliere' => 'PC', 
                'nomFiliere' => 'Physique - Chimie (PC)', 
                'departement' => 'Physique'
            ],
            [
                'codeFiliere' => 'PN', 
                'nomFiliere' => 'Physique Numérique (PN)', 
                'departement' => 'Physique'
            ]
        ];
    } else {
        // Optionnel : Retourne toutes les filières si le filtre ne correspond à rien
        $filieresSimulees = [
            ['codeFiliere' => 'D2A', 'nomFiliere' => 'Développement d\'Applications Web et Mobiles (D2A)', 'departement' => 'Informatique'],
            ['codeFiliere' => 'SRT', 'nomFiliere' => 'Systèmes, Réseaux et Télécommunications (SRT)', 'departement' => 'Informatique'],
            ['codeFiliere' => 'SID', 'nomFiliere' => 'Statistique et Informatique Décisionnelle (SID)', 'departement' => 'Mathématiques'],
            ['codeFiliere' => 'PC', 'nomFiliere' => 'Physique - Chimie (PC)', 'departement' => 'Physique'],
            ['codeFiliere' => 'PN', 'nomFiliere' => 'Physique Numérique (PN)', 'departement' => 'Physique']
        ];
    }

    return response()->json($filieresSimulees);
}
    /**
     * SIMULATION API : Renvoie les matières en dur selon les filtres sélectionnés
     */
   
    /**
     * SIMULATION API : Catalogue officiel UFR SATIC (D2A, SRT, PC, PN et SID complets)
     */
    public function filter(Request $request)
    {
        $filiere = $request->query('filiere', 'D2A');
        $semestre = $request->query('semestre', 'S1');

        // Catalogue exhaustif tiré des maquettes officielles de l'UADB
        $catalogue = [
            // =========================================================================
            // FILIÈRE : D2A (Développement d'Applications Web)
            // =========================================================================
            'D2A' => [
                'S1' => [
                    ['idMatiere' => 1, 'codeMatiere' => 'WEB1111', 'nomMatiere' => 'Architecture et technologie des ordinateurs', 'coefficient' => 1],
                    ['idMatiere' => 2, 'codeMatiere' => 'WEB1112', 'nomMatiere' => 'Système d\'exploitation', 'coefficient' => 1],
                    ['idMatiere' => 3, 'codeMatiere' => 'WEB1121', 'nomMatiere' => 'Algèbre', 'coefficient' => 1],
                    ['idMatiere' => 4, 'codeMatiere' => 'WEB1122', 'nomMatiere' => 'Analyse', 'coefficient' => 1],
                    ['idMatiere' => 5, 'codeMatiere' => 'WEB1131', 'nomMatiere' => 'Techniques d\'expression', 'coefficient' => 1],
                    ['idMatiere' => 6, 'codeMatiere' => 'WEB1132', 'nomMatiere' => 'Initiation à l\'Informatique', 'coefficient' => 1],
                    ['idMatiere' => 7, 'codeMatiere' => 'WEB1141', 'nomMatiere' => 'Introduction à l\'algorithmique et à la programmation', 'coefficient' => 1],
                ],
                'S2' => [
                    ['idMatiere' => 8, 'codeMatiere' => 'WEB1211', 'nomMatiere' => 'HTML et CSS', 'coefficient' => 1],
                    ['idMatiere' => 9, 'codeMatiere' => 'WEB1212', 'nomMatiere' => 'Approfondissement à l\'algorithmique et à la programmation', 'coefficient' => 1],
                    ['idMatiere' => 10, 'codeMatiere' => 'WEB1221', 'nomMatiere' => 'Probabilité et statistique', 'coefficient' => 1],
                    ['idMatiere' => 11, 'codeMatiere' => 'WEB1222', 'nomMatiere' => 'Comptabilité générale', 'coefficient' => 1],
                    ['idMatiere' => 12, 'codeMatiere' => 'WEB1231', 'nomMatiere' => 'Introduction sur les réseaux', 'coefficient' => 1],
                    ['idMatiere' => 13, 'codeMatiere' => 'WEB1241', 'nomMatiere' => 'Projet Personnel et Professionnel (PPP)', 'coefficient' => 1],
                    ['idMatiere' => 14, 'codeMatiere' => 'WEB1242', 'nomMatiere' => 'Anglais', 'coefficient' => 1],
                ],
                'S3' => [
                    ['idMatiere' => 15, 'codeMatiere' => 'WEB2311', 'nomMatiere' => 'Linux utilisateur', 'coefficient' => 1],
                    ['idMatiere' => 16, 'codeMatiere' => 'WEB2312', 'nomMatiere' => 'Réseaux', 'coefficient' => 1],
                    ['idMatiere' => 17, 'codeMatiere' => 'WEB2321', 'nomMatiere' => 'Conception des systèmes d\'information avec Merise', 'coefficient' => 1],
                    ['idMatiere' => 18, 'codeMatiere' => 'WEB2331', 'nomMatiere' => 'Langage C', 'coefficient' => 1],
                    ['idMatiere' => 19, 'codeMatiere' => 'WEB2332', 'nomMatiere' => 'Javascript', 'coefficient' => 1],
                    ['idMatiere' => 20, 'codeMatiere' => 'WEB2341', 'nomMatiere' => 'Technique de communication', 'coefficient' => 1],
                    ['idMatiere' => 21, 'codeMatiere' => 'WEB2342', 'nomMatiere' => 'Anglais technique', 'coefficient' => 1],
                ],
                'S4' => [
                    ['idMatiere' => 22, 'codeMatiere' => 'WEB2411', 'nomMatiere' => 'PHP (Programmation Web dynamique)', 'coefficient' => 1],
                    ['idMatiere' => 23, 'codeMatiere' => 'WEB2412', 'nomMatiere' => 'POO avec Java', 'coefficient' => 1],
                    ['idMatiere' => 24, 'codeMatiere' => 'WEB2421', 'nomMatiere' => 'Bases de données relationnelles', 'coefficient' => 1],
                    ['idMatiere' => 25, 'codeMatiere' => 'WEB2431', 'nomMatiere' => 'Administration Linux', 'coefficient' => 1],
                    ['idMatiere' => 26, 'codeMatiere' => 'WEB2432', 'nomMatiere' => 'Administration Windows', 'coefficient' => 1],
                ],
                'S5' => [
                    ['idMatiere' => 27, 'codeMatiere' => 'WEB3511', 'nomMatiere' => 'Modélisation orientée objet avec UML', 'coefficient' => 1],
                    ['idMatiere' => 28, 'codeMatiere' => 'WEB3512', 'nomMatiere' => 'Java avancé', 'coefficient' => 1],
                    ['idMatiere' => 29, 'codeMatiere' => 'WEB3521', 'nomMatiere' => 'Frameworks Web', 'coefficient' => 1],
                    ['idMatiere' => 30, 'codeMatiere' => 'WEB3522', 'nomMatiere' => 'JSP et Servlet', 'coefficient' => 1],
                    ['idMatiere' => 31, 'codeMatiere' => 'WEB3531', 'nomMatiere' => 'Administration de base de données Oracle', 'coefficient' => 1],
                    ['idMatiere' => 32, 'codeMatiere' => 'WEB3532', 'nomMatiere' => 'Technologies XML', 'coefficient' => 1],
                ],
                'S6' => [
                    ['idMatiere' => 33, 'codeMatiere' => 'WEB3611', 'nomMatiere' => 'Multimédia', 'coefficient' => 1],
                    ['idMatiere' => 34, 'codeMatiere' => 'WEB3612', 'nomMatiere' => 'CMS', 'coefficient' => 1],
                    ['idMatiere' => 35, 'codeMatiere' => 'WEB3621', 'nomMatiere' => 'Méthodologie de rédaction de mémoire', 'coefficient' => 1],
                    ['idMatiere' => 36, 'codeMatiere' => 'WEB3622', 'nomMatiere' => 'Anglais (Communication)', 'coefficient' => 1],
                    ['idMatiere' => 37, 'codeMatiere' => 'WEB3623', 'nomMatiere' => 'Droit des TIC', 'coefficient' => 1],
                    ['idMatiere' => 38, 'codeMatiere' => 'WEB3624', 'nomMatiere' => 'Gestion de projet', 'coefficient' => 1],
                    ['idMatiere' => 39, 'codeMatiere' => 'WEB3631', 'nomMatiere' => 'Stage Professionnel', 'coefficient' => 2],
                ]
            ],

            // =========================================================================
            // FILIÈRE : SRT (Systèmes, Réseaux et Télécommunications)
            // =========================================================================
            'SRT' => [
                'S1' => [
                    ['idMatiere' => 101, 'codeMatiere' => 'SRT1111', 'nomMatiere' => 'Architecture et Technologie des ordinateurs', 'coefficient' => 1],
                    ['idMatiere' => 102, 'codeMatiere' => 'SRT1112', 'nomMatiere' => 'Système d\'exploitation', 'coefficient' => 1],
                    ['idMatiere' => 103, 'codeMatiere' => 'SRT1121', 'nomMatiere' => 'Algèbre I', 'coefficient' => 1],
                    ['idMatiere' => 104, 'codeMatiere' => 'SRT1122', 'nomMatiere' => 'Analyse I', 'coefficient' => 1],
                    ['idMatiere' => 105, 'codeMatiere' => 'SRT1131', 'nomMatiere' => 'Technique d\'expression', 'coefficient' => 1],
                    ['idMatiere' => 106, 'codeMatiere' => 'SRT1132', 'nomMatiere' => 'Anglais / Initiation en Informatique', 'coefficient' => 1],
                    ['idMatiere' => 107, 'codeMatiere' => 'SRT1141', 'nomMatiere' => 'Initiation à l\'Algorithmique', 'coefficient' => 1],
                    ['idMatiere' => 108, 'codeMatiere' => 'SRT1142', 'nomMatiere' => 'Initiation à la Programmation', 'coefficient' => 1],
                ],
                'S2' => [
                    ['idMatiere' => 109, 'codeMatiere' => 'SRT1211', 'nomMatiere' => 'Introduction aux Réseaux', 'coefficient' => 2],
                    ['idMatiere' => 110, 'codeMatiere' => 'SRT1212', 'nomMatiere' => 'Structure de données', 'coefficient' => 1],
                    ['idMatiere' => 111, 'codeMatiere' => 'SRT1221', 'nomMatiere' => 'Algèbre II', 'coefficient' => 1],
                    ['idMatiere' => 112, 'codeMatiere' => 'SRT1222', 'nomMatiere' => 'Analyse II', 'coefficient' => 1],
                    ['idMatiere' => 113, 'codeMatiere' => 'SRT1223', 'nomMatiere' => 'Statistique et Probabilité', 'coefficient' => 1],
                    ['idMatiere' => 114, 'codeMatiere' => 'SRT1231', 'nomMatiere' => 'Électricité', 'coefficient' => 1],
                    ['idMatiere' => 115, 'codeMatiere' => 'SRT1232', 'nomMatiere' => 'Traitement du signal', 'coefficient' => 1],
                ],
                'S3' => [
                    ['idMatiere' => 116, 'codeMatiere' => 'SRT2311', 'nomMatiere' => 'Algorithmique II', 'coefficient' => 1],
                    ['idMatiere' => 117, 'codeMatiere' => 'SRT2312', 'nomMatiere' => 'Programmation II', 'coefficient' => 1],
                    ['idMatiere' => 118, 'codeMatiere' => 'SRT2321', 'nomMatiere' => 'Électronique', 'coefficient' => 1],
                    ['idMatiere' => 119, 'codeMatiere' => 'SRT2322', 'nomMatiere' => 'Recherche Opérationnelle', 'coefficient' => 1],
                    ['idMatiere' => 120, 'codeMatiere' => 'SRT2331', 'nomMatiere' => 'Services Réseau', 'coefficient' => 1],
                    ['idMatiere' => 121, 'codeMatiere' => 'SRT2332', 'nomMatiere' => 'Réseaux sans fil', 'coefficient' => 1],
                    ['idMatiere' => 122, 'codeMatiere' => 'SRT2333', 'nomMatiere' => 'Linux', 'coefficient' => 1],
                    ['idMatiere' => 123, 'codeMatiere' => 'SRT2341', 'nomMatiere' => 'Technique de communication', 'coefficient' => 1],
                    ['idMatiere' => 124, 'codeMatiere' => 'SRT2342', 'nomMatiere' => 'Anglais Technique', 'coefficient' => 1],
                    ['idMatiere' => 125, 'codeMatiere' => 'SRT2343', 'nomMatiere' => 'Projet Professionnel Personnel', 'coefficient' => 1],
                ],
                'S4' => [
                    ['idMatiere' => 126, 'codeMatiere' => 'SRT2411', 'nomMatiere' => 'Java', 'coefficient' => 1],
                    ['idMatiere' => 127, 'codeMatiere' => 'SRT2412', 'nomMatiere' => 'SIBD (Système d\'Information et Base de Données)', 'coefficient' => 1],
                    ['idMatiere' => 128, 'codeMatiere' => 'SRT2413', 'nomMatiere' => 'Programmation WEB (HTML/PHP)', 'coefficient' => 1],
                    ['idMatiere' => 129, 'codeMatiere' => 'SRT2421', 'nomMatiere' => 'Bases des Télécoms', 'coefficient' => 1],
                    ['idMatiere' => 130, 'codeMatiere' => 'SRT2422', 'nomMatiere' => 'Réseaux de mobiles', 'coefficient' => 1],
                    ['idMatiere' => 131, 'codeMatiere' => 'SRT2423', 'nomMatiere' => 'Maintenance des ordinateurs', 'coefficient' => 1],
                    ['idMatiere' => 132, 'codeMatiere' => 'SRT2431', 'nomMatiere' => 'Administration Linux', 'coefficient' => 1],
                    ['idMatiere' => 133, 'codeMatiere' => 'SRT2432', 'nomMatiere' => 'Administration Windows', 'coefficient' => 1],
                ],
                'S5' => [
                    ['idMatiere' => 134, 'codeMatiere' => 'SRT3511', 'nomMatiere' => 'Routage', 'coefficient' => 1],
                    ['idMatiere' => 135, 'codeMatiere' => 'SRT3512', 'nomMatiere' => 'Réseaux étendus', 'coefficient' => 1],
                    ['idMatiere' => 136, 'codeMatiere' => 'SRT3521', 'nomMatiere' => 'Sécurité des systèmes', 'coefficient' => 1],
                    ['idMatiere' => 137, 'codeMatiere' => 'SRT3522', 'nomMatiere' => 'Maintenance des périphériques', 'coefficient' => 1],
                    ['idMatiere' => 138, 'codeMatiere' => 'SRT3531', 'nomMatiere' => 'Java avancé', 'coefficient' => 1],
                    ['idMatiere' => 139, 'codeMatiere' => 'SRT3532', 'nomMatiere' => 'Web Services', 'coefficient' => 1],
                    ['idMatiere' => 140, 'codeMatiere' => 'SRT3533', 'nomMatiere' => 'Développement mobile', 'coefficient' => 1],
                ],
                'S6' => [
                    ['idMatiere' => 141, 'codeMatiere' => 'SRT3611', 'nomMatiere' => 'Méthodologie de rédaction', 'coefficient' => 1],
                    ['idMatiere' => 142, 'codeMatiere' => 'SRT3612', 'nomMatiere' => 'Droit des TIC', 'coefficient' => 1],
                    ['idMatiere' => 143, 'codeMatiere' => 'SRT3613', 'nomMatiere' => 'Gestion de Projet', 'coefficient' => 2],
                    ['idMatiere' => 144, 'codeMatiere' => 'SRT3614', 'nomMatiere' => 'Anglais (Communication)', 'coefficient' => 1],
                    ['idMatiere' => 145, 'codeMatiere' => 'SRT3621', 'nomMatiere' => 'Stage Pratique', 'coefficient' => 1],
                    ['idMatiere' => 146, 'codeMatiere' => 'SRT3622', 'nomMatiere' => 'Rédaction du Mémoire', 'coefficient' => 1],
                    ['idMatiere' => 147, 'codeMatiere' => 'SRT3623', 'nomMatiere' => 'Soutenance devant Jury', 'coefficient' => 1],
                ]
            ],

            // =========================================================================
            // FILIÈRE : PC (Physique - Chimie)
            // =========================================================================
            'PC' => [
                'S1' => [
                    ['idMatiere' => 201, 'codeMatiere' => 'MPCI1111', 'nomMatiere' => 'Algorithmique et Programmation 1 (Langage C)', 'coefficient' => 1],
                    ['idMatiere' => 202, 'codeMatiere' => 'MPCI1121', 'nomMatiere' => 'Anglais 1', 'coefficient' => 1],
                    ['idMatiere' => 203, 'codeMatiere' => 'MPCI1131', 'nomMatiere' => 'Chimie Physique 1', 'coefficient' => 1],
                    ['idMatiere' => 204, 'codeMatiere' => 'MPCI1132', 'nomMatiere' => 'Chimie Physique 2', 'coefficient' => 1],
                    ['idMatiere' => 205, 'codeMatiere' => 'MPCI1141', 'nomMatiere' => 'Électricité 1', 'coefficient' => 1],
                    ['idMatiere' => 206, 'codeMatiere' => 'MPCI1142', 'nomMatiere' => 'Mécanique', 'coefficient' => 1],
                    ['idMatiere' => 207, 'codeMatiere' => 'MPCI1151', 'nomMatiere' => 'Algèbre 1', 'coefficient' => 1],
                    ['idMatiere' => 208, 'codeMatiere' => 'MPCI1152', 'nomMatiere' => 'Analyse 1', 'coefficient' => 1],
                ],
                'S2' => [
                    ['idMatiere' => 209, 'codeMatiere' => 'MPCI1211', 'nomMatiere' => 'Algorithmique et Programmation 2 (Langage C)', 'coefficient' => 1],
                    ['idMatiere' => 210, 'codeMatiere' => 'MPCI1221', 'nomMatiere' => 'Anglais 2', 'coefficient' => 1],
                    ['idMatiere' => 211, 'codeMatiere' => 'MPCI1231', 'nomMatiere' => 'Chimie Physique 1', 'coefficient' => 1],
                    ['idMatiere' => 212, 'codeMatiere' => 'MPCI1232', 'nomMatiere' => 'Chimie Physique 2', 'coefficient' => 1],
                    ['idMatiere' => 213, 'codeMatiere' => 'MPCI1241', 'nomMatiere' => 'Électricité 2', 'coefficient' => 1],
                    ['idMatiere' => 214, 'codeMatiere' => 'MPCI1242', 'nomMatiere' => 'Optique', 'coefficient' => 1],
                    ['idMatiere' => 215, 'codeMatiere' => 'MPCI1251', 'nomMatiere' => 'Algèbre 2', 'coefficient' => 1],
                    ['idMatiere' => 216, 'codeMatiere' => 'MPCI1252', 'nomMatiere' => 'Analyse 2', 'coefficient' => 1],
                ],
                'S3' => [
                    ['idMatiere' => 217, 'codeMatiere' => 'MPCI2311', 'nomMatiere' => 'Algorithmique et Programmation 3 (Langage C)', 'coefficient' => 1],
                    ['idMatiere' => 218, 'codeMatiere' => 'MPCI2321', 'nomMatiere' => 'Anglais 3', 'coefficient' => 1],
                    ['idMatiere' => 219, 'codeMatiere' => 'MPCI2331', 'nomMatiere' => 'Chimie Minérale', 'coefficient' => 1],
                    ['idMatiere' => 220, 'codeMatiere' => 'MPC12332', 'nomMatiere' => 'Chimie Organique 1', 'coefficient' => 1],
                    ['idMatiere' => 221, 'codeMatiere' => 'MPC12341', 'nomMatiere' => 'Mécanique Quantique', 'coefficient' => 1],
                    ['idMatiere' => 222, 'codeMatiere' => 'MPC12342', 'nomMatiere' => 'Thermodynamique Physique', 'coefficient' => 1],
                    ['idMatiere' => 223, 'codeMatiere' => 'MPCI2351', 'nomMatiere' => 'Analyse 3', 'coefficient' => 3],
                    ['idMatiere' => 224, 'codeMatiere' => 'MPC12352', 'nomMatiere' => 'Algèbre 3', 'coefficient' => 3],
                    ['idMatiere' => 225, 'codeMatiere' => 'MPC12353', 'nomMatiere' => 'Probabilités', 'coefficient' => 2],
                ],
                'S4' => [
                    ['idMatiere' => 226, 'codeMatiere' => 'MPCI2411', 'nomMatiere' => 'Architecture des Ordinateurs', 'coefficient' => 1],
                    ['idMatiere' => 227, 'codeMatiere' => 'MPCI2421', 'nomMatiere' => 'Chimie Organique 2', 'coefficient' => 3],
                    ['idMatiere' => 228, 'codeMatiere' => 'MPCI2422', 'nomMatiere' => 'Biochimie', 'coefficient' => 3],
                    ['idMatiere' => 229, 'codeMatiere' => 'MPCI2423', 'nomMatiere' => 'Cinétique Chimique', 'coefficient' => 2],
                    ['idMatiere' => 230, 'codeMatiere' => 'MPC12431', 'nomMatiere' => 'Notions d\'Électronique Analogique', 'coefficient' => 1],
                    ['idMatiere' => 231, 'codeMatiere' => 'MPC12432', 'nomMatiere' => 'Mécanique des Fluides', 'coefficient' => 1],
                    ['idMatiere' => 232, 'codeMatiere' => 'MPCI2441', 'nomMatiere' => 'Systèmes et Équations', 'coefficient' => 1],
                    ['idMatiere' => 233, 'codeMatiere' => 'MPC12442', 'nomMatiere' => 'Analyse 4', 'coefficient' => 1],
                ],
                'S5' => [
                    ['idMatiere' => 234, 'codeMatiere' => 'PC3511', 'nomMatiere' => 'Thermodynamique Chimique et Électrochimie', 'coefficient' => 1],
                    ['idMatiere' => 235, 'codeMatiere' => 'PC3512', 'nomMatiere' => 'Chimie Inorganique', 'coefficient' => 1],
                    ['idMatiere' => 236, 'codeMatiere' => 'PC3513', 'nomMatiere' => 'Chimie Analytique et Spectroscopie', 'coefficient' => 1],
                    ['idMatiere' => 237, 'codeMatiere' => 'PC3521', 'nomMatiere' => 'Mécanique des Fluides', 'coefficient' => 1],
                    ['idMatiere' => 238, 'codeMatiere' => 'PC3522', 'nomMatiere' => 'Ondes et Vibrations', 'coefficient' => 1],
                    ['idMatiere' => 239, 'codeMatiere' => 'PC3523', 'nomMatiere' => 'Électronique Analogique', 'coefficient' => 1],
                ],
                'S6' => [
                    ['idMatiere' => 240, 'codeMatiere' => 'PC3611', 'nomMatiere' => 'Méthodes de Synthèse Organique', 'coefficient' => 1],
                    ['idMatiere' => 241, 'codeMatiere' => 'PC3612', 'nomMatiere' => 'Chimie Quantique', 'coefficient' => 1],
                    ['idMatiere' => 242, 'codeMatiere' => 'PC3613', 'nomMatiere' => 'Polymères Organiques et Inorganiques', 'coefficient' => 1],
                    ['idMatiere' => 243, 'codeMatiere' => 'PC3621', 'nomMatiere' => 'Électromagnétisme dans la Matière', 'coefficient' => 1],
                    ['idMatiere' => 244, 'codeMatiere' => 'PC3622', 'nomMatiere' => 'Optique Ondulatoire', 'coefficient' => 1],
                    ['idMatiere' => 245, 'codeMatiere' => 'PC3623', 'nomMatiere' => 'Électronique Numérique', 'coefficient' => 1],
                ]
            ],

            // =========================================================================
            // FILIÈRE : PN (Physique Numérique)
            // =========================================================================
            'PN' => [
                'S1' => [
                    ['idMatiere' => 301, 'codeMatiere' => 'MPCI111', 'nomMatiere' => 'Algorithmique et Program I (Lang C)', 'coefficient' => 1],
                    ['idMatiere' => 302, 'codeMatiere' => 'MPCI121', 'nomMatiere' => 'Anglais I', 'coefficient' => 1],
                    ['idMatiere' => 303, 'codeMatiere' => 'MPCI1131', 'nomMatiere' => 'Chimie physique I', 'coefficient' => 1],
                    ['idMatiere' => 304, 'codeMatiere' => 'MPCI1132', 'nomMatiere' => 'Chimie physique II', 'coefficient' => 1],
                    ['idMatiere' => 305, 'codeMatiere' => 'MPCI1141', 'nomMatiere' => 'Electricité I', 'coefficient' => 1],
                    ['idMatiere' => 306, 'codeMatiere' => 'MPCI1142', 'nomMatiere' => 'Mécanique', 'coefficient' => 1],
                    ['idMatiere' => 307, 'codeMatiere' => 'MPCI1151', 'nomMatiere' => 'Algèbre I', 'coefficient' => 1],
                    ['idMatiere' => 308, 'codeMatiere' => 'MPCI1152', 'nomMatiere' => 'Analyse I', 'coefficient' => 1],
                ],
                'S2' => [
                    ['idMatiere' => 309, 'codeMatiere' => 'MPCI211', 'nomMatiere' => 'Algorithmique et Program II (Lang C)', 'coefficient' => 1],
                    ['idMatiere' => 310, 'codeMatiere' => 'MPCI1221', 'nomMatiere' => 'Anglais II', 'coefficient' => 1],
                    ['idMatiere' => 311, 'codeMatiere' => 'MPCI1231', 'nomMatiere' => 'Chimie physique I', 'coefficient' => 1],
                    ['idMatiere' => 312, 'codeMatiere' => 'MPCI1232', 'nomMatiere' => 'Chimie physique II', 'coefficient' => 1],
                    ['idMatiere' => 313, 'codeMatiere' => 'MPCI1241', 'nomMatiere' => 'Electricité II', 'coefficient' => 1],
                    ['idMatiere' => 314, 'codeMatiere' => 'MPCI1242', 'nomMatiere' => 'Optique', 'coefficient' => 1],
                    ['idMatiere' => 315, 'codeMatiere' => 'MPCI1251', 'nomMatiere' => 'Algèbre II', 'coefficient' => 1],
                    ['idMatiere' => 316, 'codeMatiere' => 'MPCI1252', 'nomMatiere' => 'Analyse II', 'coefficient' => 1],
                ],
                'S3' => [
                    ['idMatiere' => 317, 'codeMatiere' => 'MPI2311', 'nomMatiere' => 'Intégrales et Séries', 'coefficient' => 1],
                    ['idMatiere' => 318, 'codeMatiere' => 'MPI2321', 'nomMatiere' => 'Complément d\'algèbre linéaire', 'coefficient' => 1],
                    ['idMatiere' => 319, 'codeMatiere' => 'MPI2331', 'nomMatiere' => 'Calcul Probabilités', 'coefficient' => 1],
                    ['idMatiere' => 320, 'codeMatiere' => 'MPI2341', 'nomMatiere' => 'Mécanique quantique', 'coefficient' => 1],
                    ['idMatiere' => 321, 'codeMatiere' => 'MPCI2342', 'nomMatiere' => 'Thermodynamique physique', 'coefficient' => 1],
                    ['idMatiere' => 322, 'codeMatiere' => 'MPCI2351', 'nomMatiere' => 'Algorithmique et structures de données', 'coefficient' => 1],
                    ['idMatiere' => 323, 'codeMatiere' => 'MPI2352', 'nomMatiere' => 'Anglais scientifique', 'coefficient' => 1],
                ],
                'S4' => [
                    ['idMatiere' => 324, 'codeMatiere' => 'MPI2411', 'nomMatiere' => 'Calcul différentiel et intégral sur IRn', 'coefficient' => 1],
                    ['idMatiere' => 325, 'codeMatiere' => 'MPI2421', 'nomMatiere' => 'Algèbre bilinéaire et sesquilinéaire', 'coefficient' => 1],
                    ['idMatiere' => 326, 'codeMatiere' => 'MPI2431', 'nomMatiere' => 'Calcul numérique', 'coefficient' => 1],
                    ['idMatiere' => 327, 'codeMatiere' => 'MPCI2441', 'nomMatiere' => 'Électromagnétisme dans le vide et relativité restreinte', 'coefficient' => 1],
                    ['idMatiere' => 328, 'codeMatiere' => 'MPCI2442', 'nomMatiere' => 'Mécanique du solide', 'coefficient' => 1],
                    ['idMatiere' => 329, 'codeMatiere' => 'MPI2451', 'nomMatiere' => 'Programmation Orientée Objet en Python', 'coefficient' => 1],
                    ['idMatiere' => 330, 'codeMatiere' => 'MPI2452', 'nomMatiere' => 'Anglais Scientifique IV', 'coefficient' => 1],
                ],
                'S5' => [
                    ['idMatiere' => 331, 'codeMatiere' => 'PN 3511', 'nomMatiere' => 'Analyse numérique', 'coefficient' => 1],
                    ['idMatiere' => 332, 'codeMatiere' => 'PN 3512', 'nomMatiere' => 'Programmation avancée', 'coefficient' => 1],
                    ['idMatiere' => 333, 'codeMatiere' => 'PC 3521', 'nomMatiere' => 'Mécanique des fluides', 'coefficient' => 1],
                    ['idMatiere' => 334, 'codeMatiere' => 'PC 3522', 'nomMatiere' => 'Ondes et vibrations', 'coefficient' => 1],
                    ['idMatiere' => 335, 'codeMatiere' => 'PC 3523', 'nomMatiere' => 'Electronique analogique', 'coefficient' => 1],
                    ['idMatiere' => 336, 'codeMatiere' => 'PN 3531', 'nomMatiere' => 'Laser et applications', 'coefficient' => 1],
                    ['idMatiere' => 337, 'codeMatiere' => 'PN 3532', 'nomMatiere' => 'Physique nucléaire et radiations', 'coefficient' => 1],
                ],
                'S6' => [
                    ['idMatiere' => 338, 'codeMatiere' => 'PN 3611', 'nomMatiere' => 'Initiation à l\'optimisation', 'coefficient' => 1],
                    ['idMatiere' => 339, 'codeMatiere' => 'PN 3612', 'nomMatiere' => 'Introduction à l\'Internet des objets', 'coefficient' => 1],
                    ['idMatiere' => 340, 'codeMatiere' => 'PC 3621', 'nomMatiere' => 'Electromagnétisme dans la matière', 'coefficient' => 1],
                    ['idMatiere' => 341, 'codeMatiere' => 'PC 3622', 'nomMatiere' => 'Optique ondulatoire', 'coefficient' => 1],
                    ['idMatiere' => 342, 'codeMatiere' => 'PC 3623', 'nomMatiere' => 'Electronique numérique', 'coefficient' => 1],
                    ['idMatiere' => 343, 'codeMatiere' => 'PN 3631', 'nomMatiere' => 'Mécanique quantique avancée', 'coefficient' => 1],
                    ['idMatiere' => 344, 'codeMatiere' => 'PN 3632', 'nomMatiere' => 'Traitement du signal', 'coefficient' => 0.5],
                    ['idMatiere' => 345, 'codeMatiere' => 'PN 3633', 'nomMatiere' => 'Introduction à l\'automatique', 'coefficient' => 0.5],
                    ['idMatiere' => 346, 'codeMatiere' => 'PN 364', 'nomMatiere' => 'Projet tutoré', 'coefficient' => 1],
                ]
            ],

            // =========================================================================
            // FILIÈRE : SID (Statistique et Informatique Décisionnelle)
            // =========================================================================
            'SID' => [
                // --- LICENCE 1 (Tronc commun MPCI) ---
                'S1' => [
                    ['idMatiere' => 401, 'codeMatiere' => 'MPCI1111', 'nomMatiere' => 'Algorithmique et Programmation 1 (Langage C)', 'coefficient' => 1],
                    ['idMatiere' => 402, 'codeMatiere' => 'MPCI1121', 'nomMatiere' => 'Anglais 1', 'coefficient' => 1],
                    ['idMatiere' => 403, 'codeMatiere' => 'MPCI1131', 'nomMatiere' => 'Chimie Physique 1', 'coefficient' => 1],
                    ['idMatiere' => 404, 'codeMatiere' => 'MPCI1132', 'nomMatiere' => 'Chimie Physique 2', 'coefficient' => 1],
                    ['idMatiere' => 405, 'codeMatiere' => 'MPCI1141', 'nomMatiere' => 'Électricité 1', 'coefficient' => 1],
                    ['idMatiere' => 406, 'codeMatiere' => 'MPCI1142', 'nomMatiere' => 'Mécanique', 'coefficient' => 1],
                    ['idMatiere' => 407, 'codeMatiere' => 'MPCI1151', 'nomMatiere' => 'Algèbre 1', 'coefficient' => 1],
                    ['idMatiere' => 408, 'codeMatiere' => 'MPCI1152', 'nomMatiere' => 'Analyse 1', 'coefficient' => 1],
                ],
                'S2' => [
                    ['idMatiere' => 409, 'codeMatiere' => 'MPCI1211', 'nomMatiere' => 'Algorithmique et Programmation 2 (Langage C)', 'coefficient' => 1],
                    ['idMatiere' => 410, 'codeMatiere' => 'MPCI1221', 'nomMatiere' => 'Anglais 2', 'coefficient' => 1],
                    ['idMatiere' => 411, 'codeMatiere' => 'MPCI1231', 'nomMatiere' => 'Chimie Physique 1', 'coefficient' => 1],
                    ['idMatiere' => 412, 'codeMatiere' => 'MPCI1232', 'nomMatiere' => 'Chimie Physique 2', 'coefficient' => 1],
                    ['idMatiere' => 413, 'codeMatiere' => 'MPCI1241', 'nomMatiere' => 'Électricité 2', 'coefficient' => 1],
                    ['idMatiere' => 414, 'codeMatiere' => 'MPCI1242', 'nomMatiere' => 'Optique', 'coefficient' => 1],
                    ['idMatiere' => 415, 'codeMatiere' => 'MPCI1251', 'nomMatiere' => 'Algèbre 2', 'coefficient' => 1],
                    ['idMatiere' => 416, 'codeMatiere' => 'MPCI1252', 'nomMatiere' => 'Analyse 2', 'coefficient' => 1],
                ],
                // --- LICENCE 2 (Tronc commun MPI) ---
                'S3' => [
                    ['idMatiere' => 417, 'codeMatiere' => 'MPI2311', 'nomMatiere' => 'Intégrales et Séries', 'coefficient' => 1],
                    ['idMatiere' => 418, 'codeMatiere' => 'MPI2321', 'nomMatiere' => 'Complément d\'algèbre linéaire', 'coefficient' => 1],
                    ['idMatiere' => 419, 'codeMatiere' => 'MPI2331', 'nomMatiere' => 'Calcul Probabilités', 'coefficient' => 1],
                    ['idMatiere' => 420, 'codeMatiere' => 'MPI2341', 'nomMatiere' => 'Mécanique quantique', 'coefficient' => 1],
                    ['idMatiere' => 421, 'codeMatiere' => 'MPCI2342', 'nomMatiere' => 'Thermodynamique physique', 'coefficient' => 1],
                    ['idMatiere' => 422, 'codeMatiere' => 'MPCI2351', 'nomMatiere' => 'Algorithmique et structures de données', 'coefficient' => 1],
                    ['idMatiere' => 423, 'codeMatiere' => 'MPI2352', 'nomMatiere' => 'Anglais scientifique', 'coefficient' => 1],
                ],
                'S4' => [
                    ['idMatiere' => 424, 'codeMatiere' => 'MPI2411', 'nomMatiere' => 'Calcul différentiel et intégral sur IRn', 'coefficient' => 1],
                    ['idMatiere' => 425, 'codeMatiere' => 'MPI2421', 'nomMatiere' => 'Algèbre bilinéaire et sesquilinéaire', 'coefficient' => 1],
                    ['idMatiere' => 426, 'codeMatiere' => 'MPI2431', 'nomMatiere' => 'Calcul numérique', 'coefficient' => 1],
                    ['idMatiere' => 427, 'codeMatiere' => 'MPCI2441', 'nomMatiere' => 'Électromagnétisme dans le vide et relativité restreinte', 'coefficient' => 1],
                    ['idMatiere' => 428, 'codeMatiere' => 'MPCI2442', 'nomMatiere' => 'Mécanique du solide', 'coefficient' => 1],
                    ['idMatiere' => 429, 'codeMatiere' => 'MPI2451', 'nomMatiere' => 'Programmation Orientée Objet en Python', 'coefficient' => 1],
                    ['idMatiere' => 430, 'codeMatiere' => 'MPI2452', 'nomMatiere' => 'Anglais Scientifique IV', 'coefficient' => 1],
                ],
                // --- LICENCE 3 Spécifique SID ---
                'S5' => [
                    ['idMatiere' => 431, 'codeMatiere' => 'SID3511', 'nomMatiere' => 'Sondage', 'coefficient' => 1],
                    ['idMatiere' => 432, 'codeMatiere' => 'SID3512', 'nomMatiere' => 'Modèle Linéaire et Économétrie', 'coefficient' => 1],
                    ['idMatiere' => 433, 'codeMatiere' => 'SID3521', 'nomMatiere' => 'Bases de Données Relationnelles', 'coefficient' => 1],
                    ['idMatiere' => 434, 'codeMatiere' => 'SID3522', 'nomMatiere' => 'Systèmes d\'Information Mathématiques', 'coefficient' => 1],
                    ['idMatiere' => 435, 'codeMatiere' => 'SID3523', 'nomMatiere' => 'Initiation Réseaux', 'coefficient' => 1],
                    ['idMatiere' => 436, 'codeMatiere' => 'SID3531', 'nomMatiere' => 'Recherche Opérationnelle', 'coefficient' => 1],
                    ['idMatiere' => 437, 'codeMatiere' => 'SID3532', 'nomMatiere' => 'Optimisation Convexe', 'coefficient' => 1],
                    ['idMatiere' => 438, 'codeMatiere' => 'SID3541', 'nomMatiere' => 'Environnement d\'Entreprise', 'coefficient' => 2],
                    ['idMatiere' => 439, 'codeMatiere' => 'SID3542', 'nomMatiere' => 'Management Qualité / Marketing', 'coefficient' => 1],
                    ['idMatiere' => 440, 'codeMatiere' => 'SID3543', 'nomMatiere' => 'Technique d\'Expression', 'coefficient' => 1],
                ],
                'S6' => [
                    ['idMatiere' => 441, 'codeMatiere' => 'SID3611', 'nomMatiere' => 'Présence en Entreprise (Stage)', 'coefficient' => 1],
                    ['idMatiere' => 442, 'codeMatiere' => 'SID3612', 'nomMatiere' => 'Rapport de Fin de Cycle', 'coefficient' => 1],
                ]
            ]
        ];

        // Normalisation de l'identifiant filière
        $filiereKey = strtoupper($filiere);
        if ($filiereKey === 'MPCI' || $filiereKey === 'MPC') { $filiereKey = 'PC'; }
        if ($filiereKey === 'D2AW') { $filiereKey = 'D2A'; }

        // Récupération des matières filtrées
        $matieresAffichees = $catalogue[$filiereKey][$semestre] ?? [
            ['idMatiere' => 991, 'codeMatiere' => $filiereKey.'-GEN', 'nomMatiere' => 'Enseignement Généralisé UFR SATIC', 'coefficient' => 1],
        ];

        return response()->json($matieresAffichees);
    }

    /**
     * SIMULATION : Actions Modales (Store / Update / Delete)
     */
    public function store(Request $request) { return response()->json(['success' => true]); }
    public function edit($id) { return response()->json(['idMatiere' => $id, 'codeMatiere' => 'EDIT', 'nomMatiere' => 'Matière modifiée', 'coefficient' => 2]); }
    public function update(Request $request, $id) { return response()->json(['success' => true]); }
    public function destroyAnonyme($id) { return response()->json(['success' => true]); }
}
    
