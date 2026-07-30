<?php
require_once 'includes/header_public.php';

// Demo static lab services
$services = [
    ['name' => 'Complete Blood Count (CBC)', 'price' => '৳ 1,200', 'desc' => 'Comprehensive blood test to evaluate your overall health and detect disorders.', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
    ['name' => 'MRI Scan', 'price' => '৳ 8,500', 'desc' => 'Magnetic Resonance Imaging using strong magnetic fields and radio waves to generate images.', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
    ['name' => 'Digital X-Ray', 'price' => '৳ 1,500', 'desc' => 'Quick and painless test that produces images of the structures inside your body.', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['name' => 'Lipid Profile', 'price' => '৳ 1,800', 'desc' => 'Measures the level of specific lipids in blood to assess cardiovascular risk.', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
    ['name' => 'ECG / EKG', 'price' => '৳ 1,000', 'desc' => 'Records the electrical signal from your heart to check for different heart conditions.', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
    ['name' => 'Thyroid Test (TSH)', 'price' => '৳ 2,200', 'desc' => 'Blood test that measures the levels of thyroid-stimulating hormone (TSH).', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
];
?>

<!-- Header Section -->
<div class="bg-indigo-600 dark:bg-slate-800 py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20 hero-pattern"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center animate-fade-in-up">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">Laboratory Services</h1>
        <p class="text-indigo-100 text-lg max-w-2xl mx-auto">Equipped with the latest medical technology, our laboratory provides highly accurate and swift diagnostic services.</p>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 animate-fade-in-up delay-100">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach($services as $service): ?>
            <div class="glass p-8 rounded-3xl hover:-translate-y-2 hover:shadow-2xl transition-all duration-300 border border-white/50 dark:border-white/10 flex flex-col h-full group">
                <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/40 rounded-2xl flex items-center justify-center mb-6 text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $service['icon']; ?>"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2"><?php echo $service['name']; ?></h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6 flex-grow"><?php echo $service['desc']; ?></p>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                    <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400"><?php echo $service['price']; ?></span>
                    <a href="book_lab.php?test_name=<?php echo urlencode($service['name']); ?>" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Book Test &rarr;</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
require_once 'includes/footer_public.php';
?>
