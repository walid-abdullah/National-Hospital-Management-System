<?php
require_once 'includes/header_public.php';
?>

<div class="bg-teal-600 dark:bg-slate-800 py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20 hero-pattern"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center animate-fade-in-up">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">Online Reports</h1>
        <p class="text-teal-100 text-lg max-w-2xl mx-auto">Access your medical data securely from anywhere, at any time.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 animate-fade-in-up delay-100">
    <div class="glass p-12 rounded-3xl shadow-xl border border-white/50 dark:border-white/10">
        <div class="flex flex-col md:flex-row gap-12 items-center">
            <div class="w-full md:w-1/2">
                <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="Online Reports Portal" class="rounded-2xl shadow-lg border-4 border-white dark:border-gray-800">
            </div>
            <div class="w-full md:w-1/2">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Your Health Data in Your Pocket</h2>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                    Say goodbye to carrying physical files. With the NHIMS Patient Portal, all your medical history, prescriptions, and lab test results are securely stored and accessible online.
                </p>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-6 h-6 text-teal-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Instant access to Lab Test Results
                    </li>
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-6 h-6 text-teal-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Digital Prescriptions directly from Doctors
                    </li>
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-6 h-6 text-teal-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Complete Medical History Timeline
                    </li>
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-6 h-6 text-teal-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Secure and Encrypted Data Storage
                    </li>
                </ul>
                <div>
                    <a href="login.php" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition-colors">Login to Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer_public.php';
?>
