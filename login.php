<?php
// login.php
require_once "config.php";
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                // Verificar la contraseña
                if (password_verify($password, $user['password'])) {
                    // Guardar datos en sesión
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_role'] = $user['rol'];

                    if ($user['rol'] === 'admin') {
                        header("Location: admin.php");
                    } else {
                        header("Location: survey.php");
                    }
                    exit();
                } else {
                    $message = "Contraseña incorrecta.";
                }
            } else {
                $message = "No existe una cuenta con ese correo.";
            }
        } catch (PDOException $e) {
            $message = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        $message = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Café Sumapaz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #5D4037;
            --secondary: #8D6E63;
            --accent: #D7CCC8;
            --light: #EFEBE9;
            --dark: #3E2723;
            --text: #212121;
            --gold: #D4AF37;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--text);
            line-height: 1.6;
            position: relative;
            min-height: 100vh;
        }
        
        header {
            background: linear-gradient(135deg, var(--dark), var(--primary));
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-top: 1rem;
            position: relative;
            display: inline-block;
        }
        
        h1::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background-color: var(--gold);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .subtitle {
            font-weight: 300;
            opacity: 0.9;
            margin-top: 1.5rem;
        }
        
        .container {
            max-width: 500px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            position: relative;
            z-index: 10;
        }

        .login-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            text-align: center;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        label {
            font-weight: 500;
            color: var(--primary);
        }
        
        input[type="email"],
        input[type="password"] {
            padding: 0.8rem 1rem;
            border: 1px solid var(--accent);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(93, 64, 55, 0.2);
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        
        .btn:hover {
            background-color: var(--dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(93, 64, 55, 0.3);
        }
        
        .register-link {
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            color: var(--dark);
            text-decoration: underline;
        }
        
        .error-message {
            background-color: rgba(244, 67, 54, 0.1);
            color: #C62828;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
        }
        
        .logo {
            max-width: 120px;
            margin-bottom: 0.5rem;
        }
        
        .coffee-beans {
            position: absolute;
            opacity: 0.03;
            z-index: -1;
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            padding: 1.5rem 0;
            text-align: center;
            margin-top: 3rem;
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        /* Animation for form elements */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            animation: fadeInUp 0.6s ease forwards;
        }

        .form-group:nth-child(2) {
            animation-delay: 0.2s;
        }

        .btn {
            animation: fadeInUp 0.6s ease forwards;
            animation-delay: 0.4s;
        }

        .register-link {
            animation: fadeInUp 0.6s ease forwards;
            animation-delay: 0.6s;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            
            .container {
                margin: 2rem 1rem;
                padding: 1.5rem;
            }
            
            footer {
                position: relative;
                margin-top: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Elementos decorativos de fondo (café) -->
    <img src="/api/placeholder/100/100" alt="Coffee Bean" class="coffee-beans" style="top: 20%; left: 10%;">
    <img src="/api/placeholder/100/100" alt="Coffee Bean" class="coffee-beans" style="top: 70%; right: 5%;">
    <img src="/api/placeholder/100/100" alt="Coffee Bean" class="coffee-beans" style="top: 40%; right: 15%;">
    
    <header>
        <div class="header-content">
            <img src="Logo.png" alt="Logo Café Sumapaz" class="logo">
            <h1>Califica tu Café Sumapaz</h1>
            <p class="subtitle">Descubre la excelencia en cada grano de nuestra región</p>
        </div>
    </header>
    
    <div class="container">
        <h2 class="login-title">Iniciar Sesión</h2>
        
        <?php if(!empty($message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="register-link">
            <a href="register.php">
                <i class="fas fa-user-plus"></i> ¿No tienes cuenta? Regístrate
            </a>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?= date('Y') ?> Café Sumapaz - Todos los derechos reservados</p>
    </footer>
</body>
</html>