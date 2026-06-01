<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoadShield RDC | Système de Sécurité Routière Intelligente</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    
    <style>
        :root {
            --primary-color: #dc2626;
            --secondary-color: #1e293b;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-gradient: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            --dark-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-gradient);
            color: #e2e8f0;
            overflow-x: hidden;
        }

        /* Animated Background Particles */
        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(220, 38, 38, 0.3);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        /* Navigation */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-custom .container,
        .hero-section > .container,
        .services-section > .container,
        .stats-section > .container,
        .footer-custom > .container {
            width: 100%;
            max-width: 1140px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .navbar-custom .container {
            display: flex;
            align-items: center;
        }

        .navbar-brand-custom {
            font-size: 1.5rem;
            font-weight: 800;
            color: white !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand-custom:hover {
            color: #f59e0b !important;
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .shield-icon {
            width: 40px;
            height: 40px;
            background: var(--danger-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.9) 0%, 
                rgba(30, 41, 59, 0.8) 50%, 
                rgba(220, 38, 38, 0.1) 100%),
                url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 940px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 925px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #f59e0b;
            animation: slideDown 0.8s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #f59e0b 50%, #dc2626 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: #94a3b8;
            margin-bottom: 2rem;
            max-width: 850px;
            margin-left: auto;
            margin-right: auto;
            animation: slideUp 0.8s ease-out 0.4s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: slideUp 0.8s ease-out 0.6s both;
        }

        .btn-hero-primary {
            background: var(--danger-gradient);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(220, 38, 38, 0.3);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(220, 38, 38, 0.4);
            color: white;
        }

        .btn-hero-secondary {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-hero-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            color: white;
        }

        /* Services Section */
        .services-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            position: relative;
        }

        .section-title {
            font-size: 3rem;
            font-weight: 900;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            text-align: center;
            color: #94a3b8;
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .service-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--danger-gradient);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(220, 38, 38, 0.3);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: var(--danger-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            animation: float 3s ease-in-out infinite;
        }

        .service-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .service-description {
            color: #94a3b8;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-top: 1px solid var(--glass-border);
            border-bottom: 1px solid var(--glass-border);
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            background: var(--danger-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #94a3b8;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Footer */
        .footer-custom {
            background: #0f172a;
            border-top: 1px solid var(--glass-border);
            padding: 2rem 0;
            text-align: center;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .footer-text {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-hero-primary,
            .btn-hero-secondary {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Particles Background -->
    <div class="particles-container" id="particles"></div>

    <!-- Navigation -->
    <nav class="navbar-custom">
        <div class="container">
            <a href="#" class="navbar-brand-custom">
                <div class="shield-icon">
                    <i class="bi bi-shield-fill"></i>
                </div>
                RoadShield <span style="color: #dc2626;">RDC</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-hero-secondary">
                            <i class="bi bi-speedometer2 me-2"></i>Tableau de Bord
                        </a>
                  
                       
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="hero-content text-center">
                        <div class="hero-badge">
                            <i class="bi bi-geo-alt me-2"></i>
                            République Démocratique du Congo • Haut-Katanga
                        </div>
                        <h1 class="hero-title">
                            Système de Sécurité Routière<br>
                            <span style="color: #dc2626;">Intelligente</span>
                        </h1>
                        <p class="hero-subtitle">
                            Plateforme avancée de contrôle d'immatriculation, détection automatique des infractions<br>
                            et gestion centralisée pour des routes plus sûres au Katanga.
                        </p>
                        <div class="hero-buttons">
                            <a href="{{ route('login') }}" class="btn-hero-primary">
                                <i class="bi bi-shield-check me-2"></i>
                                Accéder au Système
                            </a>
                            <a href="#services" class="btn-hero-secondary">
                                <i class="bi bi-info-circle me-2"></i>
                                Découvrir les Services
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Services de Sécurité Routière</h2>
                <p class="section-subtitle">
                    Technologie de pointe pour protéger les usagers de la route et optimiser le contrôle routier
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-camera-fill"></i>
                        </div>
                        <h3 class="service-title">Reconnaissance OCR</h3>
                        <p class="service-description">
                            Détection instantanée des plaques d'immatriculation avec intelligence artificielle. 
                            Identification précise et rapide pour un contrôle efficace en temps réel.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-shield-exclamation"></i>
                        </div>
                        <h3 class="service-title">Détection d'Infractions</h3>
                        <p class="service-description">
                            Signalement automatique des violations routières. Système intelligent pour 
                            la sécurité des agents et le respect du code de la route.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-database-lock"></i>
                        </div>
                        <h3 class="service-title">Base de Données Sécurisée</h3>
                        <p class="service-description">
                            Registre centralisé des véhicules et propriétaires. Accès sécurisé 
                            et consultation rapide pour les autorités compétentes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number" data-target="50000">50K+</div>
                        <div class="stat-label">Véhicules Enregistrés</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number" data-target="1000">1K+</div>
                        <div class="stat-label">Contrôles Quotidiens</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number" data-target="99">99%</div>
                        <div class="stat-label">Précision OCR</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number" data-target="24">24/7</div>
                        <div class="stat-label">Surveillance Active</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container">
            <div class="footer-brand">
                <div class="shield-icon" style="width: 30px; height: 30px;">
                    <i class="bi bi-shield-fill"></i>
                </div>
                RoadShield RDC
            </div>
            <p class="footer-text">
                &copy; 2026 RoadShield RDC • Lubumbashi, Haut-Katanga<br>
                Système de Sécurité Routière Intelligente • Tous droits réservés
            </p>
        </div>
    </footer>

    <script>
        // Generate animated particles
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                container.appendChild(particle);
            }
        }

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(15, 23, 42, 0.95)';
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
            } else {
                navbar.style.background = 'rgba(15, 23, 42, 0.8)';
                navbar.style.boxShadow = 'none';
            }
        });

        // Animate stats on scroll
        function animateStats() {
            const stats = document.querySelectorAll('.stat-number');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const finalValue = target.textContent;
                        target.style.opacity = '0';
                        
                        setTimeout(() => {
                            target.style.opacity = '1';
                            target.style.transform = 'scale(1.1)';
                            setTimeout(() => {
                                target.style.transform = 'scale(1)';
                            }, 300);
                        }, 100);
                        
                        observer.unobserve(target);
                    }
                });
            });
            
            stats.forEach(stat => observer.observe(stat));
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            animateStats();
        });
    </script>
</body>
</html>
