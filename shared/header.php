<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SITIn Application - Computer Laboratory Management System">
    <title>SITIn Application</title>
    
    <!-- Preload critical assets -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" as="style">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Notiflix for notifications -->
    <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
    <script>
        // Configure notifications
        Notiflix.Notify.init({
            position: 'right-top',
            distance: '20px',
            opacity: 1,
            borderRadius: '12px',
            fontFamily: 'Inter',
            useIcon: true,
            cssAnimation: true,
            cssAnimationDuration: 300,
            cssAnimationStyle: 'fade',
            success: {
                background: '#10B981',
                textColor: '#FFFFFF',
            },
            failure: {
                background: '#EF4444',
                textColor: '#FFFFFF',
            },
            warning: {
                background: '#F59E0B',
                textColor: '#FFFFFF',
            },
            info: {
                background: '#3B82F6',
                textColor: '#FFFFFF',
            }
        });
    </script>
    
    <style>
        /* Base styles */
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #7c3aed;
            --secondary-dark: #6d28d9;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgb(241 245 249 / 0.8);
        }

        ::-webkit-scrollbar-thumb {
            background: rgb(148 163 184 / 0.8);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgb(100 116 139 / 0.8);
        }

        /* Dark mode scrollbar */
        .dark ::-webkit-scrollbar-track {
            background: rgb(30 41 59 / 0.8);
        }

        .dark ::-webkit-scrollbar-thumb {
            background: rgb(71 85 105 / 0.8);
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgb(100 116 139 / 0.8);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-white transition-colors duration-200">
<?php 
$excludedPages = ['login.php', 'register.php', 'dashboard.php', 'manage_reservations.php', 'history.php', 
                 'manage_users.php', 'home.php', 'profile.php', 'sitting_records.php', 'reservation.php', 
                 'announcement.php', 'reports.php', 'admin_feedback.php', 'lab_management.php', 'pc_management.php', 'resources.php', 'manage_resources.php',];

if (!in_array(basename($_SERVER['PHP_SELF']), $excludedPages)): ?>
    <header class="fixed top-0 inset-x-0 z-50">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="hidden md:flex items-center">
                    <button onclick="toggleDarkMode()" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path class="sun hidden dark:block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            <path class="moon dark:hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center space-x-2">
                    <button onclick="toggleDarkMode()" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path class="sun hidden dark:block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            <path class="moon dark:hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <button class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" onclick="toggleMobileMenu()">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path class="menu-closed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path class="menu-open hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="mobile-menu hidden md:hidden py-4 animate-fade-in">
                <div class="flex flex-col space-y-4">
                    <a href="about.php" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        About
                    </a>
                    <a href="login.php" class="px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white text-center hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg shadow-blue-500/25">
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>

<script>
// Theme configuration
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                navy: "#0D1B2A",
                darkblue: "#1B263B",
                steelblue: "#415A77",
                bluegray: "#778DA9",
                offwhite: "#E0E1DD",
            }
        }
    }
};

// Check system dark mode preference
if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.classList.add('dark');
}

// Dark mode toggle
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
}

// Mobile menu toggle
function toggleMobileMenu() {
    const mobileMenu = document.querySelector('.mobile-menu');
    const menuIcon = document.querySelector('.menu-closed');
    const closeIcon = document.querySelector('.menu-open');
    
    mobileMenu.classList.toggle('hidden');
    menuIcon.classList.toggle('hidden');
    closeIcon.classList.toggle('hidden');
}

// Initialize dark mode from localStorage
if (localStorage.getItem('darkMode') === 'true') {
    document.documentElement.classList.add('dark');
}
</script>

</body>
</html>
