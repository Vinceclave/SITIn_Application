<?php
require_once 'config/config.php';
require_once 'shared/header.php';
session_start();

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
?>

<main class="flex justify-center items-center h-screen">
<div class="max-w-xl p-4">
    <h1 class="text-4xl font-bold mb-4">Register</h1>
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?php echo htmlspecialchars($error); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>
    <form id="registerForm" action="logic/registerHandler.php" method="post" class="space-y-4">
        <div>
            <label for="idno" class="block text-sm font-medium text-gray-700">ID Number:</label>
            <input type="number" id="idno" name="idno" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div class="grid gap-y-4 gap-x-6 grid-cols-1 md:grid-cols-3">
            <div>
                <label for="lastname" class="block text-sm font-medium text-gray-700">Last Name:</label>
                <input type="text" id="lastname" name="lastname" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="firstname" class="block text-sm font-medium text-gray-700">First Name:</label>
                <input type="text" id="firstname" name="firstname" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="middlename" class="block text-sm font-medium text-gray-700">Middle Name:</label>
                <input type="text" id="middlename" name="middlename" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </div>
        <div class="grid gap-y-4 gap-x-6 grid-cols-1 md:grid-cols-2">
            <div>
                <label for="course" class="block text-sm font-medium text-gray-700">Course:</label>
                <select id="course" name="course" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Course</option>
                    <option value="BSIT">BSIT</option>
                    <option value="BSCS">BSCS</option>
                    <option value="BSIS">BSIS</option>
                    <option value="ACT">ACT</option>
                    <option value="BSED">BSED</option>
                    <option value="BSCJ">BSCJ</option>
                    <option value="BS Custom">BS Custom</option>
                    <option value="BSHM">BSHM</option>
                </select>
            </div>
            <div>
                <label for="year_level" class="block text-sm font-medium text-gray-700">Year Level:</label>
                <select id="year_level" name="year_level" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Year Level</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div>
        </div>
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
            <input type="text" id="username" name="username" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
            <input type="password" id="password" name="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Register</button>
        </div>
    </form>
    <div class="mt-4 text-center">
        <p class="text-sm text-gray-600">Already have an account? <a href="login.php" class="text-indigo-600 hover:text-indigo-900">Login here</a></p>
    </div>
</div>

</main>

<?php
require_once 'shared/footer.php';
?>
