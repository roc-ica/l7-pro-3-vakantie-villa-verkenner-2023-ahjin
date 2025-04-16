<?php
// Reusable sidebar component

// Get the current page name to set the active class
$currentPage = basename($_SERVER['PHP_SELF']);

// Count open tickets
$openTicketsCount = 0;
if (class_exists('Database')) {
    try {
        $ticketDb = new Database();
        $ticketConn = $ticketDb->getConnection();
        if ($ticketConn) {
            $ticketStmt = $ticketConn->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'");
            $openTicketsCount = $ticketStmt->fetchColumn();
            $ticketDb->closeConnection($ticketConn);
        }
    } catch (Exception $e) {
        // Silently handle the error - we don't want to break the sidebar
        error_log("Error counting tickets: " . $e->getMessage());
    }
}

?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="admin.php"><img src="../assets/img/logo.png" alt="Logo" class="sidebar-logo"></a>
        <h3>Vakantie Villas Beheer</h3>
    </div>
    <nav class="admin-nav">
        <ul>
            <li>
                <a href="admin.php" class="<?= ($currentPage == 'admin.php') ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="villas.php" class="<?= ($currentPage == 'villas.php' || $currentPage == 'edit_villa.php') ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Woningen Beheren
                </a>
            </li>
            <li>
                <a href="tickets.php" class="<?= ($currentPage == 'tickets.php') ? 'active' : '' ?>">
                    <i class="fas fa-ticket-alt"></i> Informatieaanvragen
                    <?php if ($openTicketsCount > 0): ?>
                        <span class="ticket-count"><?= $openTicketsCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                 <a href="options.php" class="<?= ($currentPage == 'options.php') ? 'active' : '' ?>">
                     <i class="fas fa-tags"></i> Opties Beheren
                 </a>
            </li> 
            <!-- Add other sections like users, settings etc. if needed -->
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Uitloggen</a>
    </div>
</aside> 