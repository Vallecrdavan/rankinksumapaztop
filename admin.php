<?php
session_start();
require_once "config.php";

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- Borrar respuesta ---
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    try {
        $sql = "DELETE FROM responses WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $deleteId);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error al eliminar: " . $e->getMessage();
    }
    header("Location: admin.php");
    exit();
}

// --- Obtener todas las respuestas ---
try {
    $sql = "SELECT responses.id, users.nombre, responses.sabor, responses.aroma, responses.cuerpo, 
                   responses.acidez, responses.amargor, responses.balance, responses.intensidad, 
                   responses.postgusto, responses.sostenibilidad, responses.origen, responses.marca, responses.comentario,
                   responses.created_at
            FROM responses
            JOIN users ON responses.user_id = users.id
            ORDER BY responses.id DESC";
    $stmt = $conn->query($sql);
    $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener datos: " . $e->getMessage();
    exit();
}

// --- Calcular promedios de cada característica ---
$totals = [
    'sabor' => 0, 'aroma' => 0, 'cuerpo' => 0, 'acidez' => 0, 
    'amargor' => 0, 'balance' => 0, 'intensidad' => 0, 'postgusto' => 0, 'sostenibilidad' => 0
];
$count = count($responses);

if ($count > 0) {
    foreach ($responses as $res) {
        foreach ($totals as $key => $value) {
            $totals[$key] += $res[$key];
        }
    }
    foreach ($totals as $key => $value) {
        $totals[$key] = round($value / $count, 1); // Sacar el promedio con un decimal
    }
}

// Convertir los datos a JSON para Chart.js
$chartLabels = json_encode(array_keys($totals));
$chartData = json_encode(array_values($totals));

// Calcular los promedios por origen
$origenesData = [];
foreach ($responses as $res) {
    $origen = $res['origen'];
    if (!isset($origenesData[$origen])) {
        $origenesData[$origen] = [
            'count' => 0,
            'total' => 0
        ];
    }
    
    $origenesData[$origen]['count']++;
    $origenesData[$origen]['total'] += ($res['sabor'] + $res['aroma'] + $res['cuerpo'] + $res['acidez'] + 
                                       $res['amargor'] + $res['balance'] + $res['intensidad'] + 
                                       $res['postgusto'] + $res['sostenibilidad']) / 9;
}

// Calcular la mejor marca
$marcasData = [];
foreach ($responses as $res) {
    $marca = $res['marca'];
    if (!isset($marcasData[$marca])) {
        $marcasData[$marca] = [
            'count' => 0,
            'total' => 0
        ];
    }
    
    $marcasData[$marca]['count']++;
    $marcasData[$marca]['total'] += ($res['sabor'] + $res['aroma'] + $res['cuerpo'] + $res['acidez'] + 
                                    $res['amargor'] + $res['balance'] + $res['intensidad'] + 
                                    $res['postgusto'] + $res['sostenibilidad']) / 9;
}

$bestMarca = '';
$bestMarcaScore = 0;

foreach ($marcasData as $marca => $data) {
    $avgScore = $data['total'] / $data['count'];
    if ($avgScore > $bestMarcaScore) {
        $bestMarcaScore = $avgScore;
        $bestMarca = $marca;
    }
}

$origenesLabels = [];
$origenesValues = [];

foreach ($origenesData as $origen => $data) {
    $origenesLabels[] = $origen;
    $origenesValues[] = round($data['total'] / $data['count'], 1);
}

