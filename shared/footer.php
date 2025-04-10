<?php if (basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php'): ?>
    <footer class="backdrop-blur-sm border-t border-gray-200/50 p-4 text-center w-full fixed bottom-0 
        <?php echo ($_SESSION['role'] == 'Admin') ? 'pl-52' : ''; ?>">
        <div class="container mx-auto px-4">
            <p class="text-gray-700">&copy; 2023 SITIn Application. All rights reserved.</p>
        </div>
    </footer>
<?php endif; ?>
