<x-guest-layout>
    <h4 class="fw-bold mb-2">Verification de l'email</h4>
    <p class="text-muted small mb-4">
        Verifiez votre boite email puis cliquez sur le lien de confirmation.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success py-2">
            Un nouveau lien de verification a ete envoye.
        </div>
    @endif

    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-grow-1">
            @csrf
            <button class="btn btn-primary w-100" type="submit">Renvoyer le lien</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Se deconnecter</button>
        </form>
    </div>
</x-guest-layout>
