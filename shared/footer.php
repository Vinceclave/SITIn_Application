<?php 
// Ensure session is started
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

if (basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php'): 
?>
<footer class="fixed bottom-0 inset-x-0 z-40 border-t border-slate-200/10 dark:border-slate-700/10 backdrop-blur-lg <?php echo (isset($_SESSION['role']) && $_SESSION['role'] == 'Admin') ? 'pl-64' : ''; ?>">
    <div class="absolute inset-0 bg-white/70 dark:bg-slate-900/70"></div>
    <div class="container mx-auto px-4 py-3 relative">
        <div class="flex items-center justify-center">
            <span class="text-sm text-slate-600 dark:text-slate-400">
                &copy; <?php echo date('Y'); ?> SITIn Application
            </span>
        </div>
    </div>
</footer>
<?php endif; ?>
