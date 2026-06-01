<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | RoadShield RDC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Animated background particles */
        .bg-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(139, 92, 246, 0.3);
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

        /* Main container */
        .login-container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Login card */
        .login-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(51, 65, 85, 0.95) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 120px rgba(139, 92, 246, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            transition: all 0.3s ease;
            animation: slideUp 0.6s ease-out;
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

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4), 0 0 140px rgba(139, 92, 246, 0.15);
        }

        /* Logo section */
        .logo-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 40px rgba(139, 92, 246, 0.4);
            }
        }

        .logo-title {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .logo-subtitle {
            color: #94a3b8;
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Form styles */
        .form-label {
            color: #e2e8f0;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-group {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(139, 92, 246, 0.5);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: none;
            color: #64748b;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .input-group:focus-within .input-group-text {
            color: #8b5cf6;
        }

        .form-control {
            background: transparent;
            border: none;
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .form-control:focus {
            background: transparent;
            color: white;
            box-shadow: none;
        }

        /* Checkbox */
        .form-check-input {
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-color: transparent;
        }

        .form-check-label {
            color: #94a3b8;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .forgot-link {
            color: #8b5cf6;
            font-size: 0.875rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .forgot-link:hover {
            color: #7c3aed;
            text-decoration: underline;
        }

        /* Submit button */
        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
        }

        /* Error alert */
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #ef4444;
            font-size: 0.875rem;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .alert-danger ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .alert-danger li {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
        }

        .alert-danger li:last-child {
            margin-bottom: 0;
        }

        /* Register link */
        .register-section {
            text-align: center;
            margin-top: 2rem;
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .register-link {
            color: #8b5cf6;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .register-link:hover {
            color: #7c3aed;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 1rem;
            }
            
            .login-card {
                padding: 2rem;
            }
            
            .logo-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated particles background -->
    <div class="bg-particles" id="particles"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Logo section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h1 class="logo-title">ROADSHIELD</h1>
                <p class="logo-subtitle">Système d'Immatriculation RDC</p>
            </div>

            <!-- Error alert -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>
                                <i class="bi bi-exclamation-circle me-2"></i>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Login form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="email">Adresse Email</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" 
                               id="email"
                               name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               placeholder="nom@exemple.com" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-key"></i>
                        </span>
                        <input type="password" 
                               id="password"
                               name="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               placeholder="••••••••" 
                               required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input shadow-none" id="remember_me" name="remember">
                        <label class="form-check-label" for="remember_me">Se souvenir de moi</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold py-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Se connecter
                    </button>
                </div>
            </form>

            <!-- Register link -->
            <div class="register-section">
                Pas encore de compte ? 
                <a href="{{ route('register') }}" class="register-link">Créer un compte</a>
            </div>
        </div>
    </div>

    <script>
        // Create animated particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Initialize particles on page load
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
        });
    </script>
</body>
</html>