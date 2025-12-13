<?php
// app/Http/Controllers/Api/SecureApiController.php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ArewaCollection;
use App\Models\DigitalAsset;
use App\Services\SecurityService;
use App\Services\ApiAuthService;

class SecureApiController extends Controller
{
    protected $apiAuthService;
    
    public function __construct(ApiAuthService $apiAuthService)
    {
        $this->apiAuthService = $apiAuthService;
        
        // Apply rate limiting to all API endpoints
        $this->middleware('throttle:100,1'); // 100 requests per minute
    }
    
    /**
     * Secure API authentication
     */
    public function authenticate(Request $request)
    {
        // Check rate limiting
        $key = 'api_auth:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'error' => 'Too many authentication attempts. Please try again later.'
            ], 429);
        }
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'device_id' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            RateLimiter::hit($key, 300); // 5 minutes block on failures
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Sanitize inputs
        $email = SecurityService::sanitizeInput($request->email);
        $password = $request->password;
        
        // Find user
        $user = User::where('email', $email)->first();
        
        if (!$user || !Hash::check($password, $user->password)) {
            RateLimiter::hit($key, 300);
            SecurityService::logSecurityEvent(null, 'api_auth_failed', [
                'email' => $email,
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }
        
        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'error' => 'Account is deactivated'
            ], 403);
        }
        
        // Generate API token
        $token = $this->apiAuthService->generateApiToken($user, $request->device_id);
        
        // Log successful authentication
        SecurityService::logSecurityEvent($user->id, 'api_auth_success', [
            'device_id' => $request->device_id,
            'ip' => $request->ip()
        ]);
        
        RateLimiter::clear($key);
        
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 1 hour
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type
            ]
        ]);
    }
    
    /**
     * Get collections with secure access control
     */
    public function getCollections(Request $request)
    {
        // Authenticate API request
        $auth = $this->apiAuthService->authenticateRequest($request);
        
        if (!$auth['success']) {
            return response()->json(['error' => $auth['message']], 401);
        }
        
        $user = $auth['user'];
        
        // Validate input parameters
        $validator = Validator::make($request->all(), [
            'state' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:50',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Sanitize inputs
        $state = $request->filled('state') ? SecurityService::sanitizeInput($request->state) : null;
        $category = $request->filled('category') ? SecurityService::sanitizeInput($request->category) : null;
        $limit = min($request->get('limit', 20), 100);
        
        // Build query with access control
        $query = ArewaCollection::query();
        
        // Apply filters securely
        if ($state) {
            $query->where('state_of_origin', $state);
        }
        
        if ($category) {
            $query->where('category', $category);
        }
        
        // Apply access restrictions based on user type
        if ($user->user_type === 'researcher') {
            $query->whereIn('access_level', ['public', 'researchers_only']);
        } elseif ($user->user_type === 'donor') {
            $query->where('access_level', 'public');
        }
        
        // Execute query with pagination
        $collections = $query->paginate($limit);
        
        // Transform data for API response
        $data = $collections->map(function ($collection) {
            return [
                'id' => $collection->id,
                'code' => $collection->collection_code,
                'name' => $collection->collection_name,
                'description' => $collection->description,
                'category' => $collection->category,
                'state' => $collection->state_of_origin,
                'conservation_status' => $collection->conservation_status,
                'access_level' => $collection->access_level,
                'has_digital_copy' => (bool) $collection->digital_copy_available,
                'created_at' => $collection->created_at->toISOString()
            ];
        });
        
        // Log API access
        SecurityService::logSecurityEvent($user->id, 'api_collections_access', [
            'filters' => ['state' => $state, 'category' => $category],
            'results_count' => $collections->total()
        ]);
        
        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $collections->currentPage(),
                'total_pages' => $collections->lastPage(),
                'total_items' => $collections->total(),
                'per_page' => $collections->perPage()
            ]
        ]);
    }
    
    /**
     * Request access to restricted collection via API
     */
    public function requestCollectionAccess(Request $request)
    {
        $auth = $this->apiAuthService->authenticateRequest($request);
        
        if (!$auth['success']) {
            return response()->json(['error' => $auth['message']], 401);
        }
        
        $user = $auth['user'];
        
        $validator = Validator::make($request->all(), [
            'collection_id' => 'required|exists:arewa_collections,id',
            'purpose' => 'required|string|min:30|max:500',
            'request_type' => 'required|in:research,education,publication'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid request data',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $collection = ArewaCollection::findOrFail($request->collection_id);
        
        // Check if already has access
        if ($collection->access_level === 'public' || 
            ($collection->access_level === 'researchers_only' && $user->user_type === 'researcher')) {
            return response()->json([
                'error' => 'You already have access to this collection'
            ], 400);
        }
        
        // Check for existing pending request
        $existingRequest = \App\Models\AccessRequest::where('user_id', $user->id)
            ->where('collection_id', $collection->id)
            ->where('status', 'pending')
            ->first();
            
        if ($existingRequest) {
            return response()->json([
                'error' => 'You already have a pending request for this collection'
            ], 400);
        }
        
        // Create access request
        $accessRequest = \App\Models\AccessRequest::create([
            'request_code' => 'API-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'user_id' => $user->id,
            'collection_id' => $collection->id,
            'request_type' => 'research',
            'purpose' => SecurityService::sanitizeInput($request->purpose),
            'requested_date' => now(),
            'duration_days' => 30,
            'status' => 'pending'
        ]);
        
        SecurityService::logSecurityEvent($user->id, 'api_access_request', [
            'request_id' => $accessRequest->id,
            'collection_id' => $collection->id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Access request submitted successfully',
            'request_code' => $accessRequest->request_code,
            'request_id' => $accessRequest->id
        ]);
    }
    
    /**
     * Get digital asset metadata (secure)
     */
    public function getDigitalAsset($id, Request $request)
    {
        $auth = $this->apiAuthService->authenticateRequest($request);
        
        if (!$auth['success']) {
            return response()->json(['error' => $auth['message']], 401);
        }
        
        $user = $auth['user'];
        $asset = DigitalAsset::findOrFail($id);
        
        // Check access permissions
        if (!$this->checkAssetAccessForApi($asset, $user)) {
            SecurityService::logSecurityEvent($user->id, 'api_unauthorized_asset_access', [
                'asset_id' => $asset->id,
                'user_type' => $user->user_type
            ]);
            
            return response()->json([
                'error' => 'You do not have permission to access this digital asset'
            ], 403);
        }
        
        // Return asset metadata (not the file itself)
        return response()->json([
            'id' => $asset->id,
            'code' => $asset->asset_code,
            'title' => $asset->title,
            'description' => $asset->description,
            'type' => $asset->asset_type,
            'language' => $asset->language,
            'file_format' => $asset->file_format,
            'file_size' => $asset->file_size,
            'access_level' => $asset->access_level,
            'download_count' => $asset->download_count,
            'view_count' => $asset->view_count,
            'collection' => $asset->collection ? [
                'id' => $asset->collection->id,
                'code' => $asset->collection->collection_code,
                'name' => $asset->collection->collection_name
            ] : null,
            'created_at' => $asset->created_at->toISOString(),
            'download_url' => $asset->access_level !== 'restricted' ? 
                route('api.digital-assets.download', ['id' => $asset->id, 'token' => $auth['token']]) : null
        ]);
    }
    
    /**
     * Download digital asset via API
     */
    public function downloadDigitalAsset($id, Request $request)
    {
        $auth = $this->apiAuthService->authenticateRequest($request);
        
        if (!$auth['success']) {
            return response()->json(['error' => $auth['message']], 401);
        }
        
        $user = $auth['user'];
        $asset = DigitalAsset::findOrFail($id);
        
        // Check access permissions
        if (!$this->checkAssetAccessForApi($asset, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        // Check download limits
        $dailyDownloads = \App\Models\DownloadLog::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();
            
        if ($dailyDownloads >= 10) {
            return response()->json([
                'error' => 'Daily download limit reached'
            ], 429);
        }
        
        // Check file exists
        if (!Storage::exists($asset->file_path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        // Log download
        $asset->increment('download_count');
        
        \App\Models\DownloadLog::create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'via_api' => true
        ]);
        
        SecurityService::logSecurityEvent($user->id, 'api_digital_asset_download', [
            'asset_id' => $asset->id,
            'file_size' => $asset->file_size
        ]);
        
        // Return download URL with short-lived token
        $downloadToken = $this->apiAuthService->generateDownloadToken($user, $asset);
        
        return response()->json([
            'download_url' => route('api.digital-assets.direct-download', [
                'id' => $asset->id,
                'token' => $downloadToken
            ]),
            'expires_in' => 300, // 5 minutes
            'filename' => basename($asset->file_path)
        ]);
    }
    
    /**
     * Check asset access for API
     */
    private function checkAssetAccessForApi(DigitalAsset $asset, $user)
    {
        if ($asset->access_level === 'public') {
            return true;
        }
        
        if ($asset->access_level === 'registered_users' && $user) {
            return true;
        }
        
        if ($asset->access_level === 'researchers_only' && $user->user_type === 'researcher') {
            return true;
        }
        
        if ($asset->access_level === 'restricted') {
            return \App\Models\AccessRequest::where('user_id', $user->id)
                ->where('digital_asset_id', $asset->id)
                ->where('status', 'approved')
                ->where('requested_date', '>=', now()->subDays(30))
                ->exists();
        }
        
        return false;
    }
    
    /**
     * Get API usage statistics
     */
    public function getApiStats(Request $request)
    {
        $auth = $this->apiAuthService->authenticateRequest($request);
        
        if (!$auth['success'] || $auth['user']->user_type !== 'admin') {
            return response()->json(['error' => 'Admin access required'], 403);
        }
        
        $stats = [
            'total_requests' => \App\Models\ApiLog::count(),
            'requests_today' => \App\Models\ApiLog::whereDate('created_at', today())->count(),
            'unique_users_today' => \App\Models\ApiLog::whereDate('created_at', today())
                ->distinct('user_id')
                ->count('user_id'),
            'top_endpoints' => \App\Models\ApiLog::select('endpoint', DB::raw('count(*) as count'))
                ->groupBy('endpoint')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
            'failed_requests' => \App\Models\ApiLog::where('status_code', '>=', 400)
                ->whereDate('created_at', today())
                ->count()
        ];
        
        return response()->json($stats);
    }
}

// app/Services/ApiAuthService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\ApiToken;
use App\Models\ApiLog;
use Carbon\Carbon;

class ApiAuthService
{
    /**
     * Generate API token for user
     */
    public function generateApiToken(User $user, $deviceId = null)
    {
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        
        // Hash token for storage
        $hashedToken = Hash::make($token);
        
        // Store token in database
        ApiToken::create([
            'user_id' => $user->id,
            'token_hash' => $hashedToken,
            'device_id' => $deviceId,
            'expires_at' => Carbon::now()->addHours(24),
            'last_used_at' => null
        ]);
        
        // Clear old tokens
        $this->cleanupOldTokens($user->id);
        
        return $token;
    }
    
    /**
     * Authenticate API request
     */
    public function authenticateRequest($request)
    {
        $token = $this->extractToken($request);
        
        if (!$token) {
            return ['success' => false, 'message' => 'No API token provided'];
        }
        
        // Check rate limiting
        $key = 'api_token:' . hash('sha256', $token);
        if (Cache::get($key, 0) > 100) {
            return ['success' => false, 'message' => 'Rate limit exceeded'];
        }
        
        // Find valid token
        $apiToken = ApiToken::where('expires_at', '>', Carbon::now())
            ->get()
            ->first(function ($apiToken) use ($token) {
                return Hash::check($token, $apiToken->token_hash);
            });
        
        if (!$apiToken) {
            Cache::increment($key);
            return ['success' => false, 'message' => 'Invalid or expired token'];
        }
        
        // Check if user is active
        $user = $apiToken->user;
        if (!$user || !$user->is_active) {
            return ['success' => false, 'message' => 'User account is inactive'];
        }
        
        // Update token usage
        $apiToken->update([
            'last_used_at' => Carbon::now(),
            'last_ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Log API request
        $this->logApiRequest($user, $request);
        
        return [
            'success' => true,
            'user' => $user,
            'token' => $apiToken
        ];
    }
    
    /**
     * Generate download token
     */
    public function generateDownloadToken(User $user, $asset)
    {
        $token = bin2hex(random_bytes(16));
        
        Cache::put('download_token:' . $token, [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'expires_at' => Carbon::now()->addMinutes(5)->timestamp
        ], 300); // 5 minutes
        
        return $token;
    }
    
    /**
     * Validate download token
     */
    public function validateDownloadToken($token)
    {
        $data = Cache::get('download_token:' . $token);
        
        if (!$data || $data['expires_at'] < time()) {
            return null;
        }
        
        Cache::forget('download_token:' . $token);
        
        return $data;
    }
    
    /**
     * Extract token from request
     */
    private function extractToken($request)
    {
        // Check Authorization header
        if ($request->header('Authorization')) {
            $header = $request->header('Authorization');
            if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
                return $matches[1];
            }
        }
        
        // Check query parameter
        if ($request->query('token')) {
            return $request->query('token');
        }
        
        return null;
    }
    
    /**
     * Cleanup old tokens
     */
    private function cleanupOldTokens($userId)
    {
        ApiToken::where('user_id', $userId)
            ->where('expires_at', '<', Carbon::now()->subDays(1))
            ->delete();
    }
    
    /**
     * Log API request
     */
    private function logApiRequest($user, $request)
    {
        ApiLog::create([
            'user_id' => $user->id,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => 200,
            'response_time' => microtime(true) - LARAVEL_START
        ]);
    }
}