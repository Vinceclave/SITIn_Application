<?php if (basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php'): ?>
    <footer class="bg-white shadow-md mt-8 <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['home.php', 'profile.php', 'history.php', 'reservation.php'])) ? 'pl-72 py-4 text-center fixed bottom-0 w-full' : ''; ?>">
        <div class="container mx-auto px-4">
            <p class="text-gray-700">&copy; 2023 SITIn Application. All rights reserved.</p>
        </div>
    </footer>
<?php endif; ?>
</body>
</html>
