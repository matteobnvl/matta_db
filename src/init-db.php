<?php

require_once 'vendor/autoload.php';

$projectRoot = dirname(__DIR__, 4);
$dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
$dotenv->load();


$dsn = "mysql:host=". $_ENV['DB_HOST'] .";dbname=". $_ENV['DB_NAME'] .";";
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $checkTableQuery = "SHOW TABLES LIKE 'migrations_logs'";
    $stmt = $pdo->query($checkTableQuery);
    if ($stmt->rowCount() === 0) {
        $createTableQuery = "
            CREATE TABLE migrations_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ";
        $pdo->exec($createTableQuery);
        echo "Table 'migrations' créée avec succès.\n";
    } else {
        echo "La table 'migrations' existe déjà.\n";
    }

    if (!is_dir('migrations')) {
        mkdir('migrations', 0777, true);
        echo "Le dossier 'migrations' a été créé.\n\n";
    } else {
        echo "Le dossier 'migrations' existe déjà.\n\n\n";
    }
    echo "Lancer la commande composer migration:create pour créer une migration.\n";

} catch (PDOException $e) {
    echo $e;
}
