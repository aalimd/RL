<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .icon {
            width: 3rem;
            height: 3rem;
            background: #ecfdf5;
            color: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        h2 {
            margin: 0 0 0.5rem;
            color: #111827;
        }

        p {
            margin: 0 0 1.5rem;
            color: #6b7280;
            font-size: 0.875rem;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            text-align: center;
            letter-spacing: 0.5rem;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #1d4ed8;
        }

        .resend {
            display: block;
            margin-top: 1rem;
            color: #4b5563;
            text-decoration: underline;
            font-size: 0.875rem;
            background: none;
            border: none;
            width: auto;
            margin-left: auto;
            margin-right: auto;
            cursor: pointer;
            padding: 0;
        }

        .alert {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
        }

        .alert-success {
            background: #ecfdf5;
            color: #047857;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <h2>Two-Factor Verification</h2>
        <p>
            @if($method === 'app')
                Please enter the code from your authenticator app.
            @else
                Please enter the verification code sent to your email.
            @endif
        </p>

        @if(session('error') || $errors->any())
            <div class="alert alert-error">
                {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.2fa.verify.post') }}" method="POST">
            @csrf
            <input type="text" name="code" placeholder="123456" required autofocus maxlength="6" pattern="[0-9]*"
                inputmode="numeric">
            <button type="submit">Verify</button>
        </form>

        @if($method === 'email')
            <form action="{{ route('admin.2fa.resend') }}" method="POST">
                @csrf
                <button type="submit" class="resend">Resend Code</button>
            </form>
        @endif

        <form action="{{ route('logout') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <button type="submit"
                style="background: none; color: #9ca3af; padding: 0; font-weight: normal; font-size: 0.875rem;">Cancel &
                Logout</button>
        </form>
    </div>
</body>

</html>