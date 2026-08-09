<?php
require_once 'includes/header_public.php';
require_once 'config/db.php';

// Fetch all hospitals
$hospitals_stmt = $conn->query("SELECT id, name, location, contact_number, email FROM hospitals");
$hospitals_list = $hospitals_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique cities/locations for filter
$locations = array_unique(array_map(function($h) { 
    $parts = explode(',', $h['location']);
    return count($parts) > 1 ? trim($parts[1]) : trim($h['location']); 
}, $hospitals_list));

// Enrich hospital data with quick stats
foreach($hospitals_list as &$h) {
    // Total Doctors
    $doc_stmt = $conn->prepare("SELECT COUNT(*) FROM doctors WHERE hospital_id = ?");
    $doc_stmt->execute([$h['id']]);
    $h['total_doctors'] = $doc_stmt->fetchColumn();

    // Total Available Beds
    $bed_stmt = $conn->prepare("SELECT COUNT(*) FROM beds b JOIN wards w ON b.ward_id = w.id WHERE w.hospital_id = ? AND b.status = 'Available'");
    $bed_stmt->execute([$h['id']]);
    $h['available_beds'] = $bed_stmt->fetchColumn();
    
    // Ambulances
    $amb_stmt = $conn->prepare("SELECT COUNT(*) FROM ambulances WHERE hospital_id = ? AND status = 'Available'");
    $amb_stmt->execute([$h['id']]);
    $h['available_ambulances'] = $amb_stmt->fetchColumn();
}
unset($h);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 animate-fade-in-up">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4">
            Our <span class="custom-gradient-text">Hospital Network</span>
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            Find a branch near you. We have multiple branches equipped with modern facilities across the country.
        </p>
    </div>

    <!-- Filter/Search Bar -->
    <div class="glass max-w-2xl mx-auto rounded-2xl p-4 sm:p-6 mb-12 shadow-lg border border-gray-200 dark:border-slate-700 relative z-20">
        <div class="flex flex-col sm:flex-row gap-4 items-center">
            <div class="w-full flex-1">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Filter by City</label>
                <select id="locationFilter" onchange="filterHospitals()" class="w-full bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm appearance-none font-medium">
                    <option value="all">🌐 All Locations</option>
                    <?php foreach($locations as $loc): ?>
                        <option value="<?php echo htmlspecialchars($loc); ?>"><?php echo htmlspecialchars($loc); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full flex-1">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Search Branch</label>
                <div class="relative">
                    <input type="text" id="nameSearch" onkeyup="filterHospitals()" placeholder="e.g. Apollo" class="w-full bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-gray-300 rounded-xl px-4 py-3 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm font-medium">
                    <svg class="w-5 h-5 absolute left-3 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Hospitals Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="hospitalsGrid">
        <?php foreach($hospitals_list as $h): 
            $city = count(explode(',', $h['location'])) > 1 ? trim(explode(',', $h['location'])[1]) : trim($h['location']);
        ?>
        <div class="hospital-card glass rounded-3xl p-6 shadow-xl border border-white/50 dark:border-slate-700 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-300 flex flex-col h-full group" data-city="<?php echo htmlspecialchars($city); ?>" data-name="<?php echo strtolower(htmlspecialchars($h['name'])); ?>">
            
            <div class="mb-4">
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-2.5 py-1 rounded-full dark:bg-indigo-900/50 dark:text-indigo-300">
                        <?php echo $h['total_doctors']; ?> Doctors
                    </span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2"><?php echo htmlspecialchars($h['name']); ?></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-start">
                    <svg class="w-4 h-4 mr-1 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <?php echo htmlspecialchars($h['location']); ?>
                </p>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-slate-800 p-3 rounded-xl border border-gray-100 dark:border-slate-700 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold mb-1">Available Beds</p>
                    <p class="text-xl font-bold <?php echo $h['available_beds'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500'; ?>"><?php echo $h['available_beds']; ?></p>
                </div>
                <div class="bg-gray-50 dark:bg-slate-800 p-3 rounded-xl border border-gray-100 dark:border-slate-700 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold mb-1">Ambulances</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400"><?php echo $h['available_ambulances']; ?></p>
                </div>
            </div>
            
            <div class="mt-auto pt-4 border-t border-gray-200 dark:border-slate-700/50">
                <div class="flex justify-between items-center mb-4">
                    <a href="tel:<?php echo htmlspecialchars($h['contact_number']); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <?php echo htmlspecialchars($h['contact_number']); ?>
                    </a>
                </div>
                
                <a href="book_online.php?hospital_id=<?php echo $h['id']; ?>" class="block w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl text-center shadow-lg transition-transform transform hover:-translate-y-1">
                    Book Appointment Here
                </a>
            </div>
            
        </div>
        <?php endforeach; ?>
    </div>
    
    <div id="noResults" class="hidden text-center py-12">
        <div class="text-6xl mb-4">🏥</div>
        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">No branches found</h3>
        <p class="text-gray-500 dark:text-gray-400">Try adjusting your search criteria.</p>
    </div>

</div>

<script>
function filterHospitals() {
    const selectedCity = document.getElementById('locationFilter').value.toLowerCase();
    const searchQuery = document.getElementById('nameSearch').value.toLowerCase();
    const cards = document.querySelectorAll('.hospital-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const cardCity = card.getAttribute('data-city').toLowerCase();
        const cardName = card.getAttribute('data-name');
        
        const matchesCity = selectedCity === 'all' || cardCity.includes(selectedCity);
        const matchesName = cardName.includes(searchQuery);
        
        if (matchesCity && matchesName) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
}
</script>

<?php
require_once 'includes/footer_public.php';
?>
