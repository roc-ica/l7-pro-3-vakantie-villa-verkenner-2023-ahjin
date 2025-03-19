<?php
try {
    $pdo = new PDO('sqlite:villaVerkenner.db');

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS villas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        straat TEXT NOT NULL,
        post_c TEXT NOT NULL,
        kamers SMALLINT NOT NULL,
        oppervlakte REAL NOT NULL,
        prijs INTEGER NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS labels (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        naam TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS villa_labels (
        villa_id INTEGER NOT NULL,
        label_id INTEGER NOT NULL,
        PRIMARY KEY (villa_id, label_id),
        FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE,
        FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS villa_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        villa_id INTEGER NOT NULL,
        image_path TEXT NOT NULL,
        FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        naam TEXT NOT NULL,
        email TEXT NOT NULL,
        bericht TEXT NOT NULL,
        status SMALLINT NOT NULL CHECK (status IN (0, 1, 2)),
        prioriteit SMALLINT NOT NULL CHECK (prioriteit IN (0, 1, 2)),
        datum TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS formulieren (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        naam TEXT NOT NULL,
        email TEXT NOT NULL,
        telefoon TEXT NOT NULL,
        datum TEXT NOT NULL,
        bericht TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_formulieren (
        ticket_id INTEGER NOT NULL,
        formulier_id INTEGER NOT NULL,
        PRIMARY KEY (ticket_id, formulier_id),
        FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
        FOREIGN KEY (formulier_id) REFERENCES formulieren(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        datum TEXT NOT NULL,
        ip_adres TEXT NOT NULL,
        pagina TEXT NOT NULL,
        query TEXT NOT NULL
    )");

    echo "Database tables created successfully!";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage(); 
}
?>
