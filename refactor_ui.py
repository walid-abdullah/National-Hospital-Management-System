import os
import glob
import re

base_dir = "/Users/mac/.gemini/antigravity/scratch/Drive_244/Univ/8th S/Software Engineering/nhms"

head_injection = """
    <script>
        tailwind.config = {
          darkMode: 'class',
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        
        .animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .custom-gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
    <script>
        // Check local storage for dark mode preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
    </script>
</head>
"""

dark_mode_button = """
    <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 mr-2 text-gray-600 dark:text-gray-300" title="Toggle Dark Mode">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
    </button>
"""

footer_injection = """
    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Management System. Designed for Software Engineering Project.
    </footer>
</body>
"""

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    # Skip files that were already processed completely by just re-fetching original state, but we can't easily revert.
    # Instead, we will do targeted replacements.
    
    # 1. Update Head (if not already updated with dark mode JS)
    if 'toggleDarkMode()' not in content and '</head>' in content:
        # Remove previous injection if exists by removing everything between <link rel="preconnect" and </head>
        content = re.sub(r'<link rel="preconnect".*?</head>', head_injection, content, flags=re.DOTALL)
        if 'tailwind.config' not in content:
            content = content.replace('</head>', head_injection)

    # 2. Body Layout (Fix the bottom gap and add dark mode bg)
    content = re.sub(r'<body class="([^"]*)">', r'<body class="\1 flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">', content)
    # Deduplicate flex col if it got added multiple times
    content = content.replace('flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100 flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100', 'flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100')

    # 3. Add Dark Mode Toggle to Navbar
    if 'toggleDarkMode()' not in content:
        # Find the logout button or similar and prepend the toggle button
        content = content.replace('<a href="../logout.php"', dark_mode_button + '<a href="../logout.php"')
        # Handle index.php login which doesn't have logout
        if 'index.php' in filepath and 'toggleDarkMode()' not in content:
            # We don't necessarily need a navbar on the login page, but let's add it top right
            content = content.replace('class="min-h-screen flex items-center justify-center', 'class="min-h-screen flex items-center justify-center relative')
            content = content.replace('</form>', '</form><div class="absolute top-4 right-4">' + dark_mode_button + '</div>')

    # 4. Inject Footer to fix the bottom gap visually
    if '<footer' not in content:
        content = content.replace('</body>', footer_injection)

    # 5. Add dark classes to common text and borders
    content = content.replace('text-gray-800', 'text-gray-800 dark:text-gray-100')
    content = content.replace('text-gray-700', 'text-gray-700 dark:text-gray-200')
    content = content.replace('text-gray-600', 'text-gray-600 dark:text-gray-300')
    content = content.replace('text-gray-500', 'text-gray-500 dark:text-gray-400')
    content = content.replace('bg-gray-100', 'bg-gray-100 dark:bg-gray-800')
    content = content.replace('bg-gray-50', 'bg-gray-50 dark:bg-gray-900/50')
    content = content.replace('bg-white', 'bg-white dark:bg-slate-800')
    content = content.replace('border-gray-100', 'border-gray-100 dark:border-slate-700')
    content = content.replace('border-gray-200', 'border-gray-200 dark:border-slate-700')
    content = content.replace('border-gray-300', 'border-gray-300 dark:border-slate-600')
    
    # Table headers
    content = content.replace('bg-gray-100 text-gray-600 dark:text-gray-300 uppercase', 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 uppercase')
    
    # Table rows
    content = content.replace('hover:bg-blue-50', 'hover:bg-blue-50 dark:hover:bg-slate-700')
    
    # Inputs
    content = content.replace('focus:ring-blue-500 outline-none', 'focus:ring-blue-500 outline-none dark:bg-slate-700 dark:text-white dark:border-slate-600')

    with open(filepath, 'w') as f:
        f.write(content)
    print(f"Added Dark Mode & Layout Fixes to {filepath}")

for root, dirs, files in os.walk(base_dir):
    for file in files:
        if file.endswith('.php'):
            process_file(os.path.join(root, file))

print("Dark Mode Injection Complete.")
