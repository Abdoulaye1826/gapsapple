<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — GAPS APPLE SI</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/forms-ui.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    {{-- Overlay mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Sidebar --}}
    @include('layouts.partials.sidebar')

    {{-- Contenu principal --}}
    <div class="main-wrapper">
        @include('layouts.partials.navbar')

        <main class="page-content">
            @include('layouts.partials.alerts')
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/forms-ui.js') }}"></script>
    <script>
        // Toggle sidebar mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });
        document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });
    </script>

    {{-- ── Gestion de session : garde active tant que l'utilisateur travaille,
         détecte proprement une expiration réelle et redirige vers la
         connexion avec un message clair (au lieu d'une page 419 brute). ── --}}
    <script>
        (function () {
            const loginUrl = @json(route('login'));
            const keepAliveUrl = @json(route('keep-alive'));
            let expiredHandled = false;

            function showExpiredOverlay() {
                if (expiredHandled) return;
                expiredHandled = true;

                const overlay = document.createElement('div');
                overlay.style.cssText = 'position:fixed;inset:0;z-index:2000;display:flex;align-items:center;justify-content:center;background:rgba(26,26,46,0.85);';
                overlay.innerHTML = `
                    <div style="background:#fff;border-radius:12px;padding:2rem;max-width:360px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                        <i class="bi bi-clock-history" style="font-size:2rem;color:#8a6f1f;"></i>
                        <p class="mt-3 mb-3 fw-medium">Votre session a expiré. Veuillez vous reconnecter.</p>
                        <a href="${loginUrl}" class="btn btn-primary w-100">Se reconnecter</a>
                    </div>
                `;
                document.body.appendChild(overlay);

                setTimeout(() => { window.location.href = loginUrl; }, 2500);
            }

            // Intercepteur global : toute requête fetch() de l'application qui
            // reçoit un 419 (CSRF/session expirée) ou 401 (non authentifié)
            // déclenche la même détection propre, plutôt que de laisser
            // chaque appel fetch() individuel échouer silencieusement.
            const originalFetch = window.fetch;
            window.fetch = function (...args) {
                return originalFetch.apply(this, args).then((response) => {
                    if (response.status === 419 || response.status === 401) {
                        showExpiredOverlay();
                    }
                    return response;
                });
            };

            // Keep-alive : tant que l'onglet est visible, un ping léger toutes
            // les 10 minutes repousse l'expiration de la session côté serveur.
            setInterval(() => {
                if (document.visibilityState !== 'visible') return;
                originalFetch(keepAliveUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .catch(() => {});
            }, 10 * 60 * 1000);
        })();
    </script>
    @stack('scripts')
</body>
</html>
