<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin | NeonVox</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Outfit:wght@300;400;600&family=Space+Mono:wght@400;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --background: #030305;
            --foreground: #FFFFFF;
            --card: #0A0A0F;
            --primary: #FF0099;
            --primary-foreground: #FFFFFF;
            --secondary: #00F0FF;
            --secondary-foreground: #000000;
            --muted: #1A1A24;
            --muted-foreground: #A1A1AA;
            --border: #27272A;
            --input: #18181B;
            --ring: #FF0099;
            --success: #00FF94;
            --error: #FF0055;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--background);
            color: var(--foreground);
            min-height: 100vh;
            line-height: 1.6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 5vw, 2rem);
        }

        .login-container {
            width: 100%;
            max-width: clamp(340px, 40vw, 450px);
        }

        .header {
            text-align: center;
            margin-bottom: clamp(2rem, 10vw, 3rem);
        }

        .logo {
            font-family: 'Unbounded', sans-serif;
            font-size: clamp(2rem, 15vw, 3.5rem);
            font-weight: 900;
            background: linear-gradient(135deg, #FF0099 0%, #7000FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.05em;
            display: inline-block;
        }

        .subtitle {
            color: var(--muted-foreground);
            font-size: clamp(0.9rem, 4vw, 1.1rem);
            margin-top: 0.5rem;
        }

        .card {
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: clamp(0.75rem, 3vw, 1.5rem);
            padding: clamp(1.5rem, 5vw, 2.5rem);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
        }

        h2 {
            font-family: 'Unbounded', sans-serif;
            font-size: clamp(1.5rem, 6vw, 2rem);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .subtitle-text {
            color: var(--muted-foreground);
            font-size: clamp(0.9rem, 3vw, 1rem);
            margin-bottom: clamp(1.5rem, 5vw, 2rem);
        }

        .form-group {
            margin-bottom: clamp(1.25rem, 4vw, 1.5rem);
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: clamp(0.75rem, 3vw, 1rem);
            background: var(--input);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: clamp(0.9rem, 3vw, 1rem);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 0, 153, 0.2);
        }

        .button {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: clamp(0.875rem, 4vw, 1rem);
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 9999px;
            font-family: 'Outfit', sans-serif;
            font-weight: bold;
            font-size: clamp(0.9rem, 3vw, 1rem);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            box-shadow: 0 0 15px rgba(255, 0, 153, 0.4);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .button:hover {
            box-shadow: 0 0 25px rgba(255, 0, 153, 0.6);
            transform: scale(1.02);
        }

        .button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: clamp(0.75rem, 3vw, 1rem);
            border-radius: 0.5rem;
            margin-bottom: clamp(1rem, 4vw, 1.5rem);
            font-size: clamp(0.85rem, 3vw, 0.95rem);
            display: none;
        }

        .alert.error {
            background: rgba(255, 0, 85, 0.2);
            border: 1px solid var(--error);
            color: var(--error);
            display: block;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: clamp(1.5rem, 5vw, 2rem);
            color: var(--muted-foreground);
            font-size: clamp(0.85rem, 3vw, 0.95rem);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--foreground);
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 640px) {
            .login-container {
                max-width: 100%;
            }

            .card {
                border-radius: 1rem;
            }
        }

        @media (min-width: 1920px) {
            .login-container {
                max-width: 500px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">
            <h1 class="logo">NeonVox</h1>
            <p class="subtitle">Admin Dashboard</p>
        </div>

        <div class="card">
            <h2>Welcome Back</h2>
            <p class="subtitle-text">Sign in to manage your karaoke library</p>

            <div id="alert" class="alert error"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        placeholder="Enter your username"
                        autocomplete="username"
                    >
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                </div>
                <button type="submit" class="button" id="submitBtn">
                    <span id="btnText">Sign In</span>
                </button>
            </form>
        </div>

        <a href="../index.php" class="back-link">← Back to Home</a>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const alert = document.getElementById('alert');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const data = {
                username: formData.get('username'),
                password: formData.get('password')
            };

            // Show loading state
            submitBtn.disabled = true;
            btnText.innerHTML = '<span class="loading"></span>';
            alert.style.display = 'none';

            try {
                const response = await fetch('../api/index.php/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    localStorage.setItem('admin_token', result.token);
                    localStorage.setItem('admin_username', result.username);
                    localStorage.setItem('admin_user_id', result.user_id);
                    window.location.href = 'dashboard.php';
                } else {
                    throw new Error(result.error || 'Login failed');
                }
            } catch (error) {
                alert.textContent = error.message;
                alert.style.display = 'block';
                submitBtn.disabled = false;
                btnText.textContent = 'Sign In';
            }
        });
    </script>
</body>
</html>
