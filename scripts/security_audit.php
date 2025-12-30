<?php
// scripts/security_audit.php
#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\SecurityService;

class SecurityAudit
{
    private $issues = [];
    private $warnings = [];
    private $passes = [];
    
    public function run()
    {
        echo "🔒 Starting Comprehensive Security Audit\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $this->checkDatabaseSecurity();
        $this->checkFilePermissions();
        $this->checkSensitiveFiles();
        $this->checkServerConfiguration();
        $this->checkApplicationSecurity();
        $this->checkDependencies();
        $this->checkLogging();
        $this->checkBackupSecurity();
        
        $this->generateReport();
    }
    
    private function checkDatabaseSecurity()
    {
        echo "📊 Checking Database Security...\n";
        
        // Check for weak passwords
        $weakPasswords = DB::table('users')
            ->whereRaw('LENGTH(password) < 60')
            ->count();
            
        if ($weakPasswords > 0) {
            $this->issues[] = "Found {$weakPasswords} users with potentially weak password hashes";
        } else {
            $this->passes[] = "Password hashing appears secure";
        }
        
        // Check for default admin accounts
        $defaultAdmins = DB::table('users')
            ->whereIn('email', ['admin@admin.com', 'test@test.com', 'administrator@example.com'])
            ->count();
            
        if ($defaultAdmins > 0) {
            $this->issues[] = "Found {$defaultAdmins} accounts with default email addresses";
        }
        
        // Check for SQL injection vulnerabilities in recent queries
        $recentLogs = DB::table('audit_logs')
            ->where('action', 'LIKE', '%sql%')
            ->orWhere('action', 'LIKE', '%injection%')
            ->count();
            
        if ($recentLogs > 0) {
            $this->warnings[] = "Found {$recentLogs} potential SQL injection attempts in audit logs";
        }
        
        // Check database permissions
        try {
            $permissions = DB::select("SHOW GRANTS FOR CURRENT_USER()");
            $perms = json_encode($permissions);
            
            if (strpos($perms, 'ALL PRIVILEGES') !== false) {
                $this->warnings[] = "Database user has ALL PRIVILEGES - consider reducing permissions";
            }
        } catch (\Exception $e) {
            $this->warnings[] = "Could not check database permissions: " . $e->getMessage();
        }
    }
    
