<section>
    <header class="profile-form-head">
        <span><i class="bi bi-lock"></i></span>
        <div>
            <h2>{{ __('Modifier le mot de passe') }}</h2>
            <p>{{ __('Utilisez un mot de passe long et securise.') }}</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="profile-pro-form">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="form-label">{{ __('Mot de passe actuel') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" />
            @error('current_password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="update_password_password" class="form-label">{{ __('Nouveau mot de passe') }}</label>
            <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
            @error('password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="form-label">{{ __('Confirmation') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
            @if (session('status') === 'password-updated')
                <span class="text-success small">{{ __('Enregistre.') }}</span>
            @endif
        </div>
    </form>
</section>
