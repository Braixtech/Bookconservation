<?php
// resources/views/admin/dashboard.blade.php
@extends('layouts.master')

@section('title', 'Admin Dashboard - Arewa Conservation Platform')
@section('page-title', 'Administration Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-number">{{ $stats['total_collections'] }}</div>
                        <div class="text-muted">Collections</div>
                    </div>
                    <i class="bi bi-archive text-primary" style="font-size: 2rem;"></i>
                </div>
                <div class="mt-3">
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> {{ $stats['collections_growth'] }}% this month
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-number">{{ $stats['active_projects'] }}</div>
                        <div class="text-muted">Active Projects</div>
                    </div>
                    <i class="bi bi-tools text-warning" style="font-size: 2rem;"></i>
                </div>
                <div class="mt-3">
                    <small class="text-danger">
                        <i class="bi bi-clock"></i> {{ $stats['critical_projects'] }} critical
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-number">{{ $stats['digital_assets'] }}</div>
                        <div class="text-muted">Digital Assets</div>
                    </div>
                    <i class="bi bi-file-earmark-text text-info" style="font-size: 2rem;"></i>
                </div>
                <div class="mt-3">
                    <small class="text-success">
                        <i class="bi bi-download"></i> {{ $stats['downloads_today'] }} downloads today
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-number">{{ $stats['active_users'] }}</div>
                        <div class="text-muted">Active Users</div>
                    </div>
                    <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                </div>
                <div class="mt-3">
                    <small class="text-info">
                        <i class="bi bi-person-plus"></i> {{ $stats['new_users'] }} new this week
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Northern States Distribution -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="stat-card">
                <h5>Collections by Northern States</h5>
                <canvas id="statesChart" height="200"></canvas>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card">
                <h5>Conservation Status</h5>
                <canvas id="conservationChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-6">
            <div class="stat-card">
                <h5>Recent Access Requests</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Researcher</th>
                                <th>Collection</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_requests as $request)
                            <tr>
                                <td>{{ $request->researcher->name }}</td>
                                <td>{{ $request->collection->collection_code }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $request->status == 'approved' ? 'success' : 
                                        ($request->status == 'pending' ? 'warning' : 'danger')
                                    }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td>{{ $request->created_at->format('M d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('access-requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="stat-card">
                <h5>Security Alerts</h5>
                <div class="list-group">
                    @foreach($security_alerts as $alert)
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 text-{{ $alert->type == 'critical' ? 'danger' : 'warning' }}">
                                <i class="bi bi-shield-exclamation"></i> {{ $alert->title }}
                            </h6>
                            <small>{{ $alert->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">{{ Str::limit($alert->description, 100) }}</p>
                        <small>IP: {{ $alert->ip_address }}</small>
                    </div>
                    @endforeach
                </div>
                <a href="{{ route('security.logs') }}" class="btn btn-sm btn-outline-danger mt-3">View Security Logs</a>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5>Quick Actions</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('collections.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Collection
                    </a>
                    <a href="{{ route('conservation.projects.create') }}" class="btn btn-warning">
                        <i class="bi bi-tools"></i> Start Conservation
                    </a>
                    <a href="{{ route('digital-assets.create') }}" class="btn btn-info">
                        <i class="bi bi-upload"></i> Upload Digital Asset
                    </a>
                    <a href="{{ route('reports.generate') }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-text"></i> Generate Report
                    </a>
                    <a href="{{ route('backup.create') }}" class="btn btn-secondary">
                        <i class="bi bi-database"></i> Backup Database
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // States Distribution Chart
    const statesCtx = document.getElementById('statesChart').getContext('2d');
    new Chart(statesCtx, {
        type: 'bar',
        data: {
            labels: @json($states_distribution->pluck('state')),
            datasets: [{
                label: 'Collections',
                data: @json($states_distribution->pluck('count')),
                backgroundColor: [
                    '#2c5530', '#006400', '#228B22', '#32CD32', '#90EE90',
                    '#FFD700', '#DAA520', '#B8860B', '#8B4513', '#A0522D'
                ],
                borderColor: 'rgba(255, 255, 255, 0.1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Conservation Status Chart
    const consCtx = document.getElementById('conservationChart').getContext('2d');
    new Chart(consCtx, {
        type: 'doughnut',
        data: {
            labels: ['Excellent', 'Good', 'Fair', 'Poor', 'Critical'],
            datasets: [{
                data: @json($conservation_stats),
                backgroundColor: [
                    '#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#DC2626'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
@endsection