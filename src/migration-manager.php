<?php

require_once 'vendor/autoload.php';

$projectRoot = dirname(__DIR__, 4);
$dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
$dotenv->load();

$dsn = "mysql:host=". $_ENV['DB_HOST'] .";dbname=". $_ENV['DB_NAME'] .";";
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

$pdo = new PDO($dsn, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function migrateUp($pdo)
{
    $migrationsPath = __DIR__ . '/../migrations';
    $appliedMigrations = getAppliedMigrations($pdo);

    $files = scandir($migrationsPath);
    $migrations = array_diff($files, ['.', '..']);

    foreach ($migrations as $migration) {
        if (!in_array($migration, $appliedMigrations)) {
            require_once $migrationsPath . '/' . $migration;

            $className = pathinfo($migration, PATHINFO_FILENAME);
            $migrationInstance = new $className($pdo);

            echo "Application de la migration : $className\n";
            $migrationInstance->up();

            recordMigration($pdo, $migration);
            echo "Migration $className appliquée avec succès.\n";
        }
    }
}

function migrateDown($pdo)
{
    $lastMigration = getLastAppliedMigration($pdo);

    if ($lastMigration) {
        $migrationFile = __DIR__ . '/../migrations/' . $lastMigration;
        require_once $migrationFile;

        $className = pathinfo($lastMigration, PATHINFO_FILENAME);
        $migrationInstance = new $className($pdo);

        echo "Annulation de la migration : $className\n";
        $migrationInstance->down();

        removeMigrationRecord($pdo, $lastMigration);
        echo "Migration $className annulée avec succès.\n";
    } else {
        echo "Aucune migration à annuler.\n";
    }
}

function getAppliedMigrations($pdo)
{
    $stmt = $pdo->query("SELECT migration FROM migrations_logs");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function recordMigration($pdo, $migration)
{
    $stmt = $pdo->prepare("INSERT INTO migrations_logs (migration) VALUES (:migration)");
    $stmt->execute(['migration' => $migration]);
}

function removeMigrationRecord($pdo, $migration)
{
    $stmt = $pdo->prepare("DELETE FROM migrations_logs WHERE migration = :migration");
    $stmt->execute(['migration' => $migration]);
}

function getLastAppliedMigration($pdo)
{
    $stmt = $pdo->query("SELECT migration FROM migrations_logs ORDER BY id DESC LIMIT 1");
    return $stmt->fetchColumn();
}

$action = $argv[1] ?? null;

if ($action === 'up') {
    migrateUp($pdo);
} elseif ($action === 'down') {
    migrateDown($pdo);
} else {
    echo "Usage : php migrations_manager.php [up|down]\n";
}
