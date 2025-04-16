<?php
// Add these lines at the very beginning
require_once __DIR__ . '/../../db/class/sessions.php';
require_once __DIR__ . '/../../db/class/database.php';

// Secure the page
if (!SessionManager::validateAdminSession()) {
    header('Location: login.php?error=auth');
    exit;
}

// Get real ticket data from database
$db = new Database();
$conn = $db->getConnection();
$tickets = [];

// Fetch tickets with real data
if ($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM tickets ORDER BY datum_aangemaakt DESC");
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count statistics
        $stmtOpen = $conn->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'");
        $openTickets = $stmtOpen->fetchColumn();
        
        $stmtClosed = $conn->query("SELECT COUNT(*) FROM tickets WHERE status = 'gesloten'");
        $closedTickets = $stmtClosed->fetchColumn();
        
        $stmtHighPriority = $conn->query("SELECT COUNT(*) FROM tickets WHERE prioriteit = 'hoog'");
        $highPriorityTickets = $stmtHighPriority->fetchColumn();
        
        // Calculate average response time (if you have the data)
        $avgResponseTime = "3.2 uur"; // Replace with actual calculation if available
        
    } catch (PDOException $e) {
        error_log('Error fetching tickets: ' . $e->getMessage());
    } finally {
        $db->closeConnection($conn);
    }
} else {
    // If no database connection, we'll display default values
    $openTickets = 0;
    $closedTickets = 0;
    $highPriorityTickets = 0;
    $avgResponseTime = "N/A";
}

?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets Beheer - Villa Verkenner</title>
    <link rel="stylesheet" href="styles/admin_main.css">
    <link rel="stylesheet" href="styles/tickets.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="admin-container">
        <?php include 'components/sidebar.php'; ?>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1>Tickets beheer</h1>
                <p class="subtitle">Beheer en reageer op tickets van gebruikers</p>
            </header>
            
            <div class="filter-section">
                <div class="filter-buttons">
                    <button class="status-filter active" data-status="all">Alle tickets</button>
                    <button class="status-filter" data-status="open">Open</button>
                    <button class="status-filter" data-status="in behandeling">In behandeling</button>
                    <button class="status-filter" data-status="gesloten">Gesloten</button>
                </div>
                
                <div class="search-box">
                    <input type="text" id="ticketsSearch" placeholder="Zoek op onderwerp of gebruiker...">
                    <span class="search-icon">&#128269;</span>
                </div>
            </div>
            
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon open-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Open tickets</h3>
                        <p><?= $openTickets ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon closed-icon">
                        <i class="fas fa-folder-minus"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Gesloten tickets</h3>
                        <p><?= $closedTickets ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon priority-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Hoge prioriteit</h3>
                        <p><?= $highPriorityTickets ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon response-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Gem. responstijd</h3>
                        <p><?= $avgResponseTime ?></p>
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
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="7">Geen tickets gevonden.</td>
                            </tr>
                            
                            <!-- Show sample entries if no tickets exist in the database -->
                            <tr data-status="open">
                                <td>#1001</td>
                                <td>Probleem met boeking</td>
                                <td>Jan Jansen</td>
                                <td><span class="ticket-status status-open">Open</span></td>
                                <td><span class="ticket-priority priority-high">Hoog</span></td>
                                <td>15-03-2023 14:30</td>
                                <td><a href="ticket_detail.php?id=1001" class="action-btn view-btn">Bekijken</a></td>
                            </tr>
                            <tr data-status="in behandeling">
                                <td>#1002</td>
                                <td>Vraag over villa faciliteiten</td>
                                <td>Marie de Vries</td>
                                <td><span class="ticket-status status-in-progress">In behandeling</span></td>
                                <td><span class="ticket-priority priority-medium">Gemiddeld</span></td>
                                <td>14-03-2023 10:15</td>
                                <td><a href="ticket_detail.php?id=1002" class="action-btn view-btn">Bekijken</a></td>
                            </tr>
                            <tr data-status="gesloten">
                                <td>#1003</td>
                                <td>Informatie over betaling</td>
                                <td>Peter Bakker</td>
                                <td><span class="ticket-status status-gesloten">Gesloten</span></td>
                                <td><span class="ticket-priority priority-low">Laag</span></td>
                                <td>13-03-2023 09:45</td>
                                <td><a href="ticket_detail.php?id=1003" class="action-btn view-btn">Bekijken</a></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <?php 
                                // Normalize status to match button data-status values
                                $normalizedStatus = $ticket['status'];
                                if ($normalizedStatus === 'in-progress') {
                                    $normalizedStatus = 'in behandeling';
                                }
                                ?>
                                <tr data-status="<?= htmlspecialchars($normalizedStatus) ?>">
                                    <td>#<?= htmlspecialchars($ticket['id']) ?></td>
                                    <td><?= htmlspecialchars($ticket['onderwerp']) ?></td>
                                    <td><?= htmlspecialchars($ticket['gebruiker_naam']) ?></td>
                                    <td>
                                        <span class="ticket-status status-<?= htmlspecialchars($ticket['status']) ?>">
                                            <?= ucfirst(htmlspecialchars($ticket['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="ticket-priority priority-<?= ($ticket['prioriteit'] == 'laag' ? 'low' : ($ticket['prioriteit'] == 'gemiddeld' ? 'medium' : 'high')) ?>">
                                            <?= ucfirst(htmlspecialchars($ticket['prioriteit'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d-m-Y H:i', strtotime($ticket['datum_aangemaakt'])) ?></td>
                                    <td>
                                        <a href="ticket_detail.php?id=<?= $ticket['id'] ?>" class="action-btn view-btn">Bekijken</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script src="utils/tickets.js"></script>
    
    <!-- Backup script to ensure filters work -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Backup filtering functionality
            const statusFilters = document.querySelectorAll('.status-filter');
            const ticketsSearchBox = document.getElementById('ticketsSearch');
            
            function filterTickets() {
                const activeFilter = document.querySelector('.status-filter.active');
                const status = activeFilter ? activeFilter.getAttribute('data-status') : 'all';
                const searchText = ticketsSearchBox ? ticketsSearchBox.value.toLowerCase() : '';
                
                document.querySelectorAll('.tickets-table tbody tr').forEach(row => {
                    if (row.cells && row.cells.length === 1 && row.cells[0].colSpan === 7) return;
                    
                    const rowStatus = row.getAttribute('data-status') || '';
                    const rowText = row.textContent.toLowerCase();
                    
                    let show = true;
                    
                    // Status filtering
                    if (status !== 'all' && rowStatus !== status) {
                        if (!(status === 'in behandeling' && (rowStatus === 'in-progress' || rowStatus === 'in behandeling'))) {
                            show = false;
                        }
                    }
                    
                    // Search filtering
                    if (searchText && !rowText.includes(searchText)) {
                        show = false;
                    }
                    
                    row.style.display = show ? '' : 'none';
                });
            }
            
            // Set up filter button clicks
            statusFilters.forEach(btn => {
                btn.addEventListener('click', function() {
                    statusFilters.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    filterTickets();
                });
            });
            
            // Set up search input
            if (ticketsSearchBox) {
                ticketsSearchBox.addEventListener('input', filterTickets);
            }
            
            // Initial filtering
            filterTickets();
        });
    </script>
</body>

</html> 