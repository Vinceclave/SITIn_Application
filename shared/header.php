<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SITIn Application</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen text-gray-900">
<?php if (basename($_SERVER['PHP_SELF']) != 'login.php' && (basename($_SERVER['PHP_SELF']) != 'manage_reservation.php' && basename($_SERVER['PHP_SELF']) != 'register.php' && basename($_SERVER['PHP_SELF']) != 'dashboard.php' && basename($_SERVER['PHP_SELF']) != 'history.php' && basename($_SERVER['PHP_SELF']) != 'manage_users.php' && basename($_SERVER['PHP_SELF']) != 'home.php' && basename($_SERVER['PHP_SELF']) != 'profile.php'  && basename($_SERVER['PHP_SELF']) != 'sitting_records.php' && basename($_SERVER['PHP_SELF']) != 'reservation.php'  && basename($_SERVER['PHP_SELF']) != 'announcement.php' && basename($_SERVER['PHP_SELF']) != 'reports.php' && basename($_SERVER['PHP_SELF']) != 'admin_feedback.php') : ?>
    <header class="fixed w-full backdrop-blur-sm border-b border-gray-200/50">
        <nav>
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <a href="index.php" class="text-2xl font-bold text-gray-900 hover:text-indigo-600 transition-colors">SITIn Application</a>
                <div class="space-x-5">
                    <a href="about.php" class="text-lg text-gray-600 hover:text-indigo-600 transition-colors">About Us</a>
                    <a href="login.php" class="text-lg text-gray-600 hover:text-indigo-600 transition-colors">Login</a>
                </div>
            </div>
        </nav>
    </header>
<?php endif; ?>

<script>
    tailwind.config = {
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
    }
</script>

</body>
</html>
