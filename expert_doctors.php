<?php
require_once 'includes/header_public.php';
?>

<div class="bg-indigo-600 dark:bg-slate-800 py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20 hero-pattern"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center animate-fade-in-up">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">Expert Doctors</h1>
        <p class="text-indigo-100 text-lg max-w-2xl mx-auto">Meet the brilliant minds dedicated to your health and well-being.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 animate-fade-in-up delay-100">
    <div class="glass p-12 rounded-3xl shadow-xl border border-white/50 dark:border-white/10">
        <div class="flex flex-col md:flex-row-reverse gap-12 items-center">
            <div class="w-full md:w-1/2">
                <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="Expert Doctors" class="rounded-2xl shadow-lg border-4 border-white dark:border-gray-800">
            </div>
            <div class="w-full md:w-1/2">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">World-Renowned Specialists</h2>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                    Our team consists of highly qualified and experienced specialist doctors from around the globe. They bring decades of combined experience, cutting-edge medical knowledge, and deep compassion to patient care.
                </p>
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="bg-blue-50 dark:bg-slate-700 p-4 rounded-xl text-center">
                        <div class="text-3xl font-black text-blue-600 dark:text-blue-400 mb-1">50+</div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">Specialists</div>
                    </div>
                    <div class="bg-indigo-50 dark:bg-slate-700 p-4 rounded-xl text-center">
                        <div class="text-3xl font-black text-indigo-600 dark:text-indigo-400 mb-1">15+</div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">Departments</div>
                    </div>
                    <div class="bg-teal-50 dark:bg-slate-700 p-4 rounded-xl text-center">
                        <div class="text-3xl font-black text-teal-600 dark:text-teal-400 mb-1">24/7</div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">Availability</div>
                    </div>
                    <div class="bg-rose-50 dark:bg-slate-700 p-4 rounded-xl text-center">
                        <div class="text-3xl font-black text-rose-600 dark:text-rose-400 mb-1">100%</div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">Commitment</div>
                    </div>
                </div>
                <div>
                    <a href="public_doctors.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition-colors">Find a Doctor</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer_public.php';
?>
