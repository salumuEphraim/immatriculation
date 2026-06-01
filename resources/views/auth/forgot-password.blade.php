<x-guest-layout>
    <h4 class="fw-bold mb-2">Mot de passe oublie</h4>
    <p class="text-muted small mb-4">
        Entrez votre adresse email pour recevoir un lien de reinitialisation.
    </p>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus />
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-primary w-100" type="submit">
            Envoyer le lien
        </button>
    </form>
</x-guest-layout>
