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
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid #00BFFF;
        }
        .input-field:focus {
            outline: none;
            border-color: #00BFFF;
            box-shadow: 0 0 0 2px rgba(0, 191, 255, 0.2);
        }
    </style>
</head>
<body class="bg-white min-h-screen relative flex items-center justify-center">
    <!-- Particles Background -->
    <div id="particles-js"></div>

    <div class="login-card rounded-xl shadow-2xl p-6 sm:p-8 w-full max-w-sm sm:max-w-md relative z-10">


        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4 sm:space-y-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Usuario</label>
                <input type="text" name="username" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="Ingresa tu usuario" minlength="4" maxlength="10" required>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Contraseña</label>
                <input type="password" name="password" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="Ingresa tu contraseña" minlength="6" maxlength="6" required>
            </div>
            <button type="submit" class="w-full bg-dark-blue text-white py-3 rounded-lg font-semibold hover:bg-blue-900 transition duration-300 shadow-lg">
                Iniciar Sesión
            </button>
        </form>

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