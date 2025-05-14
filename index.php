<?php
include 'config.php'; // Asegúrate de que este archivo tiene la conexión correcta a coffee_db

try {
    // Primero, obtenemos todos los orígenes únicos
    $queryOrigenes = "SELECT DISTINCT origen FROM responses ORDER BY origen";
    $stmtOrigenes = $conn->prepare($queryOrigenes);
    $stmtOrigenes->execute();
    $origenes = $stmtOrigenes->fetchAll(PDO::FETCH_COLUMN);
    
    // Obtenemos todas las marcas únicas
    $queryMarcas = "SELECT DISTINCT marca FROM responses WHERE marca IS NOT NULL AND marca != '' ORDER BY marca";
    $stmtMarcas = $conn->prepare($queryMarcas);
    $stmtMarcas->execute();
    $marcas = $stmtMarcas->fetchAll(PDO::FETCH_COLUMN);
    
    // Ahora, para cada origen, obtenemos todas las métricas individuales
    $datosCafe = [];
    $colores = [
        'rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 
        'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)', 
        'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
        'rgba(199, 199, 199, 0.7)', 'rgba(83, 102, 255, 0.7)', 
        'rgba(40, 159, 64, 0.7)', 'rgba(210, 105, 30, 0.7)'
    ];
    
    $bordeColores = [
        'rgb(255, 99, 132)', 'rgb(54, 162, 235)', 
        'rgb(255, 206, 86)', 'rgb(75, 192, 192)', 
        'rgb(153, 102, 255)', 'rgb(255, 159, 64)',
        'rgb(199, 199, 199)', 'rgb(83, 102, 255)', 
        'rgb(40, 159, 64)', 'rgb(210, 105, 30)'
    ];
    
    // También necesitamos el promedio general para la clasificación de lista
    $queryPromedios = "SELECT 
        origen AS nombre_cafe, 
        ROUND(AVG((sabor + aroma + cuerpo + acidez + amargor + balance + intensidad + postgusto + sostenibilidad) / 9), 2) as promedio,
        MAX(DATE_FORMAT(created_at, '%d/%m/%Y')) as ultima_evaluacion 
        FROM responses 
        GROUP BY origen 
        ORDER BY promedio DESC";
        
    $stmtPromedios = $conn->prepare($queryPromedios);
    $stmtPromedios->execute();
    $datos = $stmtPromedios->fetchAll(PDO::FETCH_ASSOC);
    
    // Consulta para obtener todas las métricas por origen
    $queryDetalles = "SELECT 
        origen,
        ROUND(AVG(sabor), 1) as sabor,
        ROUND(AVG(aroma), 1) as aroma,
        ROUND(AVG(cuerpo), 1) as cuerpo,
        ROUND(AVG(acidez), 1) as acidez,
        ROUND(AVG(amargor), 1) as amargor,
        ROUND(AVG(balance), 1) as balance,
        ROUND(AVG(intensidad), 1) as intensidad,
        ROUND(AVG(postgusto), 1) as postgusto,
        ROUND(AVG(sostenibilidad), 1) as sostenibilidad
    FROM responses 
    GROUP BY origen";
    
    $stmtDetalles = $conn->prepare($queryDetalles);
    $stmtDetalles->execute();
    $detallesCafe = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
    
    // Consulta para obtener todas las métricas por marca
    $queryDetallesMarca = "SELECT 
        marca,
        ROUND(AVG(sabor), 1) as sabor,
        ROUND(AVG(aroma), 1) as aroma,
        ROUND(AVG(cuerpo), 1) as cuerpo,
        ROUND(AVG(acidez), 1) as acidez,
        ROUND(AVG(amargor), 1) as amargor,
        ROUND(AVG(balance), 1) as balance,
        ROUND(AVG(intensidad), 1) as intensidad,
        ROUND(AVG(postgusto), 1) as postgusto,
        ROUND(AVG(sostenibilidad), 1) as sostenibilidad
    FROM responses 
    WHERE marca IS NOT NULL AND marca != ''
    GROUP BY marca";
    
    $stmtDetallesMarca = $conn->prepare($queryDetallesMarca);
    $stmtDetallesMarca->execute();
    $detallesMarca = $stmtDetallesMarca->fetchAll(PDO::FETCH_ASSOC);
    
    // Consulta para obtener el promedio por marca
    $queryPromediosMarca = "SELECT 
        marca AS nombre_cafe, 
        ROUND(AVG((sabor + aroma + cuerpo + acidez + amargor + balance + intensidad + postgusto + sostenibilidad) / 9), 2) as promedio,
        MAX(DATE_FORMAT(created_at, '%d/%m/%Y')) as ultima_evaluacion 
        FROM responses 
        WHERE marca IS NOT NULL AND marca != ''
        GROUP BY marca 
        ORDER BY promedio DESC";
        
    $stmtPromediosMarca = $conn->prepare($queryPromediosMarca);
    $stmtPromediosMarca->execute();
    $datosMarca = $stmtPromediosMarca->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking del Mejor Café - Sumapaz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary: #5D4037;
            --secondary: #8D6E63;
            --accent: #D7CCC8;
            --light: #EFEBE9;
            --dark: #3E2723;
            --text: #212121;
            --gold: #D4AF37;
            --silver: #A9A9A9;
            --bronze: #CD7F32;
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
        }
        
        header {
            background: linear-gradient(135deg, var(--dark), var(--primary));
            color: white;
            padding: 2rem 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            text-align: center;
            position: relative;
        }
        
        .auth-buttons {
            position: absolute;
            top: 1rem;
            right: 2rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .auth-btn {
            background-color: transparent;
            color: white;
            border: 1px solid white;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            text-decoration: none;
        }
        
        .auth-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
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
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .chart-container {
            position: relative;
            margin: 0 auto;
            width: 100%;
            max-width: 800px; /* Limitar el ancho máximo */
            height: auto;
            aspect-ratio: 16/9;
            max-height: 500px; /* Reducir la altura máxima */
            margin-bottom: 2rem;
        }
        
        .chart-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }
        
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
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
            gap: 0.5rem;
            text-decoration: none;
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
        
        .ranking-list {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .ranking-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        
        .ranking-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .rank {
            font-size: 1.5rem;
            font-weight: 600;
            min-width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .rank-1 {
            color: var(--gold);
        }
        
        .rank-2 {
            color: var(--silver);
        }
        
        .rank-3 {
            color: var(--bronze);
        }
        
        .coffee-info {
            flex-grow: 1;
            padding: 0 1rem;
        }
        
        .coffee-name {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .evaluation-date {
            font-size: 0.85rem;
            color: #777;
            margin-top: 0.2rem;
        }
        
        .score {
            font-weight: 600;
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background-color: var(--light);
            color: var(--dark);
        }
        
        .score-high {
            background-color: rgba(76, 175, 80, 0.2);
            color: #2E7D32;
        }
        
        .score-medium {
            background-color: rgba(255, 193, 7, 0.2);
            color: #F57F17;
        }
        
        .score-low {
            background-color: rgba(244, 67, 54, 0.2);
            color: #C62828;
        }
        
        .coffee-beans {
            position: absolute;
            opacity: 0.03;
            z-index: -1;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 1.8rem;
            }
            
            .container {
                padding: 1.5rem;
                margin: 1.5rem auto;
            }
            
            .chart-container {
                aspect-ratio: 1/1;
                max-height: 400px;
                max-width: 100%;
            }
            
            .btn-container {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
                justify-content: center;
            }
            
            .auth-buttons {
                position: static;
                justify-content: center;
                margin-bottom: 1rem;
            }
            
            .ranking-item {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem 1rem;
            }
            
            .rank {
                margin-bottom: 0.5rem;
            }
            
            .coffee-info {
                margin-bottom: 0.5rem;
                padding: 0;
            }
            
            .score {
                width: 100%;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .chart-container {
                aspect-ratio: 4/5;
                max-height: 300px;
            }
            
            .cafe-selector select {
                width: 100%;
                max-width: 100%;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .subtitle {
                font-size: 0.9rem;
            }
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            padding: 2rem 0;
            text-align: center;
            margin-top: 3rem;
        }
        
        .toggle-view {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }
        
        .view-btn {
            background-color: var(--accent);
            color: var(--dark);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .view-btn.active {
            background-color: var(--primary);
            color: white;
        }
        
        /* Estilos para selector de café */
        .cafe-selector {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .cafe-selector select {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            border: 1px solid var(--accent);
            background-color: white;
            font-family: 'Poppins', sans-serif;
            color: var(--primary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 200px;
            max-width: 100%;
        }
        
        .cafe-selector select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(93, 64, 55, 0.2);
        }
        
        .cafe-selector label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <!-- Elementos decorativos de fondo (café) -->
    <img src="img/coffee-bean.png" alt="Coffee Bean" class="coffee-beans" style="top: 20%; left: 10%;">
    <img src="img/coffee-bean.png" alt="Coffee Bean" class="coffee-beans" style="top: 70%; right: 5%;">
    <img src="img/coffee-bean.png" alt="Coffee Bean" class="coffee-beans" style="top: 40%; right: 15%;">
    
    <header>
        <div class="header-content">
            <!-- Botones de autenticación en esquina superior derecha -->
            <div class="auth-buttons">
                <a href="login.php" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
                <a href="register.php" class="auth-btn">
                    <i class="fas fa-user-plus"></i> Registrarse
                </a>
            </div>
            
            <img src="Logo.png" alt="Logo Café Sumapaz" class="logo">
            <h1>Ranking del Mejor Café del Sumapaz</h1>
            <p class="subtitle">Descubre la excelencia en cada grano de nuestra región</p>
        </div>
    </header>
    
    <div class="container">
        <h2 class="section-title">Clasificación de Café por Calidad</h2>
        
        <div class="toggle-view">
            <button class="view-btn active" id="chartView">
                <i class="fas fa-chart-radar"></i> Gráfico de Radar
            </button>
            <button class="view-btn" id="listView">
                <i class="fas fa-list"></i> Lista
            </button>
        </div>
        
        <div class="data-type-selector">
            <label for="dataTypeSelector">Mostrar datos por:</label>
            <select id="dataTypeSelector" onchange="cambiarTipoDatos()">
                <option value="origen">Origen</option>
                <option value="marca">Marca</option>
            </select>
        </div>
        
        <div id="chartSection" class="chart-container">
            <!-- Selector de cafés por origen -->
            <div class="cafe-selector" id="origenSelector">
                <label for="cafeSelector">Selecciona un café por origen para ver su perfil detallado:</label>
                <select id="cafeSelector" onchange="actualizarGrafico()">
                    <option value="todos">Todos los cafés</option>
                    <?php foreach ($detallesCafe as $index => $cafe): ?>
                    <option value="<?= $index ?>"><?= htmlspecialchars($cafe['origen']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Selector de cafés por marca -->
            <div class="cafe-selector" id="marcaSelector" style="display: none;">
                <label for="marcaSelector">Selecciona una marca para ver su perfil detallado:</label>
                <select id="cafeMarcaSelector" onchange="actualizarGraficoMarca()">
                    <option value="todos">Todas las marcas</option>
                    <?php foreach ($detallesMarca as $index => $marca): ?>
                    <option value="<?= $index ?>"><?= htmlspecialchars($marca['marca']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="chart-wrapper">
                <canvas id="graficoCafe"></canvas>
            </div>
        </div>
        
        <!-- Sección de lista por origen -->
        <div id="listSection" class="ranking-list" style="display: none;">
            <?php 
            $index = 1;
            foreach ($datos as $cafe): 
                $scoreClass = '';
                if ($cafe['promedio'] >= 8) {
                    $scoreClass = 'score-high';
                } elseif ($cafe['promedio'] >= 6) {
                    $scoreClass = 'score-medium';
                } else {
                    $scoreClass = 'score-low';
                }
                
                $rankClass = '';
                if ($index == 1) {
                    $rankClass = 'rank-1';
                } elseif ($index == 2) {
                    $rankClass = 'rank-2';
                } elseif ($index == 3) {
                    $rankClass = 'rank-3';
                }
            ?>
            <div class="ranking-item">
                <div class="rank <?= $rankClass ?>">
                    <?php 
                    if ($index == 1) {
                        echo '<i class="fas fa-trophy"></i>';
                    } else {
                        echo $index;
                    }
                    ?>
                </div>
                <div class="coffee-info">
                    <div class="coffee-name"><?= htmlspecialchars($cafe['nombre_cafe']) ?></div>
                    <div class="origin">Región de Sumapaz</div>
                    <div class="evaluation-date">Última evaluación: <?= $cafe['ultima_evaluacion'] ?></div>
                </div>
                <div class="score <?= $scoreClass ?>"><?= $cafe['promedio'] ?> / 10</div>
            </div>
            <?php 
            $index++;
            endforeach; 
            ?>
        </div>
        
        <!-- Sección de lista por marca -->
        <div id="listSectionMarca" class="ranking-list" style="display: none;">
            <?php 
            $index = 1;
            foreach ($datosMarca as $marca): 
                $scoreClass = '';
                if ($marca['promedio'] >= 8) {
                    $scoreClass = 'score-high';
                } elseif ($marca['promedio'] >= 6) {
                    $scoreClass = 'score-medium';
                } else {
                    $scoreClass = 'score-low';
                }
                
                $rankClass = '';
                if ($index == 1) {
                    $rankClass = 'rank-1';
                } elseif ($index == 2) {
                    $rankClass = 'rank-2';
                } elseif ($index == 3) {
                    $rankClass = 'rank-3';
                }
            ?>
            <div class="ranking-item">
                <div class="rank <?= $rankClass ?>">
                    <?php 
                    if ($index == 1) {
                        echo '<i class="fas fa-trophy"></i>';
                    } else {
                        echo $index;
                    }
                    ?>
                </div>
                <div class="coffee-info">
                    <div class="coffee-name"><?= htmlspecialchars($marca['nombre_cafe']) ?></div>
                    <div class="origin">Mejor Marca</div>
                    <div class="evaluation-date">Última evaluación: <?= $marca['ultima_evaluacion'] ?></div>
                </div>
                <div class="score <?= $scoreClass ?>"><?= $marca['promedio'] ?> / 10</div>
            </div>
            <?php 
            $index++;
            endforeach; 
            ?>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?= date('Y') ?> Café Sumapaz - Todos los derechos reservados</p>
    </footer>

    <script>
        // Configuración del gráfico
        const ctx = document.getElementById('graficoCafe').getContext('2d');
        
        // Datos de todos los cafés para el gráfico radar
        const cafeData = <?= json_encode($detallesCafe) ?>;
        const marcaData = <?= json_encode($detallesMarca) ?>;
        const colores = <?= json_encode($colores) ?>;
        const bordeColores = <?= json_encode($bordeColores) ?>;
        
        // Etiquetas para el gráfico radar (características del café)
        const etiquetas = ['Sabor', 'Aroma', 'Cuerpo', 'Acidez', 'Amargor', 'Balance', 'Intensidad', 'Postgusto', 'Sostenibilidad'];
        
        // Configuración inicial del gráfico
        let myChart;
        let currentDataType = 'origen'; // Por defecto mostramos datos por origen
        
        // Función para cambiar entre visualización por origen o marca
        function cambiarTipoDatos() {
            currentDataType = document.getElementById('dataTypeSelector').value;
            
            // Mostrar/ocultar selectores según el tipo de datos
            if (currentDataType === 'origen') {
                document.getElementById('origenSelector').style.display = 'block';
                document.getElementById('marcaSelector').style.display = 'none';
                
                // Si estamos en vista de lista, mostrar la lista correcta
                if (document.getElementById('chartView').classList.contains('active')) {
                    actualizarGrafico();
                } else {
                    document.getElementById('listSection').style.display = 'block';
                    document.getElementById('listSectionMarca').style.display = 'none';
                }
            } else {
                document.getElementById('origenSelector').style.display = 'none';
                document.getElementById('marcaSelector').style.display = 'block';
                
                // Si estamos en vista de lista, mostrar la lista correcta
                if (document.getElementById('chartView').classList.contains('active')) {
                    actualizarGraficoMarca();
                } else {
                    document.getElementById('listSection').style.display = 'none';
                    document.getElementById('listSectionMarca').style.display = 'block';
                }
            }
        }
        
        // Función para inicializar el gráfico con todos los cafés por origen
        function inicializarGrafico() {
            if (currentDataType === 'origen') {
                actualizarGrafico();
            } else {
                actualizarGraficoMarca();
            }
        }
        
        // Función para actualizar el gráfico según el café seleccionado por origen
        function actualizarGrafico() {
            const datasets = [];
            
            // Configuración para mostrar "Todos los cafés"
            if (document.getElementById('cafeSelector').value === 'todos') {
                cafeData.forEach((cafe, index) => {
                    datasets.push({
                        label: cafe.origen,
                        data: [
                            cafe.sabor, cafe.aroma, cafe.cuerpo, 
                            cafe.acidez, cafe.amargor, cafe.balance, 
                            cafe.intensidad, cafe.postgusto, cafe.sostenibilidad
                        ],
                        backgroundColor: colores[index % colores.length],
                        borderColor: bordeColores[index % bordeColores.length],
                        borderWidth: 2,
                        pointBackgroundColor: bordeColores[index % bordeColores.length],
                        pointRadius: 4,
                        pointHoverRadius: 6
                    });
                });
            } else {
                // Configuración para mostrar un solo café seleccionado
                const cafeIndex = parseInt(document.getElementById('cafeSelector').value);
                const cafe = cafeData[cafeIndex];
                
                datasets.push({
                    label: cafe.origen,
                    data: [
                        cafe.sabor, cafe.aroma, cafe.cuerpo, 
                        cafe.acidez, cafe.amargor, cafe.balance, 
                        cafe.intensidad, cafe.postgusto, cafe.sostenibilidad
                    ],
                    backgroundColor: colores[cafeIndex % colores.length],
                    borderColor: bordeColores[cafeIndex % bordeColores.length],
                    borderWidth: 2,
                    pointBackgroundColor: bordeColores[cafeIndex % bordeColores.length],
                    pointRadius: 4,
                    pointHoverRadius: 6
                });
            }
            
            actualizarChartJS(datasets);
        }
        
        // Función para actualizar el gráfico según la marca seleccionada
        function actualizarGraficoMarca() {
            const datasets = [];
            
            // Configuración para mostrar "Todas las marcas"
            if (document.getElementById('cafeMarcaSelector').value === 'todos') {
                marcaData.forEach((marca, index) => {
                    datasets.push({
                        label: marca.marca,
                        data: [
                            marca.sabor, marca.aroma, marca.cuerpo, 
                            marca.acidez, marca.amargor, marca.balance, 
                            marca.intensidad, marca.postgusto, marca.sostenibilidad
                        ],
                        backgroundColor: colores[index % colores.length],
                        borderColor: bordeColores[index % bordeColores.length],
                        borderWidth: 2,
                        pointBackgroundColor: bordeColores[index % bordeColores.length],
                        pointRadius: 4,
                        pointHoverRadius: 6
                    });
                });
            } else {
                // Configuración para mostrar una sola marca seleccionada
                const marcaIndex = parseInt(document.getElementById('cafeMarcaSelector').value);
                const marca = marcaData[marcaIndex];
                
                datasets.push({
                    label: marca.marca,
                    data: [
                        marca.sabor, marca.aroma, marca.cuerpo, 
                        marca.acidez, marca.amargor, marca.balance, 
                        marca.intensidad, marca.postgusto, marca.sostenibilidad
                    ],
                    backgroundColor: colores[marcaIndex % colores.length],
                    borderColor: bordeColores[marcaIndex % bordeColores.length],
                    borderWidth: 2,
                    pointBackgroundColor: bordeColores[marcaIndex % bordeColores.length],
                    pointRadius: 4,
                    pointHoverRadius: 6
                });
            }
            
            actualizarChartJS(datasets);
        }
        
        // Función para actualizar el gráfico ChartJS
        function actualizarChartJS(datasets) {
            // Si ya existe un gráfico, lo destruimos
            if (myChart) {
                myChart.destroy();
            }
            
            // Creamos el nuevo gráfico
            myChart = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: etiquetas,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    layout: {
                        padding: {
                            top: 20,
                            bottom: 20
                        }
                    },
                    scales: {
                        r: {
                            angleLines: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            pointLabels: {
                                font: {
                                    size: 12,
                                    family: "'Poppins', sans-serif"
                                },
                                color: '#5D4037'
                            },
                            ticks: {
                                backdropColor: 'transparent',
                                color: '#5D4037',
                                z: 100,
                                font: {
                                    size: 10
                                },
                                stepSize: 2
                            },
                            suggestedMin: 0,
                            suggestedMax: 10
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: "'Poppins', sans-serif",
                                    size: 12
                                },
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#5D4037',
                            bodyColor: '#5D4037',
                            bodyFont: {
                                family: "'Poppins', sans-serif"
                            },
                            titleFont: {
                                family: "'Poppins', sans-serif",
                                weight: 'bold'
                            },
                            borderColor: '#EFEBE9',
                            borderWidth: 1,
                            caretSize: 6,
                            cornerRadius: 6,
                            displayColors: true,
                            boxPadding: 6
                        }
                    }
                }
            });
        }
        
        // Inicializar el gráfico al cargar la página
        window.onload = function() {
            inicializarGrafico();
            
            // Configurar los botones de cambio de vista
            document.getElementById('chartView').addEventListener('click', function() {
                this.classList.add('active');
                document.getElementById('listView').classList.remove('active');
                document.getElementById('chartSection').style.display = 'block';
                document.getElementById('listSection').style.display = 'none';
                document.getElementById('listSectionMarca').style.display = 'none';
                inicializarGrafico();
            });
            
            document.getElementById('listView').addEventListener('click', function() {
                this.classList.add('active');
                document.getElementById('chartView').classList.remove('active');
                document.getElementById('chartSection').style.display = 'none';
                
                if (currentDataType === 'origen') {
                    document.getElementById('listSection').style.display = 'block';
                    document.getElementById('listSectionMarca').style.display = 'none';
                } else {
                    document.getElementById('listSection').style.display = 'none';
                    document.getElementById('listSectionMarca').style.display = 'block';
                }
            });
        };
    </script>
</body>
</html>