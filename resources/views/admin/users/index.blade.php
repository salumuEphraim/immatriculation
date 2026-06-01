<x-app-layout>
    <x-slot name="header">
        <div class="admin-modern-header">
            <div>
                <span class="admin-modern-eyebrow">Administration</span>
                <h2 class="h3 mb-1 fw-bold text-white">
                    <i class="bi bi-people-fill me-2"></i>Gestion des utilisateurs
                </h2>
                <p class="mb-0 text-white-50">Creation des comptes, roles et acces au systeme.</p>
            </div>
            <button type="button" class="admin-modern-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus-fill"></i>
                Nouvel utilisateur
            </button>
        </div>
    </x-slot>

    @php
        $roles = [
            'admin' => ['label' => 'Administrateurs', 'icon' => 'bi-shield-lock-fill'],
            'agent' => ['label' => 'Agents', 'icon' => 'bi-person-badge-fill'],
            'proprietaire' => ['label' => 'Proprietaires', 'icon' => 'bi-car-front-fill'],
        ];
    @endphp

    <div class="admin-modern-page">
        <section class="admin-modern-stats">
            <div class="admin-modern-stat">
                <span>Total</span>
                <strong>{{ $users->count() }}</strong>
                <i class="bi bi-people"></i>
            </div>
            @foreach($roles as $role => $meta)
                <div class="admin-modern-stat is-{{ $role }}">
                    <span>{{ $meta['label'] }}</span>
                    <strong>{{ $users->where('role', $role)->count() }}</strong>
                    <i class="bi {{ $meta['icon'] }}"></i>
                </div>
            @endforeach
        </section>

        @if(session('success'))
            <div class="admin-modern-alert is-success">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-modern-alert is-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="admin-modern-alert is-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Verifiez les champs du formulaire utilisateur.
            </div>
        @endif

        <section class="admin-modern-panel">
            <div class="admin-modern-panel-head">
                <div>
                    <h3>Comptes utilisateurs</h3>
                    <p>{{ $users->count() }} compte(s) enregistre(s)</p>
                </div>
            </div>

            <div class="admin-user-list">
                @forelse($users as $user)
                    @php
                        $initials = collect(explode(' ', trim($user->name)))
                            ->filter()
                            ->map(fn ($part) => mb_substr($part, 0, 1))
                            ->take(2)
                            ->implode('');
                    @endphp
                    <article class="admin-user-item">
                        <div class="admin-user-avatar is-{{ $user->role }}">
                            {{ $initials ?: 'U' }}
                        </div>

                        <div class="admin-user-main">
                            <div class="admin-user-title">
                                <h4>{{ $user->name }}</h4>
                                <span class="admin-role-pill is-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                            </div>
                            <div class="admin-user-meta">
                                <span><i class="bi bi-envelope"></i>{{ $user->email }}</span>
                                <span><i class="bi bi-calendar3"></i>{{ $user->created_at->format('d/m/Y') }}</span>
                                @if($user->telephone)
                                    <span><i class="bi bi-telephone"></i>{{ $user->telephone }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="admin-user-actions">
                            <form action="{{ route('admin.users.updateRole', $user) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="role" class="admin-modern-select" onchange="this.form.submit()" @disabled(Auth::id() === $user->id)>
                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    <option value="agent" @selected($user->role === 'agent')>Agent</option>
                                    <option value="proprietaire" @selected($user->role === 'proprietaire')>Proprietaire</option>
                                </select>
                            </form>

                            @if(Auth::id() !== $user->id)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Supprimer definitivement ce compte ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-modern-icon-btn is-danger" title="Supprimer">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="admin-modern-empty">
                        <i class="bi bi-inbox"></i>
                        <p>Aucun utilisateur trouve.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content admin-modern-modal">
                <form action="{{ route('admin.accounts.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="createUserModalLabel">
                                <i class="bi bi-person-plus-fill me-2"></i>Ajouter un utilisateur
                            </h5>
                            <p class="mb-0">Creez un compte admin, agent ou proprietaire.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>

                    <div class="modal-body">
                        <div class="admin-modern-form-grid">
                            <div class="admin-modern-field is-wide">
                                <label for="name">Nom complet</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-person"></i>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Ex: Jean Mukendi">
                                </div>
                            </div>

                            <div class="admin-modern-field">
                                <label for="email">Adresse email</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-envelope"></i>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="nom@example.com">
                                </div>
                            </div>

                            <div class="admin-modern-field">
                                <label for="role_selector">Role</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-shield-check"></i>
                                    <select name="role" id="role_selector" required>
                                        <option value="" disabled @selected(!old('role'))>Selectionner...</option>
                                        <option value="agent" @selected(old('role') === 'agent')>Agent</option>
                                        <option value="admin" @selected(old('role') === 'admin')>Administrateur</option>
                                        <option value="proprietaire" @selected(old('role') === 'proprietaire')>Proprietaire</option>
                                    </select>
                                </div>
                            </div>

                            <div class="admin-modern-field">
                                <label for="password">Mot de passe</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-lock"></i>
                                    <input type="password" name="password" id="password" required autocomplete="new-password">
                                </div>
                            </div>

                            <div class="admin-modern-field">
                                <label for="password_confirmation">Confirmation</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-lock-fill"></i>
                                    <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>

                            <div class="admin-modern-owner-fields is-wide" id="proprietaire_fields">
                                <div class="admin-modern-field">
                                    <label for="prenom">Prenom</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-person-vcard"></i>
                                        <input type="text" name="prenom" id="prenom" value="{{ old('prenom') }}" placeholder="Prenom">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="postnom">Postnom</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-person-vcard-fill"></i>
                                        <input type="text" name="postnom" id="postnom" value="{{ old('postnom') }}" placeholder="Postnom">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="telephone">Telephone</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-telephone"></i>
                                        <input type="text" name="telephone" id="telephone" value="{{ old('telephone') }}" placeholder="+243812345678">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="commune">Commune</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-signpost"></i>
                                        <input type="text" name="commune" id="commune" value="{{ old('commune') }}" placeholder="Ex: Lubumbashi">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="quartier">Quartier</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-pin-map"></i>
                                        <input type="text" name="quartier" id="quartier" value="{{ old('quartier') }}" placeholder="Quartier / avenue">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="numero_identite">Numero d'identite</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-credit-card-2-front"></i>
                                        <input type="text" name="numero_identite" id="numero_identite" value="{{ old('numero_identite') }}" placeholder="Carte, passeport, NIU...">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="sexe">Sexe</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-person-standing"></i>
                                        <select name="sexe" id="sexe">
                                            <option value="" @selected(!old('sexe'))>Non renseigne</option>
                                            <option value="masculin" @selected(old('sexe') === 'masculin')>Masculin</option>
                                            <option value="feminin" @selected(old('sexe') === 'feminin')>Feminin</option>
                                            <option value="autre" @selected(old('sexe') === 'autre')>Autre</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="date_naissance">Date de naissance</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-calendar-event"></i>
                                        <input type="date" name="date_naissance" id="date_naissance" value="{{ old('date_naissance') }}">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="lieu_naissance">Lieu de naissance</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-geo"></i>
                                        <input type="text" name="lieu_naissance" id="lieu_naissance" value="{{ old('lieu_naissance') }}" placeholder="Ville / territoire">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="nationalite">Nationalite</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-flag"></i>
                                        <input type="text" name="nationalite" id="nationalite" value="{{ old('nationalite', 'Congolaise') }}" placeholder="Congolaise">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="profession">Profession</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-briefcase"></i>
                                        <input type="text" name="profession" id="profession" value="{{ old('profession') }}" placeholder="Profession">
                                    </div>
                                </div>

                                <div class="admin-modern-field">
                                    <label for="adresse">Adresse</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-geo-alt"></i>
                                        <input type="text" name="adresse" id="adresse" value="{{ old('adresse') }}" placeholder="Lubumbashi, commune...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="admin-modern-primary">
                            <i class="bi bi-check-circle"></i>
                            Creer le compte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelector = document.getElementById('role_selector');
            const ownerFields = document.getElementById('proprietaire_fields');
            const phoneInput = document.getElementById('telephone');

            function toggleOwnerFields() {
                const isOwner = roleSelector.value === 'proprietaire';
                ownerFields.classList.toggle('is-visible', isOwner);
                phoneInput.toggleAttribute('required', isOwner);
            }

            roleSelector.addEventListener('change', toggleOwnerFields);
            toggleOwnerFields();

            @if($errors->any())
                new bootstrap.Modal(document.getElementById('createUserModal')).show();
            @endif
        });
    </script>
</x-app-layout>
