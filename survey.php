<?php
session_start();
require_once "config.php";

// Verificar si el usuario está logueado y tiene rol "usuario"
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'usuario') {
    header("Location: login.php");
    exit();
}

$message = "";

// Lista de zonas cafeteras del Sumapaz
$zonas_cafeteras = [
    "Fusagasugá", "Arbeláez", "Silvania", "Tibacuy", "Pasca",
    "San Bernardo", "Venecia", "Cabrera", "Pandi","Granada"
];

// Asociación de marcas con cada provincia
$marcas_por_provincia = [
    "Fusagasugá" => ["Marca1-Fusa", "Marca2-Fusa", "Marca3-Fusa", "Marca4-Fusa", "Marca5-Fusa", 
                     "Marca6-Fusa", "Marca7-Fusa", "Marca8-Fusa", "Marca9-Fusa", "Marca10-Fusa"],
    "Arbeláez" => ["Marca1-Arb", "Marca2-Arb", "Marca3-Arb", "Marca4-Arb", "Marca5-Arb", 
                  "Marca6-Arb", "Marca7-Arb", "Marca8-Arb", "Marca9-Arb", "Marca10-Arb"],
    "Silvania" => ["Marca1-Sil", "Marca2-Sil", "Marca3-Sil", "Marca4-Sil", "Marca5-Sil", 
                  "Marca6-Sil", "Marca7-Sil", "Marca8-Sil", "Marca9-Sil", "Marca10-Sil"],
    "Tibacuy" => ["Marca1-Tib", "Marca2-Tib", "Marca3-Tib", "Marca4-Tib", "Marca5-Tib", 
                 "Marca6-Tib", "Marca7-Tib", "Marca8-Tib", "Marca9-Tib", "Marca10-Tib"],
    "Pasca" => ["Marca1-Pas", "Marca2-Pas", "Marca3-Pas", "Marca4-Pas", "Marca5-Pas", 
               "Marca6-Pas", "Marca7-Pas", "Marca8-Pas", "Marca9-Pas", "Marca10-Pas"],
    "San Bernardo" => ["Marca1-SB", "Marca2-SB", "Marca3-SB", "Marca4-SB", "Marca5-SB", 
                      "Marca6-SB", "Marca7-SB", "Marca8-SB", "Marca9-SB", "Marca10-SB"],
    "Venecia" => ["Marca1-Ven", "Marca2-Ven", "Marca3-Ven", "Marca4-Ven", "Marca5-Ven", 
                 "Marca6-Ven", "Marca7-Ven", "Marca8-Ven", "Marca9-Ven", "Marca10-Ven"],
    "Cabrera" => ["Marca1-Cab", "Marca2-Cab", "Marca3-Cab", "Marca4-Cab", "Marca5-Cab", 
                 "Marca6-Cab", "Marca7-Cab", "Marca8-Cab", "Marca9-Cab", "Marca10-Cab"],
    "Pandi" => ["Marca1-Pan", "Marca2-Pan", "Marca3-Pan", "Marca4-Pan", "Marca5-Pan", 
               "Marca6-Pan", "Marca7-Pan", "Marca8-Pan", "Marca9-Pan", "Marca10-Pan"],
    "Granada" => ["Marca1-Gra", "Marca2-Gra", "Marca3-Gra", "Marca4-Gra", "Marca5-Gra", 
                 "Marca6-Gra", "Marca7-Gra", "Marca8-Gra", "Marca9-Gra", "Marca10-Gra"]
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id       = $_SESSION['user_id'];
    $sabor         = (int) $_POST['sabor'];
    $aroma         = (int) $_POST['aroma'];
    $cuerpo        = (int) $_POST['cuerpo'];
    $acidez        = (int) $_POST['acidez'];
    $amargor       = (int) $_POST['amargor'];
    $balance       = (int) $_POST['balance'];
    $intensidad    = (int) $_POST['intensidad'];
    $postgusto     = (int) $_POST['postgusto'];
    $sostenibilidad = (int) $_POST['sostenibilidad'];
    $origen        = trim($_POST['origen']);
    $marca         = trim($_POST['marca']); // Añadido: Capturar la marca del formulario
    $comentario    = trim($_POST['comentario']);

    // Validar que el origen sea una opción válida
    if (!in_array($origen, $zonas_cafeteras)) {
        $message = "Por favor, selecciona una zona cafetera válida.";
        $messageType = "error";
    } else {
        // Validar que todos los valores estén entre 1 y 10
        $valores = [$sabor, $aroma, $cuerpo, $acidez, $amargor, $balance, $intensidad, $postgusto, $sostenibilidad];
        if (min($valores) < 1 || max($valores) > 10) {
            $message = "Por favor, ingresa valores válidos entre 1 y 10.";
            $messageType = "error";
        } else {
            try {
                $sql = "INSERT INTO responses 
                        (user_id, sabor, aroma, cuerpo, acidez, amargor, balance, 
                         intensidad, postgusto, sostenibilidad, origen, marca, comentario) 
                        VALUES (:user_id, :sabor, :aroma, :cuerpo, :acidez, :amargor, 
                                :balance, :intensidad, :postgusto, :sostenibilidad, :origen, :marca, :comentario)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':sabor', $sabor);
                $stmt->bindParam(':aroma', $aroma);
                $stmt->bindParam(':cuerpo', $cuerpo);
                $stmt->bindParam(':acidez', $acidez);
                $stmt->bindParam(':amargor', $amargor);
                $stmt->bindParam(':balance', $balance);
                $stmt->bindParam(':intensidad', $intensidad);
                $stmt->bindParam(':postgusto', $postgusto);
                $stmt->bindParam(':sostenibilidad', $sostenibilidad);
                $stmt->bindParam(':origen', $origen);
                $stmt->bindParam(':marca', $marca);
                $stmt->bindParam(':comentario', $comentario);
                $stmt->execute();

                $message = "¡Gracias por tu opinión! Tu evaluación ha sido registrada correctamente.";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Error al guardar tu opinión: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación de Café - Sumapaz</title>
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
            --success: #4CAF50;
            --error: #F44336;
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
        
        .background-decoration {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .coffee-bean {
            position: absolute;
            width: 50px;
            height: 50px;
            background-color: rgba(93, 64, 55, 0.05);
            border-radius: 50%;
        }
        
        header {
            background: linear-gradient(135deg, var(--dark), var(--primary));
            color: white;
            padding: 1.5rem 0;
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
            font-size: 2.2rem;
            margin-top: 0.5rem;
        }
        
        .user-greeting {
            font-weight: 300;
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }
        
        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            font-size: 1.8rem;
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .form-title::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background-color: var(--secondary);
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .form-subtitle {
            color: var(--secondary);
            font-weight: 300;
        }
        
        .notification {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-weight: 500;
            text-align: center;
        }
        
        .notification.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .notification.error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--error);
            border-left: 4px solid var(--error);
        }
        
        form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary);
        }
        
        .rating-input {
            display: flex;
            align-items: center;
        }
        
        input[type="range"] {
            flex: 1;
            -webkit-appearance: none;
            appearance: none;
            height: 8px;
            background: var(--accent);
            border-radius: 5px;
            outline: none;
        }
        
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        input[type="range"]::-webkit-slider-thumb:hover {
            background: var(--dark);
            transform: scale(1.1);
        }
        
        .rating-value {
            width: 40px;
            text-align: center;
            font-weight: 600;
            color: var(--primary);
            margin-left: 0.5rem;
        }
        
        select, textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--accent);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            transition: all 0.3s ease;
        }
        
        select:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(93, 64, 55, 0.2);
            outline: none;
        }
        
        textarea {
            height: 120px;
            resize: vertical;
            grid-column: 1 / -1;
        }
        
        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            margin-top: 1rem;
        }
        
        button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        button:hover {
            background-color: var(--dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(93, 64, 55, 0.3);
        }
        
        .btn-logout {
            background-color: transparent;
            color: var(--secondary);
            border: 1px solid var(--accent);
            padding: 0.6rem 1.5rem;
        }
        
        .btn-logout:hover {
            background-color: var(--accent);
            color: var(--dark);
            box-shadow: none;
        }
        
        .attribute-tooltip {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        
        .info-icon {
            color: var(--secondary);
            font-size: 0.8rem;
            margin-left: 0.3rem;
            cursor: help;
            transition: color 0.3s ease;
        }
        
        .info-icon:hover {
            color: var(--primary);
        }
        
        .tooltip-text {
            visibility: hidden;
            width: 240px;
            background-color: var(--dark);
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 0.8rem;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.8rem;
            font-weight: 400;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: var(--dark) transparent transparent transparent;
        }
        
        .attribute-tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            padding: 1.5rem 0;
            text-align: center;
            margin-top: 3rem;
            width: 100%;
        }
        
        .back-to-home {
            color: var(--light);
            margin-top: 0.5rem;
            display: inline-block;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-to-home:hover {
            color: white;
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
                margin: 1rem;
                border-radius: 8px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
            
            form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="background-decoration" id="beans-container"></div>
    
    <header>
        <div class="header-content">
            <h1>Evaluación de Café</h1>
            <p class="user-greeting">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Tu opinión es importante para nosotros.</p>
        </div>
    </header>
    
    <div class="container">
        <div class="form-header">
            <h2 class="form-title">Perfil Sensorial del Café</h2>
            <p class="form-subtitle">Evalúa las características que definen la calidad del café de la región de Sumapaz donde 1 es muy deficiente, 5 muy bueno y 10 perfecto de acuerdo a cada característica.</p>
        </div>
        
        <?php if(!empty($message)): ?>
            <div class="notification <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="attribute-tooltip">
                    Sabor
                    <span class="info-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="tooltip-text">El sabor es la característica principal que describe la impresión general del café en tu paladar.</span>
                </label>
                <div class="rating-input">
                    <input type="range" name="sabor" id="sabor" min="1" max="10" value="5" step="1" oninput="updateRating('sabor')">
                    <span class="rating-value" id="saborValue">5</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="attribute-tooltip">
                    Aroma
                    <span class="info-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="tooltip-text">El aroma se refiere a los olores percibidos cuando el café es molido y preparado.</span>
                </label>
                <div class="rating-input">
                    <input type="range" name="aroma" id="aroma" min="1" max="10" value="5" step="1" oninput="updateRating('aroma')">
                    <span class="rating-value" id="aromaValue">5</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="attribute-tooltip">
                    Cuerpo
                    <span class="info-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="tooltip-text">El cuerpo se refiere a la textura y sensación en boca, la densidad y peso del café.</span>
                </label>
                <div class="rating-input">
                    <input type="range" name="cuerpo" id="cuerpo" min="1" max="10" value="5" step="1" oninput="updateRating('cuerpo')">
                    <span class="rating-value" id="cuerpoValue">5</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="attribute-tooltip">
                    Acidez
                    <span class="info-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="tooltip-text">La acidez es la vivacidad y brillo del café, puede ser brillante, alta, media o baja.</span>
                </label>
                <div class="rating-input">
                    <input type="range" name="acidez" id="acidez" min="1" max="10" value="5" step="1" oninput="updateRating('acidez')">
                    <span class="rating-value" id="acidezValue">5</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="attribute-tooltip">
                    Amargor
                    <span class="info-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="tooltip-text">El amargor es una característica natural del café que puede ser equilibrada o excesiva.</span>
                </label>
                <div class="rating-input">
                    <input type="range" name="amargor" id="amargor" min="1" max="10" value="5" step="1" oninput="updateRating('amargor')">
                    <span class="rating-value" id="amargorValue">5</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="attribute-tooltip">
                    Balance
                    <span class="info-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="tooltip-text">El balance se refiere al equilibrio entre todas las características: sabor, acidez, cuerpo y amargor.</span>
                </label>
                <div class="rating-input">
                    <input type="range" name="balance" id="balance" min="1" max="10" value="5" step="1" oninput="updateRating('balance')">
                    <span class="rating-value" id="balanceValue">5</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="attribute-tooltip">
                    Intensidad
                    <span class="info-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="tooltip-text">La intensidad se refiere a la fuerza y robustez general del café.</span>
                </label>
                <div class="rating-input">
                    <input type="range" name="intensidad" id="intensidad" min="1" max="10" value="5" step="1" oninput="updateRating('intensidad')">
                    <span class="rating-value" id="intensidadValue">5</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="attribute-tooltip">
                    Postgusto
                    <span class="info-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="tooltip-text">El postgusto es la sensación que queda en el paladar después de beber el café.</span>
                </label>
                <div class="rating-input">
                    <input type="range" name="postgusto" id="postgusto" min="1" max="10" value="5" step="1" oninput="updateRating('postgusto')">
                    <span class="rating-value" id="postgustoValue">5</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="attribute-tooltip">
                    Sostenibilidad
                    <span class="info-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="tooltip-text">Evalúa la percepción de prácticas sostenibles en el cultivo y procesamiento del café.</span>
                </label>
                <div class="rating-input">
                    <input type="range" name="sostenibilidad" id="sostenibilidad" min="1" max="10" value="5" step="1" oninput="updateRating('sostenibilidad')">
                    <span class="rating-value" id="sostenibilidadValue">5</span>
                </div>
            </div>
            
            <div class="form-group full-width">
                <label for="origen">Origen del Café (Provincia)</label>
                <select name="origen" id="origen" onchange="updateMarcas()">
                    <option value="">Selecciona una provincia</option>
                    <?php foreach($zonas_cafeteras as $zona): ?>
                        <option value="<?php echo htmlspecialchars($zona); ?>"><?php echo htmlspecialchars($zona); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group full-width">
                <label for="marca">Marca de Café</label>
                <select name="marca" id="marca">
                    <option value="">Primero selecciona una provincia</option>
                </select>
            </div>
            
            <div class="form-group full-width">
                <label for="comentario">Comentarios adicionales:</label>
                <textarea name="comentario" id="comentario" placeholder="Describe tu experiencia con este café. ¿Qué notas predominan? ¿Qué te gustó más?"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit">
                    <i class="fas fa-paper-plane"></i> Enviar Evaluación
                </button>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </form>
    </div>
    
    <footer>
        <p>&copy; <?= date('Y') ?> Café Sumapaz - Todos los derechos reservados</p>
        <a href="index.php" class="back-to-home">
            <i class="fas fa-home"></i> Volver al Inicio
        </a>
    </footer>

    <script>
        // Función para actualizar los valores numéricos de los sliders
        function updateRating(id) {
            const slider = document.getElementById(id);
            const display = document.getElementById(id + 'Value');
            display.textContent = slider.value;
        }
        
        // Datos de marcas por provincia
        const marcasPorProvincia = <?php echo json_encode($marcas_por_provincia); ?>;
        
        // Función para actualizar las marcas según la provincia seleccionada
        function updateMarcas() {
            const provinciaSelect = document.getElementById('origen');
            const marcaSelect = document.getElementById('marca');
            const provinciaSeleccionada = provinciaSelect.value;
            
            // Limpiar el selector de marcas
            marcaSelect.innerHTML = '';
            
            // Si no hay provincia seleccionada, mostrar mensaje predeterminado
            if (!provinciaSeleccionada) {
                const opcionDefault = document.createElement('option');
                opcionDefault.value = '';
                opcionDefault.textContent = 'Seleccione primero una zona cafetera...';
                marcaSelect.appendChild(opcionDefault);
                return;
            }
            
            // Obtener las marcas para la provincia seleccionada
            const marcas = marcasPorProvincia[provinciaSeleccionada];
            
            // Añadir opción predeterminada
            const opcionDefault = document.createElement('option');
            opcionDefault.value = '';
            opcionDefault.textContent = 'Seleccione una marca...';
            marcaSelect.appendChild(opcionDefault);
            
            // Añadir las marcas al selector
            marcas.forEach(marca => {
                const opcion = document.createElement('option');
                opcion.value = marca;
                opcion.textContent = marca;
                marcaSelect.appendChild(opcion);
            });
        }
        
        // Crear elementos decorativos de fondo (granos de café)
        const container = document.getElementById('beans-container');
        const numberOfBeans = 15;
        
        for (let i = 0; i < numberOfBeans; i++) {
            const bean = document.createElement('div');
            bean.classList.add('coffee-bean');
            
            // Posición aleatoria
            const randomTop = Math.floor(Math.random() * 100);
            const randomLeft = Math.floor(Math.random() * 100);
            
            // Tamaño aleatorio para los granos
            const randomSize = Math.floor(Math.random() * 30) + 30;
            
            bean.style.top = `${randomTop}%`;
            bean.style.left = `${randomLeft}%`;
            bean.style.width = `${randomSize}px`;
            bean.style.height = `${randomSize}px`;
            bean.style.transform = `rotate(${Math.random() * 360}deg)`;
            
            container.appendChild(bean);
        }
    </script>
</body>
</html>