<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Villa Verkenner</title>
    <!-- <link rel="stylesheet" href="/frontend/assets/css/main.css"> -->
    <link rel="stylesheet" href="../protected/styles/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- SVG pattern definitions -->
    <svg width="0" height="0" style="position:absolute">
        <defs>
            <pattern id="villaPattern" patternUnits="userSpaceOnUse" width="20" height="20" patternTransform="rotate(45)">
                <rect width="20" height="20" fill="#eaf7fd"/>
                <circle cx="5" cy="5" r="1" fill="#3498db" fill-opacity="0.2"/>
                <circle cx="15" cy="15" r="1" fill="#3498db" fill-opacity="0.2"/>
            </pattern>
            
            <pattern id="ticketPattern" patternUnits="userSpaceOnUse" width="20" height="20">
                <rect width="20" height="20" fill="#e6f9ee"/>
                <line x1="0" y1="5" x2="20" y2="5" stroke="#27ae60" stroke-width="0.5" stroke-opacity="0.2"/>
                <line x1="0" y1="15" x2="20" y2="15" stroke="#27ae60" stroke-width="0.5" stroke-opacity="0.2"/>
            </pattern>
            
            <pattern id="monitorPattern" patternUnits="userSpaceOnUse" width="20" height="20">
                <rect width="20" height="20" fill="#f0f0f0"/>
                <rect x="5" y="5" width="10" height="10" fill="#777" fill-opacity="0.1"/>
            </pattern>
            
            <pattern id="serverPattern" patternUnits="userSpaceOnUse" width="20" height="20" patternTransform="rotate(45)">
                <rect width="20" height="20" fill="#f8f8f8"/>
                <line x1="0" y1="10" x2="20" y2="10" stroke="#888" stroke-width="0.5" stroke-opacity="0.2"/>
                <line x1="10" y1="0" x2="10" y2="20" stroke="#888" stroke-width="0.5" stroke-opacity="0.2"/>
            </pattern>
            
            <pattern id="villaCardPattern" patternUnits="userSpaceOnUse" width="50" height="50">
                <rect width="50" height="50" fill="#3498db" fill-opacity="0.1"/>
                <path d="M25,10 L40,25 L35,25 L35,40 L15,40 L15,25 L10,25 Z" fill="#3498db" fill-opacity="0.2"/>
            </pattern>
            
            <pattern id="ticketCardPattern" patternUnits="userSpaceOnUse" width="50" height="50">
                <rect width="50" height="50" fill="#27ae60" fill-opacity="0.1"/>
                <rect x="15" y="15" width="20" height="25" rx="2" ry="2" fill="#27ae60" fill-opacity="0.2"/>
                <line x1="20" y1="25" x2="30" y2="25" stroke="#27ae60" stroke-width="1.5" stroke-opacity="0.3"/>
                <line x1="20" y1="30" x2="30" y2="30" stroke="#27ae60" stroke-width="1.5" stroke-opacity="0.3"/>
            </pattern>
        </defs>
    </svg>

    <?php include 'components/header.php'; ?>
    
    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Admin Dashboard</h1>
            <p>Welkom terug, Admin!</p>
        </div>
        
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-icon villa-icon">
                    <svg viewBox="0 0 24 24">
                        <rect width="24" height="24" fill="url(#villaPattern)"/>
                        <path d="M10,3L3,10h2v9h5v-5h4v5h5v-9h2L14,3L14,4.5h-4V3Z" fill="#3498db"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">0</p>
                    <p class="stat-label">Villa's</p>
                </div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon ticket-icon">
                    <svg viewBox="0 0 24 24">
                        <rect width="24" height="24" fill="url(#ticketPattern)"/>
                        <path d="M20,12A2,2,0,0,0,22,10V6A2,2,0,0,0,20,4H4A2,2,0,0,0,2,6v4a2,2,0,0,0,2,2,2,2,0,0,1,0,4,2,2,0,0,0-2,2v4a2,2,0,0,0,2,2H20a2,2,0,0,0,2-2V18a2,2,0,0,0-2-2,2,2,0,0,1,0-4Z" fill="#27ae60"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">0</p>
                    <p class="stat-label">Open tickets</p>
                </div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon monitor-icon">
                    <svg viewBox="0 0 24 24">
                        <rect width="24" height="24" fill="url(#monitorPattern)"/>
                        <path d="M21,16H3V4H21M21,2H3C1.89,2 1,2.89 1,4V16A2,2 0 0,0 3,18H10V20H8V22H16V20H14V18H21A2,2 0 0,0 23,16V4C23,2.89 22.1,2 21,2Z" fill="#777"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">0</p>
                    <p class="stat-label">Bezoekers</p>
                </div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon server-icon">
                    <svg viewBox="0 0 24 24">
                        <rect width="24" height="24" fill="url(#serverPattern)"/>
                        <path d="M4,1H20A1,1 0 0,1 21,2V6A1,1 0 0,1 20,7H4A1,1 0 0,1 3,6V2A1,1 0 0,1 4,1M4,9H20A1,1 0 0,1 21,10V14A1,1 0 0,1 20,15H4A1,1 0 0,1 3,14V10A1,1 0 0,1 4,9M4,17H20A1,1 0 0,1 21,18V22A1,1 0 0,1 20,23H4A1,1 0 0,1 3,22V18A1,1 0 0,1 4,17M9,5H10V3H9V5M9,13H10V11H9V13M9,21H10V19H9V21M5,3V5H7V3H5M5,11V13H7V11H5M5,19V21H7V19H5Z" fill="#777"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">100%</p>
                    <p class="stat-label">Uptime</p>
                </div>
            </div>
        </div>
        
        <div class="management-cards">
            <div class="management-card villa-management">
                <div class="card-pattern" style="background: url(#villaCardPattern)"></div>
                <div class="card-content">
                    <h2>Villa Beheer</h2>
                    <p>Beheer alle villas, beoordelingen en details</p>
                    <a href="../protected/villas.php" class="card-btn">Beheren</a>
                </div>
            </div>
            
            <div class="management-card ticket-management">
                <div class="card-pattern" style="background: url(#ticketCardPattern)"></div>
                <div class="card-content">
                    <h2>Ticket Beheer</h2>
                    <p>Bekijk en beantwoord vragen en problemen van gebruikers</p>
                    <a href="../protected/tickets.php" class="card-btn">Bekijken</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../protected/utils/admin.js"></script>
</body>

</html>