<?php
// resources/views/security/dashboard.blade.php
@extends('layouts.master')

@section('title', 'Security Dashboard - Arewa Conservation Platform')
@section('page-title', 'Security Monitoring Center')

@section('content')
<div class="container-fluid">
    <!-- Security Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card border-start border-5 border-danger">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-number">{{ $stats['failed_logins'] }}</div>
                        <div class="text-muted">Failed Logins (24h)</div>
                    </div>
                    <i class="bi bi-shield-exclamation text-danger" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card border-start border-5 border-warning">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-number">{{ $stats['suspicious_activities'] }}</div>
                        <div class="text-muted">Suspicious Activities</div>
                    </div>
                    <i class="bi bi-eye text-warning" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card border-start border-5 border-info">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-number">{{ $stats['access_violations'] }}</div>
                        <div class="text-muted">Access Violations</div>
                    </div>
                    <i class="bi bi-door-closed text-info" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card border-start border-5 border-success">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-number">{{ $stats['security_scans'] }}</div>
                        <div class="text-muted">Security Scans Today</div>
                    </div>
                    <i class="bi bi-shield-check text-success" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Security Monitoring -->
    <div class="row">
        <div class="col-md-8">
            <div class="stat-card">
                <h5>Real-time Security Events</h5>
                <div id="securityEvents" style="max-height: 400px; overflow-y: auto;">
                    <div class="list-group">
                        @foreach($recent_events as $event)
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="bi bi-{{ 
                                        $event->severity == 'critical' ? 'exclamation-triangle-fill text-danger' : 
                                        ($event->severity == 'high' ? 'exclamation-circle-fill text-warning' : 
                                        'info-circle-fill text-info')
                                    }}"></i>
                                    <strong>{{ $event->event_type }}</strong>
                                </div>
                                <small class="text-muted">{{ $event->created_at->format('H:i:s') }}</small>
                            </div>
                            <p class="mb-1">{{ $event->description }}</p>
                            <small>
                                <span class="badge bg-secondary">IP: {{ $event->ip_address }}</span>
                                @if($event->user_id)
                                <span class="badge bg-info">User: {{ $event->user->name }}</span>
                                @endif
                            </small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div class="stat-card mt-4">
                <h5>Security Configuration</h5>
                <form action="{{ route('security.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Login Attempts</label>
                                <input type="number" name="max_login_attempts" 
                                       value="{{ $settings->max_login_attempts }}" 
                                       class="form-control" min="3" max="10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Session Timeout (minutes)</label>
                                <input type="number" name="session_timeout" 
                                       value="{{ $settings->session_timeout }}" 
                                       class="form-control" min="15" max="120">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_2fa" 
                                   id="enable_2fa" {{ $settings->enable_2fa ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_2fa">
                                Enable Two-Factor Authentication
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_captcha" 
                                   id="enable_captcha" {{ $settings->enable_captcha ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_captcha">
                                Enable CAPTCHA on Login
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="log_all_activities" 
                                   id="log_all_activities" {{ $settings->log_all_activities ? 'checked' : '' }}>
                            <label class="form-check-label" for="log_all_activities">
                                Log All User Activities
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Security Settings
                    </button>
                </form>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- User Activity Heatmap -->
            <div class="stat-card">
                <h5>User Activity Heatmap (Last 24h)</h5>
                <canvas id="activityChart" height="300"></canvas>
            </div>
            
            <!-- Security Checklist -->
            <div class="stat-card mt-4">
                <h5>Security Checklist</h5>
                <div class="list-group">
                    @foreach($checklist as $item)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($item['status'] == 'completed')
                                <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif($item['status'] == 'warning')
                                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                @else
                                <i class="bi bi-x-circle-fill text-danger"></i>
                                @endif
                                <span class="ms-2">{{ $item['task'] }}</span>
                            </div>
                            @if($item['status'] != 'completed')
                            <button class="btn btn-sm btn-outline-primary">Fix</button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Quick Security Actions -->
            <div class="stat-card mt-4">
                <h5>Quick Security Actions</h5>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger" onclick="runSecurityScan()">
                        <i class="bi bi-shield-exclamation"></i> Run Security Scan
                    </button>
                    <button class="btn btn-outline-warning" onclick="blockIP()">
                        <i class="bi bi-ban"></i> Block Suspicious IP
                    </button>
                    <button class="btn btn-outline-info" onclick="exportLogs()">
                        <i class="bi bi-download"></i> Export Security Logs
                    </button>
                    <button class="btn btn-outline-success" onclick="forceLogoutAll()">
                        <i class="bi bi-box-arrow-right"></i> Force Logout All Users
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Audit Logs -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5>Recent Audit Logs</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>Record ID</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($audit_logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                                <td>{{ $log->table_name }}</td>
                                <td>{{ $log->record_id }}</td>
                                <td><code>{{ $log->ip_address }}</code></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" 
                                            onclick="showLogDetails({{ $log->id }})">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $audit_logs->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Activity Heatmap Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: @json($activity_hours),
            datasets: [{
                label: 'Activities',
                data: @json($activity_counts),
                borderColor: '#2c5530',
                backgroundColor: 'rgba(44, 85, 48, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Security Functions
    function runSecurityScan() {
        if (confirm('Run full security scan? This may take a few minutes.')) {
            $.ajax({
                url: '{{ route("security.scan") }}',
                method: 'POST',
                success: function(response) {
                    alert('Security scan completed: ' + response.message);
                    location.reload();
                },
                error: function() {
                    alert('Error running security scan');
                }
            });
        }
    }
    
    function showLogDetails(logId) {
        $.ajax({
            url: '{{ url("security/logs") }}/' + logId,
            method: 'GET',
            success: function(response) {
                $('#logDetailsContent').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Old Values:</strong>
                            <pre class="bg-light p-3">${JSON.stringify(response.old_values, null, 2)}</pre>
                        </div>
                        <div class="col-md-6">
                            <strong>New Values:</strong>
                            <pre class="bg-light p-3">${JSON.stringify(response.new_values, null, 2)}</pre>
                        </div>
                    </div>
                    <div class="mt-3">
                        <strong>User Agent:</strong>
                        <code>${response.user_agent}</code>
                    </div>
                    <div class="mt-2">
                        <strong>URL:</strong>
                        <code>${response.url}</code>
                    </div>
                `);
                $('#logDetailsModal').modal('show');
            }
        });
    }
    
    // Real-time updates for security events
    const eventSource = new EventSource('{{ route("security.events.stream") }}');
    
    eventSource.onmessage = function(event) {
        const data = JSON.parse(event.data);
        const eventsContainer = document.getElementById('securityEvents');
        const newEvent = document.createElement('div');
        newEvent.className = 'list-group-item list-group-item-action';
        newEvent.innerHTML = `
            <div class="d-flex w-100 justify-content-between">
                <div>
                    <i class="bi bi-${data.severity == 'critical' ? 'exclamation-triangle-fill text-danger' : 
                                       data.severity == 'high' ? 'exclamation-circle-fill text-warning' : 
                                       'info-circle-fill text-info'}"></i>
                    <strong>${data.event_type}</strong>
                </div>
                <small class="text-muted">Just now</small>
            </div>
            <p class="mb-1">${data.description}</p>
            <small>
                <span class="badge bg-secondary">IP: ${data.ip_address}</span>
            </small>
        `;
        eventsContainer.insertBefore(newEvent, eventsContainer.firstChild);
    };
</script>
@endpush
@endsection