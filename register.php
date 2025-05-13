<?php
session_start();
require_once 'config/config.php';

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';

require_once 'shared/header.php';
?>

<main class="min-h-screen bg-gradient-to-br from-slate-50 to-white dark:from-slate-900 dark:to-slate-800 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="relative">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-purple-500/10 animate-gradient rounded-2xl filter blur-3xl"></div>
        <div class="relative max-w-2xl w-full backdrop-blur-lg bg-white/60 dark:bg-slate-800/60 p-8 rounded-2xl shadow-lg border border-slate-200/50 dark:border-slate-700/50">
            <!-- Logo -->
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg shadow-blue-500/25 mb-4">
                    <span class="text-2xl font-bold text-white">S</span>
                </div>
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Create your account</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Already have an account?
                    <a href="login.php" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 transition-colors">Sign in</a>
                </p>
            </div>

            <form id="registerForm" action="logic/registerHandler.php" method="post" class="space-y-6">
                <div class="space-y-4">
                    <div>
                        <label for="idno" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ID Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                            </div>
                            <input type="text" id="idno" name="idno" required
                                   class="block w-full pl-10 rounded-xl border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:text-white text-sm transition-all duration-200">
                        </div>
                    </div>

                    <div class="grid gap-6 grid-cols-1 md:grid-cols-3">
                        <div>
                            <label for="lastname" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name</label>
                            <input type="text" id="lastname" name="lastname" required
                                   class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:text-white text-sm transition-all duration-200">
                        </div>
                        <div>
                            <label for="firstname" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">First Name</label>
                            <input type="text" id="firstname" name="firstname" required
                                   class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:text-white text-sm transition-all duration-200">
                        </div>
                        <div>
                            <label for="middlename" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Middle Name</label>
                            <input type="text" id="middlename" name="middlename"
                                   class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:text-white text-sm transition-all duration-200">
                        </div>
                    </div>

                    <div class="grid gap-6 grid-cols-1 md:grid-cols-2">
                        <div>
                            <label for="course" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Course</label>
                            <select id="course" name="course" required
                                    class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:text-white text-sm transition-all duration-200">
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
                        <div>
                            <label for="year_level" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Year Level</label>
                            <select id="year_level" name="year_level" required
                                    class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:text-white text-sm transition-all duration-200">
                                <option value="">Select Year Level</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="username" name="username" required
                                   class="block w-full pl-10 rounded-xl border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:text-white text-sm transition-all duration-200">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required
                                   class="block w-full pl-10 rounded-xl border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:text-white text-sm transition-all duration-200">
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] hover:shadow-lg">
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

<?php require_once 'shared/footer.php'; ?>
