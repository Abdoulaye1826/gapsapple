<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Session expirée — GAPS APPLE SI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card text-center">
            <div class="auth-logo">
                <div class="logo-icon">
                    <img src="{{ asset('images/profil.jpeg') }}" alt="GAPS APPLE">
                </div>
                <h4 class="fw-bold mb-0">GAPS APPLE</h4>
                <small class="text-muted">Système d'information</small>
            </div>

            <i class="bi bi-clock-history" style="font-size:2.5rem;color:#fff;"></i>
            <p class="mt-3 mb-4" style="color:#fff;">
                Votre session a expiré. Veuillez vous reconnecter.
            </p>

            <a href="{{ route('login') }}" class="btn btn-primary w-100 py-2 fw-medium">
                <i class="bi bi-box-arrow-in-right me-2"></i>Se reconnecter
            </a>
        </div>
    </div>

    <script>
        setTimeout(function () {
            window.location.href = @json(route('login'));
        }, 3000);
    </script>
</body>
</html>
