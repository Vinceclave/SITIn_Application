<?php
require_once 'config/config.php';
require_once 'shared/header.php';
?>
<main class="bg-gray-100 text-gray-900">
        <div class="pt-16 container mx-auto min-h-screen flex flex-col items-start justify-center">
        <h1 class="text-6xl font-bold mb-8 text-center">Welcome to SITIn Application</h1>
        <p class="text-2xl mb-10 text-center">Efficiently schedule your appointments to use laboratory PCs.</p>
        <div class="flex space-x-6">
            <a href="register.php" class="px-8 py-4 bg-green-500 text-white rounded-lg text-xl hover:bg-green-600">Register Now</a>
        </div>
    </div>
</main>
<?php
require_once 'shared/footer.php';
?>
