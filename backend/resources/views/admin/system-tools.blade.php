@extends('layouts.admin')

@section('title', 'System Tools')
@section('page-title', 'System Tools')

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i data-feather="cpu"></i>
            </div>
            <div class="stat-title">PHP Version</div>
            <div class="stat-value" style="font-size: 1.5rem;">{{ $systemInfo['php_version'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i data-feather="box"></i>
            </div>
            <div class="stat-title">Laravel Version</div>
            <div class="stat-value" style="font-size: 1.5rem;">{{ $systemInfo['laravel_version'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <i data-feather="database"></i>
            </div>
            <div class="stat-title">Database</div>
            <div class="stat-value" style="font-size: 1.25rem;">{{ $systemInfo['database_connection'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon {{ $systemInfo['pending_migrations'] > 0 ? 'red' : 'green' }}">
                <i data-feather="git-merge"></i>
            </div>
            <div class="stat-title">Pending Migrations</div>
            <div class="stat-value">
                {{ $systemInfo['pending_migrations'] >= 0 ? $systemInfo['pending_migrations'] : 'Error' }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">

                <!-- Update Database -->
                <div style="background: #f9fafb; border-radius: 0.75rem; padding: 1.5rem; border: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <div
                            style="width: 40px; height: 40px; border-radius: 0.5rem; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                            <i data-feather="database"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 1rem; font-weight: 600;">Update Database</h4>
                            <p style="margin: 0; font-size: 0.75rem; color: #6b7280;">Run pending migrations</p>
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; color: #4b5563; margin-bottom: 1rem;">
                        Applies any pending database changes. Your data is safe.
                    </p>
                    <button onclick="confirmMigrate()" class="btn btn-primary" style="width: 100%;">
                        <i data-feather="play"></i>
                        Run Migrations
                    </button>
                </div>

                <!-- Clear Cache -->
                <div style="background: #f9fafb; border-radius: 0.75rem; padding: 1.5rem; border: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <div
                            style="width: 40px; height: 40px; border-radius: 0.5rem; background: #ecfdf5; display: flex; align-items: center; justify-content: center; color: #10b981;">
                            <i data-feather="refresh-cw"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 1rem; font-weight: 600;">Clear Cache</h4>
                            <p style="margin: 0; font-size: 0.75rem; color: #6b7280;">Clear config, route & view cache</p>
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; color: #4b5563; margin-bottom: 1rem;">
                        Clears cached files to reflect recent code changes.
                    </p>
                    <button onclick="clearCache()" class="btn btn-primary" style="width: 100%; background: #10b981;">
                        <i data-feather="trash-2"></i>
                        Clear Cache
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- System Info -->
    <div class="card">
        <div class="card-header">
            <h3>System Information</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <tr>
                    <td style="font-weight: 600; width: 200px;">Server Software</td>
                    <td>{{ $systemInfo['server_software'] }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Disk Space</td>
                    <td>{{ $systemInfo['disk_free'] }} free of {{ $systemInfo['disk_total'] }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Database Status</td>
                    <td>
                        @if($systemInfo['database_connection'] === 'Connected')
                            <span class="badge badge-approved">{{ $systemInfo['database_connection'] }}</span>
                        @else
                            <span class="badge badge-rejected">{{ $systemInfo['database_connection'] }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Output Area -->
    <div class="card" id="outputCard" style="display: none;">
        <div class="card-header">
            <h3>Command Output</h3>
        </div>
        <div class="card-body">
            <pre id="outputArea"
                style="background: #1f2937; color: #10b981; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-family: monospace; font-size: 0.875rem; white-space: pre-wrap;"></pre>
        </div>
    </div>
@endsection

@section('modals')
    <!-- Migrate Confirmation Modal -->
    <div class="modal" id="migrateModal">
        <div class="modal-overlay" onclick="closeMigrateModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Database Update</h3>
                <button class="btn btn-ghost" onclick="closeMigrateModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div
                    style="background: #fffbeb; border: 1px solid #fcd34d; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem;">
                    <strong style="color: #92400e;">⚠️ Important</strong>
                    <p style="margin: 0.5rem 0 0; color: #92400e; font-size: 0.875rem;">
                        It's recommended to backup your database before running migrations. Your existing data will NOT be
                        deleted.
                    </p>
                </div>
                <p>Are you sure you want to run pending database migrations?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeMigrateModal()">Cancel</button>
                <button class="btn btn-primary" onclick="runMigrate()">
                    <i data-feather="play"></i>
                    Run Migrations
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function confirmMigrate() {
            document.getElementById('migrateModal').style.display = 'flex';
        }

        function closeMigrateModal() {
            document.getElementById('migrateModal').style.display = 'none';
        }

        function runMigrate() {
            closeMigrateModal();
            showOutput('Running migrations...');

            fetch('{{ route("admin.system-tools.migrate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        showOutput(data.output || 'Done.');
                    } else {
                        showToast(data.message, 'error');
                        showOutput('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    showToast('Request failed', 'error');
                    showOutput('Error: ' + error.message);
                });
        }

        function clearCache() {
            showOutput('Clearing cache...');

            fetch('{{ route("admin.system-tools.clear-cache") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        showOutput('Cache cleared successfully!\n\n✓ Config cache cleared\n✓ Route cache cleared\n✓ View cache cleared');
                    } else {
                        showToast(data.message, 'error');
                        showOutput('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    showToast('Request failed', 'error');
                    showOutput('Error: ' + error.message);
                });
        }

        function showOutput(text) {
            document.getElementById('outputCard').style.display = 'block';
            document.getElementById('outputArea').textContent = text;
            document.getElementById('outputCard').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
@endsection