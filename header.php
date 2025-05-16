<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Sumapaz - Evaluaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* Estilos para la barra de navegación */
        .browser-nav {
            display: flex;
            align-items: center;
            background-color: #8BC34A;
            padding: 8px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .nav-buttons {
            display: flex;
            margin-right: 15px;
        }
        
        .nav-button {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            color: #555;
            background-color: #f0f0f0;
            cursor: pointer;
        }
        
        .search-bar {
            flex: 1;
            background-color: #fff;
            border-radius: 20px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
        }
        
        .search-icon {
            margin-right: 10px;
            color: #555;
        }
        
        .tab {
            display: flex;
            align-items: center;
            background-color: #9CCC65;
            padding: 8px 15px;
            border-radius: 5px 5px 0 0;
            margin-right: 10px;
        }
        
        .tab img {
            height: 20px;
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .browser-nav {
                flex-wrap: wrap;
            }
            
            .tab {
                margin-bottom: 8px;
            }
            
            .search-bar {
                width: 100%;
                margin-top: 8px;
            }
        }
    </style>
    
    <!-- Incluir los estilos existentes -->
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
        
        /* Resto de tus estilos existentes */
    </style>
</head>
<body>
    <!-- Barra de navegación estilo navegador web -->
    <div class="browser-nav">
        <div class="nav-buttons">
            <div class="nav-button">&#10094;</div>
            <div class="nav-button">&#10095;</div>
            <div class="nav-button">&#8635;</div>
        </div>
        <div class="tab">
            <img src="Logo.png" alt="Logo Café Sumapaz">
            <span>Café Sumapaz</span>
        </div>
        <div class="search-bar">
            <span class="search-icon">🔍</span>
            <span>Buscar en Google o escribir una URL</span>
        </div>
    </div>