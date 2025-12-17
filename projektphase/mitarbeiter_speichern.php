<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vorname = trim($_POST['vorname']);
    $nachname = trim($_POST['nachname']);
    $rfid_code = trim($_POST['rfid_code']);
    $abteilung = trim($_POST['abteilung']);
    
    try {
        $check_stmt = $conn->prepare("SELECT id FROM mitarbeiter WHERE rfid_code = ?");
        $check_stmt->execute([$rfid_code]);
        
        if ($check_stmt->fetch()) {
            $_SESSION['message'] = "❌ Fehler: RFID-Code '$rfid_code' existiert bereits!";
            $_SESSION['message_type'] = 'error';
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO mitarbeiter (vorname, nachname, rfid_code, abteilung) VALUES (?, ?, ?, ?)");
            $insert_stmt->execute([$vorname, $nachname, $rfid_code, $abteilung]);
            
            $_SESSION['message'] = "✅ Mitarbeiter $vorname $nachname erfolgreich hinzugefügt!";
            $_SESSION['message_type'] = 'success';
        }
        
    } catch(PDOException $e) {
        $_SESSION['message'] = "❌ Datenbank-Fehler: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
    header("Location: mitarbeiter_verwaltung.php");
    exit();
}
?>