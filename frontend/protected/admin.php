<?php
require_once __DIR__ . '/../../db/class/sessions.php';
require_once __DIR__ . '/../../db/class/database.php'; // Include Database class

// Secure the page: check if admin is logged in
if (!SessionManager::validateAdminSession()) {
    header('Location: login.php?error=auth'); // Redirect to login if not authenticated
    exit;
}

// Get admin info from session
$adminUsername = $_SESSION['admin_username'] ?? 'Admin';
$adminId = $_SESSION['admin_id'] ?? 0;

// --- Fetch Dashboard Stats --- 
$db = new Database();
$conn = $db->getConnection();

$totalVillas = 0;
$openTickets = 0;
$soldVillas = 0;

if ($conn) {
    try {
        // Count total villas
        $villasStmt = $conn->query("SELECT COUNT(*) FROM villas");
        $totalVillas = $villasStmt->fetchColumn();

        // Count open tickets
        $ticketsStmt = $conn->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'");
        $openTickets = $ticketsStmt->fetchColumn();

        // Count sold villas (can be refined for recent sales if needed)
        $soldStmt = $conn->query("SELECT COUNT(*) FROM villas WHERE verkocht = 1");
        $soldVillas = $soldStmt->fetchColumn();

    } catch (PDOException $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        // Handle error appropriately, maybe show default values or an error message
    } finally {
        $db->closeConnection($conn);
    }
}

?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vakantie Villas</title>
    <link rel="stylesheet" href="styles/admin_main.css"> <!-- Main admin styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/logo.png" alt="Logo" class="sidebar-logo">
                <h3>Vakantie Villas Beheer</h3>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="villas.php"><i class="fas fa-home"></i> Woningen Beheren</a></li>
                    <li><a href="tickets.php"><i class="fas fa-ticket-alt"></i> Informatieaanvragen</a></li>
                    <li><a href="options.php"><i class="fas fa-tags"></i> Opties Beheren</a></li> 
                    <!-- Add other sections like users, settings etc. if needed -->
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Uitloggen</a>
            </div>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Admin Dashboard</h1>
                <div class="admin-user">
                    <span>Welkom, <?= htmlspecialchars($adminUsername) ?></span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </header>

            <section class="dashboard-overview">
                <h2>Overzicht</h2>
                <div class="stats-container">
                    <div class="stat-card">
                        <i class="fas fa-home icon-villa"></i>
                        <div class="stat-info">
                            <span class="stat-number"><?= $totalVillas ?></span>
                            <span class="stat-label">Totaal Woningen</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-ticket-alt icon-ticket"></i>
                        <div class="stat-info">
                            <span class="stat-number"><?= $openTickets ?></span>
                            <span class="stat-label">Open Aanvragen</span>
                        </div>
                    </div>
                     <div class="stat-card">
                        <i class="fas fa-check-circle icon-sold"></i>
                        <div class="stat-info">
                            <span class="stat-number"><?= $soldVillas ?></span>
                            <span class="stat-label">Verkochte Woningen</span>
                        </div>
                    </div>
                    <!-- Add more relevant stats as needed -->
                </div>
            </section>

            <section class="quick-actions">
                <h2>Snelle Acties</h2>
                <div class="action-buttons">
                    <a href="edit_villa.php" class="action-btn add-villa"><i class="fas fa-plus-circle"></i> Nieuwe Woning Toevoegen</a>
                    <a href="tickets.php?status=open" class="action-btn view-tickets"><i class="fas fa-envelope-open-text"></i> Open Aanvragen Bekijken</a>
                    <a href="options.php" class="action-btn manage-options"><i class="fas fa-cogs"></i> Opties Beheren</a>
                </div>
            </section>

            <!-- Add more dashboard widgets/sections as needed -->
            <!-- e.g., Recent Activity, Pending Tasks, etc. -->

        </main>
    </div>

    <!-- Optional: Add JS for dynamic updates or interactions -->
    <!-- <script src="scripts/admin_dashboard.js"></script> -->
</body>

</html>