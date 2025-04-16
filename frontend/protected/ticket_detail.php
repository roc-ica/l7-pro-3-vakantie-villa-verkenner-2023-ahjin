<?php
require_once __DIR__ . '/../../db/class/sessions.php';
require_once __DIR__ . '/../../db/class/database.php';

// Secure the page
if (!SessionManager::validateAdminSession()) {
    header('Location: login.php?error=auth');
    exit;
}

// Get ticket ID from URL
$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize variables
$ticket = null;
$messages = [];
$statusOptions = ['open', 'in behandeling', 'gesloten'];
$priorityOptions = ['laag', 'gemiddeld', 'hoog'];

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Process form submission for updates
$updateMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_ticket'])) {
        try {
            // Get form data
            $newStatus = $_POST['status'] ?? '';
            $newPriority = $_POST['priority'] ?? '';
            $adminReply = $_POST['admin_reply'] ?? '';
            
            // Update ticket status and priority
            $updateStmt = $conn->prepare("UPDATE tickets SET status = :status, prioriteit = :priority WHERE id = :id");
            $updateStmt->bindParam(':status', $newStatus);
            $updateStmt->bindParam(':priority', $newPriority);
            $updateStmt->bindParam(':id', $ticket_id);
            $updateStmt->execute();
            
            // If there's a reply, add it to ticket_messages
            if (!empty($adminReply)) {
                $replyStmt = $conn->prepare("INSERT INTO ticket_messages (ticket_id, sender_type, message, created_at) VALUES (:ticket_id, 'admin', :message, NOW())");
                $replyStmt->bindParam(':ticket_id', $ticket_id);
                $replyStmt->bindParam(':message', $adminReply);
                $replyStmt->execute();
            }
            
            $updateMessage = "Ticket successfully updated.";
        } catch (PDOException $e) {
            $updateMessage = "Error updating ticket: " . $e->getMessage();
            error_log("Ticket update error: " . $e->getMessage());
        }
    }
}

