<?php
session_start();

$host = '127.0.0.1';
$dbname = 'justpaper';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // If database doesn't exist, create it
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->exec("USE $dbname");
}

// Now, check if the tables exist, if not, create them
$tables = ['mitarbeiter', 'zeiterfassung'];
foreach ($tables as $table) {
    $stmt = $conn->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() == 0) {
        // Table doesn't exist, create it
        if ($table == 'mitarbeiter') {
            $conn->exec("
            CREATE TABLE mitarbeiter (
                id INT AUTO_INCREMENT PRIMARY KEY,
                vorname VARCHAR(50) NOT NULL,
                nachname VARCHAR(50) NOT NULL,
                rfid_code VARCHAR(20) UNIQUE NOT NULL,
                abteilung VARCHAR(50),
                aktiv BOOLEAN DEFAULT TRUE
            )");
        } elseif ($table == 'zeiterfassung') {
            $conn->exec("
            CREATE TABLE zeiterfassung (
                id INT AUTO_INCREMENT PRIMARY KEY,
                mitarbeiter_id INT,
                uhrzeit DATETIME DEFAULT CURRENT_TIMESTAMP,
                ereignis ENUM('Kommen', 'Gehen') NOT NULL,
                FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter(id)
            )");
        }
    }
}


$stmt = $conn->query("SELECT COUNT(*) as count FROM mitarbeiter");
$row = $stmt->fetch();
if ($row['count'] == 0) {
    $conn->exec("
    INSERT IGNORE INTO mitarbeiter (vorname, nachname, rfid_code, abteilung) VALUES
    ('Max', 'Mustermann', 'RFID001', 'IT'),
    ('Anna', 'Schmidt', 'RFID002', 'Marketing'), 
    ('Thomas', 'Weber', 'RFID003', 'Vertrieb'),
    ('Sarah', 'Müller', 'RFID004', 'Produktion'),
    ('Admin', 'User', 'ADMIN001', 'IT')
    ");
}

date_default_timezone_set('Europe/Berlin');

?>
