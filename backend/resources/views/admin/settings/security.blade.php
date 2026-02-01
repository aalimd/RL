@extends('layouts.admin')

@section('page-title', 'Security Settings')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Two-Factor Authentication (2FA)</h3>
            <p class="text-sm text-gray-500">Secure your account with an extra layer of protection.</p>
        </div>
        <div class="card-body">
            @if($enabled)
                <div class="alert alert-success"
                    style="background: #ecfdf5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <strong><i data-feather="check-circle" style="width:16px; display:inline;"></i> 2FA is Enabled</strong>
                    <p>You are currently using <strong>{{ ucfirst($method) }}</strong> authentication.</p>
                </div>

                <form action="{{ route('admin.settings.security.disable') }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to disable 2FA? This will lower your account security.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                        style="background: #ef4444; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; border:none; cursor:pointer;">
                        Disable 2FA
                    </button>
                </form>
            @else
                <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <!-- App Method -->
                    <div class="method-card" style="border: 1px solid #e5e7eb; padding: 1.5rem; border-radius: 0.5rem;">
                        <div style="margin-bottom: 1rem;">
                            <span
                                style="background: #eff6ff; color: #1d4ed8; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">Recommended</span>
                        </div>
                        <h4 style="margin: 0 0 0.5rem 0;">Authenticator App</h4>
                        <p class="text-gray-500 text-sm" style="margin-bottom: 1.5rem;">Use Google Authenticator or Authy to
                            generate one-time codes.</p>
                        <button onclick="startSetup('app')" class="btn"
                            style="width: 100%; background: #3b82f6; color: white; padding: 0.5rem; border-radius: 0.5rem; border: none; cursor: pointer;">
                            Enable via App
                        </button>
                    </div>

                    <!-- Email Method -->
                    <div class="method-card" style="border: 1px solid #e5e7eb; padding: 1.5rem; border-radius: 0.5rem;">
                        <h4 style="margin: 0 0 0.5rem 0;">Email OTP</h4>
                        <p class="text-gray-500 text-sm" style="margin-bottom: 1.5rem;">Receive a verification code via email
                            every time you login.</p>
                        <button onclick="startSetup('email')" class="btn"
                            style="width: 100%; background: white; border: 1px solid #d1d5db; color: #374151; padding: 0.5rem; border-radius: 0.5rem; cursor: pointer;">
                            Enable via Email
                        </button>
                    </div>
                </div>

                <!-- Setup Modal -->
                <div id="setupArea" style="display: none; margin-top: 2rem; border-top: 1px solid #e5e7eb; padding-top: 2rem;">
                    <h4 id="setupTitle" style="margin-bottom: 1rem;">Setup 2FA</h4>

                    <div id="qrArea" style="display: none; margin-bottom: 1.5rem; text-align: center;">
                        <p class="text-sm text-gray-500" style="margin-bottom: 1rem;">Scan this QR code with your authenticator
                            app:</p>
                        <div id="qrImage"
                            style="background: white; padding: 1rem; display: inline-block; border: 1px solid #e5e7eb; border-radius: 0.5rem;">
                        </div>
                        <p class="text-xs text-gray-400" style="margin-top: 0.5rem;">Secret: <span id="manualSecret"
                                style="font-family: monospace;"></span></p>
                    </div>

                    <div id="emailMsg" style="display: none; margin-bottom: 1.5rem;" class="alert alert-info">
                        A verification code has been sent to <strong>{{ auth()->user()->email }}</strong>.
                    </div>

                    <form id="confirmForm" action="{{ route('admin.settings.security.confirm') }}" method="POST">
                        @csrf
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Enter
                            Verification Code</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" name="code" required placeholder="123456"
                                style="flex: 1; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                            <button type="submit" class="btn"
                                style="background: #10b981; color: white; padding: 0.5rem 1.5rem; border: none; border-radius: 0.5rem; cursor: pointer;">
                                Verify & Enable
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function startSetup(method) {
            document.getElementById('setupArea').style.display = 'block';
            document.getElementById('qrArea').style.display = 'none';
            document.getElementById('emailMsg').style.display = 'none';
            document.getElementById('setupTitle').innerText = method === 'app' ? 'Setup Authenticator App' : 'Setup Email Verification';

            // Call API
            fetch("{{ route('admin.settings.security.enable') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ method: method })
            })
                .then(response => response.json())
                .then(data => {
                    if (method === 'app') {
                        document.getElementById('qrArea').style.display = 'block';
                        document.getElementById('manualSecret').innerText = data.secret;

                        // Render QR
                        document.getElementById('qrImage').innerHTML = "";
                        new QRCode(document.getElementById("qrImage"), {
                            text: data.qr_code_url,
                            width: 256,
                            height: 256
                        });
                    } else {
                        document.getElementById('emailMsg').style.display = 'block';
                    }
                })
                .catch(err => alert('Error starting setup'));
        }

        // Handle Confirmation
        document.getElementById('confirmForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const form = this;
            const btn = form.querySelector('button');
            const originalText = btn.innerText;

            btn.disabled = true;
            btn.innerText = 'Verifying...';

            fetch(form.action, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    code: form.querySelector('input[name="code"]').value
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Invalid code');
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                })
                .catch(err => {
                    alert('Verification failed. Use the code sent to you.');
                    btn.disabled = false;
                    btn.innerText = originalText;
                });
        });
    </script>
@endsection