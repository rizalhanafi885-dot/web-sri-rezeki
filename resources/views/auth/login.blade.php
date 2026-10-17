<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sumber Rezeki - Login</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('sumber-rezeki-mark.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #109b67;
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            border-radius: 18px;
            padding: 40px 36px 32px 36px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18), 0 4px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            position: relative;
        }

        .brand-logo-wrapper {
            margin-bottom: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo-icon {
            width: 64px;
            height: 64px;
            background: #e8f5e9;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
        }

        .brand-logo-icon img {
            display: block;
            width: 54px;
            height: 54px;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .brand-subtitle {
            font-size: 13.5px;
            color: #6b7280;
            margin-bottom: 28px;
            font-weight: 500;
        }

        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: #4b5563;
            font-size: 15px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 42px 0 42px;
            font-size: 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background-color: #ffffff;
            color: #1f2937;
            outline: none;
            transition: all 0.25s ease;
        }

        .form-control::placeholder {
            color: #9ca3af;
            font-size: 13.5px;
        }

        .form-control:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3.5px rgba(22, 163, 74, 0.18);
        }

        .form-control:focus + .input-icon-left,
        .input-group:focus-within .input-icon-left {
            color: #16a34a;
        }

        .toggle-password-btn {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-password-btn:hover {
            color: #16a34a;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            margin-top: 10px;
            background: #1e7e4e;
            background: linear-gradient(180deg, #1b8751 0%, #156d40 100%);
            border: none;
            border-radius: 10px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(21, 128, 61, 0.25);
            transition: all 0.25s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(180deg, #157947 0%, #125e36 100%);
            box-shadow: 0 6px 16px rgba(21, 128, 61, 0.35);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Default Akun info box */
        .default-accounts-box {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px dashed #e5e7eb;
            font-size: 12.5px;
            color: #4b5563;
        }

        .account-badge-row {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 8px;
        }

        .badge-pill {
            background: #f3f4f6;
            padding: 6px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .badge-pill:hover {
            background: #e8f5e9;
            border-color: #86efac;
        }

        .badge-tag {
            font-weight: 700;
            color: #15803d;
            font-family: 'Plus Jakarta Sans', sans-serif;
            text-transform: uppercase;
            font-size: 10.5px;
        }

        /* Alert notifications */
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .alert-info {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Brand Logo Icon -->
        <div class="brand-logo-wrapper">
            <div class="brand-logo-icon">
                <img src="{{ asset('sumber-rezeki-mark.svg') }}" alt="Logo Sumber Rezeki">
            </div>
        </div>

        <h1 class="brand-title">Sumber Rezeki</h1>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" autocomplete="off">
            @csrf

            <!-- Username -->
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <i class="fa-regular fa-user input-icon-left"></i>
                    <input 
                        type="text" 
                        name="username" 
                        id="username" 
                        class="form-control" 
                        placeholder="Masukkan username" 
                        value="{{ old('username') }}"
                        required 
                        autofocus
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <i class="fa-solid fa-lock input-icon-left"></i>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="form-control" 
                        placeholder="Masukkan password" 
                        required
                    >
                    <button type="button" class="toggle-password-btn" id="togglePasswordBtn" title="Lihat password">
                        <i class="fa-regular fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit">
                Masuk
            </button>
        </form>

        <!-- Default Akun Info (Persis seperti pada tangkapan layar) -->
        <div class="default-accounts-box">
            <div><strong>Default Akun:</strong></div>
            <div class="account-badge-row">
                <div class="badge-pill" onclick="fillAccount('admin', 'Admin@2026')" title="Klik untuk isi otomatis form">
                    <span><span class="badge-tag">ADMIN</span> admin</span>
                    <span>Admin@2026</span>
                </div>
                <div class="badge-pill" onclick="fillAccount('owner', 'Owner@2026')" title="Klik untuk isi otomatis form">
                    <span><span class="badge-tag">OWNER</span> owner</span>
                    <span>Owner@2026</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle lihat / sembunyikan password
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePasswordBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        // Fitur klik untuk mengisi akun otomatis
        function fillAccount(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
