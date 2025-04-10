<?php
require_once 'config/config.php';
require_once 'shared/header.php';
?>
<main class="min-h-screen">
    <!-- Hero Section with Animated Background -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50 animate-gradient"></div>
        <div class="relative container mx-auto px-4 py-32">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-6xl font-bold tracking-tight text-gray-900 mb-6 animate-fade-in">
                    Welcome to <span class="text-indigo-600">SITIn</span> Lab Scheduler
                </h1>
                <p class="text-xl text-gray-600 mb-12 animate-fade-in-up">
                    Streamline your computer laboratory access with our efficient scheduling system. Book your lab sessions and manage your time effectively.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up">
                    <a href="register.php" 
                       class="group inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-indigo-500/50">
                        <span>Get Started</span>
                        <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="login.php" 
                       class="group inline-flex items-center justify-center px-8 py-4 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-gray-500/20">
                        <span>Sign In</span>
                        <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-24 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Why Choose SITIn?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-200 transition-all duration-300 hover:shadow-lg group">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Easy Lab Booking</h3>
                    <p class="text-gray-600">Quickly reserve computer lab slots for your classes, projects, or research work with our user-friendly interface.</p>
                </div>
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-200 transition-all duration-300 hover:shadow-lg group">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Real-time Availability</h3>
                    <p class="text-gray-600">View up-to-date lab schedules and availability to plan your sessions efficiently.</p>
                </div>
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-200 transition-all duration-300 hover:shadow-lg group">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Secure Access</h3>
                    <p class="text-gray-600">Your academic credentials are protected with university-standard security measures.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="py-16 bg-gradient-to-br from-indigo-50 to-purple-50">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-indigo-600 mb-2">6</div>
                    <div class="text-gray-600">Computer Labs</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-indigo-600 mb-2">500+</div>
                    <div class="text-gray-600">Daily Bookings</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-indigo-600 mb-2">8AM-5PM</div>
                    <div class="text-gray-600">Access Hours</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-indigo-600 mb-2">100%</div>
                    <div class="text-gray-600">Student Satisfaction</div>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="py-24 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-indigo-600">1</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Register</h3>
                    <p class="text-gray-600">Create your account using your university credentials</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-indigo-600">2</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Book</h3>
                    <p class="text-gray-600">Select your preferred lab and time slot</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-indigo-600">3</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Access</h3>
                    <p class="text-gray-600">Use your booking confirmation to access the lab</p>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    @keyframes gradient {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }

    .animate-gradient {
        background-size: 200% 200%;
        animation: gradient 15s ease infinite;
    }

    @keyframes fade-in {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 1s ease-out;
    }

    .animate-fade-in-up {
        animation: fade-in-up 1s ease-out;
    }
</style>

<?php
require_once 'shared/footer.php';
?>
