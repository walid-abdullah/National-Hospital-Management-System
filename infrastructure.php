<?php
require_once 'includes/header_public.php';
?>

<div class="bg-blue-600 dark:bg-slate-800 py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20 hero-pattern"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center animate-fade-in-up">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">Modern Infrastructure</h1>
        <p class="text-blue-100 text-lg max-w-2xl mx-auto">Experience healthcare in a state-of-the-art environment designed for healing.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 animate-fade-in-up delay-100">
    <div class="glass p-12 rounded-3xl shadow-xl border border-white/50 dark:border-white/10">
        <div class="flex flex-col md:flex-row gap-12 items-center">
            <div class="w-full md:w-1/2">
                <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="Hospital Infrastructure" class="rounded-2xl shadow-lg border-4 border-white dark:border-gray-800">
            </div>
            <div class="w-full md:w-1/2">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">World-Class Facilities</h2>
                <ul class="space-y-6">
                    <li class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold mt-1">1</div>
                        <div class="ml-4">
                            <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200">Advanced ICUs & CCUs</h4>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Equipped with central monitoring systems and life-saving ventilators ensuring 24/7 critical care.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold mt-1">2</div>
                        <div class="ml-4">
                            <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200">Digital Operation Theaters</h4>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Modular OTs with laminar airflow, ensuring zero-infection environments for complex surgeries.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold mt-1">3</div>
                        <div class="ml-4">
                            <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200">24/7 Pathology & Imaging</h4>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">High-end MRI, CT Scan, and automated pathology labs delivering precise results instantly.</p>
                        </div>
                    </li>
                </ul>
                <div class="mt-8">
                    <a href="book_online.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition-colors">Book an Appointment</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer_public.php';
?>
