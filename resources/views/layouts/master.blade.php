<?php
// resources/views/layouts/master.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Arewa Conservation Platform')</title>
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:;">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #d6ad60;
            --arewa-green: #006400;
            --arewa-gold: #FFD700;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background: #f8f9fa;
        }
        
        .sidebar {
            background: var(--primary-color);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        
        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
        }
        
        .menu-item.active {
            background: var(--secondary-color);
            color: white;
        }
        
        .menu-icon {
            width: 24px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .header-bar {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: none;
            min-width: 200px;
        }
        
        .user-menu:hover .user-dropdown {
            display: block;
        }
        
        /* Security indicators */
        .security-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--arewa-green);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 1000;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Security Badge -->
    <div class="security-badge">
        <i class="bi bi-shield-check"></i> Secure Session
    </div>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h3><i class="bi bi-house-door"></i> Arewa House Conservation</h3>
            <small>Centre of Excellence in Historical Documentation</small>
        </div>
        
        <div class="sidebar-menu">
            @php
                $currentUser = Auth::user();
                $userType = $currentUser->user_type ?? 'donor';
                
                // Define menus based on user type
                $menus = [
                    'admin' => [
                        ['icon' => 'bi-speedometer2', 'name' => 'Dashboard', 'route' => 'admin.dashboard'],
                        ['icon' => 'bi-archive', 'name' => 'Collections', 'route' => 'collections.index'],
                        ['icon' => 'bi-tools', 'name' => 'Conservation', 'route' => 'conservation.projects'],
                        ['icon' => 'bi-book', 'name' => 'Digital Library', 'route' => 'digital-library'],
                        ['icon' => 'bi-people', 'name' => 'Volunteers', 'route' => 'volunteers.index'],
                        ['icon' => 'bi-building', 'name' => 'Libraries', 'route' => 'libraries.index'],
                        ['icon' => 'bi-search', 'name' => 'Research', 'route' => 'research.projects'],
                        ['icon' => 'bi-shield-check', 'name' => 'Security', 'route' => 'security.dashboard'],
                        ['icon' => 'bi-graph-up', 'name' => 'Analytics', 'route' => 'analytics.dashboard'],
                        ['icon' => 'bi-gear', 'name' => 'Settings', 'route' => 'settings.index'],
                    ],
                    'conservator' => [
                        ['icon' => 'bi-speedometer2', 'name' => 'My Dashboard', 'route' => 'conservator.dashboard'],
                        ['icon' => 'bi-tools', 'name' => 'My Projects', 'route' => 'conservator.projects'],
                        ['icon' => 'bi-clock-history', 'name' => 'Project Queue', 'route' => 'conservator.queue'],
                        ['icon' => 'bi-clipboard-check', 'name' => 'Quality Checks', 'route' => 'conservator.quality'],
                        ['icon' => 'bi-box-seam', 'name' => 'Materials', 'route' => 'conservator.materials'],
                    ],
                    'researcher' => [
                        ['icon' => 'bi-speedometer2', 'name' => 'Research Portal', 'route' => 'researcher.dashboard'],
                        ['icon' => 'bi-search', 'name' => 'Browse Collections', 'route' => 'collections.browse'],
                        ['icon' => 'bi-file-earmark-text', 'name' => 'My Requests', 'route' => 'access-requests.index'],
                        ['icon' => 'bi-journal-text', 'name' => 'Research Projects', 'route' => 'research-projects.my'],
                        ['icon' => 'bi-download', 'name' => 'Digital Assets', 'route' => 'digital-assets.index'],
                        ['icon' => 'bi-calendar-check', 'name' => 'Appointments', 'route' => 'appointments.index'],
                    ],
                    'volunteer' => [
                        ['icon' => 'bi-speedometer2', 'name' => 'Volunteer Hub', 'route' => 'volunteer.dashboard'],
                        ['icon' => 'bi-calendar-week', 'name' => 'My Schedule', 'route' => 'volunteer.schedule'],
                        ['icon' => 'bi-clock-history', 'name' => 'Hours Log', 'route' => 'volunteer.hours'],
                        ['icon' => 'bi-award', 'name' => 'Training', 'route' => 'volunteer.training'],
                        ['icon' => 'bi-chat-left-text', 'name' => 'Feedback', 'route' => 'volunteer.feedback'],
                    ],
                    'donor' => [
                        ['icon' => 'bi-speedometer2', 'name' => 'My Dashboard', 'route' => 'donor.dashboard'],
                        ['icon' => 'bi-box-seam', 'name' => 'My Donations', 'route' => 'donations.my'],
                        ['icon' => 'bi-truck', 'name' => 'Track Donation', 'route' => 'donations.track'],
                        ['icon' => 'bi-heart', 'name' => 'Wishlist', 'route' => 'wishlist.index'],
                        ['icon' => 'bi-award', 'name' => 'Certificates', 'route' => 'certificates.my'],
                    ],
                ];
                
                $currentMenu = $menus[$userType] ?? $menus['donor'];
            @endphp
            
            @foreach($currentMenu as $item)
                <a href="{{ route($item['route']) }}" class="menu-item @if(Route::currentRouteName() == $item['route']) active @endif">
                    <i class="bi {{ $item['icon'] }} menu-icon"></i> {{ $item['name'] }}
                </a>
            @endforeach
            
            <!-- Logout -->
            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                @csrf
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                   class="menu-item text-danger">
                    <i class="bi bi-box-arrow-right menu-icon"></i> Logout
                </a>
            </form>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header-bar d-flex justify-content-between align-items-center">
            <h4 class="mb-0">@yield('page-title')</h4>
            
            <div class="user-menu">
                <div class="d-flex align-items-center">
                    <img src="{{ Auth::user()->profile_picture ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" 
                         class="rounded-circle" width="40" height="40">
                    <div class="ms-3">
                        <strong>{{ Auth::user()->name }}</strong>
                        <div class="small text-muted">
                            <span class="badge bg-{{ 
                                Auth::user()->user_type == 'admin' ? 'danger' : 
                                (Auth::user()->user_type == 'conservator' ? 'warning' : 
                                (Auth::user()->user_type == 'researcher' ? 'info' : 'success'))
                            }}">
                                {{ ucfirst(Auth::user()->user_type) }}
                            </span>
                            • Last login: {{ Auth::user()->last_login_at?->diffForHumans() ?? 'First time' }}
                        </div>
                    </div>
                </div>
                
                <div class="user-dropdown">
                    <div class="p-3">
                        <div class="mb-3">
                            <strong>{{ Auth::user()->name }}</strong><br>
                            <small>{{ Auth::user()->email }}</small>
                        </div>
                        <hr>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="bi bi-person me-2"></i> Profile
                        </a>
                        <a href="{{ route('security.settings') }}" class="dropdown-item">
                            <i class="bi bi-shield me-2"></i> Security
                        </a>
                        <a href="{{ route('notifications') }}" class="dropdown-item">
                            <i class="bi bi-bell me-2"></i> Notifications
                        </a>
                        <hr>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // CSRF Protection for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Auto logout after inactivity
        let timeout;
        function resetTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(logout, 30 * 60 * 1000); // 30 minutes
        }
        
        function logout() {
            window.location.href = '{{ route("logout") }}';
        }
        
        document.addEventListener('mousemove', resetTimer);
        document.addEventListener('keypress', resetTimer);
        
        resetTimer();
        
        // Input sanitization
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const inputs = form.querySelectorAll('input, textarea, select');
                    inputs.forEach(input => {
                        // Basic sanitization
                        if (input.value) {
                            input.value = input.value.replace(/[<>]/g, '');
                        }
                    });
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>