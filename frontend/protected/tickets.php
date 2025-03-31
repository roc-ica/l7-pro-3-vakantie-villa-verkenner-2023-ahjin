<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets Beheer - Villa Verkenner</title>
    <link rel="stylesheet" href="../protected/styles/tickets.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- SVG pattern definitions -->
    <svg width="0" height="0" style="position:absolute">
        <defs>
            <pattern id="openTicketPattern" patternUnits="userSpaceOnUse" width="20" height="20" patternTransform="rotate(45)">
                <rect width="20" height="20" fill="#e6f7ff"/>
                <line x1="0" y1="5" x2="20" y2="5" stroke="#3498db" stroke-width="0.5" stroke-opacity="0.2"/>
                <line x1="0" y1="15" x2="20" y2="15" stroke="#3498db" stroke-width="0.5" stroke-opacity="0.2"/>
            </pattern>
            
            <pattern id="closedTicketPattern" patternUnits="userSpaceOnUse" width="20" height="20" patternTransform="rotate(45)">
                <rect width="20" height="20" fill="#e6f9ee"/>
                <circle cx="5" cy="5" r="1" fill="#27ae60" fill-opacity="0.2"/>
                <circle cx="15" cy="15" r="1" fill="#27ae60" fill-opacity="0.2"/>
                <circle cx="5" cy="15" r="1" fill="#27ae60" fill-opacity="0.2"/>
                <circle cx="15" cy="5" r="1" fill="#27ae60" fill-opacity="0.2"/>
            </pattern>
            
            <pattern id="warningPattern" patternUnits="userSpaceOnUse" width="20" height="20">
                <rect width="20" height="20" fill="#fff5e6"/>
                <path d="M10,3 L18,18 H2 Z" fill="#e67e22" fill-opacity="0.15"/>
            </pattern>
            
            <pattern id="clockPattern" patternUnits="userSpaceOnUse" width="20" height="20">
                <rect width="20" height="20" fill="#f0f0f0"/>
                <circle cx="10" cy="10" r="7" fill="none" stroke="#777" stroke-opacity="0.2" stroke-width="0.5"/>
                <line x1="10" y1="10" x2="10" y2="5" stroke="#777" stroke-opacity="0.3" stroke-width="0.5"/>
                <line x1="10" y1="10" x2="14" y2="10" stroke="#777" stroke-opacity="0.3" stroke-width="0.5"/>
            </pattern>
        </defs>
    </svg>

    <?php include 'components/header.php'; ?>
    
    <div class="container">
        <div class="titel-overzicht">
            <h1>Tickets beheer</h1>
            <p class="subtitle">Beheer en reageer op tickets van gebruikers</p>
        </div>
        
        <div class="filter-section">
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">Alle tickets</button>
                <button class="filter-btn" data-filter="open">Open</button>
                <button class="filter-btn" data-filter="in-progress">In behandeling</button>
                <button class="filter-btn" data-filter="closed">Gesloten</button>
            </div>
            
            <div class="search-box">
                <input type="text" placeholder="Zoek op onderwerp of gebruiker...">
                <span class="search-icon">&#128269;</span>
            </div>
        </div>
        
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon open-icon">
                    <svg viewBox="0 0 24 24">
                        <rect width="24" height="24" fill="url(#openTicketPattern)" />
                        <path d="M20,6h-8l-2-2H4C2.9,4,2,4.9,2,6v12c0,1.1,0.9,2,2,2h16c1.1,0,2-0.9,2-2V8C22,6.9,21.1,6,20,6z" fill="#3498db"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Open tickets</h3>
                    <p>12</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon closed-icon">
                    <svg viewBox="0 0 24 24">
                        <rect width="24" height="24" fill="url(#closedTicketPattern)" />
                        <path d="M18,6h-2c0-2.21-1.79-4-4-4S8,3.79,8,6H6C4.9,6,4,6.9,4,8v12c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2V8C20,6.9,19.1,6,18,6z M12,4c1.1,0,2,0.9,2,2h-4C10,4.9,10.9,4,12,4z M16,16H8v-2h8V16z M16,12H8v-2h8V12z" fill="#27ae60"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Gesloten tickets</h3>
                    <p>28</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon priority-icon">
                    <svg viewBox="0 0 24 24">
                        <rect width="24" height="24" fill="url(#warningPattern)" />
                        <path d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M12,18c-0.83,0-1.5-0.67-1.5-1.5 S11.17,15,12,15s1.5,0.67,1.5,1.5S12.83,18,12,18z M13,13h-2V7h2V13z" fill="#e67e22"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Hoge prioriteit</h3>
                    <p>5</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon response-icon">
                    <svg viewBox="0 0 24 24">
                        <rect width="24" height="24" fill="url(#clockPattern)" />
                        <path d="M11.99,2C6.47,2,2,6.48,2,12s4.47,10,9.99,10C17.52,22,22,17.52,22,12S17.52,2,11.99,2z M12,20c-4.42,0-8-3.58-8-8 s3.58-8,8-8s8,3.58,8,8S16.42,20,12,20z M12.5,7H11v6l5.25,3.15l0.75-1.23l-4.5-2.67V7z" fill="#777"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Gem. responstijd</h3>
                    <p>3.2 uur</p>
                </div>
            </div>
        </div>
        
        <div class="tickets-container">
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Onderwerp</th>
                        <th>Gebruiker</th>
                        <th>Status</th>
                        <th>Prioriteit</th>
                        <th>Datum</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Hardcoded ticket data -->
                    <tr data-status="open">
                        <td>#1001</td>
                        <td>Probleem met boeking</td>
                        <td>Jan Jansen</td>
                        <td><span class="ticket-status status-open">Open</span></td>
                        <td><span class="ticket-priority priority-high">Hoog</span></td>
                        <td>15-03-2023 14:30</td>
                        <td><button class="action-btn view-btn" data-id="1001">Bekijken</button></td>
                    </tr>
                    <tr data-status="in-progress">
                        <td>#1002</td>
                        <td>Vraag over villa faciliteiten</td>
                        <td>Marie de Vries</td>
                        <td><span class="ticket-status status-in-progress">In behandeling</span></td>
                        <td><span class="ticket-priority priority-medium">Gemiddeld</span></td>
                        <td>14-03-2023 10:15</td>
                        <td><button class="action-btn view-btn" data-id="1002">Bekijken</button></td>
                    </tr>
                    <tr data-status="closed">
                        <td>#1003</td>
                        <td>Aanvraag terugbetaling</td>
                        <td>Pieter Bakker</td>
                        <td><span class="ticket-status status-closed">Gesloten</span></td>
                        <td><span class="ticket-priority priority-high">Hoog</span></td>
                        <td>12-03-2023 09:45</td>
                        <td><button class="action-btn view-btn" data-id="1003">Bekijken</button></td>
                    </tr>
                    <tr data-status="open">
                        <td>#1004</td>
                        <td>Klacht over schoonmaak</td>
                        <td>Sophie Visser</td>
                        <td><span class="ticket-status status-open">Open</span></td>
                        <td><span class="ticket-priority priority-medium">Gemiddeld</span></td>
                        <td>11-03-2023 16:20</td>
                        <td><button class="action-btn view-btn" data-id="1004">Bekijken</button></td>
                    </tr>
                    <tr data-status="closed">
                        <td>#1005</td>
                        <td>Vraag over wifi toegang</td>
                        <td>Thomas Groot</td>
                        <td><span class="ticket-status status-closed">Gesloten</span></td>
                        <td><span class="ticket-priority priority-low">Laag</span></td>
                        <td>10-03-2023 11:35</td>
                        <td><button class="action-btn view-btn" data-id="1005">Bekijken</button></td>
                    </tr>
                    <tr data-status="open">
                        <td>#1006</td>
                        <td>Probleem met website</td>
                        <td>Emma Smit</td>
                        <td><span class="ticket-status status-open">Open</span></td>
                        <td><span class="ticket-priority priority-high">Hoog</span></td>
                        <td>09-03-2023 14:50</td>
                        <td><button class="action-btn view-btn" data-id="1006">Bekijken</button></td>
                    </tr>
                    <tr data-status="in-progress">
                        <td>#1007</td>
                        <td>Vraag over check-in tijd</td>
                        <td>Lars van Dijk</td>
                        <td><span class="ticket-status status-in-progress">In behandeling</span></td>
                        <td><span class="ticket-priority priority-medium">Gemiddeld</span></td>
                        <td>08-03-2023 09:15</td>
                        <td><button class="action-btn view-btn" data-id="1007">Bekijken</button></td>
                    </tr>
                    <tr data-status="closed">
                        <td>#1008</td>
                        <td>Feedback over verblijf</td>
                        <td>Nina Jansen</td>
                        <td><span class="ticket-status status-closed">Gesloten</span></td>
                        <td><span class="ticket-priority priority-low">Laag</span></td>
                        <td>07-03-2023 17:20</td>
                        <td><button class="action-btn view-btn" data-id="1008">Bekijken</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="/frontend/protected/utils/tickets.js"></script>
</body>

</html> 