// Fetch ticket details
if ($conn && $ticket_id > 0) {
    try {
        // Get ticket info
        $stmt = $conn->prepare("SELECT * FROM tickets WHERE id = :id");
        $stmt->bindParam(':id', $ticket_id);
        $stmt->execute();
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get ticket messages
        if ($ticket) {
            $msgStmt = $conn->prepare("SELECT * FROM ticket_messages WHERE ticket_id = :ticket_id ORDER BY created_at ASC");
            $msgStmt->bindParam(':ticket_id', $ticket_id);
            $msgStmt->execute();
            $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Error fetching ticket: " . $e->getMessage());
    }
}

// Handle dummy ticket data for testing if the ticket doesn't exist
if (!$ticket && $ticket_id > 0) {
    // Create dummy data for testing
    if ($ticket_id == 1001) {
        $ticket = [
            'id' => 1001,
            'onderwerp' => 'Probleem met boeking',
            'gebruiker_naam' => 'Jan Jansen',
            'email' => 'jan.jansen@example.com',
            'bericht' => 'Ik heb problemen met het boeken van een villa. Ik kan niet naar de betalingspagina gaan nadat ik alle gegevens heb ingevuld.',
            'status' => 'open',
            'prioriteit' => 'hoog',
            'datum_aangemaakt' => '2023-03-15 14:30:00'
        ];
        $messages = [
            [
                'id' => 101,
                'ticket_id' => 1001,
                'sender_type' => 'customer',
                'message' => 'Ik heb problemen met het boeken van een villa. Ik kan niet naar de betalingspagina gaan nadat ik alle gegevens heb ingevuld.',
                'created_at' => '2023-03-15 14:30:00'
            ]
        ];
    } elseif ($ticket_id == 1002) {
        $ticket = [
            'id' => 1002,
            'onderwerp' => 'Vraag over villa faciliteiten',
            'gebruiker_naam' => 'Marie de Vries',
            'email' => 'marie.vries@example.com',
            'bericht' => 'Ik wil graag weten of de villa in Texel ook toegankelijk is voor rolstoelgebruikers. Zijn er speciale voorzieningen aanwezig?',
            'status' => 'in-progress',
            'prioriteit' => 'medium',
            'datum_aangemaakt' => '2023-03-14 10:15:00'
        ];
        $messages = [
            [
                'id' => 201,
                'ticket_id' => 1002,
                'sender_type' => 'customer',
                'message' => 'Ik wil graag weten of de villa in Texel ook toegankelijk is voor rolstoelgebruikers. Zijn er speciale voorzieningen aanwezig?',
                'created_at' => '2023-03-14 10:15:00'
            ],
            [
                'id' => 202,
                'ticket_id' => 1002,
                'sender_type' => 'admin',
                'message' => 'Beste Marie, dank voor uw vraag. Ik ga dit voor u uitzoeken en kom zo snel mogelijk bij u terug.',
                'created_at' => '2023-03-14 11:30:00'
            ]
        ];
    }
}

// Close database connection
if ($conn) {
    $db->closeConnection($conn);
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details - Admin Dashboard</title>
    <link rel="stylesheet" href="styles/admin_main.css">
    <link rel="stylesheet" href="styles/tickets.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .ticket-detail-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .ticket-id {
            font-size: 1.1em;
            color: #777;
            font-weight: 500;
        }
        
        .ticket-subject {
            font-size: 1.4em;
            margin: 0 0 10px 0;
        }
        
        .ticket-metadata {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .metadata-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .metadata-item i {
            color: #666;
        }
        
        .ticket-messages {
            margin-top: 30px;
        }
        
        .message {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .message.admin {
            background-color: #f0f7ff;
            margin-left: 20px;
        }
        
        .message.customer {
            background-color: #f0f5f0;
            margin-right: 20px;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.9em;
            color: #666;
        }
        
        .message-content {
            line-height: 1.5;
        }
        
        .ticket-reply {
            margin-top: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        textarea.form-control {
            min-height: 120px;
        }
        
        .status-controls {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: #4a6fdc;
            color: white;
        }
        
        .btn-secondary {
            background-color: #e2e8f0;
            color: #333;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php include 'components/sidebar.php'; ?>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Ticket Details</h1>
                <a href="tickets.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Tickets</a>
            </header>

            <?php if (!$ticket): ?>
                <div class="alert alert-danger">
                    <p>Ticket not found or invalid ticket ID.</p>
                </div>
            <?php else: ?>
                
                <?php if (!empty($updateMessage)): ?>
                    <div class="alert alert-success">
                        <p><?= $updateMessage ?></p>
                    </div>
                <?php endif; ?>

                <div class="ticket-detail-container">
                    <div class="ticket-header">
                        <div>
                            <h2 class="ticket-subject"><?= htmlspecialchars($ticket['onderwerp']) ?></h2>
                            <span class="ticket-id">#<?= $ticket['id'] ?></span>
                        </div>
                        <div>
                            <span class="ticket-status status-<?= htmlspecialchars($ticket['status']) ?>">
                                <?= ucfirst(htmlspecialchars($ticket['status'])) ?>
                            </span>
                        </div>
                    </div>

                    <div class="ticket-metadata">
                        <div class="metadata-item">
                            <i class="fas fa-user"></i>
                            <span><?= htmlspecialchars($ticket['gebruiker_naam']) ?></span>
                        </div>
                        <div class="metadata-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($ticket['email'] ?? 'No email provided') ?></span>
                        </div>
                        <div class="metadata-item">
                            <i class="fas fa-calendar"></i>
                            <span><?= date('d-m-Y H:i', strtotime($ticket['datum_aangemaakt'])) ?></span>
                        </div>
                        <div class="metadata-item">
                            <i class="fas fa-flag"></i>
                            <span class="ticket-priority priority-<?= ($ticket['prioriteit'] == 'laag' ? 'low' : ($ticket['prioriteit'] == 'gemiddeld' ? 'medium' : 'high')) ?>">
                                <?= ucfirst(htmlspecialchars($ticket['prioriteit'])) ?>
                            </span>
                        </div>
                    </div>

                    <div class="ticket-messages">
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?= $message['sender_type'] ?>">
                                <div class="message-header">
                                    <span>
                                        <?= $message['sender_type'] === 'admin' ? 'Administrator' : htmlspecialchars($ticket['gebruiker_naam']) ?>
                                    </span>
                                    <span><?= date('d-m-Y H:i', strtotime($message['created_at'])) ?></span>
                                </div>
                                <div class="message-content">
                                    <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="ticket-reply">
                        <h3>Reply to Ticket</h3>
                        <form method="post" action="">
                            <div class="status-controls">
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select class="form-control" id="status" name="status">
                                        <?php foreach ($statusOptions as $statusOption): ?>
                                            <option value="<?= $statusOption ?>" <?= $ticket['status'] === $statusOption ? 'selected' : '' ?>>
                                                <?= ucfirst($statusOption) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="priority">Priority:</label>
                                    <select class="form-control" id="priority" name="priority">
                                        <?php foreach ($priorityOptions as $priorityOption): ?>
                                            <option value="<?= $priorityOption ?>" <?= $ticket['prioriteit'] === $priorityOption ? 'selected' : '' ?>>
                                                <?= ucfirst($priorityOption) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="admin_reply">Your Response:</label>
                                <textarea class="form-control" id="admin_reply" name="admin_reply" placeholder="Type your response here..."></textarea>
                            </div>

                            <div class="action-buttons">
                                <button type="submit" name="update_ticket" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Response
                                </button>
                                <a href="tickets.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Script to handle form submission and confirm actions
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const statusSelect = document.getElementById('status');
                    const adminReply = document.getElementById('admin_reply');
                    
                    // If changing to closed status, confirm with user
                    if (statusSelect.value === 'gesloten' && !confirm('Are you sure you want to close this ticket?')) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // If submitting empty reply, warn user
                    if (adminReply.value.trim() === '' && !confirm('You are submitting without a response. Continue?')) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
    </script>
</body>

</html> 