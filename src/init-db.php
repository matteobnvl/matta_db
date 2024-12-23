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
        echo "Le dossier 'migrations' a été créé.\n";
    } else {
        echo "Le dossier 'migrations' existe déjà.\n";
    }

    $timestamp = time();
    $migrationFilename = 'migrations/' . $timestamp . '.php';
    $migrationContent = <<<PHP
<?php

class $timestamp
{
    private \$pdo;

    public function __construct(\$pdo)
    {
        \$this->pdo = \$pdo;
    }

    public function up()
    {
        \$sql = "CREATE TABLE example (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL
        )";
        \$this->pdo->exec(\$sql);
    }

    public function down()
    {
        \$sql = "DROP TABLE IF EXISTS example";
        \$this->pdo->exec(\$sql);
    }
}

PHP;

    file_put_contents($migrationFilename, $migrationContent);
    echo "Le fichier de migration par défaut '$migrationFilename' a été créé.\n";

} catch (PDOException $e) {
    echo $e;
}
