<?php
require_once 'config/db.php';

// Fetch specializations for filter
try {
    $stmt = $conn->prepare("SELECT DISTINCT specialization FROM doctors WHERE specialization IS NOT NULL AND specialization != ''");
    $stmt->execute();
    $specializations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $specializations = [];
}

// Fetch doctors
$filter = isset($_GET['specialization']) ? $_GET['specialization'] : '';
try {
    if ($filter) {
        $stmt = $conn->prepare("SELECT * FROM doctors WHERE specialization = :spec ORDER BY doctor_name ASC");
        $stmt->bindParam(':spec', $filter);
    } else {
        $stmt = $conn->prepare("SELECT * FROM doctors ORDER BY doctor_name ASC");
    }
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $doctors = [];
}

require_once 'includes/header_public.php';
?>

<!-- Header Section -->
<div class="bg-blue-600 dark:bg-slate-800 py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20 hero-pattern"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center animate-fade-in-up">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">Find a Specialist</h1>
        <p class="text-blue-100 text-lg max-w-2xl mx-auto">Browse through our directory of world-class medical professionals and find the right expert for your needs.</p>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col md:flex-row gap-8 animate-fade-in-up delay-100">
    
    <!-- Sidebar Filter -->
    <div class="w-full md:w-1/4">
        <div class="glass p-6 rounded-2xl shadow-lg border border-white/50 dark:border-white/10 sticky top-28">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Filter by Specialty</h3>
            <div class="space-y-2">
                <a href="public_doctors.php" class="block px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter == '' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'; ?>">
                    All Specialists
                </a>
                <?php foreach($specializations as $spec): ?>
                    <a href="public_doctors.php?specialization=<?php echo urlencode($spec); ?>" class="block px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter == $spec ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'; ?>">
                        <?php echo htmlspecialchars($spec); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Doctors Grid -->
    <div class="w-full md:w-3/4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if(!empty($doctors)): ?>
                <?php foreach($doctors as $doc): ?>
                    <div class="glass rounded-2xl overflow-hidden shadow-lg border border-white/50 dark:border-white/10 hover:-translate-y-2 hover:shadow-xl transition-all duration-300 flex flex-col">
                        <div class="h-32 bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center">
                            <!-- Avatar Placeholder -->
                            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-inner mt-16 text-3xl font-bold text-blue-600">
                                <?php echo substr($doc['doctor_name'], 0, 1); ?>
                            </div>
                        </div>
                        <div class="p-6 pt-10 flex-grow text-center">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1"><?php echo htmlspecialchars($doc['doctor_name']); ?></h3>
                            <p class="text-indigo-600 dark:text-indigo-400 font-medium mb-4"><?php echo htmlspecialchars($doc['specialization']); ?></p>
                            
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 text-sm text-gray-600 dark:text-gray-400 mb-6 flex flex-col gap-2">
                                <div class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <?php echo htmlspecialchars($doc['schedule']); ?>
                                </div>
                                <div class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    <?php echo htmlspecialchars($doc['phone']); ?>
                                </div>
                            </div>
                            
                            <a href="book_online.php?doctor_id=<?php echo $doc['doctor_id']; ?>" class="block w-full bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-semibold py-2 px-4 rounded-xl transition-colors">
                                Book Appointment
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full glass p-12 rounded-3xl text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Doctors Found</h3>
                    <p class="text-gray-500">We couldn't find any doctors matching the selected specialty.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer_public.php';
?>
