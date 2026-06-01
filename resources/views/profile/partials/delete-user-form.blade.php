<section>
    <header class="profile-form-head profile-form-head-danger">
        <span><i class="bi bi-exclamation-triangle"></i></span>
        <div>
            <h2>{{ __('Supprimer le compte') }}</h2>
            <p>{{ __('Cette action est irreversible et supprimera vos donnees.') }}</p>
        </div>
    </header>

    <button class="btn btn-danger profile-danger-button" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
        <i class="bi bi-trash3 me-1"></i>
        {{ __('Supprimer mon compte') }}
    </button>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Confirmer la suppression') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            {{ __('Entrez votre mot de passe pour confirmer la suppression definitive du compte.') }}
                        </p>
                        <label for="delete_password" class="form-label">{{ __('Mot de passe') }}</label>
                        <input id="delete_password" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="{{ __('Mot de passe') }}">
                        @error('password', 'userDeletion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Supprimer definitivement') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
