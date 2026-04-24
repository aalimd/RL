@extends('layouts.admin')

@section('page-title', 'General Settings')

@section('content')
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

    @if($errors->any())
        <div
            style="background: var(--error-bg); color: var(--error-text); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid var(--error-border);">
            <strong>Could not save settings.</strong>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- General Settings -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3>General Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="siteName" class="form-input" value="{{ $settings['siteName'] ?? '' }}"
                            placeholder="My App">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Welcome Title</label>
                        <input type="text" name="welcomeTitle" class="form-input"
                            value="{{ $settings['welcomeTitle'] ?? '' }}" placeholder="Welcome to...">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Welcome Text</label>
                        <textarea name="welcomeText" class="form-textarea" rows="4"
                            placeholder="Enter your welcome message...">{{ $settings['welcomeText'] ?? '' }}</textarea>
                    </div>

                    <!-- Hybrid Logo Upload -->
                    <div class="form-group">
                        <label class="form-label">Logo</label>
                        <div class="image-upload-wrapper" id="logoUpload">
                            <div class="upload-tabs">
                                <div class="upload-tab active" data-target="file">Upload File</div>
                                <div class="upload-tab" data-target="url">Image URL</div>
                            </div>

                            <div class="upload-content" data-type="file">
                                <div class="file-upload-container">
                                    <input type="file" name="logoUrl_file" id="logoUrl_file" class="file-upload-input"
                                        accept="image/*"
                                        onchange="previewImage(this, 'logoPreview'); document.getElementById('logoFileName').textContent = this.files[0]?.name || 'No file chosen'">
                                    <label for="logoUrl_file" class="file-upload-btn">
                                        <i data-feather="upload" style="width: 16px; height: 16px;"></i>
                                        Choose File
                                    </label>
                                    <span class="file-upload-name" id="logoFileName">No file chosen</span>
                                </div>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Recommended
                                    size:
                                    200x50px. Max: 2MB.</p>
                            </div>

                            <div class="upload-content" data-type="url" style="display: none;">
                                <input type="text" name="logoUrl" class="form-input"
                                    value="{{ $settings['logoUrl'] ?? '' }}" placeholder="https://example.com/logo.png"
                                    oninput="updatePreview(this.value, 'logoPreview')">
                            </div>

                            <div class="upload-preview {{ !empty($settings['logoUrl']) ? 'has-image' : '' }}"
                                id="logoPreviewBox">
                                <img id="logoPreview" src="{{ $settings['logoUrl'] ?? '' }}"
                                    style="{{ empty($settings['logoUrl']) ? 'display: none;' : '' }}"
                                    onerror="this.style.display='none'; this.parentElement.classList.remove('has-image');">
                                <span class="placeholder-text"
                                    style="{{ !empty($settings['logoUrl']) ? 'display: none;' : 'color: var(--text-muted);' }}">
                                    No image selected
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <label class="form-label" style="margin-bottom: 0;">Maintenance Mode</label>
                                <p style="font-size: 0.8rem; color: var(--text-muted);">Prevent users from accessing the
                                    site</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="maintenanceMode" id="maintenanceMode" {{ ($settings['maintenanceMode'] ?? 'false') === 'true' ? 'checked' : '' }}
                                    onchange="toggleMaintenanceMessage(this.checked)">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="maintenanceMessageGroup"
                        style="{{ ($settings['maintenanceMode'] ?? 'false') === 'true' ? '' : 'display: none;' }}">
                        <label class="form-label">Maintenance Message</label>
                        <textarea name="maintenanceMessage" class="form-textarea" rows="3"
                            placeholder="We are currently upgrading our system...">{{ $settings['maintenanceMessage'] ?? '' }}</textarea>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">This message will be
                            displayed to
                            visitors when maintenance mode is active.</p>
                    </div>

                    <!-- AI Settings -->
                    <div style="margin: 2rem 0; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                        <h4
                            style="font-size: 1rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-feather="cpu" style="width: 18px; height: 18px; color: var(--primary);"></i>
                            AI Writing Assistant
                        </h4>
                        <div class="form-group">
                            <label class="form-label">Google Gemini API Key</label>
                            <div style="position: relative;">
                                <input type="password" name="geminiApiKey" class="form-input" value=""
                                    placeholder="••••••••{{ !empty($settings['geminiApiKey']) ? ' (configured)' : '' }}"
                                    style="padding-right: 80px;">
                                <a href="https://aistudio.google.com/app/apikey" target="_blank"
                                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: var(--primary); text-decoration: none; font-weight: 500;">
                                    Get Key &rarr;
                                </a>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                                Uses Google's free tier. Get your key for free from Google AI Studio.
                            </p>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary">Save General Settings</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- Telegram Integration -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header" style="background: linear-gradient(135deg, #24A1DE 0%, #2086BF 100%); color: white;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-feather="send" style="width: 20px; height: 20px;"></i>
                    <h3 style="color: white; margin: 0;">Telegram Integration</h3>
                </div>
                <span style="font-size: 0.875rem; color: rgba(255,255,255,0.9);">Receive instant notifications and manage
                    requests</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Bot Token</label>
                            <input type="password" name="telegram_bot_token" class="form-input" value=""
                                placeholder="••••••••{{ !empty($settings['telegram_bot_token']) ? ' (configured)' : '' }}">
                            <small style="color: var(--text-muted); font-size: 0.75rem;">Get this from @BotFather</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Your Chat ID</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" name="telegram_chat_id" class="form-input"
                                    value="{{ $settings['telegram_chat_id'] ?? '' }}" placeholder="123456789">
                            </div>
                            <small style="color: var(--text-muted); font-size: 0.75rem;">Your personal numeric ID</small>
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary">Save Telegram Settings</button>

                            <button type="button" class="btn btn-secondary" onclick="setupWebhook()">
                                <i data-feather="link" style="width: 16px; height: 16px;"></i> Connect Webhook
                            </button>

                            <button type="button" class="btn btn-secondary" onclick="testTelegram()">
                                <i data-feather="bell" style="width: 16px; height: 16px;"></i> Test Notification
                            </button>
                        </div>

                        <div id="telegramResult"
                            style="display: none; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem;"></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>Email Settings (SMTP)</h3>
                <span style="font-size: 0.875rem; color: var(--text-muted);">Configure email delivery for
                    notifications</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" name="smtpHost" class="form-input"
                                    value="{{ $settings['smtpHost'] ?? '' }}" placeholder="smtp.example.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" name="smtpPort" class="form-input"
                                    value="{{ $settings['smtpPort'] ?? '587' }}" placeholder="587">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" name="smtpUsername" class="form-input"
                                    value="{{ $settings['smtpUsername'] ?? '' }}" placeholder="email@domain.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <input type="password" name="smtpPassword" class="form-input" value=""
                                    placeholder="••••••••{{ !empty($settings['smtpPassword']) ? ' (configured)' : '' }}">
                                <small style="color: var(--text-muted); font-size: 0.75rem;">Leave blank to keep existing
                                    password</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Encryption</label>
                            <select name="smtpEncryption" class="form-select">
                                <option value="tls" {{ ($settings['smtpEncryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>
                                    STARTTLS (Port 587)
                                </option>
                                <option value="ssl" {{ ($settings['smtpEncryption'] ?? '') === 'ssl' ? 'selected' : '' }}>
                                    SSL/TLS (Port 465)
                                </option>
                                <option value="" {{ ($settings['smtpEncryption'] ?? '') === '' ? 'selected' : '' }}>
                                    None
                                </option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">From Email</label>
                                <input type="email" name="mailFromAddress" class="form-input"
                                    value="{{ $settings['mailFromAddress'] ?? '' }}" placeholder="noreply@domain.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">From Name</label>
                                <input type="text" name="mailFromName" class="form-input"
                                    value="{{ $settings['mailFromName'] ?? '' }}"
                                    placeholder="{{ $settings['siteName'] ?? config('app.name') }}">
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary">Save Email Settings</button>
                            <button type="button" class="btn btn-secondary" onclick="testEmail()">
                                <i data-feather="send" style="width: 16px; height: 16px;"></i>
                                Test Email
                            </button>
                        </div>

                        <div id="testEmailResult" style="display: none; padding: 1rem; border-radius: 0.5rem;"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem; border-color: rgba(34, 197, 94, 0.2);">
        <div class="card-header" style="background: linear-gradient(135deg, #166534 0%, #15803d 100%); color: white;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-feather="hard-drive" style="width: 20px; height: 20px;"></i>
                <h3 style="color: white; margin: 0;">Google Drive Letter Backup</h3>
            </div>
            <span style="font-size: 0.875rem; color: rgba(255,255,255,0.9);">Keep approved letters backed up outside the app</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="settingsGroup" value="google_drive">

                <div class="form-grid">
                    <div class="form-group">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                            <div>
                                <label class="form-label" style="margin-bottom: 0;">Enable Google Drive backup</label>
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0.25rem 0 0;">
                                    Keep approved letters in a Drive folder so admins can recover them during outages.
                                </p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="googleDriveEnabled" {{ ($settings['googleDriveEnabled'] ?? 'false') === 'true' ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Service Account JSON</label>
                        <textarea name="googleDriveServiceAccountJson" class="form-textarea" rows="7"
                            placeholder="{{ !empty($settings['googleDriveServiceAccountJson']) ? 'Configured. Paste a new JSON key only if you want to replace it.' : 'Paste the full Google service account JSON key here...' }}"></textarea>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                            Leave this blank to keep the current saved key.
                            @if(!empty($googleDriveSummary['service_account_email']))
                                Current service account:
                                <strong>{{ $googleDriveSummary['service_account_email'] }}</strong>.
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Drive Folder ID or URL</label>
                        <input type="text" name="googleDriveFolderId" class="form-input"
                            value="{{ $settings['googleDriveFolderId'] ?? '' }}"
                            placeholder="Paste the folder ID or the full Google Drive folder URL">
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                            Create a folder in your Google Drive, share that folder with the service account email above, then paste the folder URL or ID here.
                        </p>
                    </div>

                    <div
                        style="padding: 1rem 1.125rem; border-radius: 0.85rem; border: 1px solid var(--border-color); background: rgba(255,255,255,0.03);">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                            <div>
                                <div style="font-weight: 600; color: var(--text-main);">Current backup target</div>
                                <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.25rem;">
                                    @if($googleDriveSummary['configured'])
                                        Ready to sync approved letters into the configured folder.
                                    @else
                                        Not fully configured yet.
                                    @endif
                                </div>
                            </div>
                            @if(!empty($googleDriveSummary['folder_url']))
                                <a href="{{ $googleDriveSummary['folder_url'] }}" target="_blank" rel="noopener" class="btn btn-secondary">
                                    <i data-feather="external-link" style="width: 16px; height: 16px;"></i>
                                    Open Drive Folder
                                </a>
                            @endif
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; margin-top: 1rem;">
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">Status</div>
                                <div style="font-weight: 600; color: var(--text-main); margin-top: 0.2rem;">
                                    {{ $googleDriveSummary['configured'] ? 'Configured' : 'Needs setup' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">Service account</div>
                                <div style="font-weight: 600; color: var(--text-main); margin-top: 0.2rem;">
                                    {{ $googleDriveSummary['service_account_email'] ?? 'Not saved yet' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">Folder</div>
                                <div style="font-weight: 600; color: var(--text-main); margin-top: 0.2rem;">
                                    {{ $googleDriveSummary['folder_id'] ?? 'Not saved yet' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-top: 0.5rem;">
                        <button type="submit" class="btn btn-primary">Save Drive Settings</button>
                        <button type="button" class="btn btn-secondary" onclick="testGoogleDrive()">
                            <i data-feather="shield" style="width: 16px; height: 16px;"></i>
                            Test Drive Connection
                        </button>
                    </div>

                    <div id="googleDriveResult"
                        style="display: none; padding: 1rem; border-radius: 0.75rem; margin-top: 1rem;"></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem; border-color: rgba(59, 130, 246, 0.2);">
        <div class="card-header" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); color: white;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-feather="printer" style="width: 20px; height: 20px;"></i>
                <h3 style="color: white; margin: 0;">PDF Export Renderer</h3>
            </div>
            <span style="font-size: 0.875rem; color: rgba(255,255,255,0.9);">Use Browserless on shared hosting instead of local Chrome</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="settingsGroup" value="pdf_export">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">PDF Export Driver</label>
                        <select name="pdfExportDriver" class="form-select">
                            <option value="local_browser" {{ ($pdfRendererSummary['driver'] ?? 'local_browser') === 'local_browser' ? 'selected' : '' }}>
                                Local Chrome / Chromium
                            </option>
                            <option value="browserless" {{ ($pdfRendererSummary['driver'] ?? 'local_browser') === 'browserless' ? 'selected' : '' }}>
                                Browserless (recommended for Hostinger shared hosting)
                            </option>
                        </select>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                            On shared hosting, choose <strong>Browserless</strong>. Local Chrome export needs a real browser binary on the server. Production `.env` values can also force Browserless if the admin panel is not reachable yet.
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Browserless Base URL</label>
                        <input type="text" name="browserlessBaseUrl" class="form-input"
                            value="{{ $settings['browserlessBaseUrl'] ?? ($pdfRendererSummary['browserless_base_url'] ?? 'https://production-sfo.browserless.io') }}"
                            placeholder="https://production-sfo.browserless.io">
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                            Use the Browserless region URL from your account. The default production endpoint is shown above.
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Browserless Token</label>
                        <input type="password" name="browserlessToken" class="form-input" value=""
                            placeholder="••••••••{{ !empty($settings['browserlessToken']) ? ' (configured)' : '' }}">
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                            Leave this blank to keep the current token.
                        </p>
                    </div>

                    <div
                        style="padding: 1rem 1.125rem; border-radius: 0.85rem; border: 1px solid var(--border-color); background: rgba(255,255,255,0.03);">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                            <div>
                                <div style="font-weight: 600; color: var(--text-main);">Current renderer status</div>
                                <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.25rem;">
                                    {{ $pdfRendererSummary['status_message'] ?? 'Renderer status is not available yet.' }}
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; margin-top: 1rem;">
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">Driver</div>
                                <div style="font-weight: 600; color: var(--text-main); margin-top: 0.2rem;">
                                    {{ ($pdfRendererSummary['driver'] ?? 'local_browser') === 'browserless' ? 'Browserless' : 'Local browser' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">Local Chrome</div>
                                <div style="font-weight: 600; color: var(--text-main); margin-top: 0.2rem;">
                                    {{ !empty($pdfRendererSummary['local_browser_available']) ? 'Available' : 'Not available' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">Browserless URL</div>
                                <div style="font-weight: 600; color: var(--text-main); margin-top: 0.2rem; word-break: break-word;">
                                    {{ $pdfRendererSummary['browserless_base_url'] ?? 'Not configured' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted);">Token</div>
                                <div style="font-weight: 600; color: var(--text-main); margin-top: 0.2rem;">
                                    {{ !empty($pdfRendererSummary['browserless_token_configured']) ? 'Configured' : 'Not configured' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-top: 0.5rem;">
                        <button type="submit" class="btn btn-primary">Save PDF Export Settings</button>
                        <button type="button" class="btn btn-secondary" onclick="testBrowserless()">
                            <i data-feather="shield" style="width: 16px; height: 16px;"></i>
                            Test Browserless
                        </button>
                    </div>

                    <div id="browserlessResult"
                        style="display: none; padding: 1rem; border-radius: 0.75rem; margin-top: 1rem;"></div>
                </div>
            </form>
        </div>
    </div>

    <!-- System Maintenance & Backup -->
    <div class="card" style="border-color: var(--primary);">
        <div class="card-header" style="background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%); color: white;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-feather="database" style="width: 20px; height: 20px;"></i>
                <h3 style="color: white; margin: 0;">System Security & Backup</h3>
            </div>
            <span style="font-size: 0.875rem; color: rgba(255,255,255,0.9);">Protect your data</span>
        </div>
        <div class="card-body">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 0.25rem;">Full Database Backup</h4>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0;">
                        Download a complete SQL dump of the database. <br>
                        <span style="color: var(--error-text); font-size: 0.75rem;">Requires password
                            re-confirmation.</span>
                    </p>
                </div>
                <button type="button" class="btn" style="background: var(--bg-color); color: var(--text-main);"
                    onclick="openBackupModal()">
                    <i data-feather="download-cloud" style="width: 16px; height: 16px;"></i>
                    Download .SQL
                </button>
            </div>
        </div>
    </div>
@endsection

<!-- Password Confirmation Modal -->
<div id="backupModal" class="modal-backdrop"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
    <div class="modal-content" style="padding: 2rem; width: 100%; max-width: 400px;">
        <h3
            style="margin-top: 0; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
            <i data-feather="lock" style="color: #6366F1;"></i> Security Check
        </h3>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
            Please enter your password to confirm your identity and start the download.
        </p>

        <form action="{{ route('admin.settings.backup') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" style="color: var(--text-main);">Current Password</label>
                <input type="password" name="password" class="form-input" required autofocus placeholder="••••••••">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-ghost" onclick="closeBackupModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" onclick="closeBackupModal()">Confirm & Download</button>
            </div>
        </form>
    </div>
</div>

@section('styles')
    {{-- Styles are now in layouts.admin --}}
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tab switching for uploads
            const uploadWrappers = document.querySelectorAll('.image-upload-wrapper');
            uploadWrappers.forEach(wrapper => {
                const tabs = wrapper.querySelectorAll('.upload-tab');
                const contents = wrapper.querySelectorAll('.upload-content');

                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => t.classList.remove('active'));
                        tab.classList.add('active');

                        const target = tab.dataset.target;
                        contents.forEach(c => {
                            if (c.dataset.type === target) c.style.display = 'block';
                            else c.style.display = 'none';
                        });
                    });
                });
            });
        });

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const wrapper = preview.parentElement;
            const placeholder = wrapper.querySelector('.placeholder-text');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    wrapper.classList.add('has-image');
                    if (placeholder) placeholder.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function updatePreview(url, previewId) {
            const preview = document.getElementById(previewId);
            const wrapper = preview.parentElement;
            const placeholder = wrapper.querySelector('.placeholder-text');

            if (url) {
                preview.src = url;
                preview.style.display = 'block';
                wrapper.classList.add('has-image');
                if (placeholder) placeholder.style.display = 'none';
            } else {
                preview.style.display = 'none';
                wrapper.classList.remove('has-image');
                if (placeholder) placeholder.style.display = 'block';
            }
        }

        function testEmail() {
            const email = prompt("Enter email address to receive test email:", "{{ auth()->user()->email }}");
            if (!email) return;

            const result = document.getElementById('testEmailResult');
            result.style.display = 'block';
            result.style.background = 'var(--info-bg, #dbeafe)';
            result.style.color = 'var(--info-text, #1e40af)';
            result.innerHTML = '<i data-feather="loader" style="width: 16px; height: 16px; animation: spin 1s linear infinite;"></i> Sending test email...';
            feather.replace();

            fetch('{{ route("admin.settings.test-email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        result.style.background = 'var(--success-bg)';
                        result.style.color = 'var(--success-text)';
                        result.innerHTML = '✓ ' + data.message;
                    } else {
                        result.style.background = 'var(--error-bg)';
                        result.style.color = 'var(--error-text)';
                        result.innerHTML = '✗ Failed: ' + (data.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    result.style.background = 'var(--error-bg)';
                    result.style.color = 'var(--error-text)';
                    result.innerHTML = '✗ Error: ' + error.message;
                });
        }

        function testGoogleDrive() {
            const result = document.getElementById('googleDriveResult');
            result.style.display = 'block';
            result.style.background = 'var(--info-bg, #dbeafe)';
            result.style.color = 'var(--info-text, #1e40af)';
            result.innerHTML = '<i data-feather="loader" style="width: 16px; height: 16px; animation: spin 1s linear infinite;"></i> Checking Google Drive access...';
            feather.replace();

            fetch('{{ route("admin.settings.test-google-drive") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        result.style.background = 'var(--success-bg)';
                        result.style.color = 'var(--success-text)';
                        const folderLink = data.folder_url
                            ? ` <a href="${data.folder_url}" target="_blank" rel="noopener" style="color: inherit; text-decoration: underline;">Open folder</a>`
                            : '';
                        result.innerHTML = `✓ ${data.message}${data.folder_name ? ` Folder: <strong>${data.folder_name}</strong>.` : ''}${folderLink}`;
                    } else {
                        result.style.background = 'var(--error-bg)';
                        result.style.color = 'var(--error-text)';
                        result.innerHTML = '✗ ' + (data.message || 'Could not connect to Google Drive.');
                    }
                })
                .catch(error => {
                    result.style.background = 'var(--error-bg)';
                    result.style.color = 'var(--error-text)';
                    result.innerHTML = '✗ Error: ' + error.message;
                });
        }

        function testBrowserless() {
            const result = document.getElementById('browserlessResult');
            result.style.display = 'block';
            result.style.background = 'var(--info-bg, #dbeafe)';
            result.style.color = 'var(--info-text, #1e40af)';
            result.innerHTML = '<i data-feather="loader" style="width: 16px; height: 16px; animation: spin 1s linear infinite;"></i> Checking Browserless PDF export...';
            feather.replace();

            fetch('{{ route("admin.settings.test-browserless") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        result.style.background = 'var(--success-bg)';
                        result.style.color = 'var(--success-text)';
                        result.innerHTML = `✓ ${data.message}${data.response_bytes ? ` Returned <strong>${data.response_bytes}</strong> bytes.` : ''}`;
                    } else {
                        result.style.background = 'var(--error-bg)';
                        result.style.color = 'var(--error-text)';
                        result.innerHTML = '✗ ' + (data.message || 'Could not connect to Browserless.');
                    }
                })
                .catch(error => {
                    result.style.background = 'var(--error-bg)';
                    result.style.color = 'var(--error-text)';
                    result.innerHTML = '✗ Error: ' + error.message;
                });
        }

        function toggleMaintenanceMessage(isChecked) {
            const group = document.getElementById('maintenanceMessageGroup');
            if (isChecked) {
                group.style.display = 'flex';
            } else {
                group.style.display = 'none';
            }
        }

        function testTelegram() {
            const result = document.getElementById('telegramResult');
            result.style.display = 'block';
            result.style.background = 'var(--info-bg, #dbeafe)';
            result.style.color = 'var(--info-text, #1e40af)';
            result.innerHTML = '<i data-feather="loader" style="width: 16px; height: 16px; animation: spin 1s linear infinite;"></i> Sending test notification...';
            feather.replace();

            fetch('{{ url("/admin/settings/test-telegram") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        result.style.background = 'var(--success-bg)';
                        result.style.color = 'var(--success-text)';
                        result.innerHTML = '✓ ' + (data.response.result ? 'Message sent successfully!' : 'Sent, but check your Telegram.');
                    } else {
                        result.style.background = 'var(--error-bg)';
                        result.style.color = 'var(--error-text)';
                        result.innerHTML = '✗ Failed: ' + (data.message || 'Check logs');
                    }
                })
                .catch(err => {
                    result.style.background = 'var(--error-bg)';
                    result.style.color = 'var(--error-text)';
                    result.innerHTML = '✗ Error: ' + err.message;
                });
        }

        function setupWebhook() {
            const result = document.getElementById('telegramResult');
            result.style.display = 'block';
            result.innerHTML = 'Setting up webhook...';

            fetch('{{ url("/admin/settings/setup-webhook") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        result.style.background = 'var(--success-bg)';
                        result.innerHTML = '✓ Webhook connected successfully!';
                    } else {
                        result.style.background = 'var(--error-bg)';
                        result.innerHTML = '✗ connection failed: ' + data.description;
                    }
                });
        }

        // Backup Modal Logic
        function openBackupModal() {
            const modal = document.getElementById('backupModal');
            modal.style.display = 'flex';
            // Focus password field
            setTimeout(() => modal.querySelector('input[name="password"]').focus(), 100);
        }

        function closeBackupModal() {
            document.getElementById('backupModal').style.display = 'none';
        }

        // Close on click outside
        window.onclick = function (event) {
            const modal = document.getElementById('backupModal');
            if (event.target == modal) {
                closeBackupModal();
            }
        }
    </script>
    <style>
        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection
