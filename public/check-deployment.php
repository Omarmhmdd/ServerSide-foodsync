<?php
/**
 * Deployment Diagnostic Script
 * Access this file directly: https://your-domain.com/check-deployment.php
 * Remove this file after fixing deployment issues
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laravel Deployment Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        h1 { color: #333; }
        ul { line-height: 1.8; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Laravel Deployment Diagnostic</h1>
        
        <?php
        $issues = [];
        $warnings = [];
        $success = [];
        
        // Check 1: PHP Version
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '8.1.0', '>=')) {
            $success[] = "PHP Version: $phpVersion ✓";
        } else {
            $issues[] = "PHP Version: $phpVersion (Requires 8.1 or higher)";
        }
        
        // Check 2: Required Extensions
        $requiredExtensions = ['pdo', 'mbstring', 'openssl', 'json', 'tokenizer', 'xml', 'ctype', 'fileinfo'];
        $missingExtensions = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }
        if (empty($missingExtensions)) {
            $success[] = "Required PHP extensions installed ✓";
        } else {
            $issues[] = "Missing PHP extensions: " . implode(', ', $missingExtensions);
        }
        
        // Check 3: Directory Structure
        $baseDir = dirname(__DIR__);
        $requiredDirs = [
            'app' => "$baseDir/app",
            'bootstrap' => "$baseDir/bootstrap",
            'config' => "$baseDir/config",
            'routes' => "$baseDir/routes",
            'storage' => "$baseDir/storage",
            'vendor' => "$baseDir/vendor",
        ];
        
        foreach ($requiredDirs as $name => $path) {
            if (is_dir($path)) {
                $success[] = "Directory exists: $name ✓";
            } else {
                $issues[] = "Missing directory: $name ($path)";
            }
        }
        
        // Check 4: Storage Permissions
        $storagePath = "$baseDir/storage";
        if (is_dir($storagePath)) {
            if (is_writable($storagePath)) {
                $success[] = "Storage directory is writable ✓";
            } else {
                $warnings[] = "Storage directory is not writable (run: chmod -R 755 storage)";
            }
        }
        
        // Check 5: .env File
        $envPath = "$baseDir/.env";
        if (file_exists($envPath)) {
            $success[] = ".env file exists ✓";
            
            // Check APP_KEY
            $envContent = file_get_contents($envPath);
            if (strpos($envContent, 'APP_KEY=base64:') !== false || strpos($envContent, 'APP_KEY=') !== false) {
                $success[] = "APP_KEY is set ✓";
            } else {
                $issues[] = "APP_KEY is not set (run: php artisan key:generate)";
            }
            
            // Check APP_URL
            if (preg_match('/APP_URL=(.+)/', $envContent, $matches)) {
                $appUrl = trim($matches[1]);
                $currentUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                if ($appUrl === $currentUrl || $appUrl === 'http://localhost') {
                    $warnings[] = "APP_URL may need to be updated to match your domain";
                } else {
                    $success[] = "APP_URL is configured ✓";
                }
            }
        } else {
            $issues[] = ".env file not found (create from .env.example)";
        }
        
        // Check 6: Vendor Directory
        $vendorPath = "$baseDir/vendor";
        if (is_dir($vendorPath) && is_dir("$vendorPath/laravel")) {
            $success[] = "Composer dependencies installed ✓";
        } else {
            $issues[] = "Vendor directory missing or incomplete (run: composer install)";
        }
        
        // Check 7: Autoload File
        $autoloadPath = "$baseDir/vendor/autoload.php";
        if (file_exists($autoloadPath)) {
            $success[] = "Composer autoload file exists ✓";
        } else {
            $issues[] = "Composer autoload file missing (run: composer install)";
        }
        
        // Check 8: Document Root
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $scriptPath = __DIR__;
        if ($documentRoot === $scriptPath || strpos($scriptPath, $documentRoot) === 0) {
            $success[] = "Document root configuration appears correct ✓";
        } else {
            $warnings[] = "Document root: $documentRoot (should point to public directory)";
        }
        
        // Display Results
        if (!empty($success)) {
            echo "<h2 class='success'>✓ Success Checks</h2><ul>";
            foreach ($success as $msg) {
                echo "<li class='success'>$msg</li>";
            }
            echo "</ul>";
        }
        
        if (!empty($warnings)) {
            echo "<h2 class='warning'>⚠ Warnings</h2><ul>";
            foreach ($warnings as $msg) {
                echo "<li class='warning'>$msg</li>";
            }
            echo "</ul>";
        }
        
        if (!empty($issues)) {
            echo "<h2 class='error'>✗ Issues Found</h2><ul>";
            foreach ($issues as $msg) {
                echo "<li class='error'>$msg</li>";
            }
            echo "</ul>";
        }
        
        if (empty($issues) && empty($warnings)) {
            echo "<h2 class='success'>🎉 All checks passed!</h2>";
            echo "<p>If you still can't see the welcome page, check:</p>";
            echo "<ul>";
            echo "<li>Web server configuration (Apache/Nginx)</li>";
            echo "<li>Virtual host settings</li>";
            echo "<li>Firewall rules</li>";
            echo "<li>DNS configuration</li>";
            echo "</ul>";
        }
        ?>
        
        <hr style="margin: 30px 0;">
        <h2 class="info">📋 Next Steps</h2>
        <ol>
            <li>Fix all issues listed above</li>
            <li>Ensure document root points to <code>public</code> directory</li>
            <li>Run <code>php artisan config:cache</code> after fixing .env</li>
            <li>Check web server error logs: <code>/var/log/apache2/error.log</code> or <code>/var/log/nginx/error.log</code></li>
            <li>Delete this file after fixing issues</li>
        </ol>
        
        <p><strong>Current URL:</strong> <?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        <p><strong>Script Path:</strong> <?php echo __DIR__; ?></p>
    </div>
</body>
</html>

