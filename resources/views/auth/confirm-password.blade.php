<x-guest-layout>
    <h4 class="fw-bold mb-2">Confirmation de securite</h4>
    <p class="text-muted small mb-4">Veuillez confirmer votre mot de passe pour continuer.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="current-password" />
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button class="btn btn-primary w-100" type="submit">Confirmer</button>
    </form>
</x-guest-layout>
