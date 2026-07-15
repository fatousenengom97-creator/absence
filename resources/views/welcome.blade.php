<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UFR SATIC — Gestion des Absences de l' UFR SATIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1a3a5c;
            --accent:  #2e86de;
        }
        body { font-family: 'Segoe UI', sans-serif; color: #1a1a2e; }

        /* Navbar */
        .navbar-satic {
            background: #fff;
            box-shadow: 0 1px 10px rgba(0,0,0,.05);
            padding: 1rem 0;
        }
        .navbar-satic .brand { font-weight: 700; color: var(--primary); font-size: 1.2rem; }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: #fff;
            padding: 6rem 0 7rem;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 600px; height: 600px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
        }
        .hero .badge-pill {
            background: rgba(255,255,255,.15);
            padding: .4rem 1rem;
            border-radius: 30px;
            font-size: .85rem;
            font-weight: 600;
        }
        .hero h1 { font-size: 2.6rem; font-weight: 800; line-height: 1.2; }
        .hero p.lead { font-size: 1.1rem; opacity: .92; max-width: 560px; margin: 1.5rem auto; }
        .btn-hero-primary {
            background: #fff; color: var(--primary); font-weight: 700;
            padding: .8rem 2rem; border-radius: 10px; border: none;
        }
        .btn-hero-primary:hover { background: #f0f4f8; color: var(--primary); }
        .btn-hero-outline {
            border: 2px solid rgba(255,255,255,.6); color: #fff;
            padding: .8rem 2rem; border-radius: 10px; font-weight: 700;
        }
        .btn-hero-outline:hover { background: rgba(255,255,255,.1); color: #fff; }

        /* Sections */
        .section-title { text-align: center; margin-bottom: 3rem; }
        .section-title h2 { font-weight: 800; color: var(--primary); font-size: 2rem; }
        .section-title p { color: #6b7280; font-size: 1.05rem; }
        .section-padding { padding: 5rem 0; }

        /* Feature cards */
        .feature-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            height: 100%;
            border: 1px solid #eef1f5;
            transition: all .25s;
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(26,58,92,.1);
            border-color: transparent;
        }
        .feature-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.2rem;
        }
        .feature-card h5 { font-weight: 700; margin-bottom: .7rem; }
        .feature-card p { color: #6b7280; font-size: .92rem; margin: 0; }

        /* How it works */
        .step-circle {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.3rem;
            margin: 0 auto 1.2rem;
        }
        .how-it-works { background: #f8fafc; }
        .step-box { text-align: center; padding: 1rem; }
        .step-box h5 { font-weight: 700; margin-bottom: .6rem; }
        .step-box p { color: #6b7280; font-size: .92rem; }

        /* CTA */
        .cta-section {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            border-radius: 24px;
            padding: 3.5rem 2rem;
            text-align: center;
            margin: 0 1rem;
        }
        .cta-section h2 { font-weight: 800; }

        /* Footer */
        footer { background: var(--primary); color: rgba(255,255,255,.75); padding: 2rem 0; }
        footer .brand { color: #fff; font-weight: 700; }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-satic navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand brand" href="{{ url('/') }}">
            <i class="bi bi-mortarboard-fill me-2"></i>UFR SATIC
        </a>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-outline-primary px-4">Connexion</a>
            {{-- Décommente si tu as une route register --}}
            {{-- <a href="{{ route('register') }}" class="btn btn-primary px-4">Inscription</a> --}}
        </div>
    </div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero text-center">
    <div class="container position-relative">
        <span class="badge-pill mb-3 d-inline-block">La gestion des absences de l' UFR SATIC</span>
        <h1 class="mb-3">Suivez les présences et les absences de l' UFR SATIC </h1>
        <p class="lead mx-auto">
              <strong>UFR SATIC-UADB</strong>  modernise le suivi pédagogique 
              avec une solution sécurisée et automatisée de gestion des absences, s' appuyant
               sur l' Intelligence Artificielle pour 
              la Reconnaissance faciale. Opptimisez les présences et l' excellence academique
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
            <a href="{{ route('login') }}" class="btn-hero-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i>Commencer maintenant
            </a>
            <a href="#features" class="btn-hero-outline">
                En savoir plus
            </a>
        </div>
    </div>
</section>

<!-- ===== FEATURES ===== -->
<section id="features" class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Tout ce dont vous avez besoin</h2>
            <p>Une plateforme complète pour gérer les absences académiques.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#dbeafe;color:#1e40af;">
                        <i class="bi bi-camera-video"></i>
                    </div>
                    <h5>Reconnaissance faciale</h5>
                    <p>Pointage automatique des étudiants par caméra en temps réel, sans contact et sans triche possible.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#ede9fe;color:#7c3aed;">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5>Gestion des utilisateurs</h5>
                    <p>Administrateurs, professeurs, étudiants et chef de service pédagogique, chacun avec son espace dédié.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#d1fae5;color:#065f46;">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <h5>Planification des cours</h5>
                    <p>Créez classes, matières, salles et emplois du temps, puis générez les feuilles de présence automatiquement.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#fee2e2;color:#991b1b;">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h5>Suivi des absences</h5>
                    <p>Visualisez présences, absences, retards et justifications en temps réel pour chaque étudiant.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#fef3c7;color:#92400e;">
                        <i class="bi bi-bell"></i>
                    </div>
                    <h5>Alertes intelligentes</h5>
                    <p>Le chef de service pédagogique est automatiquement notifié des étudiants à risque d'exclusion.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#e0f2fe;color:#075985;">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <h5>Tableau de bord</h5>
                    <p>Indicateurs clés, statistiques par classe et export PDF des rapports en un seul clic.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="how-it-works section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Comment ça marche</h2>
            <p>Trois étapes simples pour démarrer le suivi des présences.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-box">
                    <div class="step-circle">1</div>
                    <h5>Connexion sécurisée</h5>
                    <p>Chaque utilisateur accède à son espace personnel selon son rôle (admin, prof, étudiant, chef de service).</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-box">
                    <div class="step-circle">2</div>
                    <h5>Enregistrement biométrique</h5>
                    <p>L'étudiant enregistre son visage une seule fois via la caméra. Le système le reconnaîtra ensuite automatiquement.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-box">
                    <div class="step-circle">3</div>
                    <h5>Pointage automatique</h5>
                    <p>Pendant le cours, la caméra reconnaît chaque étudiant et enregistre sa présence en quelques secondes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="section-padding">
    <div class="container">
        <div class="cta-section">
            <h2 class="mb-3">Prêt à moderniser le suivi des absences ?</h2>
            <p class="mb-4" style="opacity:.9;">Rejoignez la plateforme de l'UFR SATIC dès aujourd'hui et simplifiez la gestion des présences.</p>
            <a href="{{ route('login') }}" class="btn-hero-primary">
                <i class="bi bi-arrow-right-circle me-2"></i>Accéder à mon espace
            </a>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
    <div class="container text-center">
        <p class="brand mb-1"><i class="bi bi-mortarboard-fill me-2"></i>UFR SATIC</p>
        <p class="small mb-0">© {{ date('Y') }} UFR SATIC — Tous droits réservés.</p>
    </div>
</footer>

</body>
</html>
