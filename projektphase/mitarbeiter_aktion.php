<?php
include 'config.php';

$action = $_GET['action'] ?? ''; 
$id = $_GET['id'] ?? 0;

if ($action && $id) {
    try {
        if ($action == 'activate') {
            $stmt = $conn->prepare("UPDATE mitarbeiter SET aktiv = TRUE WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = "✅ Mitarbeiter erfolgreich aktiviert!";
        } elseif ($action == 'deactivate') {
            $stmt = $conn->prepare("UPDATE mitarbeiter SET aktiv = FALSE WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = "✅ Mitarbeiter erfolgreich deaktiviert!";
        }
        
        $_SESSION['message_type'] = 'success';
        
    } catch(PDOException $e) {
        $_SESSION['message'] = "❌ Fehler: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
}

header("Location: mitarbeiter_verwaltung.php");
exit();
?>