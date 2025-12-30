<?php
// config/security.php - Security Configuration
return [
    'password_min_length' => 12,
    'password_requirements' => [
        'uppercase' => true,
        'lowercase' => true,
        'numbers' => true,
        'symbols' => true,
    ],
    'max_login_attempts' => 5,
    'lockout_time' => 900, // 15 minutes in seconds
    'session_timeout' => 1800, // 30 minutes
    'csrf_token_lifetime' => 3600, // 1 hour
    'honeypot_field' => 'website', // Hidden field for bots
    'rate_limiting' => [
        'api' => 100, // requests per minute
        'web' => 60, // requests per minute
    ],
];

// app/Http/Middleware/SecurityHeaders.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net;"
        );
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        return $response;
    }
}

// app/Services/SecurityService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditLog;

class SecurityService
{
    /**
     * Sanitize input against SQL injection and XSS
     */
    public static function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove potential SQL injection patterns
        $sqlPatterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|FROM|WHERE)\b/i',
            '/--/', // SQL comments
            '/\/\*.*\*\//', // SQL comments
            '/\b(OR|AND)\s+[\d\']/i',
        ];
        
        $input = preg_replace($sqlPatterns, '', $input);
        
        return trim($input);
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880)
    {
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Invalid file type');
        }
        
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds limit');
        }
        
        // Check for malicious content in images
        if (strpos($file->getMimeType(), 'image') !== false) {
            $imageInfo = getimagesize($file->path());
            if (!$imageInfo) {
                throw new \Exception('Invalid image file');
            }
        }
        
        // Generate safe filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '', $originalName);
        $extension = $file->getClientOriginalExtension();
        
        return $safeName . '_' . time() . '.' . $extension;
    }
    
    /**
     * Check for brute force attacks
     */
    public static function checkBruteForce($ip, $action)
    {
        $key = "brute_force_{$ip}_{$action}";
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= config('security.max_login_attempts', 5)) {
            return false;
        }
        
        Cache::put($key, $attempts + 1, now()->addMinutes(15));
        return true;
    }
    
    /**
     * Log security event
     */
    public static function logSecurityEvent($userId, $action, $details = [])
    {
        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'table_name' => $details['table'] ?? null,
                'record_id' => $details['record_id'] ?? null,
                'old_values' => $details['old'] ?? null,
                'new_values' => $details['new'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log security event: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate secure random token
     */
    public static function generateSecureToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRF($token)
    {
        $sessionToken = session()->token();
        
        if (!hash_equals($sessionToken, $token)) {
            self::logSecurityEvent(null, 'csrf_attempt', [
                'provided_token' => substr($token, 0, 10) . '...',
                'session_token' => substr($sessionToken, 0, 10) . '...',
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * SQL Injection prevention for raw queries
     */
    public static function safeRawQuery($query, $bindings = [])
    {
        foreach ($bindings as $key => $value) {
            if (is_string($value)) {
                $bindings[$key] = DB::connection()->getPdo()->quote($value);
            }
        }
        
        return DB::select(DB::raw($query), $bindings);
    }
}

// app/Http/Middleware/InputSanitizer.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SecurityService;

class InputSanitizer
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        
        // Sanitize all input
        $sanitized = SecurityService::sanitizeInput($input);
        
        // Replace request input with sanitized data
        $request->replace($sanitized);
        
        // Add honeypot check
        if ($request->has('website') && !empty($request->website)) {
            SecurityService::logSecurityEvent(null, 'honeypot_triggered', [
                'field' => 'website',
                'value' => $request->website,
            ]);
            abort(403, 'Access Denied');
        }
        
        return $next($request);
    }
}

// app/Http/Middleware/RateLimiter.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RateLimiter
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'rate_limit:' . $request->ip() . ':' . $request->path();
        $maxRequests = config('security.rate_limiting.web', 60);
        $decayMinutes = 1;
        
        $requests = Cache::get($key, 0);
        
        if ($requests >= $maxRequests) {
            return response()->json([
                'error' => 'Too many requests. Please try again later.'
            ], 429);
        }
        
        Cache::put($key, $requests + 1, now()->addMinutes($decayMinutes));
        
        return $next($request);
    }
}