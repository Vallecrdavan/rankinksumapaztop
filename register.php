<?php
// register.php
require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre   = trim($_POST["nombre"]);
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $rol      = $_POST["rol"]; // 'usuario' o 'admin'

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        // Hashear la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (nombre, email, password, rol) 
                    VALUES (:nombre, :email, :password, :rol)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashedPassword);
            $stmt->bindParam(":rol", $rol);

            $stmt->execute();
            $message = "Registro exitoso. Ahora puedes iniciar sesión.";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                // Código de error 1062: entrada duplicada
                $message = "El correo ya está registrado. Intenta con otro.";
            } else {
                $message = "Error al registrar: " . $e->getMessage();
            }
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
    <title>Registro - Café Sumapaz</title>
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
            min-height: 100vh;
            position: relative;
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
        
        h1, h2 {
            font-family: 'Playfair Display', serif;
            position: relative;
            display: inline-block;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-top: 1rem;
        }
        
        h1::after, h2::after {
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
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            position: relative;
            z-index: 2;
        }
        
        .section-title {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        input, select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--accent);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
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
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
            font-size: 1rem;
        }
        
        .btn:hover {
            background-color: var(--dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(93, 64, 55, 0.3);
        }
        
        .logo {
            max-width: 120px;
            margin-bottom: 0.5rem;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            text-align: center;
        }
        
        .message-error {
            background-color: rgba(244, 67, 54, 0.1);
            color: #C62828;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
        
        .message-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #2E7D32;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .login-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .login-link:hover {
            color: var(--dark);
            text-decoration: underline;
        }
        
        .coffee-beans {
            position: fixed;
            opacity: 0.05;
            z-index: 1;
            width: 150px;
            height: 150px;
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            padding: 2rem 0;
            text-align: center;
            margin-top: 3rem;
            position: relative;
            z-index: 2;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            
            .container {
                margin: 2rem 1rem;
                padding: 1.5rem;
            }
            
            .btn {
                padding: 0.7rem 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Elementos decorativos de fondo (café) -->
    <img src="/api/placeholder/150/150" alt="Coffee Bean" class="coffee-beans" style="top: 20%; left: 10%;">
    <img src="/api/placeholder/150/150" alt="Coffee Bean" class="coffee-beans" style="top: 70%; right: 5%;">
    <img src="/api/placeholder/150/150" alt="Coffee Bean" class="coffee-beans" style="top: 40%; right: 15%;">
    
    <header>
        <div class="header-content">
            <img src="Logo.png" alt="Logo Café Sumapaz" class="logo">
            <h1>Café Sumapaz</h1>
            <p class="subtitle">Descubre la excelencia en cada grano de nuestra región</p>
        </div>
    </header>
    
    <div class="container">
        <h2 class="section-title">Crear una cuenta</h2>
        
        <?php if(!empty($message)): ?>
            <div class="message <?php echo strpos($message, "exitoso") !== false ? 'message-success' : 'message-error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre"><i class="fas fa-user"></i> Nombre:</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Correo electrónico:</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña:</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-group">
                <label for="rol"><i class="fas fa-user-tag"></i> Rol:</label>
                <select name="rol" id="rol">
                    <option value="usuario">Califiacador</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i> Registrarse
            </button>
        </form>
        
        <a href="login.php" class="login-link">
            <i class="fas fa-sign-in-alt"></i> ¿Ya tienes cuenta? Inicia sesión
        </a>
    </div>
    
    <footer>
        <p>&copy; <?= date('Y') ?> Café Sumapaz - Todos los derechos reservados</p>
    </footer>
</body>
</html>