<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rfid_code = trim($_POST['rfid_code']);
    
    try {
        $stmt = $conn->prepare("SELECT id, vorname, nachname FROM mitarbeiter WHERE rfid_code = ? AND aktiv = TRUE");
        $stmt->execute([$rfid_code]);
        $mitarbeiter = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($mitarbeiter) {
            $mitarbeiter_id = $mitarbeiter['id'];
            $name = $mitarbeiter['vorname'] . ' ' . $mitarbeiter['nachname'];
            
            $stmt_last = $conn->prepare("SELECT ereignis FROM zeiterfassung WHERE mitarbeiter_id = ? ORDER BY uhrzeit DESC LIMIT 1");
            $stmt_last->execute([$mitarbeiter_id]);
            $last_event = $stmt_last->fetch(PDO::FETCH_ASSOC);
            
            if ($last_event) {
                $neues_ereignis = ($last_event['ereignis'] == 'Kommen') ? 'Gehen' : 'Kommen';
            } else {
                $neues_ereignis = 'Kommen'; 
            }
            
            // Insert new time record
            $stmt_insert = $conn->prepare("INSERT INTO zeiterfassung (mitarbeiter_id, ereignis) VALUES (?, ?)");
            $stmt_insert->execute([$mitarbeiter_id, $neues_ereignis]);
            
            $_SESSION['message'] = "Hallo $name! $neues_ereignis erfolgreich erfasst um " . date('H:i:s');
            $_SESSION['message_type'] = 'success';
            
        } else {
            $_SESSION['message'] = "RFID-Code nicht gefunden oder nicht aktiv.";
            $_SESSION['message_type'] = 'error';
        }
        
    } catch(PDOException $e) {
        $_SESSION['message'] = "Datenbank-Fehler: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
    header("Location: index.php");
    exit();
}
?>