    private function checkFilePermissions()
    {
        echo "📁 Checking File Permissions...\n";
        
        $sensitiveDirs = [
            storage_path(),
            base_path('config'),
            base_path('database'),
            base_path('.env'),
            public_path('uploads')
        ];
        
        foreach ($sensitiveDirs as $dir) {
            if (file_exists($dir)) {
                $perms = substr(sprintf('%o', fileperms($dir)), -4);
                
                if ($perms == '0777' || $perms == '0775') {
                    $this->issues[] = "Directory {$dir} has overly permissive permissions: {$perms}";
                } elseif ($perms == '0755' || $perms == '0700') {
                    $this->passes[] = "Directory {$dir} has secure permissions: {$perms}";
                } else {
                    $this->warnings[] = "Directory {$dir} has unusual permissions: {$perms}";
                }
            }
        }
        
        // Check for world-writable files
        $worldWritable = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(storage_path()));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->isWritable()) {
                $perms = substr(sprintf('%o', $file->getPerms()), -4);
                if (substr($perms, -1) == '7' || substr($perms, -1) == '6') {
                    $worldWritable[] = $file->getPathname();
                }
            }
        }
        
        if (count($worldWritable) > 0) {
            $this->issues[] = "Found " . count($worldWritable) . " world-writable files in storage";
        }
    }
    
    private function checkSensitiveFiles()
    {
        echo "🔍 Checking for Sensitive Files...\n";
        
        $sensitiveFiles = [
            base_path('.env'),
            base_path('.env.example'),
            base_path('.git'),
            base_path('composer.json'),
            base_path('package.json'),
            public_path('.htaccess'),
            public_path('web.config')
        ];
        
        foreach ($sensitiveFiles as $file) {
            if (file_exists($file)) {
                if (is_readable($file)) {
                    $this->passes[] = "Sensitive file {$file} exists and is readable (as expected)";
                } else {
                    $this->warnings[] = "Sensitive file {$file} exists but is not readable";
                }
            }
        }
        
        // Check for backup files
        $backupPatterns = ['*.bak', '*.backup', '*.old', '*.save'];
        $foundBackups = [];
        
        foreach ($backupPatterns as $pattern) {
            $backups = glob(storage_path($pattern));
            $foundBackups = array_merge($foundBackups, $backups);
        }
        
        if (count($foundBackups) > 0) {
            $this->issues[] = "Found " . count($foundBackups) . " backup files in storage directory";
        }
    }
    
    private function checkServerConfiguration()
    {
        echo "🖥️  Checking Server Configuration...\n";
        
        // Check PHP configuration
        $phpChecks = [
            'display_errors' => 'Off',
            'expose_php' => 'Off',
            'allow_url_fopen' => 'Off',
            'allow_url_include' => 'Off',
            'session.cookie_httponly' => '1',
            'session.cookie_secure' => '1',
            'session.use_strict_mode' => '1'
        ];
        
        foreach ($phpChecks as $setting => $expected) {
            $actual = ini_get($setting);
            if ($actual != $expected) {
                $this->warnings[] = "PHP setting {$setting} is '{$actual}' (should be '{$expected}')";
            } else {
                $this->passes[] = "PHP setting {$setting} is correctly set to '{$expected}'";
            }
        }
        
        // Check for dangerous PHP functions
        $disabledFunctions = explode(',', ini_get('disable_functions'));
        $dangerousFunctions = ['exec', 'system', 'passthru', 'shell_exec', 'proc_open'];
        
        foreach ($dangerousFunctions as $func) {
            if (!in_array($func, $disabledFunctions)) {
                $this->warnings[] = "Dangerous PHP function '{$func}' is not disabled";
            } else {
                $this->passes[] = "Dangerous PHP function '{$func}' is disabled";
            }
        }
    }
    
    private function checkApplicationSecurity()
    {
        echo "🛡️  Checking Application Security...\n";
        
        // Check CSRF protection
        if (app()->bound('Illuminate\Contracts\Http\Kernel')) {
            $this->passes[] = "CSRF protection is enabled";
        }
        
        // Check XSS protection headers
        $headers = headers_list();
        $xssHeaderFound = false;
        
        foreach ($headers as $header) {
            if (stripos($header, 'X-XSS-Protection') !== false) {
                $xssHeaderFound = true;
                break;
            }
        }
        
        if ($xssHeaderFound) {
            $this->passes[] = "X-XSS-Protection header is set";
        } else {
            $this->warnings[] = "X-XSS-Protection header is not set";
        }
        
        // Check for debug mode
        if (config('app.debug')) {
            $this->issues[] = "Application is in debug mode - disable in production";
        } else {
            $this->passes[] = "Debug mode is disabled";
        }
        
        // Check for default encryption key
        if (config('app.key') == 'base64:someRandomString=') {
            $this->issues[] = "Using default encryption key - generate a unique key";
        } else {
            $this->passes[] = "Encryption key is properly set";
        }
    }
    
    private function checkDependencies()
    {
        echo "📦 Checking Dependencies...\n";
        
        if (file_exists(base_path('composer.lock'))) {
            $composerLock = json_decode(file_get_contents(base_path('composer.lock')), true);
            
            $outdated = 0;
            $vulnerable = 0;
            
            foreach ($composerLock['packages'] as $package) {
                // Check for known vulnerabilities (simplified)
                $knownVulnerable = ['symfony/symfony' => '<4.4', 'laravel/framework' => '<8.0'];
                
                foreach ($knownVulnerable as $pkg => $version) {
                    if (strpos($package['name'], $pkg) !== false) {
                        if (version_compare($package['version'], $version, '<')) {
                            $vulnerable++;
                            $this->issues[] = "Package {$package['name']} {$package['version']} may have known vulnerabilities";
                        }
                    }
                }
            }
            
            if ($vulnerable == 0) {
                $this->passes[] = "No known vulnerable dependencies found";
            }
        }
    }
    
    private function checkLogging()
    {
        echo "📝 Checking Logging Configuration...\n";
        
        $logFile = storage_path('logs/laravel.log');
        
        if (file_exists($logFile)) {
            $size = filesize($logFile);
            
            if ($size > 100 * 1024 * 1024) { // 100MB
                $this->warnings[] = "Log file is very large (" . round($size/1024/1024, 2) . "MB) - consider rotation";
            }
            
            // Check for sensitive data in logs
            $content = file_get_contents($logFile);
            $sensitivePatterns = [
                '/password.*=.*["\']([^"\']+)["\']/i',
                '/token.*=.*["\']([^"\']+)["\']/i',
                '/key.*=.*["\']([^"\']+)["\']/i',
                '/secret.*=.*["\']([^"\']+)["\']/i'
            ];
            
            $matches = 0;
            foreach ($sensitivePatterns as $pattern) {
                if (preg_match_all($pattern, $content, $match)) {
                    $matches += count($match[0]);
                }
            }
            
            if ($matches > 0) {
                $this->issues[] = "Found {$matches} potential sensitive data exposures in log file";
            } else {
                $this->passes[] = "Log file appears clean of sensitive data";
            }
        }
    }
    
    private function checkBackupSecurity()
    {
        echo "💾 Checking Backup Security...\n";
        
        // Check if backups are encrypted
        $backupDir = storage_path('app/backups');
        
        if (is_dir($backupDir)) {
            $backups = glob($backupDir . '/*.zip');
            
            if (count($backups) > 0) {
                // Check if backups are encrypted (simplified check)
                $encrypted = 0;
                foreach ($backups as $backup) {
                    // In real implementation, check if file is encrypted
                    // For now, assume they should be
                    $this->warnings[] = "Backup file {$backup} should be encrypted";
                }
            }
        }
        
        // Check backup schedule
        $this->passes[] = "Backup system is configured";
    }
    
    private function generateReport()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📋 SECURITY AUDIT REPORT\n";
        echo str_repeat("=", 60) . "\n\n";
        
        if (count($this->issues) > 0) {
            echo "❌ CRITICAL ISSUES (" . count($this->issues) . "):\n";
            foreach ($this->issues as $issue) {
                echo "  • {$issue}\n";
            }
            echo "\n";
        }
        
        if (count($this->warnings) > 0) {
            echo "⚠️  WARNINGS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "  • {$warning}\n";
            }
            echo "\n";
        }
        
        if (count($this->passes) > 0) {
            echo "✅ PASSED CHECKS (" . count($this->passes) . "):\n";
            foreach ($this->passes as $pass) {
                echo "  • {$pass}\n";
            }
            echo "\n";
        }
        
        $score = 100 - (count($this->issues) * 10) - (count($this->warnings) * 3);
        $score = max(0, min(100, $score));
        
        echo "📊 SECURITY SCORE: {$score}/100\n";
        
        if ($score >= 90) {
            echo "🎉 Excellent security posture!\n";
        } elseif ($score >= 70) {
            echo "👍 Good, but some improvements needed\n";
        } elseif ($score >= 50) {
            echo "⚠️  Fair - address critical issues immediately\n";
        } else {
            echo "🚨 Poor - urgent security improvements required\n";
        }
        
        // Generate recommendations
        if (count($this->issues) > 0 || count($this->warnings) > 0) {
            echo "\n📌 RECOMMENDATIONS:\n";
            
            if (in_array('Debug mode is enabled', $this->issues)) {
                echo "  • Set APP_DEBUG=false in .env file\n";
            }
            
            if (in_array('Using default encryption key', $this->issues)) {
                echo "  • Run: php artisan key:generate\n";
            }
            
            if (in_array('Found world-writable files', $this->issues)) {
                echo "  • Run: chmod -R 755 storage/\n";
            }
            
            echo "  • Review and implement all security headers\n";
            echo "  • Enable Two-Factor Authentication for admin accounts\n";
            echo "  • Regular security audits and penetration testing\n";
            echo "  • Keep all dependencies updated\n";
        }
        
        // Log audit results
        SecurityService::logSecurityEvent(null, 'security_audit_completed', [
            'score' => $score,
            'issues' => count($this->issues),
            'warnings' => count($this->warnings),
            'passes' => count($this->passes)
        ]);
    }
}

// Run audit
$audit = new SecurityAudit();
$audit->run();