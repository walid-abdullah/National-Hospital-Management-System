<?php
require_once 'includes/header_public.php';
?>

<!-- Hero Section -->
<div class="relative overflow-hidden hero-pattern">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 md:pt-32 md:pb-32 relative z-10">
        <div class="text-center max-w-4xl mx-auto animate-fade-in-up">
            <span class="inline-block py-1 px-3 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-semibold mb-6 border border-blue-200 dark:border-blue-800/50">
                🚀 Welcome to the Future of Healthcare
            </span>
            <h1 class="text-5xl md:text-7xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-8 leading-tight">
                Your Health Is Our <br/>
                <span class="custom-gradient-text">Top Priority</span>
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-10 max-w-2xl mx-auto leading-relaxed">
                Experience world-class medical facilities, expert doctors, and seamless online appointments with the National Hospital Management System.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="book_online.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 px-8 rounded-2xl shadow-xl hover:shadow-indigo-500/40 transform hover:-translate-y-1 transition-all duration-300 text-lg flex items-center justify-center">
                    Book Appointment Now
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="public_doctors.php" class="glass text-gray-800 dark:text-white font-bold py-4 px-8 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-300 text-lg flex items-center justify-center">
                    Find a Doctor
                </a>
            </div>
        </div>
    </div>
    
    <!-- Decorative Blurs -->
    <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 w-96 h-96 bg-blue-400/20 dark:bg-blue-600/20 rounded-full mix-blend-multiply filter blur-3xl animate-pulse"></div>
    <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 w-96 h-96 bg-indigo-400/20 dark:bg-indigo-600/20 rounded-full mix-blend-multiply filter blur-3xl animate-pulse animation-delay-2000"></div>
</div>

<!-- Stats Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 relative z-20 animate-fade-in-up delay-100 mb-24">
    <div class="glass rounded-3xl p-8 shadow-2xl border border-white/50 dark:border-white/10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 divide-x divide-gray-200 dark:divide-gray-800">
            <div class="text-center px-4">
                <div class="text-4xl font-extrabold text-blue-600 dark:text-blue-400 mb-2">50+</div>
                <div class="text-gray-600 dark:text-gray-400 font-medium">Specialist Doctors</div>
            </div>
            <div class="text-center px-4">
                <div class="text-4xl font-extrabold text-indigo-600 dark:text-indigo-400 mb-2">10k+</div>
                <div class="text-gray-600 dark:text-gray-400 font-medium">Happy Patients</div>
            </div>
            <div class="text-center px-4">
                <div class="text-4xl font-extrabold text-blue-600 dark:text-blue-400 mb-2">24/7</div>
                <div class="text-gray-600 dark:text-gray-400 font-medium">Emergency Care</div>
            </div>
            <div class="text-center px-4">
                <div class="text-4xl font-extrabold text-indigo-600 dark:text-indigo-400 mb-2">15+</div>
                <div class="text-gray-600 dark:text-gray-400 font-medium">Departments</div>
            </div>
        </div>
    </div>
</div>

<!-- Services Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24 animate-fade-in-up delay-200">
    <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Our Premium Services</h2>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">We provide a wide range of medical services to ensure you and your family receive the best possible care.</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Service 1 -->
        <div class="glass p-8 rounded-3xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer border border-transparent hover:border-blue-200 dark:hover:border-blue-800/50 hover:shadow-2xl hover:shadow-blue-500/10">
            <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center mb-6 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Modern Infrastructure</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">State-of-the-art facilities equipped with the latest medical technology for accurate diagnosis.</p>
        </div>
        
        <!-- Service 2 -->
        <div class="glass p-8 rounded-3xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer border border-transparent hover:border-indigo-200 dark:hover:border-indigo-800/50 hover:shadow-2xl hover:shadow-indigo-500/10">
            <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/40 rounded-2xl flex items-center justify-center mb-6 text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Expert Doctors</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Our team consists of highly qualified and experienced specialist doctors from around the globe.</p>
        </div>
        
        <!-- Service 3 -->
        <div class="glass p-8 rounded-3xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer border border-transparent hover:border-blue-200 dark:hover:border-blue-800/50 hover:shadow-2xl hover:shadow-blue-500/10">
            <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center mb-6 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Online Reports</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Access your medical history, prescriptions, and lab test results from anywhere via our portal.</p>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer_public.php';
?>
