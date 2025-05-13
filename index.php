<?php
require_once 'config/config.php';
require_once 'shared/header.php';
?>
<main class="min-h-screen bg-gradient-to-b from-slate-50 to-white dark:from-slate-900 dark:to-slate-800">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-grid-slate-900/[0.04] bg-[size:32px_32px] dark:bg-grid-slate-400/[0.05]"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-purple-500/10 animate-gradient"></div>
        <div class="container mx-auto px-4 py-24 relative">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex mb-8 p-2 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl shadow-lg">
                    <div class="px-3 py-1 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-sm rounded-xl">
                        UC Main Campus • 5th Floor Labs
                    </div>
                </div>
                <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-slate-900 dark:text-white mb-6">
                    Welcome to <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">SITIn</span>
                </h1>
                <p class="text-lg md:text-xl text-slate-600 dark:text-slate-300 mb-12 max-w-2xl mx-auto">
                    Streamline your access to UC's computer laboratories. Real-time availability tracking, instant reservations, and automated session management.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a href="register.php" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100 transition-all duration-200 shadow-lg shadow-slate-900/10 dark:shadow-white/10">
                        Get Started
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="login.php" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 shadow-lg shadow-slate-900/10 dark:shadow-black/10">
                        Sign In
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Labs Overview -->
            <div class="mt-24 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
                <?php
                $labs = [
                    ['517', 'Web Development Lab'],
                    ['524', 'Programming Lab'],
                    ['526', 'Network Security Lab'],
                    ['528', 'Database Systems Lab'],
                    ['530', 'Software Engineering Lab'],
                    ['542', 'AI & Machine Learning Lab'],
                    ['544', 'IoT & Embedded Systems Lab']
                ];
                
                foreach($labs as $lab): ?>
                <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-lg rounded-2xl p-6 hover:scale-[1.02] transition-all duration-300 border border-slate-200/50 dark:border-slate-700/50">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white">Lab <?php echo $lab[0]; ?></h3>
                            <p class="text-slate-600 dark:text-slate-400"><?php echo $lab[1]; ?></p>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            <span class="text-slate-600 dark:text-slate-400">Available</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Capacity</span>
                            <span class="font-medium text-slate-900 dark:text-white">50 PCs</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Location</span>
                            <span class="font-medium text-slate-900 dark:text-white">5th Floor</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full" style="width: <?php echo rand(30, 90); ?>%"></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-24 bg-slate-50/50 dark:bg-slate-800/50 backdrop-blur-sm">
        <div class="container mx-auto px-4">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4">Smart Features</h2>
                    <p class="text-lg text-slate-600 dark:text-slate-400">Modern tools for efficient lab management</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200/50 dark:border-slate-700/50">
                        <div class="w-12 h-12 bg-blue-500/10 dark:bg-blue-500/20 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">Real-time Tracking</h3>
                        <p class="text-slate-600 dark:text-slate-400">Monitor lab availability and PC status in real-time across all seven computer laboratories.</p>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200/50 dark:border-slate-700/50">
                        <div class="w-12 h-12 bg-purple-500/10 dark:bg-purple-500/20 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">Easy Reservations</h3>
                        <p class="text-slate-600 dark:text-slate-400">Book your preferred PC in any lab up to 7 days in advance with our streamlined reservation system.</p>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200/50 dark:border-slate-700/50">
                        <div class="w-12 h-12 bg-green-500/10 dark:bg-green-500/20 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">Rewards System</h3>
                        <p class="text-slate-600 dark:text-slate-400">Earn points for responsible lab usage and unlock additional session time and special privileges.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="py-24">
        <div class="container mx-auto px-4">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4">How It Works</h2>
                    <p class="text-lg text-slate-600 dark:text-slate-400">Three simple steps to get started</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="relative">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200/50 dark:border-slate-700/50">
                            <div class="absolute -top-4 -left-4 w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-xl">1</div>
                            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mt-4 mb-2">Create Account</h3>
                            <p class="text-slate-600 dark:text-slate-400">Register using your student ID and verify your academic credentials.</p>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200/50 dark:border-slate-700/50">
                            <div class="absolute -top-4 -left-4 w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-xl">2</div>
                            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mt-4 mb-2">Book Session</h3>
                            <p class="text-slate-600 dark:text-slate-400">Choose your lab, select an available PC, and book your time slot instantly.</p>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200/50 dark:border-slate-700/50">
                            <div class="absolute -top-4 -left-4 w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-xl">3</div>
                            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mt-4 mb-2">Start Working</h3>
                            <p class="text-slate-600 dark:text-slate-400">Check in at your reserved time and begin your lab session.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="py-24 bg-slate-50/50 dark:bg-slate-800/50 backdrop-blur-sm">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-6">Ready to Get Started?</h2>
                <p class="text-lg text-slate-600 dark:text-slate-400 mb-8">Join your fellow students in making the most of UC's computer laboratory resources.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="register.php" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg shadow-blue-500/25">
                        Create Your Account
                    </a>
                    <a href="#" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 shadow-lg shadow-slate-900/10 dark:shadow-black/10">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.animate-gradient {
    animation: gradient 15s ease infinite;
    background-size: 400% 400%;
}

.bg-grid-slate-900 {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(15 23 42 / 0.04)'%3E%3Cpath d='M0 .5H31.5V32'/%3E%3C/svg%3E");
}

.bg-grid-slate-400 {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(148 163 184 / 0.05)'%3E%3Cpath d='M0 .5H31.5V32'/%3E%3C/svg%3E");
}
</style>

<?php require_once 'shared/footer.php'; ?>
