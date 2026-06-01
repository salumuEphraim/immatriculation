<section>
    <header class="profile-form-head">
        <span><i class="bi bi-person-gear"></i></span>
        <div>
            <h2>{{ __('Informations du profil') }}</h2>
            <p>{{ __("Mettez a jour votre nom et votre email.") }}</p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="profile-pro-form">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="form-label">{{ __('Nom') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="small mb-1">{{ __('Votre adresse email n est pas verifiee.') }}</p>
                    <button form="send-verification" type="submit" class="btn btn-outline-secondary btn-sm">
                        {{ __('Renvoyer la verification') }}
                    </button>
                    @if (session('status') === 'verification-link-sent')
                        <div class="text-success small mt-2">{{ __('Un nouveau lien a ete envoye.') }}</div>
                    @endif
                </div>
            @endif
        </div>

        @if($user->proprietaire)
            <div>
                <label for="adresse" class="form-label">{{ __('Adresse du proprietaire') }}</label>
                <div class="profile-address-field">
                    <i class="bi bi-geo-alt"></i>
                    <input id="adresse" name="adresse" type="text" class="form-control @error('adresse') is-invalid @enderror" value="{{ old('adresse', $user->proprietaire->adresse) }}" maxlength="255" autocomplete="street-address" placeholder="Ex: Lubumbashi, commune de Kampemba" />
                </div>
                @error('adresse') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        @endif

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
            @if (session('status') === 'profile-updated')
                <span class="text-success small">{{ __('Enregistre.') }}</span>
            @endif
        </div>
    </form>
</section>
