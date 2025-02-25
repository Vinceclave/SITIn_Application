<aside class="z-50 fixed h-screen top-0 left-0 w-72 p-4 bg-gray-100 rounded-md shadow-md">
    <h2 class="text-xl font-bold mb-4">Navigation</h2>
    <ul class="space-y-2">
        <li><a href="home.php" class="text-indigo-600 hover:text-indigo-900">Home</a></li>
        <li><a href="profile.php" class="text-indigo-600 hover:text-indigo-900">Profile</a></li>
        <li><a href="history.php" class="text-indigo-600 hover:text-indigo-900">History</a></li>
        <li><a href="reservation.php" class="text-indigo-600 hover:text-indigo-900">Reservation</a></li>
        <?php if ($role == 'admin'): ?>
            <li><a href="admin.php" class="text-indigo-600 hover:text-indigo-900">Admin</a></li>
        <?php endif; ?>
        <li><a href="../logout.php" class="text-red-600 hover:text-red-900">Log Out</a></li>
    </ul>
</aside>
