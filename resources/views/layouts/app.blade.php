<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPP Sistema - Tecsup Norte</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ── VARIABLES ── */
        :root {
            --sidebar-w: 250px;
            --tecsup-blue: #003366;
            --topbar-h: 60px;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ════════════════════════════════
           SIDEBAR (desktop: fixed left)
        ════════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #fff;
            border-right: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 20px;
            color: var(--tecsup-blue);
            flex-shrink: 0;
        }

        .sidebar nav {
            overflow-y: auto;
            flex: 1;
        }

        .nav-link {
            color: #333;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: 0.25s;
            text-decoration: none;
            border-left: 3px solid transparent;
        }
        .nav-link i { margin-right: 15px; font-size: 1.1rem; flex-shrink: 0; }
        .nav-link:hover,
        .nav-link.active {
            background-color: #f0f4f8;
            color: var(--tecsup-blue);
            border-left-color: var(--tecsup-blue);
        }

        .user-section {
            padding: 16px 20px;
            border-top: 1px solid #eee;
            flex-shrink: 0;
        }

        .nav-link.logout {
            color: #e74c3c;
            background: #fff5f5;
            border-radius: 8px;
            margin: 6px 16px 4px;
            padding: 10px 16px;
            border-left: none;
        }
        .nav-link.logout:hover { background: #fde8e8; }

        /* ════════════════════════════════
           OVERLAY (mobile: behind drawer)
        ════════════════════════════════ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1035;
        }
        .sidebar-overlay.show { display: block; }

        /* ════════════════════════════════
           TOP BAR (mobile only)
        ════════════════════════════════ */
        .topbar {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            align-items: center;
            padding: 0 16px;
            z-index: 1030;
            gap: 12px;
        }
        .topbar .topbar-title { font-weight: 700; color: var(--tecsup-blue); font-size: 1rem; }
        .topbar .topbar-subtitle { font-size: 0.72rem; color: #6c757d; }
        .btn-hamburger {
            background: none;
            border: none;
            padding: 4px 6px;
            font-size: 1.5rem;
            color: var(--tecsup-blue);
            line-height: 1;
            cursor: pointer;
        }

        /* ════════════════════════════════
           MAIN CONTENT
        ════════════════════════════════ */
        .main-content {
            margin-left: var(--sidebar-w);
            padding: 30px;
            min-height: 100vh;
        }

        /* ════════════════════════════════
           ALERT CONTAINER
        ════════════════════════════════ */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
            width: min(350px, calc(100vw - 32px));
        }

        /* ════════════════════════════════
           RESPONSIVE ≤ 991px
        ════════════════════════════════ */
        @media (max-width: 991.98px) {

            /* Hide fixed sidebar, convert to off-canvas drawer */
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 4px 0 20px rgba(0,0,0,0.12);
            }
            .sidebar.open {
                transform: translateX(0);
            }

            /* Show top bar */
            .topbar { display: flex; }

            /* Push content down below topbar, remove left margin */
            .main-content {
                margin-left: 0;
                padding: 20px 16px;
                padding-top: calc(var(--topbar-h) + 20px);
            }

            /* Alert repositioned for mobile */
            .alert-container {
                top: calc(var(--topbar-h) + 10px);
                right: 12px;
            }
        }

        @media (max-width: 575.98px) {
            .main-content { padding: 16px 12px; padding-top: calc(var(--topbar-h) + 16px); }
        }
    </style>
</head>
<body>

    {{-- ══════════════════════════════════════
         TOP BAR (visible on mobile only)
    ══════════════════════════════════════ --}}
    <header class="topbar" id="topbar">
        <button class="btn-hamburger" id="btnOpenSidebar" aria-label="Abrir menú" aria-expanded="false" aria-controls="sidebar">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <div class="topbar-title">EPP Sistema</div>
            <div class="topbar-subtitle">Tecsup Norte</div>
        </div>
    </header>

    {{-- ══════════════════════════════════════
         OVERLAY (mobile drawer backdrop)
    ══════════════════════════════════════ --}}
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    {{-- ══════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════ --}}
    <aside class="sidebar" id="sidebar" role="navigation" aria-label="Menú principal">

        <div class="sidebar-header d-flex align-items-start justify-content-between">
            <div>
                <h4 class="fw-bold mb-0">EPP Sistema</h4>
                <small class="text-muted">Tecsup Norte</small>
            </div>
            {{-- Close button — visible only when drawer is open on mobile --}}
            <button class="btn-hamburger d-lg-none ms-2 mt-1" id="btnCloseSidebar" aria-label="Cerrar menú">
                <i class="bi bi-x-lg" style="font-size:1.3rem;"></i>
            </button>
        </div>

        <nav class="nav flex-column mt-2">

            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                <i class="bi bi-bar-chart-line"></i> Dashboard
            </a>

            <a class="nav-link {{ request()->is('epps*') ? 'active' : '' }}"
               href="{{ route('epps.index') }}">
                <i class="bi bi-box-seam"></i> Inventario / Catálogo
            </a>

            <a class="nav-link {{ request()->is('categorias*') ? 'active' : '' }}"
               href="{{ route('categorias.index') }}">
                <i class="bi bi-tags"></i> Categorías
            </a>

            <a class="nav-link {{ request()->routeIs('personals.index') ? 'active' : '' }}"
               href="{{ route('personals.index') }}">
                <i class="bi bi-person-badge"></i> Base de Datos Personal
            </a>

            <a class="nav-link {{ request()->routeIs('departamentos.index') ? 'active' : '' }}"
               href="{{ route('departamentos.index') }}">
                <i class="bi bi-building"></i> Departamentos
            </a>

            <a class="nav-link {{ request()->is('entregas*') ? 'active' : '' }}"
               href="{{ route('entregas.index') }}">
                <i class="bi bi-box-arrow-in-down"></i> Entrega de EPPS
            </a>

            <a class="nav-link {{ request()->is('asignaciones*') ? 'active' : '' }}"
               href="{{ route('asignaciones.index') }}">
                <i class="bi bi-clock-history"></i> Historial de Entregas
            </a>

            <a class="nav-link {{ request()->routeIs('reportes.index') ? 'active' : '' }}"
               href="{{ route('reportes.index') }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Reportes
            </a>

        </nav>

        <hr class="mx-3 my-2 text-secondary opacity-25">

        <div class="user-section">
            <a class="nav-link p-0 mb-3" href="{{ route('perfil.show') }}" style="border-left: none;">
                <i class="bi bi-person-circle"></i> Mi Perfil ({{ Auth::user()->name }})
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link logout border-0 w-100 text-start" style="cursor: pointer;">
                    <i class="bi bi-box-arrow-left"></i> Salir
                </button>
            </form>
        </div>

    </aside>

    {{-- ══════════════════════════════════════
         MAIN CONTENT
    ══════════════════════════════════════ --}}
    <main class="main-content">

        {{-- Flash messages --}}
        <div class="alert-container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        @yield('content')

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        (function () {
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('sidebarOverlay');
            const btnOpen  = document.getElementById('btnOpenSidebar');
            const btnClose = document.getElementById('btnCloseSidebar');

            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('show');
                btnOpen.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden'; // prevent background scroll
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                btnOpen.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            btnOpen.addEventListener('click', openSidebar);
            btnClose.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);

            // Close on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                    closeSidebar();
                }
            });

            // Close drawer if window resizes to desktop width
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 992) {
                    closeSidebar();
                }
            });
        })();
    </script>

    @stack('scripts')

</body>
</html>