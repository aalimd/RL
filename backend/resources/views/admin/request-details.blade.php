@extends('layouts.admin')

@section('page-title', 'Request Details')

@section('content')
    @php
        $canManageRequests = auth()->check() && in_array(auth()->user()->role, ['admin', 'editor'], true);
        $statusValue = old('status', $request->status);
        $adminMessageValue = old(
            'admin_message',
            $statusValue === 'Rejected' ? ($request->rejection_reason ?? '') : ($request->admin_message ?? '')
        );
    @endphp
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('admin.requests') }}" class="btn btn-ghost">
            <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
            Back to Requests
        </a>
        <div style="display: flex; gap: 0.5rem;">
            @if($canManageRequests)
                <button type="button" class="btn btn-secondary" onclick="openEditModal()">
                    <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                    Edit Request
                </button>
            @endif
            @if($canManageRequests && $request->status === 'Approved')
                <a href="{{ route('admin.requests.letter-pdf', $request->id) }}" class="btn btn-secondary"
                    style="background: #059669; color: white; border-color: #059669;">
                    <i data-feather="download" style="width: 16px; height: 16px;"></i>
                    Download PDF
                </a>
                <form method="POST" action="{{ route('admin.requests.letter-drive', $request->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary"
                        style="background: #166534; color: white; border-color: #166534;">
                        <i data-feather="hard-drive" style="width: 16px; height: 16px;"></i>
                        Sync to Drive
                    </button>
                </form>
            @endif
            <button type="button" class="btn btn-primary" onclick="openPreviewModal()">
                <i data-feather="file-text" style="width: 16px; height: 16px;"></i>
                Preview Letter
            </button>
        </div>
    </div>

    @if(session('success'))
        <div
            style="background: var(--success-bg); color: var(--success-text); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid var(--success-border);">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div
            style="background: var(--error-bg); color: var(--error-text); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid var(--error-border);">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->has('status') || $errors->has('admin_message'))
        @php
            $statusErrors = array_merge($errors->get('status'), $errors->get('admin_message'));
        @endphp
        <div
            style="background: var(--warning-bg); color: var(--warning-text); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid var(--warning-border);">
            <div style="font-weight: 600; margin-bottom: 0.5rem;">Could not update status:</div>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($statusErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card">
        <div class="card-header">
            <div>
                <h3 style="margin-bottom: 0.25rem;">Request #{{ $request->tracking_id }}</h3>
                <span style="font-size: 0.875rem; color: var(--text-muted);">Created
                    {{ $request->created_at->format('M d, Y H:i') }}</span>
            </div>
            <span class="badge 
                                                                                    @if($request->status === 'Approved') badge-approved
                                                                                    @elseif($request->status === 'Rejected') badge-rejected
                                                                                    @elseif($request->status === 'Needs Revision') badge-revision
                                                                                    @else badge-pending @endif">
                {{ $request->status }}
            </span>
        </div>

        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                <!-- Student Information -->
                <div>
                    <h4
                        style="font-size: 0.875rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem;">
                        Student
                        Information</h4>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">First Name</label>
                        <p style="font-weight: 500; color: var(--text-main);">{{ $request->student_name ?? 'N/A' }}</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Middle Name</label>
                        <p style="font-weight: 500; color: var(--text-main);">{{ $request->middle_name ?? 'N/A' }}</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Last Name</label>
                        <p style="font-weight: 500; color: var(--text-main);">{{ $request->last_name ?? 'N/A' }}</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Email</label>
                        <p style="font-weight: 500; color: var(--text-main);">{{ $request->student_email ?? 'N/A' }}</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">University</label>
                        <p style="font-weight: 500; color: var(--text-main);">{{ $request->university ?? 'N/A' }}</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Gender</label>
                        @php
                            $formData = $request->form_data ?? [];
                            $gender = $formData['gender'] ?? 'male';
                        @endphp
                        <p style="font-weight: 500; color: var(--text-main);">{{ ucfirst($gender) }}</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Major</label>
                        <p style="font-weight: 500; color: var(--text-main);">
                            {{ $request->major ?? ($formData['major'] ?? 'N/A') }}
                        </p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Phone Number</label>
                        <p style="font-weight: 500; color: var(--text-main);">{{ $request->phone ?? 'N/A' }}</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">ID Number</label>
                        <p style="font-family: monospace; font-weight: 500; color: var(--text-main);">
                            {{ $request->verification_token ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- Request Details -->
                <div>
                    <h4
                        style="font-size: 0.875rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem;">
                        Request
                        Details</h4>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Purpose</label>
                        <p style="font-weight: 500; color: var(--text-main);">{{ $request->purpose ?? 'N/A' }}</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Training Period</label>
                        @php
                            $trainingPeriodLabel = 'N/A';
                            if ($request->training_period) {
                                try {
                                    $trainingPeriodLabel = \Carbon\Carbon::createFromFormat('Y-m', $request->training_period)->format('F, Y');
                                } catch (\Throwable $e) {
                                    $trainingPeriodLabel = $request->training_period;
                                }
                            }
                        @endphp
                        <p style="font-weight: 500; color: var(--text-main);">
                            {{ $trainingPeriodLabel }}
                        </p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Deadline</label>
                        <p style="font-weight: 500; color: var(--text-main);">
                            {{ $request->deadline ? \Carbon\Carbon::parse($request->deadline)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>

                    @if($request->document_path)
                        <div style="margin-bottom: 1rem;">
                            <label style="font-size: 0.75rem; color: var(--text-muted);">Attachment</label>
                            <div>
                                <a href="{{ route('admin.requests.document', $request->id) }}" target="_blank"
                                    class="btn btn-sm btn-white"
                                    style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                                    <i data-feather="paperclip" style="width: 14px; height: 14px;"></i>
                                    View Document
                                </a>
                            </div>
                        </div>
                    @endif

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Content Option</label>
                        <p style="font-weight: 500; color: var(--text-main);">
                            {{ $request->content_option ?? 'Auto-Generate' }}</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Template</label>
                        @php
                            $currentTemplate = $templates->firstWhere('id', (int) $request->template_id);
                        @endphp
                        <p style="font-weight: 500; color: var(--text-main);">
                            {{ $currentTemplate ? $currentTemplate->name : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <div
                    style="display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; flex-wrap: wrap; margin-bottom: 1rem;">
                    <div>
                        <h4 style="font-size: 0.875rem; text-transform: uppercase; color: var(--text-muted); margin: 0 0 0.35rem;">
                            Google Drive Backup</h4>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">
                            Keep an off-app copy of the official letter for recovery and manual sharing.
                        </p>
                    </div>
                    @if($request->status === 'Approved' && $canManageRequests)
                        <form method="POST" action="{{ route('admin.requests.letter-drive', $request->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                <i data-feather="refresh-cw" style="width: 16px; height: 16px;"></i>
                                {{ $request->drive_backup_status === 'synced' ? 'Sync Again' : 'Sync Now' }}
                            </button>
                        </form>
                    @endif
                </div>

                @php
                    $driveStatus = $request->drive_backup_status ?? 'not_synced';
                    $driveLabel = match ($driveStatus) {
                        'synced' => 'Synced',
                        'failed' => 'Failed',
                        default => 'Not synced',
                    };
                    $driveColors = match ($driveStatus) {
                        'synced' => ['bg' => 'rgba(22, 101, 52, 0.16)', 'border' => 'rgba(34, 197, 94, 0.35)', 'text' => '#bbf7d0'],
                        'failed' => ['bg' => 'rgba(127, 29, 29, 0.2)', 'border' => 'rgba(248, 113, 113, 0.35)', 'text' => '#fecaca'],
                        default => ['bg' => 'rgba(30, 41, 59, 0.55)', 'border' => 'rgba(148, 163, 184, 0.22)', 'text' => 'var(--text-main)'],
                    };
                @endphp

                <div
                    style="padding: 1.1rem 1.2rem; border-radius: 0.9rem; border: 1px solid {{ $driveColors['border'] }}; background: {{ $driveColors['bg'] }};">
                    <div
                        style="display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap; margin-bottom: 1rem;">
                        <div>
                            <div
                                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">
                                Backup Status</div>
                            <div style="margin-top: 0.35rem; font-size: 1.1rem; font-weight: 700; color: {{ $driveColors['text'] }};">
                                {{ $driveLabel }}
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            @if($request->drive_backup_url)
                                <a href="{{ $request->drive_backup_url }}" target="_blank" rel="noopener" class="btn btn-secondary">
                                    <i data-feather="external-link" style="width: 16px; height: 16px;"></i>
                                    Open Drive File
                                </a>
                                <button type="button" class="btn btn-ghost" onclick="copyDriveLink('{{ e($request->drive_backup_url) }}')">
                                    <i data-feather="copy" style="width: 16px; height: 16px;"></i>
                                    Copy Link
                                </button>
                            @endif
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">
                                Last Synced</div>
                            <div style="margin-top: 0.25rem; font-weight: 600; color: var(--text-main);">
                                {{ $request->drive_backup_synced_at ? $request->drive_backup_synced_at->format('M d, Y h:i A') : 'Not synced yet' }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">
                                Drive File</div>
                            <div style="margin-top: 0.25rem; font-weight: 600; color: var(--text-main); word-break: break-word;">
                                {{ $request->drive_backup_file_name ?? 'No file stored yet' }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">
                                File ID</div>
                            <div style="margin-top: 0.25rem; font-weight: 600; color: var(--text-main); word-break: break-all;">
                                {{ $request->drive_backup_file_id ?? 'Not available yet' }}
                            </div>
                        </div>
                    </div>

                    @if($request->drive_backup_error)
                        <div
                            style="margin-top: 1rem; padding: 0.9rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(248, 113, 113, 0.3); background: rgba(127, 29, 29, 0.18); color: #fecaca;">
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.9;">Latest backup error</div>
                            <div style="margin-top: 0.35rem; font-weight: 500;">{{ $request->drive_backup_error }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if($request->custom_content)
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <h4 style="font-size: 0.875rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem;">
                        Custom Content / Notes</h4>
                    <div
                        style="background: var(--bg-color); color: var(--text-main); padding: 1.25rem; border-radius: 0.75rem; white-space: pre-wrap; border: 1px solid var(--border-color);">
                        {{ $request->custom_content }}
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <h4 style="font-size: 0.875rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem;">
                    Update
                    Status</h4>
                @if($canManageRequests)
                    <form method="POST" action="{{ route('admin.requests.update-status', $request->id) }}"
                        style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label
                                style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--text-main);">Status</label>
                            <select id="statusSelect" name="status" class="form-select"
                                style="padding: 0.5rem 1rem; border-radius: 0.5rem;">
                                <option value="Submitted" {{ $statusValue === 'Submitted' ? 'selected' : '' }}>Submitted
                                </option>
                                <option value="Under Review" {{ $statusValue === 'Under Review' ? 'selected' : '' }}>Under
                                    Review</option>
                                <option value="Needs Revision" {{ $statusValue === 'Needs Revision' ? 'selected' : '' }}>Needs
                                    Revision</option>
                                <option value="Approved" {{ $statusValue === 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ $statusValue === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="Archived" {{ $statusValue === 'Archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>

                        <div style="flex: 1; min-width: 250px;">
                            <label id="adminMessageLabel"
                                style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--text-main); font-weight: 500;"><span id="adminMessageLabelText">Admin
                                Message</span> <span
                                    id="adminMessageHint">
                                    @if($statusValue === 'Needs Revision')
                                        (Required for Needs Revision)
                                    @elseif($statusValue === 'Rejected')
                                        (Required for Rejected)
                                    @else
                                        (Optional)
                                    @endif
                                </span></label>
                            <textarea id="adminMessageField" name="admin_message" rows="1" class="form-textarea"
                                style="width: 100%; padding: 0.5rem 1rem; border-radius: 0.5rem; min-height: 42px; height: 42px; line-height: 1.5;"
                                placeholder="{{ $statusValue === 'Rejected' ? 'Reason for rejection...' : 'Message to student...' }}" onfocus="this.rows=3; this.style.height='auto'"
                                onblur="if(this.value==''){this.rows=1; this.style.height='42px'}" {{ in_array($statusValue, ['Needs Revision', 'Rejected'], true) ? 'required' : '' }}>{{ $adminMessageValue }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                @else
                    <div
                        style="background: var(--bg-color); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 0.75rem 1rem;">
                        Your account has read-only access. Only admin/editor roles can update request status.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closePreviewModal()"></div>
        <div class="modal-content">
            <!-- Toolbar -->
            <div class="modal-header">
                <h3>Recommendation Letter Preview</h3>
                <div class="modal-actions">
                    <button type="button" class="btn btn-primary" onclick="printLetter()"
                        style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-feather="printer" style="width: 16px; height: 16px;"></i> Print / Save as PDF
                    </button>
                    <button type="button" onclick="closePreviewModal()" class="btn btn-ghost"
                        style="color: var(--text-muted); padding: 0.5rem;">
                        <i data-feather="x" style="width: 24px; height: 24px;"></i>
                    </button>
                </div>
            </div>

            <!-- Preview Area (Grey Desk) -->
            <div class="modal-body">
                <div id="letterPreviewContent" class="letter-wrapper">
                    <div class="loading-container">
                        <div
                            style="width: 40px; height: 40px; border: 3px solid var(--border-color); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite;">
                        </div>
                        <span>Generating Letter...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($canManageRequests)
        <!-- Edit Modal -->
        <div id="editModal" class="modal" style="display: none;">
            <div class="modal-overlay" onclick="closeEditModal()"></div>
            <div class="modal-content" style="max-width: 700px; height: auto; max-height: 90vh; overflow-y: auto;">
                <div class="modal-header">
                    <h3>Edit Request Data</h3>
                    <button type="button" onclick="closeEditModal()" class="btn btn-ghost"
                        style="color: var(--text-muted); padding: 0.5rem;">
                        <i data-feather="x" style="width: 24px; height: 24px;"></i>
                    </button>
                </div>
                <div style="padding: 1.5rem;">
                    <form method="POST" action="{{ route('admin.requests.update', $request->id) }}">
                        @csrf
                        @method('PUT')

                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">First
                                    Name *</label>
                                <input type="text" name="student_name" class="form-input" value="{{ $request->student_name }}"
                                    required style="width: 100%; padding: 0.5rem 1rem; border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Middle
                                    Name</label>
                                <input type="text" name="middle_name" value="{{ $request->middle_name }}"
                                    style="width: 100%; padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Last
                                    Name</label>
                                <input type="text" name="last_name" value="{{ $request->last_name }}"
                                    style="width: 100%; padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Gender
                                    *</label>
                                @php
                                    $formData = $request->form_data ?? [];
                                    $currentGender = $formData['gender'] ?? 'male';
                                @endphp
                                <select name="gender" required
                                    style="width: 100%; padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                                    <option value="male" {{ $currentGender === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $currentGender === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Email
                                    *</label>
                                <input type="email" name="student_email" value="{{ $request->student_email }}" required
                                    style="width: 100%; padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">University</label>
                                <input type="text" name="university" value="{{ $request->university }}"
                                    style="width: 100%; padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Major</label>
                                <input type="text" name="major" value="{{ $request->major ?? ($formData['major'] ?? '') }}"
                                    style="width: 100%; padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Phone
                                    Number</label>
                                <input type="text" name="phone" value="{{ $request->phone }}"
                                    style="width: 100%; padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">ID
                                    Number</label>
                                <input type="text" name="verification_token" value="{{ $request->verification_token }}"
                                    style="width: 100%; padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Training
                                    Period</label>
                                <input type="month" name="training_period" value="{{ $request->training_period }}"
                                    style="width: 100%; padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-main);">Deadline</label>
                                <input type="date" name="deadline" class="form-input"
                                    value="{{ $request->deadline ? $request->deadline->format('Y-m-d') : '' }}"
                                    style="width: 100%; padding: 0.5rem 1rem; border-radius: 0.5rem;">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-main);">Purpose</label>
                                <select name="purpose" class="form-select"
                                    style="width: 100%; padding: 0.5rem 1rem; border-radius: 0.5rem;">
                                    <option value="">Select Purpose</option>
                                    <option value="Master's Application" {{ $request->purpose === "Master's Application" ? 'selected' : '' }}>Master's Application</option>
                                    <option value="PhD Application" {{ $request->purpose === "PhD Application" ? 'selected' : '' }}>PhD Application</option>
                                    <option value="Job Application" {{ $request->purpose === "Job Application" ? 'selected' : '' }}>Job Application</option>
                                    <option value="Internship" {{ $request->purpose === "Internship" ? 'selected' : '' }}>
                                        Internship</option>
                                    <option value="Scholarship" {{ $request->purpose === "Scholarship" ? 'selected' : '' }}>
                                        Scholarship</option>
                                    <option value="Other" {{ $request->purpose === "Other" ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-main);">Letter Source</label>
                                <select name="content_option" id="adminContentOption" class="form-select"
                                    style="width: 100%; padding: 0.5rem 1rem; border-radius: 0.5rem;"
                                    onchange="syncAdminTemplateField()">
                                    <option value="template" {{ ($request->content_option ?? 'template') !== 'custom' ? 'selected' : '' }}>Use Template</option>
                                    <option value="custom" {{ ($request->content_option ?? '') === 'custom' ? 'selected' : '' }}>Use Custom Content</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 1rem;" id="adminTemplateField">
                                <label
                                    style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-main);">Template</label>
                                <select name="template_id" id="adminTemplateId" class="form-select"
                                    style="width: 100%; padding: 0.5rem 1rem; border-radius: 0.5rem;">
                                    <option value="">Select Template</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ (string) $request->template_id === (string) $template->id ? 'selected' : '' }}>
                                            {{ $template->name }} ({{ strtoupper($template->language) }})
                                        </option>
                                    @endforeach
                                </select>
                                <small style="display: block; margin-top: 0.35rem; color: var(--text-muted);">
                                    Changing this updates the template used for preview, approval, PDF export, and student letter access.
                                </small>
                            </div>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label style="font-size: 0.875rem; font-weight: 500;">Custom Content / Notes</label>
                                <button type="button" onclick="rewriteWithAi()" class="btn btn-sm btn-secondary"
                                    style="color: #4f46e5; border-color: #4f46e5; padding: 2px 8px; font-size: 0.75rem;">
                                    <i data-feather="cpu" style="width: 12px; height: 12px; margin-right: 4px;"></i>
                                    Rewrite with AI
                                </button>
                            </div>
                            <textarea name="custom_content" id="customContentField" rows="6" class="form-textarea"
                                style="width: 100%; padding: 0.5rem 1rem; border-radius: 0.5rem;"
                                placeholder="Enter notes here or let AI generate the letter...">{{ $request->custom_content }}</textarea>
                        </div>

                        <div
                            style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                            <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('styles')
    <style>
        /* Modal Full Screen Overlay */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(17, 24, 39, 0.75);
            backdrop-filter: blur(2px);
        }

        /* Modal Content */
        .modal-content {
            position: relative;
            background: var(--card-bg);
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
            max-width: 1100px;
            /* Wide enough for A4 display */
            height: 90vh;
            /* Fixed height to allow scrolling inside */
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            z-index: 1001;
            overflow: hidden;
        }

        /* Header */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: #f9fafb;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-main);
        }

        /* Body - Grey "Desk" Background */
        .modal-body {
            flex: 1;
            overflow-y: auto;
            background: #e5e7eb;
            /* The grey background */
            padding: 2rem;
            display: flex;
            justify-content: center;
        }

        .letter-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        /* The A4 Paper */
        .letter-page {
            background: white;
            width: 210mm;
            min-height: 297mm;
            padding: 25mm;
            /* Default padding, overridden by JS */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            /* Shadow 2xl */
            margin-bottom: 2rem;
            position: relative;
            font-family: 'Times New Roman', Times, serif;
            color: black;
            box-sizing: border-box;
        }

        /* Print Overrides */
        @media print {
            body * {
                visibility: hidden;
            }

            #previewModal,
            #previewModal * {
                visibility: visible;
            }

            .modal {
                position: absolute;
                left: 0;
                top: 0;
                background: white;
                display: block;
            }

            .modal-header,
            .modal-overlay {
                display: none !important;
            }
        }

        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 400px;
            color: var(--text-muted);
            gap: 1rem;
        }
    </style>
@endsection

@section('scripts')
    <script>
        let currentLetterData = null;

        function syncStatusForm() {
            const statusSelect = document.getElementById('statusSelect');
            const adminMessageField = document.getElementById('adminMessageField');
            const adminMessageHint = document.getElementById('adminMessageHint');
            const adminMessageLabelText = document.getElementById('adminMessageLabelText');

            if (!statusSelect || !adminMessageField || !adminMessageHint || !adminMessageLabelText) return;

            const needsRevision = statusSelect.value === 'Needs Revision';
            const rejected = statusSelect.value === 'Rejected';
            adminMessageField.required = needsRevision || rejected;
            adminMessageHint.textContent = needsRevision
                ? '(Required for Needs Revision)'
                : rejected
                    ? '(Required for Rejected)'
                    : '(Optional)';
            adminMessageLabelText.textContent = rejected ? 'Rejection Reason' : 'Admin Message';
            adminMessageField.placeholder = rejected ? 'Reason for rejection...' : 'Message to student...';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const statusSelect = document.getElementById('statusSelect');
            if (statusSelect) {
                statusSelect.addEventListener('change', syncStatusForm);
                syncStatusForm();
            }
            syncAdminTemplateField();
        });

        function copyDriveLink(url) {
            if (!url) {
                return;
            }

            navigator.clipboard.writeText(url)
                .then(() => alert('Google Drive link copied.'))
                .catch(() => alert('Could not copy the Google Drive link.'));
        }

        function openPreviewModal() {
            document.getElementById('previewModal').style.display = 'flex';
            // Reset and load
            document.getElementById('letterPreviewContent').innerHTML = `
                                                                                <div class="loading-container">
                                                                                    <div style="width: 40px; height: 40px; border: 3px solid var(--border-color); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                                                                    <span>Generating Letter...</span>
                                                                                </div>
                                                                            `;
            loadPreview();
        }

        function closePreviewModal() {
            document.getElementById('previewModal').style.display = 'none';
            currentLetterData = null;
        }

        function openEditModal() {
            document.getElementById('editModal').style.display = 'flex';
            syncAdminTemplateField();
            if (typeof feather !== 'undefined') feather.replace();
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function syncAdminTemplateField() {
            const contentOption = document.getElementById('adminContentOption');
            const templateField = document.getElementById('adminTemplateField');
            const templateId = document.getElementById('adminTemplateId');
            const usingTemplate = !contentOption || contentOption.value === 'template';

            if (templateField) {
                templateField.style.display = usingTemplate ? 'block' : 'none';
            }

            if (templateId) {
                templateId.required = usingTemplate;
            }
        }

        function loadPreview() {
            fetch('{{ route("admin.requests.preview", $request->id) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentLetterData = data;
                        renderLetter(data);
                    } else {
                        showError(data.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('System Error: Could not load preview.');
                });
        }

        function showError(msg) {
            document.getElementById('letterPreviewContent').innerHTML = `
                                                                                <div class="loading-container" style="color: #dc2626;">
                                                                                    <p>Failed to generate preview.</p>
                                                                                    <p style="font-size: 0.875rem;">${msg}</p>
                                                                                </div>
                                                                            `;
        }

        function renderLetter(data) {
            const margins = data.layout?.margins || { top: 25, bottom: 25, left: 25, right: 25 };
            const fontFamily = data.layout?.fontFamily || 'Times New Roman';
            const fontSize = data.layout?.fontSize || 12;
            const direction = data.layout?.direction || 'ltr';

            // Border Logic (from Old App)
            const border = data.layout?.border || {};
            const frame = data.layout?.frame || {};
            const frameColor = /^#[0-9a-fA-F]{6}$/.test(frame.color || '') ? frame.color : '#2f8e55';
            const frameEnabled = frame.style === 'ngha_green';
            const borderStyle = frameEnabled
                ? `4px double ${frameColor}`
                : (border.enabled ? `${border.width || 1}px ${border.style || 'solid'} ${border.color || '#000000'}` : 'none');

            // Construct styles for the preview container
            const style = `
                                                                                width: 210mm;
                                                                                min-height: 297mm;
                                                                                padding-top: ${margins.top}mm;
                                                                                padding-bottom: ${margins.bottom}mm;
                                                                                padding-left: ${margins.left}mm;
                                                                                padding-right: ${margins.right}mm;
                                                                                font-family: "${fontFamily}", "Times New Roman", serif;
                                                                                font-size: ${fontSize}pt;
                                                                                direction: ${direction};
                                                                                border: ${borderStyle};
                                                                            `;

            // Footer positioning logic exact match: Math.max((margins.bottom || 25) - 10, 10)
            const footerBottom = Math.max((margins.bottom || 25) - 10, 10);

            let html = `
                                                                                <div class="letter-page" style="${style}">
                                                                                    <!-- Header -->
                                                                                    <div class="letter-header mb-8" style="margin-bottom: 2rem;">
                                                                                        ${data.header || ''}
                                                                                    </div>

                                                                                    <!-- Body -->
                                                                                    <div class="letter-body mb-0" style="margin-bottom: 0; min-height: 200px;">
                                                                                        ${data.body || ''}
                                                                                    </div>

                                                                                    <!-- Signature Section - Matching React Tailwind Classes with Inline Styles -->
                                                                                    <!-- Signature Section - Table Layout -->
                                                                                    <div class="letter-signature" style="margin-top: 3rem; page-break-inside: avoid;">
                                                                                        <table style="width: 100%; border-collapse: collapse;">
                                                                                            <tr>
                                                                                                <!-- Left Column: Signature Details & Image -->
                                                                                                <td style="vertical-align: top; width: 65%;">
                                                                                                    <div style="font-weight: 700; font-size: 1rem; line-height: 1.5rem;">${data.signature.name || ''}</div>

                                                                                                    ${data.signature.title ? `<div style="font-size: 0.875rem; color: #374151;">${data.signature.title}</div>` : ''}

                                                                                                    ${data.signature.department || data.signature.institution ? `
                                                                                                        <div style="margin-top: 5px; font-size: 0.875rem; color: #4b5563;">
                                                                                                            ${data.signature.department ? `<div>${data.signature.department}</div>` : ''}
                                                                                                            ${data.signature.institution ? `<div>${data.signature.institution}</div>` : ''}
                                                                                                        </div>
                                                                                                    ` : ''}

                                                                                                    ${data.signature.email || data.signature.phone ? `
                                                                                                        <div style="margin-top: 8px; font-size: 0.875rem; color: var(--text-muted);">
                                                                                                            ${data.signature.email ? `<div>Email: ${data.signature.email}</div>` : ''}
                                                                                                            ${data.signature.phone ? `<div>Tel: ${data.signature.phone}</div>` : ''}
                                                                                                        </div>
                                                                                                    ` : ''}

                                                                                                    <!-- Signature Image BELOW details -->
                                                                                                    ${data.signature.image ? `
                                                                                                        <div style="margin-top: 15px;">
                                                                                                            <img src="${data.signature.image}" alt="Signature" style="height: 4rem; max-width: 150px; display: block;">
                                                                                                        </div>
                                                                                                    ` : ''}
                                                                                                </td>

                                                                                                <!-- Right Column: Stamp & QR Code -->
                                                                                                <td style="vertical-align: top; text-align: right; width: 35%;">
                                                                                                    ${data.signature.stamp ? `
                                                                                                        <div style="margin-bottom: 15px;">
                                                                                                            <img src="${data.signature.stamp}" alt="Stamp" style="height: 5rem; opacity: 0.8;">
                                                                                                        </div>
                                                                                                    ` : ''}

                                                                                                    <!-- QR Code -->
                                                                                                    ${data.qrCode ? `
                                                                                                        <div style="margin-top: 10px;">
                                                                                                            ${data.qrCode}
                                                                                                        </div>
                                                                                                    ` : ''}
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </div>

                                                                                    <!-- Footer (Absolute) -->
                                                                                    ${data.footer ? `
                                                                                        <div class="letter-footer" style="position: absolute; bottom: ${footerBottom}mm; left: ${margins.left}mm; right: ${margins.right}mm; text-align: center;">
                                                                                            ${data.footer}
                                                                                        </div>
                                                                                    ` : ''}
                                                                                </div>
                                                                            `;

            document.getElementById('letterPreviewContent').innerHTML = html;
            if (typeof feather !== 'undefined') feather.replace();
        }

        function printLetter() {
            if (!currentLetterData) return;

            const data = currentLetterData;
            const margins = data.layout?.margins || { top: 25, bottom: 25, left: 25, right: 25 };
            const fontFamily = data.layout?.fontFamily || 'Times New Roman';
            const fontSize = data.layout?.fontSize || 12;
            const direction = data.layout?.direction || 'ltr';

            // Border Logic for Print
            const border = data.layout?.border || {};
            const frame = data.layout?.frame || {};
            const frameColor = /^#[0-9a-fA-F]{6}$/.test(frame.color || '') ? frame.color : '#2f8e55';
            const frameEnabled = frame.style === 'ngha_green';
            const borderStyle = frameEnabled
                ? `4px double ${frameColor}`
                : (border.enabled ? `${border.width || 1}px ${border.style || 'solid'} ${border.color || '#000000'}` : 'none');

            // Footer positioning
            const footerBottom = Math.max((margins.bottom || 25) - 10, 10);

            // Open new window for perfect printing
            const printWindow = window.open('', '_blank', 'width=900,height=1200');
            if (!printWindow) {
                alert('Please allow popups to print.');
                return;
            }

            const htmlContent = `
                                                                                <!DOCTYPE html>
                                                                                <html>
                                                                                <head>
                                                                                    <title>Recommendation Letter</title>
                                                                                    <style>
                                                                                        /* Reset */
                                                                                        * { margin: 0; padding: 0; box-sizing: border-box; }

                                                                                        /* Utility Classes Helper (Mini Tailwind) */
                                                                                        .text-center { text-align: center; }
                                                                                        .text-right { text-align: right; }
                                                                                        .text-left { text-align: left; }
                                                                                        .font-bold { font-weight: bold; }
                                                                                        .font-semibold { font-weight: 600; }
                                                                                        .italic { font-style: italic; }
                                                                                        .underline { text-decoration: underline; }
                                                                                        .mb-1 { margin-bottom: 0.25rem; }
                                                                                        .mb-2 { margin-bottom: 0.5rem; }
                                                                                        .mb-4 { margin-bottom: 1rem; }
                                                                                        .mt-1 { margin-top: 0.25rem; }
                                                                                        .mt-2 { margin-top: 0.5rem; }
                                                                                        .mt-4 { margin-top: 1rem; }
                                                                                        .w-full { width: 100%; }
                                                                                        .flex { display: flex; }
                                                                                        .justify-between { justify-content: space-between; }
                                                                                        .items-center { align-items: center; }

                                                                                        /* Page Setup */
                                                                                        @page { size: A4 portrait; margin: 0; }
                                                                                        html, body { width: 210mm; height: 297mm; }
                                                                                        body {
                                                                                            font-family: "${fontFamily}", "Times New Roman", serif;
                                                                                            font-size: ${fontSize}pt;
                                                                                            line-height: 1.35;
                                                                                            color: #000;
                                                                                            background: white;
                                                                                            -webkit-print-color-adjust: exact !important;
                                                                                            print-color-adjust: exact !important;
                                                                                            direction: ${direction};
                                                                                        }
                                                                                        .letter-page {
                                                                                            width: 210mm;
                                                                                            height: 297mm;
                                                                                            max-height: 297mm;
                                                                                            overflow: hidden;
                                                                                            padding: ${margins.top}mm ${margins.right}mm ${margins.bottom}mm ${margins.left}mm;
                                                                                            position: relative;
                                                                                            page-break-after: always;
                                                                                            border: ${borderStyle};
                                                                                        }
                                                                                        img { max-width: 100%; height: auto; }

                                                                                        /* Image Sizing Exact Match */
                                                                                        img[alt="Signature"] { height: 4rem !important; width: auto !important; margin-bottom: 0.5rem; }
                                                                                        img[alt="Stamp"] { height: 5rem !important; width: auto !important; }
                                                                                        img[alt*="Logo"] { width: 80px !important; height: auto !important; }

                                                                                        .footer-section {
                                                                                            position: absolute;
                                                                                            bottom: ${footerBottom}mm;
                                                                                            left: ${margins.left}mm;
                                                                                            right: ${margins.right}mm;
                                                                                            text-align: center;
                                                                                        }
                                                                                    </style>
                                                                                </head>
                                                                                <body>
                                                                                    <div class="letter-page">
                                                                                        <div class="letter-header" style="margin-bottom: 2rem;">
                                                                                            ${data.header || ''}
                                                                                        </div>
                                                                                        <div class="letter-body" style="margin-bottom: 0;">
                                                                                            ${data.body || ''}
                                                                                        </div>
                                                                                        <div class="letter-signature" style="margin-top: 3rem; page-break-inside: avoid;">
                                                                                            <div style="margin-bottom: 1rem;">
                                                                                                <div style="font-weight: 700; font-size: 1rem; line-height: 1.5rem;">${data.signature.name || ''}</div>
                                                                                                ${data.signature.title ? `<div style="font-size: 0.875rem; color: #374151;">${data.signature.title}</div>` : ''}
                                                                                                ${data.signature.department ? `<div style="font-size: 0.875rem; color: #4b5563;">${data.signature.department}</div>` : ''}
                                                                                                ${data.signature.institution ? `<div style="font-size: 0.875rem; color: #4b5563;">${data.signature.institution}</div>` : ''}
                                                                                                ${data.signature.email ? `<div style="font-size: 0.875rem; color: #666;">Email: ${data.signature.email}</div>` : ''}
                                                                                                ${data.signature.phone ? `<div style="font-size: 0.875rem; color: #666;">Tel: ${data.signature.phone}</div>` : ''}
                                                                                            </div>
                                                                                            ${data.signature.image ? `<img src="${data.signature.image}" alt="Signature">` : ''}
                                                                                            ${data.signature.stamp ? `<div style="margin-top: 0.5rem;"><img src="${data.signature.stamp}" alt="Stamp"></div>` : ''}
                                                                                        </div>
                                                                                        ${data.qrCode ? `<div style="text-align: right; margin-top: 10px;">${data.qrCode}</div>` : ''}
                                                                                        ${data.footer ? `<div class="footer-section">${data.footer}</div>` : ''}
                                                                                    </div>
                                                                                    <script>
                                                                                        window.onload = function() {
                                                                                            setTimeout(function() { window.print(); }, 500);
                                                                                        }
                                                                                    <\/script>
                                                                                </body>
                                                                                </html>
                                                                            `;

            printWindow.document.write(htmlContent);
            printWindow.document.close();
        }

        function rewriteWithAi() {
            const btn = document.querySelector('button[onclick="rewriteWithAi()"]');
            const textarea = document.getElementById('customContentField');
            const originalText = btn.innerHTML;

            // Set loading state
            btn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite">↻</span> Generatng...';
            btn.disabled = true;

            fetch('{{ route("admin.requests.rewrite-ai", $request->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    notes: textarea.value
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        textarea.value = data.content;
                    } else {
                        alert('AI Error: ' + (data.message || 'Check your API Key in Settings'));
                    }
                })
                .catch(err => {
                    alert('Connection Error');
                    console.error(err);
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    if (typeof feather !== 'undefined') feather.replace();
                });
        }
    </script>
@endsection
