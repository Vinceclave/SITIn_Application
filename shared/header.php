<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SITIn Application</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Raleway:ital,wght@0,100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Raleway', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen -gray-100 text-gray-900">
<?php if (basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php' && basename($_SERVER['PHP_SELF']) != 'dashboard.php' && basename($_SERVER['PHP_SELF']) != 'history.php' && basename($_SERVER['PHP_SELF']) != 'home.php' && basename($_SERVER['PHP_SELF']) != 'profile.php' && basename($_SERVER['PHP_SELF']) != 'reservation.php') : ?>
    <header class="fixed w-full">
        <nav class="">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <a href="index.php" class="text-2xl font-bold text-gray-900">SITIn Application</a>
                <div>
                    <a href="about.php" class="text-lg text-gray-700 hover:text-gray-900 mx-5">About Us</a>
                    <a href="login.php" class="text-lg text-gray-700 hover:text-gray-900 mx-5">Login</a>
                </div>
            </div>
        </nav>
    </header>
<?php endif; ?>
</body>
</html>
