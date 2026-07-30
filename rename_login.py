import os
import re

base_dir = "/Users/mac/.gemini/antigravity/scratch/Drive_244/Univ/8th S/Software Engineering/nhms"

# First, rename index.php to login.php
old_index = os.path.join(base_dir, "index.php")
new_login = os.path.join(base_dir, "login.php")

if os.path.exists(old_index):
    os.rename(old_index, new_login)
    print("Renamed index.php to login.php")

# Now search and replace in all files
for root, dirs, files in os.walk(base_dir):
    for file in files:
        if file.endswith('.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r') as f:
                content = f.read()
            
            original = content
            
            # Replace header("Location: ../index.php") or header("Location: index.php")
            content = re.sub(r'Location:\s*([^"]*)index\.php', r'Location: \1login.php', content)
            
            # Also in hrefs if there are any
            content = re.sub(r'href="([^"]*)index\.php"', r'href="\1login.php"', content)
            
            if content != original:
                with open(filepath, 'w') as f:
                    f.write(content)
                print(f"Updated references in {filepath}")

print("Done.")