$origenesLabelsJSON = json_encode($origenesLabels);
$origenesValuesJSON = json_encode($origenesValues);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Café Sumapaz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #5c2d91;
            --secondary: #6c757d;
            --dark: #343a40;
            --light: #f8f9fa;
            --accent: #e9ecef;
            --danger: #dc3545;
            --danger-light: #f8d7da;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        header {
            background-color: var(--primary);
            color: white;
            padding: 1rem 0;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            height: 50px;
        }
        
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .admin-badge {
            background-color: rgba(255, 255, 255, 0.2);
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 50px;
            margin-left: 5px;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .dashboard-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            font-size: 1.8rem;
        }

        .dashboard-actions {
            display: flex;
            gap: 1rem;
        }
        
        .logout-btn, .export-btn {
            background-color: var(--accent);
            color: var(--primary);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .logout-btn:hover, .export-btn:hover {
            background-color: var(--secondary);
            color: white;
        }
        
        .export-btn {
            background-color: #28a745;
            color: white;
        }
        
        .export-btn:hover {
            background-color: #218838;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 2rem;
            max-width: 100%;
            height: 300px; /* Altura fija más pequeña */
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .chart-title {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .stats-title {
            font-weight: 600;
            color: var(--primary);
        }
        
        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .stats-info {
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        .responses-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .responses-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--accent);
        }
        
        .responses-title {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .response-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid var(--accent);
            transition: all 0.3s ease;
        }
        
        .response-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        
        .response-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--accent);
        }
        
        .response-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .response-user-icon {
            width: 30px;
            height: 30px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .response-user-name {
            font-weight: 600;
        }
        
        .response-id {
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        .response-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.2rem;
        }
        
        .response-date {
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        .response-ratings {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.8rem;
            margin-bottom: 1rem;
        }
        
        .rating-item {
            display: flex;
            flex-direction: column;
        }
        
        .rating-label {
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        .rating-value {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .rating-value span {
            font-weight: 600;
        }
        
        .rating-bar {
            height: 6px;
            background-color: var(--accent);
            border-radius: 50px;
            flex-grow: 1;
            position: relative;
            overflow: hidden;
        }
        
        .rating-bar::after {
            content: "";
            position: absolute;
            height: 100%;
            background-color: var(--primary);
            border-radius: 50px;
            left: 0;
            top: 0;
        }
        
        .rating-bar[data-rating="1"]::after { width: 10%; }
        .rating-bar[data-rating="2"]::after { width: 20%; }
        .rating-bar[data-rating="3"]::after { width: 30%; }
        .rating-bar[data-rating="4"]::after { width: 40%; }
        .rating-bar[data-rating="5"]::after { width: 50%; }
        .rating-bar[data-rating="6"]::after { width: 60%; }
        .rating-bar[data-rating="7"]::after { width: 70%; }
        .rating-bar[data-rating="8"]::after { width: 80%; }
        .rating-bar[data-rating="9"]::after { width: 90%; }
        .rating-bar[data-rating="10"]::after { width: 100%; }
        
        .response-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: var(--light);
            border-radius: 8px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: var(--secondary);
            font-weight: 500;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .response-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .delete-btn {
            background-color: var(--danger-light);
            color: var(--danger);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .delete-btn:hover {
            background-color: var(--danger);
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .page-btn {
            background-color: white;
            border: 1px solid var(--accent);
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .page-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .page-btn:hover:not(.active) {
            background-color: var(--accent);
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
        }
        
        .filter-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .filter-select {
            padding: 0.5rem;
            border-radius: 8px;
            border: 1px solid var(--accent);
            background-color: white;
        }
        
        .filter-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            background-color: #4a2475;
        }
        
        @media (max-width: 768px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .header-title {
                text-align: center;
            }
            
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .response-ratings {
                grid-template-columns: 1fr 1fr;
            }
            
            .response-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .filter-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dashboard-actions {
                flex-direction: column;
            }
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
            <div class="header-title">
                <img src="Logo.png" alt="Logo Café Sumapaz" class="logo">
                <h1>Panel de Administración</h1>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle fa-2x" style="color: white;"></i>
                <div>
                    <div><?php echo htmlspecialchars($_SESSION['user_name']); ?> <span class="admin-badge">Admin</span></div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h2 class="dashboard-title">Resumen de Calificaciones</h2>
            <div class="dashboard-actions">
                <a href="export.php" class="export-btn">
                    <i class="fas fa-download"></i> Exportar a CSV
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>

        <div class="charts-row">
            <div class="stats-card">
                <div class="stats-title">Total de Calificaciones</div>
                <div class="stats-value"><?php echo $count; ?></div>
                <div class="stats-info">Evaluaciones registradas</div>
            </div>

            <div class="stats-card">
                <div class="stats-title">Mejor Característica</div>
                <?php 
                    $bestCat = array_keys($totals, max($totals))[0];
                    $catNames = [
                        'sabor' => 'Sabor', 
                        'aroma' => 'Aroma', 
                        'cuerpo' => 'Cuerpo', 
                        'acidez' => 'Acidez', 
                        'amargor' => 'Amargor', 
                        'balance' => 'Balance', 
                        'intensidad' => 'Intensidad', 
                        'postgusto' => 'Postgusto', 
                        'sostenibilidad' => 'Sostenibilidad'
                    ];
                ?>
                <div class="stats-value"><?php echo $catNames[$bestCat]; ?></div>
                <div class="stats-info">Calificación: <?php echo max($totals); ?>/10</div>
            </div>

            <div class="stats-card">
                <div class="stats-title">Mejor Marca</div>
                <div class="stats-value"><?php echo htmlspecialchars($bestMarca); ?></div>
                <div class="stats-info">Calificación: <?php echo round($bestMarcaScore, 1); ?>/10</div>
            </div>

            <div class="stats-card">
                <div class="stats-title">Orígenes Evaluados</div>
                <div class="stats-value"><?php echo count($origenesLabels); ?></div>
                <div class="stats-info">Variedades de café</div>
            </div>

            <div class="stats-card">
                <div class="stats-title">Marcas Evaluadas</div>
                <div class="stats-value"><?php echo count($marcasData); ?></div>
                <div class="stats-info">Diferentes marcas</div>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Calificaciones Promedio por Característica</h3>
                </div>
                <canvas id="ratingsChart"></canvas>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Calificaciones por Origen</h3>
                </div>
                <canvas id="origenesChart"></canvas>
            </div>
            
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Calificaciones por Marca</h3>
                </div>
                <canvas id="marcasChart"></canvas>
            </div>
        </div>

        <div class="responses-container">
            <div class="responses-header">
                <h3 class="responses-title">Listado de Calificaciones</h3>
                <div class="filter-controls">
                    <select id="filterType" class="filter-select">
                        <option value="all">Todos</option>
                        <option value="origen">Filtrar por Origen</option>
                        <option value="marca">Filtrar por Marca</option>
                    </select>
                    
                    <select id="filterValue" class="filter-select" disabled>
                        <option value="all">Seleccionar...</option>
                    </select>
                    
                    <button id="applyFilter" class="filter-btn">Aplicar</button>
                </div>
                <div>Total: <span id="filteredCount"><?php echo $count; ?></span> evaluaciones</div>
            </div>
            
            <div id="responsesList">
            <?php foreach ($responses as $res): ?>
                <div class="response-card" data-origen="<?php echo htmlspecialchars($res['origen']); ?>" data-marca="<?php echo htmlspecialchars($res['marca']); ?>">
                    <div class="response-header">
                        <div class="response-user">
                            <div class="response-user-icon">
                                <?php echo substr(htmlspecialchars($res['nombre']), 0, 1); ?>
                            </div>
                            <div class="response-user-name">
                                <?php echo htmlspecialchars($res['nombre']); ?>
                            </div>
                        </div>
                        <div class="response-info">
                            <div class="response-id">ID: <?php echo $res['id']; ?></div>
                            <div class="response-date">Fecha: <?php echo date('d/m/Y H:i', strtotime($res['created_at'])); ?></div>
                        </div>
                    </div>

                    <div class="response-ratings">
                        <div class="rating-item">
                            <div class="rating-label">Sabor</div>
                            <div class="rating-value">
                                <span><?php echo $res['sabor']; ?></span>
                                <div class="rating-bar" data-rating="<?php echo $res['sabor']; ?>"></div>
                            </div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">Aroma</div>
                            <div class="rating-value">
                                <span><?php echo $res['aroma']; ?></span>
                                <div class="rating-bar" data-rating="<?php echo $res['aroma']; ?>"></div>
                            </div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">Cuerpo</div>
                            <div class="rating-value">
                                <span><?php echo $res['cuerpo']; ?></span>
                                <div class="rating-bar" data-rating="<?php echo $res['cuerpo']; ?>"></div>
                            </div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">Acidez</div>
                            <div class="rating-value">
                                <span><?php echo $res['acidez']; ?></span>
                                <div class="rating-bar" data-rating="<?php echo $res['acidez']; ?>"></div>
                            </div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">Amargor</div>
                            <div class="rating-value">
                                <span><?php echo $res['amargor']; ?></span>
                                <div class="rating-bar" data-rating="<?php echo $res['amargor']; ?>"></div>
                            </div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">Balance</div>
                            <div class="rating-value">
                                <span><?php echo $res['balance']; ?></span>
                                <div class="rating-bar" data-rating="<?php echo $res['balance']; ?>"></div>
                            </div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">Intensidad</div>
                            <div class="rating-value">
                                <span><?php echo $res['intensidad']; ?></span>
                                <div class="rating-bar" data-rating="<?php echo $res['intensidad']; ?>"></div>
                            </div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">Postgusto</div>
                            <div class="rating-value">
                                <span><?php echo $res['postgusto']; ?></span>
                                <div class="rating-bar" data-rating="<?php echo $res['postgusto']; ?>"></div>
                            </div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">Sostenibilidad</div>
                            <div class="rating-value">
                                <span><?php echo $res['sostenibilidad']; ?></span>
                                <div class="rating-bar" data-rating="<?php echo $res['sostenibilidad']; ?>"></div>
                            </div>
                        </div>
                    </div>

                    <div class="response-details">
                        <div class="detail-item">
                            <div class="detail-label">Origen</div>
                            <div class="detail-value"><?php echo htmlspecialchars($res['origen']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Marca</div>
                            <div class="detail-value"><?php echo htmlspecialchars($res['marca']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Comentario</div>
                            <div class="detail-value"><?php echo htmlspecialchars($res['comentario']); ?></div>
                        </div>
                    </div>

                    <div class="response-actions">
                        <a href="admin.php?delete=<?php echo $res['id']; ?>" class="delete-btn" onclick="return confirm('¿Estás seguro de eliminar esta calificación?');">
                            <i class="fas fa-trash"></i> Eliminar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> Café Sumapaz - Todos los derechos reservados</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de calificaciones por característica
            const ctx = document.getElementById('ratingsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo $chartLabels; ?>,
                    datasets: [{
                        label: 'Calificación Promedio',
                        data: <?php echo $chartData; ?>,
                        backgroundColor: 'rgba(92, 45, 145, 0.7)',
                        borderColor: 'rgba(92, 45, 145, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    family: "'Poppins', sans-serif",
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: 'rgba(92, 45, 145, 0.3)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            titleFont: {
                                family: "'Poppins', sans-serif",
                                weight: '600'
                            },
                            bodyFont: {
                                family: "'Poppins', sans-serif"
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                borderDash: [5, 5]
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        }
                    }
                }
            });
            
            // Gráfico de calificaciones por origen
            const origenesCtx = document.getElementById('origenesChart').getContext('2d');
            new Chart(origenesCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo $origenesLabelsJSON; ?>,
                    datasets: [{
                        label: 'Calificación Promedio',
                        data: <?php echo $origenesValuesJSON; ?>,
                        backgroundColor: 'rgba(92, 45, 145, 0.7)',
                        borderColor: 'rgba(92, 45, 145, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    family: "'Poppins', sans-serif",
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: 'rgba(92, 45, 145, 0.3)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            titleFont: {
                                family: "'Poppins', sans-serif",
                                weight: '600'
                            },
                            bodyFont: {
                                family: "'Poppins', sans-serif"
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                borderDash: [5, 5]
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        }
                    }
                }
            });
            
            // Gráfico de calificaciones por marca
            const marcasCtx = document.getElementById('marcasChart').getContext('2d');
            new Chart(marcasCtx, {
                type: 'line', // Cambiado de 'bar' a 'line'
                data: {
                    labels: <?php 
                        $marcasLabelsArray = array_keys($marcasData);
                        echo json_encode($marcasLabelsArray); 
                    ?>,
                    datasets: [{
                        label: 'Calificación Promedio',
                        data: <?php 
                            $marcasValuesArray = [];
                            foreach ($marcasData as $marca => $data) {
                                $marcasValuesArray[] = round($data['total'] / $data['count'], 1);
                            }
                            echo json_encode($marcasValuesArray); 
                        ?>,
                        backgroundColor: 'rgba(92, 45, 145, 0.1)',
                        borderColor: 'rgba(92, 45, 145, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(92, 45, 145, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        tension: 0.3, // Suaviza la línea
                        fill: true // Rellena el área bajo la línea
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    family: "'Poppins', sans-serif",
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: 'rgba(92, 45, 145, 0.3)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            titleFont: {
                                family: "'Poppins', sans-serif",
                                weight: '600'
                            },
                            bodyFont: {
                                family: "'Poppins', sans-serif"
                            },
                            callbacks: {
                                label: function(context) {
                                    return 'Calificación: ' + context.parsed.y + '/10';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                borderDash: [5, 5]
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterType = document.getElementById('filterType');
        const filterValue = document.getElementById('filterValue');
        const applyFilter = document.getElementById('applyFilter');
        const responseCards = document.querySelectorAll('.response-card');
        const filteredCount = document.getElementById('filteredCount');
        
        // Obtener valores únicos de orígenes y marcas
        const origenes = [...new Set(Array.from(responseCards).map(card => card.getAttribute('data-origen')))].filter(Boolean);
        const marcas = [...new Set(Array.from(responseCards).map(card => card.getAttribute('data-marca')))].filter(Boolean);
        
        filterType.addEventListener('change', function() {
            filterValue.innerHTML = '<option value="all">Seleccionar...</option>';
            
            if (this.value === 'origen') {
                origenes.forEach(origen => {
                    const option = document.createElement('option');
                    option.value = origen;
                    option.textContent = origen;
                    filterValue.appendChild(option);
                });
                filterValue.disabled = false;
            } else if (this.value === 'marca') {
                marcas.forEach(marca => {
                    const option = document.createElement('option');
                    option.value = marca;
                    option.textContent = marca;
                    filterValue.appendChild(option);
                });
                filterValue.disabled = false;
            } else {
                filterValue.disabled = true;
            }
        });
        
        applyFilter.addEventListener('click', function() {
            let visibleCount = 0;
            
            responseCards.forEach(card => {
                if (filterType.value === 'all' || 
                    (filterType.value === 'origen' && (filterValue.value === 'all' || card.getAttribute('data-origen') === filterValue.value)) ||
                    (filterType.value === 'marca' && (filterValue.value === 'all' || card.getAttribute('data-marca') === filterValue.value))) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            filteredCount.textContent = visibleCount;
        });
    });
</script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de calificaciones por característica
            const ctx = document.getElementById('ratingsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo $chartLabels; ?>,
                    datasets: [{
                        label: 'Calificación Promedio',
                        data: <?php echo $chartData; ?>,
                        backgroundColor: 'rgba(92, 45, 145, 0.7)',
                        borderColor: 'rgba(92, 45, 145, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    family: "'Poppins', sans-serif",
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: 'rgba(92, 45, 145, 0.3)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            titleFont: {
                                family: "'Poppins', sans-serif",
                                weight: '600'
                            },
                            bodyFont: {
                                family: "'Poppins', sans-serif"
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                borderDash: [5, 5]
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        }
                    }
                }
            });
            
            // Gráfico de calificaciones por origen
            const origenesCtx = document.getElementById('origenesChart').getContext('2d');
            new Chart(origenesCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo $origenesLabelsJSON; ?>,
                    datasets: [{
                        label: 'Calificación Promedio',
                        data: <?php echo $origenesValuesJSON; ?>,
                        backgroundColor: 'rgba(92, 45, 145, 0.7)',
                        borderColor: 'rgba(92, 45, 145, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    family: "'Poppins', sans-serif",
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: 'rgba(92, 45, 145, 0.3)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            titleFont: {
                                family: "'Poppins', sans-serif",
                                weight: '600'
                            },
                            bodyFont: {
                                family: "'Poppins', sans-serif"
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                borderDash: [5, 5]
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        }
                    }
                }
            });
            
            // Gráfico de calificaciones por marca
            const marcasCtx = document.getElementById('marcasChart').getContext('2d');
            new Chart(marcasCtx, {
                type: 'line', // Cambiado de 'bar' a 'line'
                data: {
                    labels: <?php 
                        $marcasLabelsArray = array_keys($marcasData);
                        echo json_encode($marcasLabelsArray); 
                    ?>,
                    datasets: [{
                        label: 'Calificación Promedio',
                        data: <?php 
                            $marcasValuesArray = [];
                            foreach ($marcasData as $marca => $data) {
                                $marcasValuesArray[] = round($data['total'] / $data['count'], 1);
                            }
                            echo json_encode($marcasValuesArray); 
                        ?>,
                        backgroundColor: 'rgba(92, 45, 145, 0.1)',
                        borderColor: 'rgba(92, 45, 145, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(92, 45, 145, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        tension: 0.3, // Suaviza la línea
                        fill: true // Rellena el área bajo la línea
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    family: "'Poppins', sans-serif",
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: 'rgba(92, 45, 145, 0.3)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            titleFont: {
                                family: "'Poppins', sans-serif",
                                weight: '600'
                            },
                            bodyFont: {
                                family: "'Poppins', sans-serif"
                            },
                            callbacks: {
                                label: function(context) {
                                    return 'Calificación: ' + context.parsed.y + '/10';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                borderDash: [5, 5]
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>