<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 mb-0 fw-bold text-white">
                <i class="bi bi-clock-history me-2 text-warning"></i>
                Suivi des Expirations de Documents
            </h2>
            <div class="d-flex gap-2">
                <button onclick="refreshStats()" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Actualiser
                </button>
                <form action="{{ route('admin.expirations.process') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-bell me-1"></i> Traiter les notifications
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <style>
        .expirations-dashboard {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .stats-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(51, 65, 85, 0.95) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #8b5cf6, #7c3aed);
        }

        .stats-card.danger::before {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        .stats-card.warning::before {
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }

        .stats-card.success::before {
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .stats-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-card.danger .stats-number {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-card.warning .stats-number {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-card.success .stats-number {
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-label {
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stats-sublabel {
            color: #64748b;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .documents-table {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(51, 65, 85, 0.95) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .documents-table .table {
            background: transparent;
            color: #e2e8f0;
        }

        .documents-table .table thead th {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #67e8f9;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .documents-table .table tbody tr {
            background: rgba(255, 255, 255, 0.02);
            transition: all 0.3s ease;
        }

        .documents-table .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(4px);
        }

        .days-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .days-badge.expired {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .days-badge.critical {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            animation: pulse 2s infinite;
        }

        .days-badge.warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .days-badge.good {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .notification-badge {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { opacity: 0.8; }
            50% { opacity: 1; }
            100% { opacity: 0.8; }
        }
    </style>

    <div class="expirations-dashboard">
        <!-- Statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stats-card h-100 p-4 text-center">
                    <div class="stats-number">{{ $stats['total_vehicles'] }}</div>
                    <div class="stats-label mt-2">Total véhicules</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card danger h-100 p-4 text-center">
                    <div class="stats-number">{{ $stats['vehicles_with_expired_docs'] }}</div>
                    <div class="stats-label mt-2">Documents expirés</div>
                    <div class="stats-sublabel">{{ $stats['total_expired_documents'] }} documents</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card warning h-100 p-4 text-center">
                    <div class="stats-number">{{ $stats['vehicles_with_expiring_soon'] }}</div>
                    <div class="stats-label mt-2">Expirant bientôt</div>
                    <div class="stats-sublabel">{{ $stats['total_expiring_soon_documents'] }} documents</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success h-100 p-4 text-center">
                    <div class="stats-number">{{ $stats['vehicles_all_valid'] }}</div>
                    <div class="stats-label mt-2">Véhicules en règle</div>
                </div>
            </div>
        </div>

        <!-- Tableau des véhicules -->
        <div class="documents-table mb-4">
            <div class="p-4">
                <h5 class="mb-4 fw-bold text-white">
                    <i class="bi bi-list-ul me-2 text-cyan-400"></i>
                    Détail par véhicule
                </h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Véhicule</th>
                                <th>Propriétaire</th>
                                <th>Total documents</th>
                                <th>Expirés</th>
                                <th>Expirant bientôt</th>
                                <th>Valides</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summaries as $index => $summary)
                                @php
                                    $vehicle = $vehicles[$index];
                                    $hasIssues = $summary['expired'] > 0 || $summary['expiring_soon'] > 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-cyan-300">{{ $vehicle->marque }} {{ $vehicle->modele }}</div>
                                        <div class="text-muted small">{{ $vehicle->plaque_immatriculation }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $vehicle->proprietaire->nom ?? 'N/A' }}</div>
                                        <div class="text-muted small">{{ $vehicle->proprietaire->user->email ?? 'Pas d\'email' }}</div>
                                    </td>
                                    <td>
                                        <span class="days-badge good">{{ $summary['total_documents'] }}</span>
                                    </td>
                                    <td>
                                        @if($summary['expired'] > 0)
                                            <span class="days-badge expired">{{ $summary['expired'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($summary['expiring_soon'] > 0)
                                            <span class="days-badge warning">{{ $summary['expiring_soon'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="days-badge good">{{ $summary['valid'] }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.expirations.show', $vehicle->id) }}" 
                                           class="btn btn-sm btn-outline-light">
                                            <i class="bi bi-eye me-1"></i> Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tableau en temps réel des documents avec jours restants -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-clock me-2 text-primary"></i>
                    Documents en Temps Réel
                    <span class="badge bg-success ms-2" id="live-count">Chargement...</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small" id="last-update">Dernière mise à jour: --</span>
                    <button onclick="toggleAutoRefresh()" id="refresh-toggle" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-play-circle me-1"></i> Auto-rafraîchissement
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="documents-table">
                        <thead class="table-light">
                            <tr>
                                <th>Véhicule</th>
                                <th>Document</th>
                                <th>Propriétaire</th>
                                <th>Date expiration</th>
                                <th>Jours restants</th>
                                <th>Statut</th>
                                <th>Notification</th>
                            </tr>
                        </thead>
                        <tbody id="documents-tbody">
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Chargement des documents en temps réel...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let autoRefreshInterval = null;
        let isAutoRefresh = false;

        // Fonction pour charger les documents en temps réel
        function loadRealtimeDocuments() {
            fetch('{{ route('admin.expirations.realtime') }}')
                .then(response => response.json())
                .then(data => {
                    updateDocumentsTable(data.documents);
                    updateLastUpdateTime(data.timestamp);
                    updateDocumentCount(data.total_count);
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des documents:', error);
                    showError('Erreur lors du chargement des documents en temps réel');
                });
        }

        // Fonction pour mettre à jour le tableau des documents
        function updateDocumentsTable(documents) {
            const tbody = document.getElementById('documents-tbody');
            
            if (documents.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <p>Aucun document trouvé</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = documents.map(doc => {
                const rowClass = doc.days_remaining < 0 ? 'table-danger' : 
                                (doc.days_remaining <= 7 ? 'table-danger' : 
                                (doc.days_remaining <= 30 ? 'table-warning' : ''));
                
                const statusBadge = getStatusBadge(doc.status, doc.days_remaining);
                const notificationBadge = getNotificationBadge(doc.days_remaining);
                const daysClass = getDaysClass(doc.days_remaining);

                return `
                    <tr class="${rowClass}">
                        <td>
                            <div class="fw-bold">${doc.vehicle_info.marque} ${doc.vehicle_info.modele}</div>
                            <div class="text-muted small">${doc.vehicle_info.plaque}</div>
                        </td>
                        <td>
                            <span class="badge bg-info">${ucfirst(doc.document_type.replace('_', ' '))}</span>
                        </td>
                        <td>
                            <div>${doc.vehicle_info.proprietaire}</div>
                            <div class="text-muted small">${doc.vehicle_info.proprietaire_email || 'Pas d\'email'}</div>
                        </td>
                        <td>${doc.expiration_date || 'N/A'}</td>
                        <td>
                            <span class="${daysClass} fw-bold">
                                ${doc.formatted_days}
                            </span>
                        </td>
                        <td>${statusBadge}</td>
                        <td>${notificationBadge}</td>
                    </tr>
                `;
            }).join('');
        }

        // Fonction pour obtenir le badge de statut
        function getStatusBadge(status, daysRemaining) {
            if (daysRemaining < 0) {
                return '<span class="badge bg-danger">Expiré</span>';
            }
            if (daysRemaining <= 7) {
                return '<span class="badge bg-danger">Moins de 7 jours</span>';
            }
            if (daysRemaining <= 30) {
                return '<span class="badge bg-warning">8 à 30 jours</span>';
            }
            return '<span class="badge bg-success">Valide</span>';
        }

        // Fonction pour obtenir le badge de notification
        function getNotificationBadge(daysRemaining) {
            if (daysRemaining >= 0 && daysRemaining <= 7) {
                return '<span class="badge bg-danger">🔔 Moins de 7 jours</span>';
            }
            return '<span class="text-muted">-</span>';
        }

        function ucfirst(value) {
            return value.charAt(0).toUpperCase() + value.slice(1);
        }

        // Fonction pour obtenir la classe CSS des jours restants
        function getDaysClass(days) {
            if (days < 0) return 'text-danger';
            if (days <= 7) return 'text-danger fw-bold';
            if (days <= 30) return 'text-warning';
            return 'text-success';
        }

        // Fonction pour mettre à jour l'heure de dernière mise à jour
        function updateLastUpdateTime(timestamp) {
            const date = new Date(timestamp);
            const formatted = date.toLocaleString('fr-FR');
            document.getElementById('last-update').textContent = `Dernière mise à jour: ${formatted}`;
        }

        // Fonction pour mettre à jour le compteur de documents
        function updateDocumentCount(count) {
            document.getElementById('live-count').textContent = count;
        }

        // Fonction pour activer/désactiver le rafraîchissement automatique
        function toggleAutoRefresh() {
            const button = document.getElementById('refresh-toggle');
            
            if (isAutoRefresh) {
                clearInterval(autoRefreshInterval);
                isAutoRefresh = false;
                button.innerHTML = '<i class="bi bi-play-circle me-1"></i> Auto-rafraîchissement';
                button.className = 'btn btn-sm btn-outline-success';
            } else {
                autoRefreshInterval = setInterval(loadRealtimeDocuments, 30000); // Toutes les 30 secondes
                isAutoRefresh = true;
                button.innerHTML = '<i class="bi bi-pause-circle me-1"></i> Arrêter';
                button.className = 'btn btn-sm btn-outline-danger';
            }
        }

        // Fonction pour afficher une erreur
        function showError(message) {
            const tbody = document.getElementById('documents-tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                        <p>${message}</p>
                    </td>
                </tr>
            `;
        }

        // Fonction pour rafraîchir les statistiques principales
        function refreshStats() {
            fetch('{{ route('admin.expirations.stats') }}')
                .then(response => response.json())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
        }

        // Charger les données au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadRealtimeDocuments();
        });
    </script>
</x-app-layout>
