<?php
// clean_files.php - Clean BOM and whitespace from PHP files
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Clean PHP Files</h1>";
echo "<hr>";

$files = ['api.php', 'config.php', 'database.php', 'auth.php'];

if (isset($_GET['clean']) && $_GET['clean'] === 'true') {
    echo "<h2>Cleaning Files...</h2>";
    echo "<pre style='background:#f0f0f0; padding:10px;'>";
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            // Read file
            $content = file_get_contents($file);
            $original_size = strlen($content);
            
            // Remove BOM if present
            if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                $content = substr($content, 3);
                echo "$file: Removed BOM\n";
            }
            
            // Ensure it starts with <?php
            $content = ltrim($content);
            if (substr($content, 0, 5) !== '<?php') {
                echo "$file: WARNING - doesn't start with <?php\n";
            }
            
            // Remove closing ?> if at the end
            $content = rtrim($content);
            if (substr($content, -2) === '?>') {
                $content = substr($content, 0, -2);
                $content = rtrim($content);
                echo "$file: Removed closing ?>\n";
            }
            
            // Backup original
            copy($file, $file . '.backup');
            
            // Write cleaned content
            file_put_contents($file, $content);
            
            $new_size = strlen($content);
            echo "$file: Cleaned (was $original_size bytes, now $new_size bytes)\n";
        } else {
            echo "$file: NOT FOUND\n";
        }
    }
    
    echo "\nBackup files created with .backup extension";
    echo "</pre>";
    
    echo "<p style='color:green'>✓ Files cleaned! <a href='api_debug.php'>Run Debug Again</a></p>";
} else {
    echo "<h2>File Status</h2>";
    echo "<pre style='background:#f0f0f0; padding:10px;'>";
    
    $issues_found = false;
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $issues = [];
            
            // Check for BOM
            if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                $issues[] = "Has UTF-8 BOM";
                $issues_found = true;
            }
            
            // Check for whitespace before <?php
            if (trim(substr($content, 0, 5)) !== substr($content, 0, 5)) {
                $issues[] = "Has whitespace at start";
                $issues_found = true;
            }
            
            // Check for closing ?>
            if (substr(rtrim($content), -2) === '?>') {
                $issues[] = "Has closing ?> tag";
                $issues_found = true;
            }
            
            if (empty($issues)) {
                echo "✓ $file: OK\n";
            } else {
                echo "✗ $file: " . implode(", ", $issues) . "\n";
            }
        } else {
            echo "✗ $file: NOT FOUND\n";
        }
    }
    echo "</pre>";
    
    if ($issues_found) {
        echo "<p style='color:orange'>⚠️ Issues found in some files.</p>";
        echo "<p><a href='?clean=true' style='background:#FF9800; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Clean All Files</a></p>";
    } else {
        echo "<p style='color:green'>✓ All files look good!</p>";
    }
}

echo "<hr>";
echo "<a href='api_debug.php'>Run Debug</a> | ";
echo "<a href='test_setup.php'>Back to Setup</a> | ";
echo "<a href='index.html'>Go to App</a>";
?>