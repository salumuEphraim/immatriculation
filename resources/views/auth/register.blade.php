<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | RoadShield RDC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4">
                    <div class="card-body">
                        <h3 class="fw-bold mb-4 text-center">Créer un compte</h3>
                        
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Nom complet</label>
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email professionnel / personnel</label>
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Mot de passe</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmer</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>

                            <div class="alert alert-info small border-0 bg-light">
                                <i class="bi bi-info-circle me-2"></i> Votre compte sera soumis à validation par un administrateur DGI.
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary py-2 fw-bold">S'inscrire</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <p class="text-muted">Déjà un compte ? <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-bold">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>