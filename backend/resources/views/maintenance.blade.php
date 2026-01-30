<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Maintenance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f2937;
        }

        .container {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .icon {
            width: 64px;
            height: 64px;
            background: #eff6ff;
            color: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .icon svg {
            width: 32px;
            height: 32px;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #111827;
        }

        p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
        </div>
        <h1>Under Maintenance</h1>
        <p>{{ $message }}</p>
    </div>
</body>

</html>