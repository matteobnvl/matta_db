<?php

$timestamp = time();
$filename = 'Migration'.$timestamp;
$migrationFilename = dirname(__DIR__, 4) . "/migrations/{$filename}.php";

$migrationContent = <<<PHP
<?php

class $filename
{
    private \$pdo;

    public function __construct(\$pdo)
    {
        \$this->pdo = \$pdo;
    }
    // exemple
    // \$sql = "CREATE TABLE example (
    //      id INT AUTO_INCREMENT PRIMARY KEY,
    //      name VARCHAR(255) NOT NULL
    //  )";
    //  \$this->pdo->exec(\$sql);

    public function up()
    {
        // Ajoutez ici la logique pour appliquer la migration
    }

    public function down()
    {
        // Ajoutez ici la logique pour annuler la migration
    }
}

PHP;

if (!is_dir(__DIR__ . '/../migrations')) {
    mkdir(__DIR__ . '/../migrations', 0777, true);
}

file_put_contents($migrationFilename, $migrationContent);

echo "Migration file created: {$migrationFilename}\n";
