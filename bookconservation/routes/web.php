<?php
// routes/web.php
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\InputSanitizer;
use App\Http\Middleware\RateLimiter;

// Public Routes
Route::get('/', 'HomeController@index')->name('home');
Route::get('/about', 'HomeController@about')->name('about');
Route::get('/contact', 'HomeController@contact')->name('contact');
Route::post('/contact', 'HomeController@sendContact')->name('contact.send');

// Authentication Routes
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('/register', 'Auth\RegisterController@register');
Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

// Apply security middleware to all authenticated routes
Route::middleware(['auth', 'security.headers', 'input.sanitizer', 'rate.limit'])->group(function () {
    
    // Dashboard Routes
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
    
    // Profile Management
    Route::get('/profile', 'ProfileController@edit')->name('profile.edit');
    Route::put('/profile', 'ProfileController@update')->name('profile.update');
    Route::get('/profile/security', 'ProfileController@security')->name('profile.security');
    Route::put('/profile/security', 'ProfileController@updateSecurity')->name('profile.security.update');
    
    // Collections Management
    Route::prefix('collections')->name('collections.')->group(function () {
        Route::get('/', 'CollectionController@index')->name('index');
        Route::get('/create', 'CollectionController@create')->name('create')->middleware('can:create_collections');
        Route::post('/', 'CollectionController@store')->name('store')->middleware('can:create_collections');
        Route::get('/{id}', 'CollectionController@show')->name('show');
        Route::get('/{id}/edit', 'CollectionController@edit')->name('edit')->middleware('can:edit_collections');
        Route::put('/{id}', 'CollectionController@update')->name('update')->middleware('can:edit_collections');
        Route::delete('/{id}', 'CollectionController@destroy')->name('destroy')->middleware('can:delete_collections');
        Route::get('/{id}/conservation', 'CollectionController@conservation')->name('conservation');
    });
    
    // Digital Library Routes
    Route::prefix('digital-library')->name('digital-library.')->group(function () {
        Route::get('/', 'DigitalLibraryController@index')->name('index');
        Route::get('/{id}', 'DigitalLibraryController@show')->name('show');
        Route::get('/{id}/download', 'DigitalLibraryController@download')->name('download');
        Route::post('/upload', 'DigitalLibraryController@upload')->name('upload')->middleware('can:upload_digital_assets');
        Route::post('/request-access', 'DigitalLibraryController@requestAccess')->name('request-access');
    });
    
    // Conservation Projects
    Route::prefix('conservation')->name('conservation.')->group(function () {
        Route::get('/projects', 'ConservationController@index')->name('projects');
        Route::get('/projects/create', 'ConservationController@create')->name('projects.create')->middleware('can:create_conservation_projects');
        Route::post('/projects', 'ConservationController@store')->name('projects.store')->middleware('can:create_conservation_projects');
        Route::get('/projects/{id}', 'ConservationController@show')->name('projects.show');
        Route::get('/projects/{id}/edit', 'ConservationController@edit')->name('projects.edit')->middleware('can:edit_conservation_projects');
        Route::put('/projects/{id}', 'ConservationController@update')->name('projects.update')->middleware('can:edit_conservation_projects');
        Route::post('/projects/{id}/complete', 'ConservationController@complete')->name('projects.complete')->middleware('can:complete_conservation_projects');
    });
    
    // Research Portal
    Route::prefix('research')->name('research.')->group(function () {
        Route::get('/', 'ResearchController@index')->name('portal');
        Route::get('/projects', 'ResearchController@projects')->name('projects');
        Route::get('/projects/create', 'ResearchController@createProject')->name('projects.create')->middleware('can:create_research_projects');
        Route::post('/projects', 'ResearchController@storeProject')->name('projects.store')->middleware('can:create_research_projects');
        Route::get('/access-requests', 'ResearchController@accessRequests')->name('access-requests');
        Route::post('/access-requests', 'ResearchController@storeAccessRequest')->name('access-requests.store');
    });
    
    // Volunteer Management
    Route::prefix('volunteers')->name('volunteers.')->group(function () {
        Route::get('/', 'VolunteerController@index')->name('index');
        Route::get('/register', 'VolunteerController@register')->name('register');
        Route::post('/', 'VolunteerController@store')->name('store');
        Route::get('/{id}', 'VolunteerController@show')->name('show');
        Route::get('/{id}/schedule', 'VolunteerController@schedule')->name('schedule');
        Route::post('/{id}/hours', 'VolunteerController@logHours')->name('hours.log');
    });
    
    // Security Center (Admin only)
    Route::prefix('security')->name('security.')->middleware('can:access_security_center')->group(function () {
        Route::get('/dashboard', 'SecurityController@dashboard')->name('dashboard');
        Route::get('/logs', 'SecurityController@logs')->name('logs');
        Route::get('/logs/{id}', 'SecurityController@logDetails')->name('logs.details');
        Route::post('/scan', 'SecurityController@runScan')->name('scan');
        Route::get('/settings', 'SecurityController@settings')->name('settings');
        Route::put('/settings', 'SecurityController@updateSettings')->name('settings.update');
        Route::get('/events/stream', 'SecurityController@eventStream')->name('events.stream');
    });
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('can:access_admin_panel')->group(function () {
        Route::get('/dashboard', 'AdminController@dashboard')->name('dashboard');
        Route::get('/users', 'AdminController@users')->name('users');
        Route::get('/users/{id}', 'AdminController@userDetails')->name('users.details');
        Route::put('/users/{id}/role', 'AdminController@updateUserRole')->name('users.role.update');
        Route::get('/analytics', 'AdminController@analytics')->name('analytics');
        Route::get('/reports', 'AdminController@reports')->name('reports');
        Route::post('/reports/generate', 'AdminController@generateReport')->name('reports.generate');
        Route::get('/backup', 'AdminController@backup')->name('backup');
        Route::post('/backup/create', 'AdminController@createBackup')->name('backup.create');
    });
    
    // API Documentation
    Route::get('/api-docs', 'ApiDocumentationController@index')->name('api.docs');
});

