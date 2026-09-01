<?php
session_start();
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Dulcería QRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sweet-blue': '#00BFFF',
                        'dark-blue': '#1e3a8a',
                        'light-blue': '#dbeafe',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: #f3f7fb;
        }
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
        .login-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(0, 191, 255, 0.6);
            border-radius: 24px;
            width: min(100%, 420px);
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.08);
            padding: 20px 18px 22px;
        }
        .logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .logo-wrap img {
            width: 110px;
            height: 110px;
            object-fit: contain;
            border-radius: 9999px;
            border: 4px solid rgba(0, 191, 255, 0.25);
            background: #fff;
            box-shadow: 0 8px 18px rgba(0, 191, 255, 0.12);
        }
        .input-field {
            width: 100%;
            min-height: 48px;
            border-radius: 12px;
            border: 1px solid #dbe3ee;
            background: #f8fafc;
            padding: 0.85rem 1rem;
            font-size: 1rem;
            color: #1f2937;
        }
        .input-field:focus {
            outline: none;
            border-color: #00BFFF;
            box-shadow: 0 0 0 2px rgba(0, 191, 255, 0.15);
            background: #fff;
        }
        .login-label {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 8px;
        }
        .login-btn {
            display: block;
            width: 100%;
            border-radius: 12px;
            background: linear-gradient(135deg, #29c2ff 0%, #1b5fb8 100%);
            color: white;
            font-weight: 800;
            font-size: 1.1rem;
            padding: 0.9rem 1rem;
            border: none;
            box-shadow: 0 12px 22px rgba(27, 95, 184, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }
        .login-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
            box-shadow: 0 14px 26px rgba(27, 95, 184, 0.24);
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 16px;
            color: #1f3d7a;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.2s ease;
        }
        .back-link:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body class="min-h-screen relative">
    <!-- Particles Background -->
    <div id="particles-js"></div>

    <div class="login-shell">
        <div class="login-card relative z-10">
            <div class="logo-wrap">
                <img src="../assets/imgs/logo/logo.png" alt="Logo Dulcería Quiromar">
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-5">
                <div>
                    <label class="login-label">Usuario</label>
                    <input type="text" name="username" class="input-field" placeholder="Usuario" minlength="4" maxlength="10" required>
                </div>
                <div>
                    <label class="login-label">Contraseña</label>
                    <input type="password" name="password" class="input-field" placeholder="Contraseña" minlength="6" maxlength="6" required>
                </div>
                <button type="submit" class="login-btn">
                    Iniciar Sesión
                </button>
            </form>

            <a href="../index.php" class="back-link">← Volver al catálogo</a>
        </div>
    </div>

        <!-- <div class="text-center mt-6">
            <p class="text-gray-600">¿No tienes cuenta? <a href="register.php" class="text-sweet-blue hover:text-dark-blue font-medium">Regístrate</a></p>
            <p class="text-gray-500 text-sm mt-4"><a href="../index.php" class="hover:text-dark-blue">← Volver al Catálogo</a></p>
        </div> -->
    </div>

    <script>
        particlesJS('particles-js', {
            particles: {
                number: { value: 50, density: { enable: true, value_area: 800 } },
                color: { value: '#00BFFF' },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#00BFFF', opacity: 0.4, width: 1 },
                move: { enable: true, speed: 2, direction: 'none', random: true, straight: false, out_mode: 'out' }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' } },
                modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
            },
            retina_detect: true
        });
    </script>
</body>
</html>