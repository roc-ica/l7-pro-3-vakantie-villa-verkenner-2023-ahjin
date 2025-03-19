<?php
include 'ServerLogger.php';

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(ServerLogger::getLogs());
    exit;
}

$logs = ServerLogger::getLogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Logs</title>
</head>
<body>

    <h1>Server Logs</h1>
    <ul id="logList">
        <?php foreach ($logs as $log): ?>
            <li>
                <strong>[<?= htmlspecialchars($log['timestamp']) ?>]</strong> 
                <?= htmlspecialchars($log['action']) ?>
                <?= htmlspecialchars($log['message']) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        async function fetchLogs() {
            const response = await fetch('get_logs.php?ajax=1');
            const logs = await response.json();

            let logList = document.getElementById('logList');
            logList.innerHTML = ''; // Clear old logs

            logs.forEach(log => {
                let li = document.createElement('li');
                li.innerHTML = `<strong>[${log.timestamp}]</strong> ${log.action}`;
                logList.appendChild(li);
            });
        }

        setInterval(fetchLogs, 3000); 
    </script>

</body>
</html>
