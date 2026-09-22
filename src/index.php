<?php
$host = 'db';
$db   = 'appdb';
$user = 'appuser';
$pass = 'apppass';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Crea una tabla de ejemplo la primera vez que se ejecuta
    $pdo->exec("CREATE TABLE IF NOT EXISTS visitas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Registra esta visita
    $pdo->exec("INSERT INTO visitas () VALUES ()");

    // Cuenta cuántas visitas hay
    $total = $pdo->query("SELECT COUNT(*) AS total FROM visitas")->fetch()['total'];

    echo "<h1>Stack Docker: nginx + PHP-FPM + MySQL</h1>";
    echo "<p>Conexión a la base de datos correcta.</p>";
    echo "<p>Número de visitas registradas: <strong>$total</strong></p>";

} catch (PDOException $e) {
    echo "<h1>Error de conexión</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
