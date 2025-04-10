<?php
require_once 'config/config.php';
require_once 'shared/header.php';
session_start();

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
?>

<main class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900">Create your account</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Already have an account?
                    <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">Sign in</a>
                </p>
            </div>

            <form id="registerForm" action="logic/registerHandler.php" method="post" class="space-y-6">
                <div>
                    <label for="idno" class="block text-sm font-medium text-gray-700">ID Number</label>
                    <div class="mt-1">
                        <input type="text" id="idno" name="idno" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2">
                    </div>
                </div>

                <div class="grid gap-6 grid-cols-1 md:grid-cols-3">
                    <div>
                        <label for="lastname" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <div class="mt-1">
                            <input type="text" id="lastname" name="lastname" required
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label for="firstname" class="block text-sm font-medium text-gray-700">First Name</label>
                        <div class="mt-1">
                            <input type="text" id="firstname" name="firstname" required
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label for="middlename" class="block text-sm font-medium text-gray-700">Middle Name</label>
                        <div class="mt-1">
                            <input type="text" id="middlename" name="middlename"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2">
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 grid-cols-1 md:grid-cols-2">
                    <div>
                        <label for="course" class="block text-sm font-medium text-gray-700">Course</label>
                        <div class="mt-1">
                            <select id="course" name="course" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2">
                                <option value="">Select Course</option>
                                <option value="BSIT">Bachelor of Science in Information Technology (BSIT)</option>
                                <option value="BSCS">Bachelor of Science in Computer Science (BSCS)</option>
                                <option value="BSIS">Bachelor of Science in Information Systems (BSIS)</option>
                                <option value="BSBA">Bachelor of Science in Business Administration (BSBA)</option>
                                <option value="ACT">Bachelor of Science in Accountancy (ACT)</option>
                                <option value="BSED">Bachelor of Secondary Education (BSED)</option>
                                <option value="BSCJ">Bachelor of Science in Communication (BSCJ)</option>
                                <option value="BSHM">Bachelor of Science in Hospitality Management (BSHM)</option>
                                <option value="BSPsych">Bachelor of Science in Psychology (BSPsych)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="year_level" class="block text-sm font-medium text-gray-700">Year Level</label>
                        <div class="mt-1">
                            <select id="year_level" name="year_level" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2">
                                <option value="">Select Year Level</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <div class="mt-1">
                        <input type="text" id="username" name="username" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1">
                        <input type="password" id="password" name="password" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2">
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php if ($error): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.failure("<?php echo addslashes($error); ?>");
</script>
<?php endif; ?>

<?php
if(isset($_SESSION['success'])):
?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.success("<?php echo addslashes($_SESSION['success']); ?>");
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php
require_once 'shared/footer.php';
?>