// API Routes
Route::prefix('api')->name('api.')->group(function () {
    // Public API endpoints
    Route::post('/auth', 'Api\SecureApiController@authenticate')->name('auth');
    
    // Authenticated API endpoints
    Route::middleware(['api.auth'])->group(function () {
        Route::get('/collections', 'Api\SecureApiController@getCollections')->name('collections');
        Route::post('/access-requests', 'Api\SecureApiController@requestCollectionAccess')->name('access-requests.store');
        Route::get('/digital-assets/{id}', 'Api\SecureApiController@getDigitalAsset')->name('digital-assets.show');
        Route::get('/digital-assets/{id}/download', 'Api\SecureApiController@downloadDigitalAsset')->name('digital-assets.download');
        Route::get('/stats', 'Api\SecureApiController@getApiStats')->name('stats')->middleware('can:access_api_stats');
    });
    
    // Direct download with token
    Route::get('/digital-assets/{id}/direct-download', 'Api\DownloadController@directDownload')->name('digital-assets.direct-download');
});

// routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuth;
use App\Http\Middleware\ApiRateLimit;

// API Routes with versioning
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/login', 'Api\V1\AuthController@login');
    Route::post('/auth/register', 'Api\V1\AuthController@register');
    Route::post('/auth/refresh', 'Api\V1\AuthController@refresh')->middleware('auth:api');
    Route::post('/auth/logout', 'Api\V1\AuthController@logout')->middleware('auth:api');
    
    // Collections
    Route::get('/collections', 'Api\V1\CollectionController@index')->middleware(['api.auth', 'api.rate.limit']);
    Route::get('/collections/{id}', 'Api\V1\CollectionController@show')->middleware(['api.auth', 'api.rate.limit']);
    Route::post('/collections/{id}/access-request', 'Api\V1\CollectionController@requestAccess')->middleware(['api.auth', 'api.rate.limit:10,1']);
    
    // Digital Assets
    Route::get('/digital-assets', 'Api\V1\DigitalAssetController@index')->middleware(['api.auth', 'api.rate.limit']);
    Route::get('/digital-assets/{id}', 'Api\V1\DigitalAssetController@show')->middleware(['api.auth', 'api.rate.limit']);
    Route::post('/digital-assets/{id}/download', 'Api\V1\DigitalAssetController@requestDownload')->middleware(['api.auth', 'api.rate.limit:5,1']);
    
    // Research
    Route::get('/research/projects', 'Api\V1\ResearchController@index')->middleware(['api.auth', 'api.rate.limit']);
    Route::post('/research/projects', 'Api\V1\ResearchController@store')->middleware(['api.auth', 'api.rate.limit:3,1']);
    
    // User Profile
    Route::get('/profile', 'Api\V1\ProfileController@show')->middleware(['api.auth', 'api.rate.limit']);
    Route::put('/profile', 'Api\V1\ProfileController@update')->middleware(['api.auth', 'api.rate.limit:5,1']);
    
    // Statistics
    Route::get('/stats/platform', 'Api\V1\StatsController@platformStats')->middleware(['api.auth', 'api.rate.limit']);
    Route::get('/stats/personal', 'Api\V1\StatsController@personalStats')->middleware(['api.auth', 'api.rate.limit']);
    
    // Webhooks (for external integrations)
    Route::post('/webhooks/donation', 'Api\V1\WebhookController@handleDonation');
    Route::post('/webhooks/cons