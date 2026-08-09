<?php
require_once 'includes/header_public.php';
require_once 'config/db.php';

// Fetch Top 3 Patient Feedbacks (5-star)
$feedback_stmt = $conn->query("
    SELECT f.rating, f.review, f.created_at, p.name as patient_name, h.name as hospital_name 
    FROM patient_feedback f 
    JOIN patients p ON f.patient_id = p.id 
    JOIN hospitals h ON f.hospital_id = h.id 
    WHERE f.rating = 5 
    ORDER BY f.created_at DESC 
    LIMIT 3
");
$feedbacks = $feedback_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Top 3 Doctors (Highest rated simulation)
$doc_stmt = $conn->query("
    SELECT d.id, d.name, d.specialization as specialty, h.name as hospital_name 
    FROM doctors d 
    JOIN hospitals h ON d.hospital_id = h.id 
    LIMIT 3
");
$top_doctors = $doc_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Detailed Branch Data
$hospitals_stmt = $conn->query("SELECT id, name, location FROM hospitals");
$hospitals_list = $hospitals_stmt->fetchAll(PDO::FETCH_ASSOC);

$branch_data = [];
foreach($hospitals_list as $h) {
    $hid = $h['id'];
    
    // Beds by category
    $stmt = $conn->prepare("
        SELECT w.type as ward_type, 
               SUM(CASE WHEN b.status = 'Available' THEN 1 ELSE 0 END) as available,
               SUM(CASE WHEN b.status = 'Occupied' THEN 1 ELSE 0 END) as occupied
        FROM beds b
        JOIN wards w ON b.ward_id = w.id
        WHERE w.hospital_id = ?
        GROUP BY w.type
    ");
    $stmt->execute([$hid]);
    $beds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Blood bank
    $stmt = $conn->prepare("SELECT blood_group, bags_available as total_bags FROM blood_bank WHERE hospital_id = ? AND bags_available > 0 ORDER BY bags_available DESC");
    $stmt->execute([$hid]);
    $blood = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambulances
    $stmt = $conn->prepare("SELECT driver_name, driver_phone, status, vehicle_number FROM ambulances WHERE hospital_id = ?");
    $stmt->execute([$hid]);
    $ambulances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $branch_data[] = [
        'id' => $hid,
        'name' => $h['name'],
        'location' => $h['location'],
        'beds' => $beds,
        'blood' => $blood,
        'ambulances' => $ambulances
    ];
}

// Convert to JSON for frontend interactivity
$branch_data_json = json_encode($branch_data);
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
                Experience world-class medical facilities, expert doctors, and seamless online appointments with the National Hospital Information Management System.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-10">
                <a href="book_online.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 px-8 rounded-2xl shadow-xl hover:shadow-indigo-500/40 transform hover:-translate-y-1 transition-all duration-300 text-lg flex items-center justify-center">
                    Book Appointment Now
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="public_doctors.php" class="glass text-gray-800 dark:text-white font-bold py-4 px-8 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-300 text-lg flex items-center justify-center">
                    Find a Doctor
                </a>
            </div>

            <!-- Smart Hospital Search Widget (Highlighted) -->
            <div class="bg-white dark:bg-slate-800 max-w-4xl mx-auto rounded-3xl p-6 sm:p-8 shadow-[0_20px_50px_rgba(37,_99,_235,_0.2)] dark:shadow-[0_20px_50px_rgba(0,_0,_0,_0.5)] border-2 border-blue-200 dark:border-slate-700 animate-fade-in-up delay-100 relative overflow-hidden group transform hover:scale-[1.02] transition-all duration-300">
                <!-- Top Gradient Banner -->
                <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                
                <!-- Glowing background effect -->
                <div class="absolute inset-0 bg-blue-50/50 dark:bg-blue-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                
                <h3 class="text-xl md:text-2xl font-extrabold text-gray-900 dark:text-white mb-6 text-center flex items-center justify-center relative z-10">
                    <span class="bg-blue-100 dark:bg-blue-900/50 p-2 rounded-xl mr-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </span>
                    Find Nearest Hospital
                </h3>
                
                <div class="flex flex-col md:flex-row gap-4 relative z-10">
                    <div class="flex-1 relative">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Select Location</label>
                        <select id="searchLocation" onchange="filterHospitals()" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-gray-200 dark:border-slate-700 text-gray-800 dark:text-gray-200 rounded-xl px-4 py-3.5 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 shadow-inner font-semibold transition-all">
                            <option value="">🗺️ Any Location (City)</option>
                            <?php 
                                $locations = array_unique(array_map(function($h) { return explode(',', $h['location'])[1] ?? $h['location']; }, $hospitals_list));
                                foreach($locations as $loc): 
                            ?>
                                <option value="<?php echo trim(htmlspecialchars($loc)); ?>"><?php echo trim(htmlspecialchars($loc)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1 relative">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Select Branch</label>
                        <select id="searchHospital" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-gray-200 dark:border-slate-700 text-gray-800 dark:text-gray-200 rounded-xl px-4 py-3.5 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 shadow-inner font-semibold transition-all">
                            <option value="">🏥 Select Hospital Branch</option>
                            <?php foreach($hospitals_list as $index => $h): ?>
                                <option value="<?php echo $index; ?>" data-location="<?php echo htmlspecialchars($h['location']); ?>"><?php echo htmlspecialchars($h['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="checkHospitalAvailability()" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all duration-300 shadow-lg hover:shadow-indigo-500/40 md:w-auto w-full flex-shrink-0 flex items-center justify-center transform hover:-translate-y-1">
                            Check Status
                            <svg class="w-5 h-5 ml-2 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <script>
                function filterHospitals() {
                    const loc = document.getElementById('searchLocation').value.toLowerCase();
                    const hospSelect = document.getElementById('searchHospital');
                    const options = hospSelect.options;
                    
                    let firstVisible = false;
                    for (let i = 1; i < options.length; i++) {
                        const optLoc = options[i].getAttribute('data-location').toLowerCase();
                        if (loc === "" || optLoc.includes(loc)) {
                            options[i].style.display = '';
                            if(!firstVisible) {
                                hospSelect.selectedIndex = i;
                                firstVisible = true;
                            }
                        } else {
                            options[i].style.display = 'none';
                        }
                    }
                    if(loc === "") hospSelect.selectedIndex = 0;
                }

                function checkHospitalAvailability() {
                    const hospIndex = document.getElementById('searchHospital').value;
                    if (hospIndex === "") {
                        alert("Please select a hospital branch first.");
                        return;
                    }
                    // Scroll to live resources section
                    document.getElementById('live-resources').scrollIntoView({ behavior: 'smooth' });
                    // Click the specific tab
                    setTimeout(() => {
                        const tabs = document.querySelectorAll('.branch-tab');
                        if(tabs[hospIndex]) {
                            tabs[hospIndex].click();
                        }
                    }, 500);
                }
            </script>
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
        <a href="infrastructure.php" class="block glass p-8 rounded-3xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer border border-transparent hover:border-blue-200 dark:hover:border-blue-800/50 hover:shadow-2xl hover:shadow-blue-500/10">
            <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center mb-6 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Modern Infrastructure</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">State-of-the-art facilities equipped with the latest medical technology for accurate diagnosis.</p>
        </a>
        
        <!-- Service 2 -->
        <a href="expert_doctors.php" class="block glass p-8 rounded-3xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer border border-transparent hover:border-indigo-200 dark:hover:border-indigo-800/50 hover:shadow-2xl hover:shadow-indigo-500/10">
            <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/40 rounded-2xl flex items-center justify-center mb-6 text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Expert Doctors</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Our team consists of highly qualified and experienced specialist doctors from around the globe.</p>
        </a>
        
        <!-- Service 3 -->
        <a href="online_reports.php" class="block glass p-8 rounded-3xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer border border-transparent hover:border-blue-200 dark:hover:border-blue-800/50 hover:shadow-2xl hover:shadow-blue-500/10">
            <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center mb-6 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Online Reports</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Access your medical history, prescriptions, and lab test results from anywhere via our portal.</p>
        </a>
    </div>
</div>

<!-- LIVE HOSPITAL RESOURCES SECTION -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24 animate-fade-in-up delay-300" id="live-resources">
    <div class="text-center mb-12">
        <span class="inline-block py-1 px-3 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm font-semibold mb-4 border border-red-200 dark:border-red-800/50">
            🔴 Live Updates
        </span>
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Real-Time Hospital Resources</h2>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-8">Check our live bed availability, emergency blood bank stock, and ambulances before you arrive.</p>
        
        <!-- Branch Selector Tabs -->
        <div class="flex flex-wrap justify-center gap-2 mb-8" id="branch-tabs">
            <!-- Tabs will be injected here by JS -->
        </div>
    </div>
    
    <div id="branch-content" class="hidden animate-fade-in-up">
        <!-- Dynamic Content Injected via JS -->
    </div>
</div>

<!-- Live Chat Widget -->
<div id="live-chat-widget" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
    <!-- Chat Window (Hidden by default) -->
    <div id="chat-window" class="hidden w-80 sm:w-96 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-slate-700 overflow-hidden mb-4 transition-all duration-300 transform scale-95 origin-bottom-right">
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4 flex justify-between items-center text-white">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-blue-600 font-bold">NH</div>
                <div>
                    <h4 class="font-bold text-sm">NHIMS Virtual Assistant</h4>
                    <p class="text-xs text-blue-200">Online 24/7</p>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-white hover:text-gray-200 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <!-- Chat Messages Area -->
        <div id="chat-messages" class="p-4 h-64 overflow-y-auto bg-slate-50 dark:bg-gray-900 space-y-3">
            <div class="flex">
                <div class="bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-2xl rounded-tl-none max-w-[85%] text-sm">
                    Hello! Welcome to the National Hospital Information Management System. How can I help you today?
                </div>
            </div>
        </div>
        
        <!-- Chat Input Area -->
        <div class="p-3 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 flex items-center gap-2">
            <input type="text" id="chat-input" placeholder="Type your message..." class="flex-1 bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-white px-4 py-2 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" onkeypress="handleChatEnter(event)">
            <button onclick="sendMessage()" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-full transition-colors flex-shrink-0">
                <svg class="w-5 h-5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
            </button>
        </div>
    </div>

    <!-- Toggle Button -->
    <button onclick="toggleChat()" id="chat-toggle-btn" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white w-14 h-14 rounded-full shadow-2xl flex items-center justify-center transform hover:scale-105 transition-all duration-300">
        <svg id="chat-icon-open" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        <svg id="chat-icon-close" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

<script>
    const branchData = <?php echo $branch_data_json; ?>;
    const tabsContainer = document.getElementById('branch-tabs');
    const contentContainer = document.getElementById('branch-content');
    
    function renderBranch(branchIndex) {
        const branch = branchData[branchIndex];
        
        // Update active tab styling
        document.querySelectorAll('.branch-tab').forEach((tab, idx) => {
            if(idx === branchIndex) {
                tab.classList.remove('bg-gray-100', 'text-gray-600', 'dark:bg-gray-800', 'dark:text-gray-400');
                tab.classList.add('bg-blue-600', 'text-white', 'shadow-lg');
            } else {
                tab.classList.add('bg-gray-100', 'text-gray-600', 'dark:bg-gray-800', 'dark:text-gray-400');
                tab.classList.remove('bg-blue-600', 'text-white', 'shadow-lg');
            }
        });

        // Generate Beds HTML
        let bedsHtml = '';
        if(branch.beds.length > 0) {
            bedsHtml = branch.beds.map(b => `
                <div class="flex justify-between items-center bg-gray-50 dark:bg-slate-700 p-3 rounded-lg mb-2">
                    <span class="font-semibold text-gray-700 dark:text-gray-200">${b.ward_type}</span>
                    <span class="text-green-600 dark:text-green-400 font-bold">${parseInt(b.available)} Available</span>
                </div>
            `).join('');
        } else {
            bedsHtml = `<p class="text-gray-500 text-sm">No beds configured yet.</p>`;
        }

        // Generate Blood HTML
        let bloodHtml = '';
        if(branch.blood.length > 0) {
            bloodHtml = `
            <div class="grid grid-cols-4 gap-3">
                ${branch.blood.map(bl => `
                    <div class="text-center bg-gray-50 dark:bg-slate-700 rounded-lg p-3">
                        <div class="text-lg font-bold text-red-500 mb-1">${bl.blood_group}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">${bl.total_bags} Bags</div>
                    </div>
                `).join('')}
            </div>`;
        } else {
            bloodHtml = `<p class="text-gray-500 text-sm">No blood stock available.</p>`;
        }

        // Generate Ambulances HTML
        let ambHtml = '';
        if(branch.ambulances.length > 0) {
            ambHtml = branch.ambulances.map(a => `
                <div class="flex flex-col sm:flex-row justify-between sm:items-center bg-gray-50 dark:bg-slate-700 p-3 rounded-lg mb-2 gap-2">
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-200">🚑 ${a.vehicle_number}</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Driver: ${a.driver_name}</p>
                    </div>
                    <div class="text-right flex flex-col sm:items-end">
                        <a href="tel:${a.driver_phone}" class="text-blue-600 dark:text-blue-400 font-bold text-sm bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded">📞 ${a.driver_phone}</a>
                        <span class="text-xs ${a.status === 'Available' ? 'text-green-500' : 'text-red-500'} font-semibold mt-1">${a.status}</span>
                    </div>
                </div>
            `).join('');
        } else {
            ambHtml = `<p class="text-gray-500 text-sm">No ambulances assigned.</p>`;
        }

        contentContainer.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Beds -->
                <div class="glass p-6 rounded-2xl border border-white/50 dark:border-slate-700 shadow-xl">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">🛏️ Bed Availability</h3>
                    ${bedsHtml}
                </div>
                <!-- Blood -->
                <div class="glass p-6 rounded-2xl border border-white/50 dark:border-slate-700 shadow-xl">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">🩸 Blood Bank</h3>
                    ${bloodHtml}
                </div>
                <!-- Ambulances -->
                <div class="glass p-6 rounded-2xl border border-white/50 dark:border-slate-700 shadow-xl">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">🚑 Ambulances</h3>
                        <span class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-0.5 rounded-full dark:bg-red-900/50 dark:text-red-300">Total: ${branch.ambulances.length}</span>
                    </div>
                    <div class="max-h-[300px] overflow-y-auto pr-2">
                        ${ambHtml}
                    </div>
                </div>
            </div>
        `;
        contentContainer.classList.remove('hidden');
    }

    // Chatbot Logic
    let isChatOpen = false;
    const chatWindow = document.getElementById('chat-window');
    const chatIconOpen = document.getElementById('chat-icon-open');
    const chatIconClose = document.getElementById('chat-icon-close');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');

    function toggleChat() {
        isChatOpen = !isChatOpen;
        if (isChatOpen) {
            chatWindow.classList.remove('hidden');
            setTimeout(() => {
                chatWindow.classList.remove('scale-95');
                chatWindow.classList.add('scale-100');
            }, 10);
            chatIconOpen.classList.add('hidden');
            chatIconClose.classList.remove('hidden');
            chatInput.focus();
        } else {
            chatWindow.classList.remove('scale-100');
            chatWindow.classList.add('scale-95');
            setTimeout(() => {
                chatWindow.classList.add('hidden');
            }, 300);
            chatIconClose.classList.add('hidden');
            chatIconOpen.classList.remove('hidden');
        }
    }

    function handleChatEnter(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    }

    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        // Add user message
        appendMessage(text, 'user');
        chatInput.value = '';

        // Simulate typing delay
        setTimeout(() => {
            const reply = getBotReply(text.toLowerCase());
            appendMessage(reply, 'bot');
        }, 800);
    }

    function appendMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'flex ' + (sender === 'user' ? 'justify-end' : '');
        
        const innerDiv = document.createElement('div');
        innerDiv.className = sender === 'user' 
            ? 'bg-blue-600 text-white px-4 py-2 rounded-2xl rounded-tr-none max-w-[85%] text-sm'
            : 'bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-2xl rounded-tl-none max-w-[85%] text-sm';
        
        innerDiv.innerText = text;
        msgDiv.appendChild(innerDiv);
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function getBotReply(msg) {
        if (msg.includes('book') || msg.includes('appointment')) {
            return "You can book an appointment easily! Just click on the 'Book Appointment' button in the top menu or click on any doctor's profile.";
        } else if (msg.includes('doctor') || msg.includes('specialist')) {
            return "We have 50+ specialist doctors across multiple departments. You can browse them by clicking 'Find a Doctor' at the top.";
        } else if (msg.includes('blood')) {
            return "You can check live blood bank availability for all our hospital branches in the 'Live Updates' section on this homepage!";
        } else if (msg.includes('ambulance') || msg.includes('emergency')) {
            return "Our ambulance service runs 24/7. Check the 'Live Updates' section below to find direct phone numbers of available drivers in your city.";
        } else if (msg.includes('lab') || msg.includes('test') || msg.includes('report')) {
            return "We provide state-of-the-art lab testing. Once your test is done, you can log into the Patient Portal to view your Online Reports anytime.";
        } else if (msg.includes('hello') || msg.includes('hi')) {
            return "Hello there! How can I assist you with your healthcare needs today?";
        } else {
            return "I'm still learning! For specific queries, please contact our support at info@nhims-hospital.com or call +880 1234-567890.";
        }
    }

    // Initialize Branch Data
    if(branchData.length > 0) {
        branchData.forEach((branch, index) => {
            const btn = document.createElement('button');
            btn.className = `branch-tab px-6 py-2 rounded-full font-semibold transition-all duration-300`;
            btn.innerText = branch.name;
            btn.onclick = () => renderBranch(index);
            tabsContainer.appendChild(btn);
        });
        
        // Render first branch by default
        renderBranch(0);
    } else {
        tabsContainer.innerHTML = '<p class="text-gray-500">No active branches found.</p>';
    }
</script>

<!-- TOP DOCTORS SECTION -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24 animate-fade-in-up delay-400">
    <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Our Top Rated Doctors</h2>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Meet our highly acclaimed specialists, rated 5-stars by our patients.</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach($top_doctors as $doc): ?>
        <div class="glass rounded-3xl p-6 text-center border border-white/50 dark:border-slate-700 shadow-lg hover:-translate-y-2 transition duration-300">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($doc['name']); ?>&background=random" alt="Doctor" class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-md mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($doc['name']); ?></h3>
            <p class="text-blue-600 dark:text-blue-400 font-medium mb-1"><?php echo htmlspecialchars($doc['specialty']); ?></p>
            <p class="text-sm text-gray-500 mb-4"><?php echo htmlspecialchars($doc['hospital_name']); ?></p>
            <div class="flex justify-center items-center space-x-1 mb-4">
                <?php for($i=0; $i<5; $i++): ?>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                <?php endfor; ?>
                <span class="ml-2 font-bold text-gray-700 dark:text-gray-300">5.0</span>
            </div>
            <a href="book_online.php?doctor_id=<?php echo $doc['id']; ?>" class="inline-block bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-6 py-2 rounded-full font-semibold hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white transition">Book Appointment</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- PATIENT REVIEWS SECTION -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-32 animate-fade-in-up delay-500">
    <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">What Our Patients Say</h2>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Real experiences from patients who trust us with their healthcare journey.</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach($feedbacks as $fb): ?>
        <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl shadow-xl relative mt-8 border border-gray-100 dark:border-slate-700">
            <!-- Quote Icon -->
            <div class="absolute top-0 right-8 -mt-6 w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white shadow-lg">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
            </div>
            
            <div class="flex mb-4">
                <?php for($i=0; $i<$fb['rating']; $i++): ?>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                <?php endfor; ?>
            </div>
            
            <p class="text-gray-600 dark:text-gray-300 italic mb-6 leading-relaxed">"<?php echo htmlspecialchars($fb['review']); ?>"</p>
            
            <div class="flex items-center">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-lg mr-3">
                    <?php echo substr($fb['patient_name'], 0, 1); ?>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($fb['patient_name']); ?></h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($fb['hospital_name']); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
require_once 'includes/footer_public.php';
?>
