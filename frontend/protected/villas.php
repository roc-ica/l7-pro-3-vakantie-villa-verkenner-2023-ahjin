<?php
require_once __DIR__ . '/../../db/class/sessions.php';
require_once __DIR__ . '/../../db/class/database.php';

// Secure the page
if (!SessionManager::validateAdminSession()) {
    header('Location: login.php?error=auth');
    exit;
}

// Database connection
    $db = new Database();
    $conn = $db->getConnection();
$villas = [];
$message = ''; // For success/error messages

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['villa_id'])) {
    $villaIdToDelete = (int)$_POST['villa_id'];
    if ($villaIdToDelete > 0 && $conn) {
        try {
            // Add logic to delete associated images from filesystem first if needed
            // ... (implement image file deletion here)
            
            $conn->beginTransaction();
            
            // Delete relations first (optional, depends on CASCADE setup)
            $conn->prepare("DELETE FROM villa_feature_options WHERE villa_id = ?")->execute([$villaIdToDelete]);
            $conn->prepare("DELETE FROM villa_location_options WHERE villa_id = ?")->execute([$villaIdToDelete]);
            $conn->prepare("DELETE FROM villa_images WHERE villa_id = ?")->execute([$villaIdToDelete]);
            
            // Delete the villa
            $deleteStmt = $conn->prepare("DELETE FROM villas WHERE id = ?");
            if ($deleteStmt->execute([$villaIdToDelete])) {
            $conn->commit();
                $message = "<p class='success-message'>Villa succesvol verwijderd.</p>";
            } else {
            $conn->rollBack();
                $message = "<p class='error-message'>Fout bij het verwijderen van de villa.</p>";
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error deleting villa: " . $e->getMessage());
            $message = "<p class='error-message'>Databasefout bij verwijderen: " . $e->getMessage() . "</p>";
        }
    }
}

// Fetch Villas from Database
if ($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                v.id, v.titel, v.straat, v.post_c, v.plaatsnaam, v.prijs, v.verkocht, v.featured,
                (SELECT COUNT(*) FROM villa_images WHERE villa_id = v.id) as image_count
            FROM villas v 
            ORDER BY v.id DESC
        ");
        $stmt->execute();
        $villas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching villas: " . $e->getMessage());
        $message = "<p class='error-message'>Fout bij het ophalen van villa's.</p>";
    }
} else {
     $message = "<p class='error-message'>Database connectie mislukt.</p>";
}

$db->closeConnection($conn);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Woningen Beheren - Admin Dashboard</title>
    <link rel="stylesheet" href="styles/admin_main.css"> 
    <link rel="stylesheet" href="styles/admin_tables.css"> <!-- Added tables CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'components/sidebar.php'; // Use a reusable sidebar component ?>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Woningen Beheren</h1>
                <a href="edit_villa.php" class="btn btn-add"><i class="fas fa-plus"></i> Nieuwe Woning Toevoegen</a>
            </header>

            <?= $message ?> <!-- Display messages here -->

            <section class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titel</th>
                            <th>Adres</th>
                            <th>Prijs</th>
                            <th>Afbeeldingen</th>
                            <th>Verkocht</th>
                            <th>Uitgelicht</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($villas)): ?>
                            <?php foreach ($villas as $villa): ?>
                                <tr>
                                    <td><?= htmlspecialchars($villa['id']) ?></td>
                                    <td><?= htmlspecialchars($villa['titel']) ?></td>
                                    <td><?= htmlspecialchars($villa['straat'] . ', ' . $villa['post_c'] . ' ' . $villa['plaatsnaam']) ?></td>
                                    <td>€ <?= number_format($villa['prijs'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($villa['image_count']) ?></td>
                                    <td>
                                        <span class="status-badge <?= $villa['verkocht'] ? 'sold' : 'available' ?>">
                                            <?= $villa['verkocht'] ? 'Ja' : 'Nee' ?>
                                        </span>
                                        <!-- Add toggle button here later -->
                                    </td>
                                     <td>
                                        <span class="status-badge <?= $villa['featured'] ? 'featured' : 'not-featured' ?>">
                                            <?= $villa['featured'] ? 'Ja' : 'Nee' ?>
                                        </span>
                                        <!-- Add toggle button here later -->
                                    </td>
                                    <td class="action-buttons">
                                        <a href="edit_villa.php?id=<?= $villa['id'] ?>" class="btn-action btn-edit" title="Bewerken"><i class="fas fa-edit"></i></a>
                                        <form action="villas.php" method="POST" onsubmit="return confirm('Weet u zeker dat u deze villa wilt verwijderen?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="villa_id" value="<?= $villa['id'] ?>">
                                            <button type="submit" class="btn-action btn-delete" title="Verwijderen"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <!-- Add view button if needed -->
                                        <a href="../pages/detailview.php?id=<?= $villa['id'] ?>" target="_blank" class="btn-action btn-view" title="Bekijk op website"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                    <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">Geen woningen gevonden.</td>
                            </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>
            
            <!-- Add Pagination if many villas -->

        </main>
    </div>
</body>
</html>