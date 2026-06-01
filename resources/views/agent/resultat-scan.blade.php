<x-app-layout>
    <style>
        .result-container {
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 35%, #2d1b69 70%, #0f172a 100%);
            min-height: 100vh;
            padding: 2rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .result-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 75% 75%, rgba(168, 85, 247, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(236, 72, 153, 0.08) 0%, transparent 60%);
            animation: floatingOrbs 20s ease-in-out infinite;
            pointer-events: none;
        }
        
        @keyframes floatingOrbs {
            0%, 100% { transform: rotate(0deg) scale(1); }
            33% { transform: rotate(120deg) scale(1.1); }
            66% { transform: rotate(240deg) scale(0.9); }
        }
        
        .result-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.8));
            backdrop-filter: blur(25px);
            border: 2px solid transparent;
            border-radius: 24px;
            box-shadow: 
                0 32px 64px -16px rgba(0, 0, 0, 0.6),
                0 0 0 1px rgba(59, 130, 246, 0.2),
                inset 0 0 0 1px rgba(255, 255, 255, 0.1),
                0 0 120px rgba(59, 130, 246, 0.1);
            animation: slideUpGlow 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .result-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent, rgba(59, 130, 246, 0.05), transparent);
            opacity: 0;
            animation: cardGlow 3s ease-in-out infinite alternate;
        }
        
        @keyframes slideUpGlow {
            0% {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
                filter: blur(10px);
            }
            50% {
                opacity: 0.8;
                transform: translateY(10px) scale(0.98);
                filter: blur(5px);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }
        
        @keyframes cardGlow {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        
        .plate-header {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
            border-radius: 24px 24px 0 0;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .plate-header::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: 
                radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%),
                linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: dynamicShimmer 4s ease-in-out infinite;
        }
        
        @keyframes dynamicShimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg) scale(0.5); }
            50% { transform: translateX(50%) translateY(50%) rotate(45deg) scale(1.2); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg) scale(0.5); }
        }
        
        .plate-number {
            font-size: 3rem;
            font-weight: 900;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            color: #ffffff;
            text-shadow: 
                0 0 20px rgba(255, 255, 255, 0.5),
                0 4px 16px rgba(0, 0, 0, 0.4);
            letter-spacing: 0.15em;
            position: relative;
            z-index: 2;
            animation: numberGlow 2s ease-in-out infinite alternate;
        }
        
        @keyframes numberGlow {
            0% { text-shadow: 0 0 20px rgba(255, 255, 255, 0.5), 0 4px 16px rgba(0, 0, 0, 0.4); }
            100% { text-shadow: 0 0 30px rgba(255, 255, 255, 0.8), 0 4px 16px rgba(0, 0, 0, 0.4); }
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            border-radius: 60px;
            font-weight: 800;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            animation: statusPulse 2.5s ease-in-out infinite;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(10px);
        }
        
        .status-badge.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            color: white;
            box-shadow: 
                0 12px 32px -8px rgba(16, 185, 129, 0.4),
                0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        
        .status-badge.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
            color: white;
            box-shadow: 
                0 12px 32px -8px rgba(239, 68, 68, 0.4),
                0 0 0 3px rgba(239, 68, 68, 0.2);
        }
        
        @keyframes statusPulse {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 0 12px 32px -8px rgba(239, 68, 68, 0.4), 0 0 0 3px rgba(239, 68, 68, 0.2);
            }
            50% { 
                transform: scale(1.08); 
                box-shadow: 0 16px 40px -8px rgba(239, 68, 68, 0.6), 0 0 0 6px rgba(239, 68, 68, 0.3);
            }
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-card {
            background: linear-gradient(135deg, rgba(51, 65, 85, 0.6), rgba(71, 85, 105, 0.4));
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }
        
        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .info-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: rgba(59, 130, 246, 0.4);
            box-shadow: 
                0 20px 40px -12px rgba(0, 0, 0, 0.4),
                0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        
        .info-card:hover::before {
            opacity: 1;
        }
        
        .info-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
            color: white;
            box-shadow: 
                0 12px 24px -8px rgba(59, 130, 246, 0.4),
                0 0 0 4px rgba(59, 130, 246, 0.1);
            animation: iconFloat 3s ease-in-out infinite;
        }
        
        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        .document-section {
            background: linear-gradient(135deg, rgba(51, 65, 85, 0.4), rgba(71, 85, 105, 0.3));
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .document-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2rem;
            background: rgba(51, 65, 85, 0.4);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 16px;
            margin-bottom: 1rem;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }
        
        .document-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            transform: scaleY(0);
            transition: transform 0.4s ease;
        }
        
        .document-item.valid::before {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transform: scaleY(1);
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.4);
        }
        
        .document-item.invalid::before {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            transform: scaleY(1);
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.4);
        }
        
        .document-item:hover {
            transform: translateX(8px) scale(1.02);
            background: rgba(51, 65, 85, 0.6);
            box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.3);
        }
        
        .document-status {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
        }
        
        .status-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            animation: iconRotate 4s linear infinite;
        }
        
        @keyframes iconRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .status-icon.valid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 8px 16px -4px rgba(16, 185, 129, 0.4);
        }
        
        .status-icon.invalid {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 8px 16px -4px rgba(239, 68, 68, 0.4);
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2.5rem;
        }
        
        .btn-modern {
            padding: 1.25rem 2.5rem;
            border-radius: 16px;
            font-weight: 700;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: none;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }
        
        .btn-modern:hover::before {
            left: 100%;
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
            color: white;
            box-shadow: 
                0 12px 32px -8px rgba(59, 130, 246, 0.4),
                0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        
        .btn-primary-modern:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 
                0 20px 48px -12px rgba(59, 130, 246, 0.5),
                0 0 0 4px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary-modern {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.3), rgba(71, 85, 105, 0.4));
            color: white;
            border: 2px solid rgba(148, 163, 184, 0.4);
        }
        
        .btn-secondary-modern:hover {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.4), rgba(71, 85, 105, 0.5));
            transform: translateY(-4px) scale(1.05);
            border-color: rgba(59, 130, 246, 0.4);
        }
        
        @media (max-width: 768px) {
            .plate-number {
                font-size: 2rem;
                letter-spacing: 0.1em;
            }
            
            .result-card {
                margin: 1rem;
                border-radius: 20px;
            }
            
            .plate-header {
                padding: 2rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .status-badge {
                padding: 0.75rem 1.5rem;
                font-size: 0.8rem;
            }
        }
    </style>
    
    <div class="result-container">
        <div class="container">
            <div class="result-card">
                <!-- En-tête avec plaque et statut -->
                <div class="plate-header">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="plate-number">{{ $vehicule->plaque_immatriculation }}</div>
                            <div class="text-white-50 mt-3">
                                <small><i class="bi bi-clock-fill me-2"></i>Scanné le {{ now()->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="status-badge {{ $vehicule->en_regle ? 'success' : 'danger' }}">
                                <i class="bi {{ $vehicule->en_regle ? 'bi-shield-fill-check' : 'bi-shield-fill-exclamation' }}"></i>
                                {{ $vehicule->en_regle ? 'EN RÈGLE' : 'EN INFRACTION' }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Corps avec informations -->
                <div class="p-4">
                    <div class="info-grid">
                        <!-- Propriétaire -->
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                            <h6 class="text-white mb-2">Propriétaire</h6>
                            <p class="text-white fw-bold mb-1">{{ $vehicule->proprietaire->nom }}</p>
                            <p class="text-white-50 small mb-0">{{ $vehicule->proprietaire->email ?? '' }}</p>
                        </div>
                        
                        <!-- Véhicule -->
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="bi bi-car-front-fill"></i>
                            </div>
                            <h6 class="text-white mb-2">Véhicule</h6>
                            <p class="text-white fw-bold mb-1">{{ $vehicule->marque }} {{ $vehicule->modele }}</p>
                            <p class="text-white-50 small mb-0">{{ $vehicule->couleur ?? '' }} • {{ $vehicule->annee ?? '' }}</p>
                        </div>
                    </div>
                    
                    <!-- Documents -->
                    <div class="document-section">
                        <h6 class="text-white mb-4">
                            <i class="bi bi-clipboard-check-fill me-2"></i>
                            Vérification des documents
                        </h6>
                        
                        <div class="document-item {{ $vehicule->has_assurance ? 'valid' : 'invalid' }}">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-shield-fill-check text-white me-3"></i>
                                <div>
                                    <div class="text-white fw-semibold">Assurance (SONAS)</div>
                                    <div class="text-white-50 small">Vérification automatique</div>
                                </div>
                            </div>
                            <div class="document-status">
                                <div class="status-icon {{ $vehicule->has_assurance ? 'valid' : 'invalid' }}">
                                    <i class="bi {{ $vehicule->has_assurance ? 'bi-check-lg' : 'bi-x-lg' }}"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="document-item {{ $vehicule->has_vignette ? 'valid' : 'invalid' }}">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-receipt-cutoff text-white me-3"></i>
                                <div>
                                    <div class="text-white fw-semibold">Vignette Fiscale</div>
                                    <div class="text-white-50 small">Validité en cours</div>
                                </div>
                            </div>
                            <div class="document-status">
                                <div class="status-icon {{ $vehicule->has_vignette ? 'valid' : 'invalid' }}">
                                    <i class="bi {{ $vehicule->has_vignette ? 'bi-check-lg' : 'bi-x-lg' }}"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="action-buttons">
                        <button class="btn-modern btn-secondary-modern" onclick="window.history.back()">
                            <i class="bi bi-arrow-left-circle-fill me-2"></i>
                            Nouveau scan
                        </button>
                        @if(!$vehicule->en_regle)
                        <button class="btn-modern btn-primary-modern" onclick="window.location.href='{{ route('infractions.create', ['plaque' => $vehicule->plaque_immatriculation]) }}'">
                            <i class="bi bi-plus-circle-fill me-2"></i>
                            Créer infraction
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>