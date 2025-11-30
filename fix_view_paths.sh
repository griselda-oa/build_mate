#!/bin/bash
# Fix hardcoded /build_mate/ paths in view files

# Find all PHP files in views directory
find views -name "*.php" -type f | while read file; do
    # Create backup
    cp "$file" "$file.bak"
    
    # Replace href="/build_mate/..." with href="<?= \App\View::url('/...') ?>"
    sed -i '' 's|href="/build_mate/\([^"]*\)"|href="<?= \\App\\View::url('\''/\1'\'') ?>"|g' "$file"
    
    # Replace action="/build_mate/..." with action="<?= \App\View::url('/...') ?>"
    sed -i '' 's|action="/build_mate/\([^"]*\)"|action="<?= \\App\\View::url('\''/\1'\'') ?>"|g' "$file"
    
    # Replace src="/build_mate/assets/..." with src="<?= \App\View::asset('assets/...') ?>"
    sed -i '' 's|src="/build_mate/assets/\([^"]*\)"|src="<?= \\App\\View::asset('\''assets/\1'\'') ?>"|g' "$file"
    
    # Replace onerror="this.src='/build_mate/..." with onerror="this.src='<?= \App\View::asset('...') ?>'"
    sed -i '' "s|onerror=\"this.src='/build_mate/\([^']*\)'\"|onerror=\"this.src='<?= \\\\App\\\\View::asset('\''\1'\'') ?>'\"|g" "$file"
    
    echo "Fixed: $file"
done

echo "Done!"

