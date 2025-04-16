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
$locationOptions = [];
$featureOptions = [];
$message = ''; // For success/error messages
$editOptionType = null;
$editOptionData = null;

// --- Handle Actions (Add, Edit, Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $optionType = $_POST['option_type'] ?? null; // 'location' or 'feature'
    $optionId = isset($_POST['option_id']) ? (int)$_POST['option_id'] : 0;
    $optionName = trim($_POST['option_name'] ?? '');

    if ($conn && $optionType && in_array($optionType, ['location', 'feature'])) {
        $tableName = ($optionType === 'location') ? 'location_options' : 'feature_options';
        
        try {
            if ($_POST['action'] === 'add' && !empty($optionName)) {
                $stmt = $conn->prepare("INSERT INTO `$tableName` (name) VALUES (:name)");
                if ($stmt->execute([':name' => $optionName])) {
                    $message = "<p class='success-message'>Optie succesvol toegevoegd.</p>";
                } else {
                    $message = "<p class='error-message'>Fout bij toevoegen optie.</p>";
                }
            } elseif ($_POST['action'] === 'update' && $optionId > 0 && !empty($optionName)) {
                $stmt = $conn->prepare("UPDATE `$tableName` SET name = :name WHERE id = :id");
                if ($stmt->execute([':name' => $optionName, ':id' => $optionId])) {
                    $message = "<p class='success-message'>Optie succesvol bijgewerkt.</p>";
                } else {
                     $message = "<p class='error-message'>Fout bij bijwerken optie.</p>";
                }
            } elseif ($_POST['action'] === 'delete' && $optionId > 0) {
                // Check if option is in use before deleting (optional but recommended)
                $junctionTable = ($optionType === 'location') ? 'villa_location_options' : 'villa_feature_options';
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM `$junctionTable` WHERE option_id = :id");
                $checkStmt->execute([':id' => $optionId]);
                if ($checkStmt->fetchColumn() > 0) {
                    $message = "<p class='error-message'>Kan optie niet verwijderen, deze is nog in gebruik bij één of meerdere woningen.</p>";
                } else {
                    $stmt = $conn->prepare("DELETE FROM `$tableName` WHERE id = :id");
                    if ($stmt->execute([':id' => $optionId])) {
                        $message = "<p class='success-message'>Optie succesvol verwijderd.</p>";
                    } else {
                        $message = "<p class='error-message'>Fout bij verwijderen optie.</p>";
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error managing options ({$optionType}): " . $e->getMessage());
             $message = "<p class='error-message'>Databasefout: Kan de actie niet uitvoeren.</p>";
             // More specific error for unique constraint violation
             if ($e->getCode() == 23000) { 
                 $message = "<p class='error-message'>Deze optienaam bestaat al.</p>";
             }
        }
    }
}

// --- Handle Edit Request (Prepare form) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['type']) && isset($_GET['id'])) {
     $editOptionType = $_GET['type'];
     $editOptionId = (int)$_GET['id'];
     if ($conn && $editOptionId > 0 && in_array($editOptionType, ['location', 'feature'])) {
         $tableName = ($editOptionType === 'location') ? 'location_options' : 'feature_options';
         $stmt = $conn->prepare("SELECT id, name FROM `$tableName` WHERE id = :id");
         $stmt->execute([':id' => $editOptionId]);
         $editOptionData = $stmt->fetch(PDO::FETCH_ASSOC);
         if (!$editOptionData) {
             $editOptionType = null; // Reset if not found
             $message = "<p class='error-message'>Te bewerken optie niet gevonden.</p>";
         }
     }
}


// --- Fetch Options for Display ---
if ($conn) {
    try {
        $locationOptions = $conn->query("SELECT id, name FROM location_options ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $featureOptions = $conn->query("SELECT id, name FROM feature_options ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching options: " . $e->getMessage());
        $message .= "<p class='error-message'>Fout bij het ophalen van opties.</p>";
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
    <title>Opties Beheren - Admin Dashboard</title>
    <link rel="stylesheet" href="styles/admin_main.css">
    <link rel="stylesheet" href="styles/admin_tables.css">
    <link rel="stylesheet" href="styles/admin_forms.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .options-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .options-section {
             background-color: #fff;
             padding: 25px;
             border-radius: 8px;
             box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
         .options-section h2 {
            font-size: 1.3rem;
            color: #007a7a;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
         }
         .options-list {
             list-style: none;
             padding: 0;
             margin-bottom: 20px;
             max-height: 300px;
             overflow-y: auto;
         }
         .options-list li {
             display: flex;
             justify-content: space-between;
             align-items: center;
             padding: 8px 0;
             border-bottom: 1px dashed #eee;
         }
         .options-list li:last-child {
             border-bottom: none;
         }
         .option-actions a, .option-actions button {
             margin-left: 8px;
             vertical-align: middle;
         }
         .add-option-form label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
         }
          .add-option-form input[type="text"] {
            width: calc(100% - 24px); /* Adjust for button */
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
         }
         .add-option-form .form-inline {
            display: flex;
            gap: 10px;
            align-items: flex-end;
         }
         .add-option-form .form-inline input {
             flex-grow: 1;
             margin-bottom: 0;
         }
          .add-option-form .form-inline button {
             padding: 8px 15px;
         }
         .edit-form-section {
             margin-top: 20px;
             padding: 20px;
             background-color: #f0f4f8;
             border-radius: 5px;
             border: 1px solid #ddd;
         }
         .edit-form-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.1rem;
            color: #333;
         }
         @media (max-width: 768px) {
            .options-container {
                grid-template-columns: 1fr;
            }
         }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'components/sidebar.php'; ?>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Ligging & Eigenschappen Beheren</h1>
            </header>

            <?= $message ?>

            <?php if ($editOptionData): ?>
            <section class="edit-form-section">
                <h3>'<?= htmlspecialchars(ucfirst($editOptionType)) ?>' Optie Bewerken</h3>
                <form action="options.php" method="POST" class="add-option-form form-inline">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="option_type" value="<?= $editOptionType ?>">
                    <input type="hidden" name="option_id" value="<?= $editOptionData['id'] ?>">
                    <div style="flex-grow: 1;">
                         <label for="edit_option_name" class="sr-only">Nieuwe naam</label>
                         <input type="text" id="edit_option_name" name="option_name" value="<?= htmlspecialchars($editOptionData['name']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-save"><i class="fas fa-save"></i> Opslaan</button>
                    <a href="options.php" class="btn btn-cancel">Annuleren</a>
                </form>
            </section>
            <?php endif; ?>

            <div class="options-container">
                <!-- Location Options Section -->
                <section class="options-section">
                    <h2>Liggingsopties</h2>
                    <ul class="options-list">
                        <?php foreach ($locationOptions as $option): ?>
                        <li>
                            <span><?= htmlspecialchars($option['name']) ?></span>
                            <span class="option-actions">
                                <a href="options.php?action=edit&type=location&id=<?= $option['id'] ?>" class="btn-action btn-edit" title="Bewerken"><i class="fas fa-edit"></i></a>
                                <form action="options.php" method="POST" onsubmit="return confirm('Weet u zeker dat u deze optie wilt verwijderen? Dit kan alleen als de optie niet meer in gebruik is.');" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="option_type" value="location">
                                    <input type="hidden" name="option_id" value="<?= $option['id'] ?>">
                                    <button type="submit" class="btn-action btn-delete" title="Verwijderen"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </span>
                        </li>
                        <?php endforeach; ?>
                         <?php if (empty($locationOptions)): ?>
                            <li>Geen liggingsopties gevonden.</li>
                         <?php endif; ?>
                    </ul>
                    <form action="options.php" method="POST" class="add-option-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="option_type" value="location">
                        <label for="new_location_option">Nieuwe Liggingsoptie Toevoegen</label>
                        <div class="form-inline">
                            <input type="text" id="new_location_option" name="option_name" placeholder="Naam van optie" required>
                            <button type="submit" class="btn btn-add"><i class="fas fa-plus"></i> Toevoegen</button>
                        </div>
                    </form>
                </section>

                <!-- Feature Options Section -->
                 <section class="options-section">
                    <h2>Eigenschappen (Features)</h2>
                    <ul class="options-list">
                         <?php foreach ($featureOptions as $option): ?>
                        <li>
                            <span><?= htmlspecialchars($option['name']) ?></span>
                            <span class="option-actions">
                                <a href="options.php?action=edit&type=feature&id=<?= $option['id'] ?>" class="btn-action btn-edit" title="Bewerken"><i class="fas fa-edit"></i></a>
                                <form action="options.php" method="POST" onsubmit="return confirm('Weet u zeker dat u deze optie wilt verwijderen? Dit kan alleen als de optie niet meer in gebruik is.');" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="option_type" value="feature">
                                    <input type="hidden" name="option_id" value="<?= $option['id'] ?>">
                                    <button type="submit" class="btn-action btn-delete" title="Verwijderen"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </span>
                        </li>
                        <?php endforeach; ?>
                         <?php if (empty($featureOptions)): ?>
                            <li>Geen eigenschappen gevonden.</li>
                         <?php endif; ?>
                    </ul>
                     <form action="options.php" method="POST" class="add-option-form">
                         <input type="hidden" name="action" value="add">
                         <input type="hidden" name="option_type" value="feature">
                         <label for="new_feature_option">Nieuwe Eigenschap Toevoegen</label>
                         <div class="form-inline">
                            <input type="text" id="new_feature_option" name="option_name" placeholder="Naam van eigenschap" required>
                             <button type="submit" class="btn btn-add"><i class="fas fa-plus"></i> Toevoegen</button>
                        </div>
                    </form>
                </section>
            </div>

        </main>
    </div>
</body>
</html> 