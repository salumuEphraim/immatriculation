<x-app-layout>
    <x-slot name="header">
        <div class="profile-pro-header">
            <div>
                <span class="profile-pro-eyebrow">Espace {{ ucfirst($user->role ?? 'utilisateur') }}</span>
                <h2 class="h3 mb-1 fw-bold text-white">
                    <i class="bi bi-person-badge me-2"></i>Mon profil
                </h2>
                <p class="mb-0 text-white-50">Informations du compte, securite et preferences d'acces.</p>
            </div>
            <span class="profile-pro-status">
                <i class="bi bi-shield-check"></i>
                Compte actif
            </span>
        </div>
    </x-slot>

    @php
        $initials = collect(explode(' ', trim($user->name ?? 'Utilisateur')))
            ->filter()
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->take(2)
            ->implode('');
        $proprietaire = $user->proprietaire ?? null;
    @endphp

    <div class="profile-pro">
        <section class="profile-pro-hero">
            <div class="profile-pro-avatar">{{ $initials ?: 'U' }}</div>
            <div class="profile-pro-identity">
                <span class="profile-pro-role">{{ ucfirst($user->role ?? 'utilisateur') }}</span>
                <h3>{{ $user->name }}</h3>
                <p>{{ $user->email }}</p>
            </div>
            <div class="profile-pro-meta">
                <div>
                    <span>Vehicules</span>
                    <strong>{{ $user->vehicules()->count() }}</strong>
                </div>
                <div>
                    <span>Depuis</span>
                    <strong>{{ $user->created_at?->format('m/Y') ?? '-' }}</strong>
                </div>
                <div>
                    <span>Verification</span>
                    <strong>{{ $user->email_verified_at ? 'OK' : 'A faire' }}</strong>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-12 col-xl-4">
                <aside class="profile-pro-side">
                    <div class="profile-pro-side-section">
                        <h4>Coordonnees</h4>
                        <div class="profile-pro-info-line">
                            <i class="bi bi-envelope"></i>
                            <span>{{ $user->email }}</span>
                        </div>
                        <div class="profile-pro-info-line">
                            <i class="bi bi-telephone"></i>
                            <span>{{ $proprietaire?->telephone ?: ($user->telephone ?: 'Telephone non renseigne') }}</span>
                        </div>
                        <div class="profile-pro-info-line">
                            <i class="bi bi-geo-alt"></i>
                            <span>{{ $proprietaire?->adresse ?: 'Adresse non renseignee' }}</span>
                        </div>
                        <div class="profile-pro-info-line">
                            <i class="bi bi-person-vcard"></i>
                            <span>{{ $user->niuu ?: 'NIUU non renseigne' }}</span>
                        </div>
                    </div>

                    @if($proprietaire)
                        <div class="profile-pro-side-section">
                            <h4>Profil proprietaire</h4>
                            <div class="profile-pro-info-line">
                                <i class="bi bi-person-lines-fill"></i>
                                <span>{{ trim(($proprietaire->prenom ?? '') . ' ' . ($proprietaire->nom ?? '')) ?: 'Identite incomplete' }}</span>
                            </div>
                            <div class="profile-pro-info-line">
                                <i class="bi bi-car-front"></i>
                                <span>{{ $user->vehicules()->count() }} vehicule(s) rattache(s)</span>
                            </div>
                        </div>
                    @endif

                    <div class="profile-pro-side-note">
                        <i class="bi bi-info-circle"></i>
                        <span>Gardez vos informations a jour pour recevoir les recus, alertes d'expiration et confirmations de paiement.</span>
                    </div>
                </aside>
            </div>

            <div class="col-12 col-xl-8">
                <div class="profile-pro-stack">
                    <div class="profile-pro-panel">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <div class="profile-pro-panel">
                        @include('profile.partials.update-password-form')
                    </div>

                    <div class="profile-pro-panel profile-pro-danger">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
