<?php
// app/Http/Controllers/DigitalLibraryController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\DigitalAsset;
use App\Models\ArewaCollection;
use App\Models\AccessRequest;
use App\Services\SecurityService;
use App\Services\FileService;

class DigitalLibraryController extends Controller
{
    protected $fileService;
    
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
        $this->middleware('auth');
    }
    
    /**
     * Display digital library
     */
    public function index(Request $request)
    {
        // Security: Validate user access
        if (!Auth::user()->can('view_digital_library')) {
            SecurityService::logSecurityEvent(Auth::id(), 'unauthorized_digital_library_access', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            abort(403, 'Unauthorized access to digital library');
        }
        
        $query = DigitalAsset::with('collection');
        
        // Apply filters securely
        if ($request->filled('search')) {
            $search = SecurityService::sanitizeInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('language', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('category')) {
            $category = SecurityService::sanitizeInput($request->category);
            $query->whereHas('collection', function($q) use ($category) {
                $q->where('category', $category);
            });
        }
        
        if ($request->filled('state')) {
            $state = SecurityService::sanitizeInput($request->state);
            $query->whereHas('collection', function($q) use ($state) {
                $q->where('state_of_origin', $state);
            });
        }
        
        // Apply access level restrictions
        $user = Auth::user();
        if ($user->user_type == 'researcher') {
            $query->whereIn('access_level', ['public', 'registered_users', 'researchers_only']);
        } elseif ($user->user_type == 'donor') {
            $query->whereIn('access_level', ['public', 'registered_users']);
        }
        
        $assets = $query->orderBy('created_at', 'desc')->paginate(12);
        
        return view('digital-library.index', compact('assets'));
    }
    
    /**
     * View digital asset
     */
    public function show($id, Request $request)
    {
        // Prevent SQL injection using parameterized query
        $asset = DigitalAsset::where('id', $id)->firstOrFail();
        
        // Check access permissions
        $this->checkAssetAccess($asset, $request);
        
        // Log view
        $asset->increment('view_count');
        
        SecurityService::logSecurityEvent(Auth::id(), 'digital_asset_viewed', [
            'asset_id' => $asset->id,
            'asset_title' => $asset->title,
            'ip' => $request->ip()
        ]);
        
        return view('digital-library.show', compact('asset'));
    }
    
    /**
     * Download digital asset
     */
    public function download($id, Request $request)
    {
        $asset = DigitalAsset::findOrFail($id);
        
        // Check access permissions
        $this->checkAssetAccess($asset, $request);
        
        // Check download limits
        $user = Auth::user();
        $dailyDownloads = DB::table('download_logs')
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();
            
        if ($dailyDownloads >= 10) {
            return response()->json(['error' => 'Daily download limit reached'], 429);
        }
        
        // Validate file exists and is safe
        if (!Storage::exists($asset->file_path)) {
            abort(404, 'File not found');
        }
        
        // Log download
        $asset->increment('download_count');
        
        DB::table('download_logs')->insert([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);
        
        SecurityService::logSecurityEvent($user->id, 'digital_asset_downloaded', [
            'asset_id' => $asset->id,
            'asset_title' => $asset->title,
            'file_path' => $asset->file_path
        ]);
        
        // Secure file download
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($asset->file_path) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ];
        
        return Storage::download($asset->file_path, basename($asset->file_path), $headers);
    }
    
    /**
     * Upload digital asset
     */
    public function upload(Request $request)
    {
        // Security: Validate user permissions
        if (!Auth::user()->can('upload_digital_assets')) {
            abort(403, 'Unauthorized upload attempt');
        }
        
        $request->validate([
            'collection_id' => 'required|exists:arewa_collections,id',
            'file' => 'required|file|max:102400', // 100MB max
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'language' => 'required|string|max:50',
            'access_level' => 'required|in:public,registered_users,researchers_only,restricted'
        ]);
        
        try {
            // Secure file upload
            $file = $request->file('file');
            $safeFilename = $this->fileService->secureUpload($file, 'digital_assets');
            
            // Generate asset code
            $assetCode = 'DIG-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            
            // Create digital asset
            $asset = DigitalAsset::create([
                'asset_code' => $assetCode,
                'collection_id' => $request->collection_id,
                'title' => SecurityService::sanitizeInput($request->title),
                'description' => SecurityService::sanitizeInput($request->description),
                'language' => SecurityService::sanitizeInput($request->language),
                'asset_type' => $this->detectAssetType($file),
                'file_path' => $safeFilename,
                'file_format' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'access_level' => $request->access_level,
                'digitized_by' => Auth::id(),
                'digitization_date' => now(),
                'metadata' => $this->extractMetadata($file)
            ]);
            
            // Generate thumbnail if image
            if (strpos($file->getMimeType(), 'image') !== false) {
                $thumbnail = $this->fileService->generateThumbnail($file);
                $asset->thumbnail_path = $thumbnail;
                $asset->save();
            }
            
            // Extract OCR text for searchable documents
            if (in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx'])) {
                $ocrText = $this->extractOCRText($file);
                $asset->ocr_text = $ocrText;
                $asset->save();
            }
            
            SecurityService::logSecurityEvent(Auth::id(), 'digital_asset_uploaded', [
                'asset_id' => $asset->id,
                'asset_code' => $assetCode,
                'file_size' => $file->getSize()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Digital asset uploaded successfully',
                'asset' => $asset
            ]);
            
        } catch (\Exception $e) {
            SecurityService::logSecurityEvent(Auth::id(), 'digital_asset_upload_failed', [
                'error' => $e->getMessage(),
                'file_name' => $request->file('file')->getClientOriginalName()
            ]);
            
            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Request access to restricted asset
     */
    public function requestAccess(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:digital_assets,id',
            'purpose' => 'required|string|min:50|max:1000',
            'duration_days' => 'required|integer|min:1|max:30'
        ]);
        
        $asset = DigitalAsset::findOrFail($request->asset_id);
        
        // Check if already requested
        $existingRequest = AccessRequest::where('user_id', Auth::id())
            ->where('digital_asset_id', $asset->id)
            ->where('status', 'pending')
            ->first();
            
        if ($existingRequest) {
            return response()->json([
                'error' => 'You already have a pending request for this asset'
            ], 400);
        }
        
        // Create access request
        $accessRequest = AccessRequest::create([
            'request_code' => 'ACC-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'user_id' => Auth::id(),
            'digital_asset_id' => $asset->id,
            'request_type' => 'digital_access',
            'purpose' => SecurityService::sanitizeInput($request->purpose),
            'requested_date' => now(),
            'duration_days' => $request->duration_days,
            'status' => 'pending'
        ]);
        
        // Notify administrators
        $this->notifyAdminsOfAccessRequest($accessRequest);
        
        SecurityService::logSecurityEvent(Auth::id(), 'access_request_created', [
            'request_id' => $accessRequest->id,
            'asset_id' => $asset->id,
            'purpose' => substr($request->purpose, 0, 100)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Access request submitted successfully',
            'request_code' => $accessRequest->request_code
        ]);
    }
    
    /**
     * Check asset access permissions
     */
    private function checkAssetAccess(DigitalAsset $asset, Request $request)
    {
        $user = Auth::user();
        
        // Public assets accessible to all
        if ($asset->access_level === 'public') {
            return true;
        }
        
        // Registered users access
        if ($asset->access_level === 'registered_users' && $user) {
            return true;
        }
        
        // Researchers only
        if ($asset->access_level === 'researchers_only' && $user->user_type === 'researcher') {
            return true;
        }
        
        // Restricted - need approved access request
        if ($asset->access_level === 'restricted') {
            $hasAccess = AccessRequest::where('user_id', $user->id)
                ->where('digital_asset_id', $asset->id)
                ->where('status', 'approved')
                ->where('requested_date', '>=', now()->subDays(30))
                ->exists();
                
            if ($hasAccess) {
                return true;
            }
        }
        
        // Log unauthorized access attempt
        SecurityService::logSecurityEvent($user->id, 'unauthorized_asset_access_attempt', [
            'asset_id' => $asset->id,
            'asset_access_level' => $asset->access_level,
            'user_type' => $user->user_type,
            'ip' => $request->ip()
        ]);
        
        abort(403, 'You do not have permission to access this digital asset');
    }
    
    /**
     * Detect asset type from file
     */
    private function detectAssetType($file)
    {
        $mime = $file->getMimeType();
        
        if (strpos($mime, 'image') !== false) {
            return 'photograph';
        } elseif (strpos($mime, 'pdf') !== false) {
            return 'scanned_manuscript';
        } elseif (strpos($mime, 'audio') !== false) {
            return 'interview_recording';
        } elseif (strpos($mime, 'video') !== false) {
            return 'documentary';
        } elseif (in_array($file->getClientOriginalExtension(), ['doc', 'docx'])) {
            return 'research_paper';
        }
        
        return 'document';
    }
    
    /**
     * Extract metadata from file
     */
    private function extractMetadata($file)
    {
        $metadata = [
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now()->toISOString(),
            'uploaded_by' => Auth::user()->name,
            'file_hash' => hash_file('sha256', $file->path())
        ];
        
        // Add image metadata if applicable
        if (strpos($file->getMimeType(), 'image') !== false) {
            $imageInfo = getimagesize($file->path());
            if ($imageInfo) {
                $metadata['image_dimensions'] = [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'type' => $imageInfo[2]
                ];
            }
        }
        
        return $metadata;
    }
    
    /**
     * Extract OCR text from document
     */
    private function extractOCRText($file)
    {
        // This would integrate with Tesseract OCR or Google Vision API
        // For now, return placeholder
        return "OCR text extraction would be implemented here with Tesseract OCR";
    }
    
    /**
     * Notify admins of access request
     */
    private function notifyAdminsOfAccessRequest($accessRequest)
    {
        // Implementation for email/notification system
        // Would send notification to administrators
    }
}

// resources/views/digital-library/index.blade.php
@extends('layouts.master')

@section('title', 'Digital Library - Arewa Conservation Platform')
@section('page-title', 'Digital Library')

@section('content')
<div class="container-fluid">
    <!-- Search and Filters -->
    <div class="stat-card mb-4">
        <form action="{{ route('digital-library.index') }}" method="GET" id="searchForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search manuscripts, documents..." 
                               value="{{ request('search') }}"
                               onkeydown="if(event.keyCode==13) document.getElementById('searchForm').submit()">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select" onchange="document.getElementById('searchForm').submit()">
                        <option value="">All Categories</option>
                        <option value="manuscript" {{ request('category') == 'manuscript' ? 'selected' : '' }}>Manuscripts</option>
                        <option value="document" {{ request('category') == 'document' ? 'selected' : '' }}>Documents</option>
                        <option value="photograph" {{ request('category') == 'photograph' ? 'selected' : '' }}>Photographs</option>
                        <option value="map" {{ request('category') == 'map' ? 'selected' : '' }}>Maps</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="state" class="form-select" onchange="document.getElementById('searchForm').submit()">
                        <option value="">All States</option>
                        @foreach(['Kaduna', 'Kano', 'Sokoto', 'Borno', 'Katsina', 'Bauchi', 'Zamfara', 'Kebbi', 'Jigawa', 'Gombe', 'Adamawa', 'Yobe', 'Taraba', 'Benue', 'Kogi', 'Kwara', 'Nasarawa', 'Niger', 'Plateau'] as $state)
                        <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>
                            {{ $state }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Digital Assets Grid -->
    <div class="row">
        @foreach($assets as $asset)
        <div class="col-md-4 mb-4">
            <div class="stat-card h-100">
                <!-- Thumbnail -->
                <div class="text-center mb-3" style="height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                    @if($asset->thumbnail_path)
                    <img src="{{ Storage::url($asset->thumbnail_path) }}" 
                         alt="{{ $asset->title }}" 
                         style="max-height: 100%; max-width: 100%; object-fit: contain;">
                    @else
                    <div class="text-muted">
                        <i class="bi bi-file-earmark-text" style="font-size: 4rem;"></i>
                        <div>{{ strtoupper($asset->file_format) }}</div>
                    </div>
                    @endif
                </div>
                
                <!-- Access Badge -->
                <div class="mb-2">
                    <span class="badge bg-{{ 
                        $asset->access_level == 'public' ? 'success' : 
                        ($asset->access_level == 'researchers_only' ? 'warning' : 'danger')
                    }}">
                        {{ str_replace('_', ' ', $asset->access_level) }}
                    </span>
                    @if($asset->collection)
                    <span class="badge bg-info">{{ $asset->collection->state_of_origin }}</span>
                    @endif
                </div>
                
                <!-- Title and Info -->
                <h5>{{ Str::limit($asset->title, 50) }}</h5>
                <p class="text-muted small">
                    <i class="bi bi-translate"></i> {{ $asset->language }} • 
                    <i class="bi bi-file-earmark"></i> {{ strtoupper($asset->file_format) }} • 
                    <i class="bi bi-eye"></i> {{ $asset->view_count }} views
                </p>
                
                <!-- Actions -->
                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('digital-library.show', $asset->id) }}" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> View
                    </a>
                    
                    @if($asset->access_level != 'restricted' || Auth::user()->can('download_digital_assets'))
                    <a href="{{ route('digital-library.download', $asset->id) }}" 
                       class="btn btn-sm btn-outline-success"
                       onclick="return confirm('Download this asset?')">
                        <i class="bi bi-download"></i> Download
                    </a>
                    @else
                    <button class="btn btn-sm btn-outline-warning" 
                            onclick="requestAccess({{ $asset->id }})">
                        <i class="bi bi-key"></i> Request Access
                    </button>
                    @endif
                </div>
                
                <!-- Quick Info -->
                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted">
                        Uploaded: {{ $asset->digitization_date?->format('M d, Y') ?? 'N/A' }}<br>
                        Size: {{ round($asset->file_size / 1024 / 1024, 2) }} MB
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $assets->links() }}
    </div>
    
    <!-- Upload Section (Admin only) -->
    @if(Auth::user()->can('upload_digital_assets'))
    <div class="stat-card mt-4">
        <h5>Upload Digital Asset</h5>
        <form action="{{ route('digital-library.upload') }}" method="POST" enctype="multipart/form-data" 
              id="uploadForm" onsubmit="return validateUpload()">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Select Collection</label>
                        <select name="collection_id" class="form-select" required>
                            <option value="">Choose collection...</option>
                            @foreach($collections as $collection)
                            <option value="{{ $collection->id }}">
                                {{ $collection->collection_code }} - {{ $collection->collection_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Access Level</label>
                        <select name="access_level" class="form-select" required>
                            <option value="public">Public</option>
                            <option value="registered_users">Registered Users</option>
                            <option value="researchers_only">Researchers Only</option>
                            <option value="restricted">Restricted</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required
                               placeholder="Enter descriptive title">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Language</label>
                        <input type="text" name="language" class="form-control" 
                               value="Arabic" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Brief description of the digital asset"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">File</label>
                <input type="file" name="file" class="form-control" required
                       accept=".pdf,.jpg,.jpeg,.png,.tiff,.doc,.docx,.mp3,.wav,.mp4">
                <div class="form-text">
                    Supported formats: PDF, Images (JPG, PNG, TIFF), Documents (DOC, DOCX), Audio (MP3, WAV), Video (MP4)
                    Max size: 100MB
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                All uploaded files will be scanned for malware and processed for optimal viewing.
                Uploading copyrighted material without permission is prohibited.
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-upload"></i> Upload Digital Asset
            </button>
        </form>
    </div>
    @endif
</div>

<!-- Access Request Modal -->
<div class="modal fade" id="accessRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Access</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="accessRequestForm">
                    @csrf
                    <input type="hidden" name="asset_id" id="requestAssetId">
                    
                    <div class="mb-3">
                        <label class="form-label">Purpose of Access</label>
                        <textarea name="purpose" class="form-control" rows="4" required
                                  placeholder="Please describe in detail why you need access to this asset..."></textarea>
                        <div class="form-text">Minimum 50 characters</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Duration Needed (days)</label>
                        <input type="number" name="duration_days" class="form-control" 
                               min="1" max="30" value="7" required>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Your request will be reviewed by administrators. 
                        Access will be granted based on academic/research merit.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAccessRequest()">Submit Request</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentAssetId = null;
    
    function requestAccess(assetId) {
        currentAssetId = assetId;
        document.getElementById('requestAssetId').value = assetId;
        $('#accessRequestModal').modal('show');
    }
    
    function submitAccessRequest() {
        const form = document.getElementById('accessRequestForm');
        const formData = new FormData(form);
        
        fetch('{{ route("digital-library.request-access") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Access request submitted successfully! Your request code: ' + data.request_code);
                $('#accessRequestModal').modal('hide');
                form.reset();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the request');
        });
    }
    
    function validateUpload() {
        const fileInput = document.querySelector('input[name="file"]');
        const maxSize = 100 * 1024 * 1024; // 100MB
        
        if (fileInput.files[0].size > maxSize) {
            alert('File size exceeds 100MB limit');
            return false;
        }
        
        // Additional validation can be added here
        return true;
    }
</script>
@endpush
@endsection