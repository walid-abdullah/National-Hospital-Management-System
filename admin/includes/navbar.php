<!-- Navbar -->
<nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm text-gray-800 dark:text-gray-100 flex justify-between items-center bg-white/90 dark:bg-slate-800/90 backdrop-blur-md border-b border-gray-200 dark:border-slate-700">
    <h1 class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 tracking-tight">NHMS Admin</h1>
    <div class="flex items-center space-x-4">
        <span class="font-medium">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm transition text-white shadow-md">Logout</a>
    </div>
</nav>
