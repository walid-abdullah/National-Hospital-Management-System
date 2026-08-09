<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, hospital_id FROM patients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if ($patient) {
    $patient_id = $patient['id'];
    $hospital_id = $patient['hospital_id'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
        $rating = (int)$_POST['rating'];
        $review = trim($_POST['review']);
        
        $ins = $conn->prepare("INSERT INTO patient_feedback (hospital_id, patient_id, rating, review) VALUES (?, ?, ?, ?)");
        $ins->execute([$hospital_id, $patient_id, $rating, $review]);
        $success = "Thank you for your feedback!";
    }

    $stmt2 = $conn->prepare("SELECT * FROM patient_feedback WHERE patient_id = :patient_id ORDER BY created_at DESC");
    $stmt2->execute([':patient_id' => $patient_id]);
    $feedbacks = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} else {
    $feedbacks = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provide Feedback - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        .custom-gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHIMS - Patient Portal</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Dashboard</a>
            <a href="feedback.php" class="text-blue-600 dark:text-blue-400 font-medium border-b-2 border-blue-600">Feedback</a>
            <span class="border-l border-teal-400 h-6 mx-2"></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto mt-10 p-6 w-full grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <div class="glass p-8 rounded-3xl shadow-xl border border-white/50 h-fit">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">Share Your Experience</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6 text-sm">We value your feedback to improve our services.</p>
            
            <?php if(isset($success)) echo "<div class='mb-6 p-4 bg-green-100 text-green-800 rounded-lg text-sm font-semibold border border-green-200'>$success</div>"; ?>
            
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rating (1 to 5 Stars)</label>
                    <div class="flex gap-2 text-2xl cursor-pointer" id="star-rating">
                        <span data-val="1">⭐</span>
                        <span data-val="2" class="opacity-30">⭐</span>
                        <span data-val="3" class="opacity-30">⭐</span>
                        <span data-val="4" class="opacity-30">⭐</span>
                        <span data-val="5" class="opacity-30">⭐</span>
                    </div>
                    <input type="hidden" name="rating" id="rating-input" value="1">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Review</label>
                    <textarea name="review" rows="4" required class="w-full p-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm transition"></textarea>
                </div>
                
                <button type="submit" name="submit_feedback" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">Submit Feedback</button>
            </form>
        </div>
        
        <div class="glass p-8 rounded-3xl shadow-xl border border-white/50">
            <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-white">Your Past Feedbacks</h2>
            <div class="space-y-4">
                <?php foreach($feedbacks as $fb): ?>
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex gap-1 text-sm">
                                <?php for($i=0; $i<$fb['rating']; $i++) echo "⭐"; ?>
                            </div>
                            <span class="text-xs text-gray-500"><?php echo date('d M Y, h:i A', strtotime($fb['created_at'])); ?></span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 italic">"<?php echo htmlspecialchars($fb['review']); ?>"</p>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($feedbacks)): ?>
                    <p class="text-gray-500 text-sm text-center italic">No feedbacks submitted yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script>
        const stars = document.querySelectorAll('#star-rating span');
        const ratingInput = document.getElementById('rating-input');
        
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const val = parseInt(star.dataset.val);
                ratingInput.value = val;
                stars.forEach(s => {
                    if (parseInt(s.dataset.val) <= val) {
                        s.classList.remove('opacity-30');
                    } else {
                        s.classList.add('opacity-30');
                    }
                });
            });
        });
    </script>
</body>
</html>
