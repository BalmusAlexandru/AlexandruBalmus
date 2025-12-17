<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Manuelle Zeiterfassung</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select, input { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .success { background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>⏰ Manuelle Zeiterfassung</h2>
    <p><a href="index.php">← Zurück zur RFID-Erfassung</a></p>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $mitarbeiter_id = $_POST['mitarbeiter_id'];
        $datum = $_POST['datum'];
        $kommen_zeit = $_POST['kommen_zeit'];
        $gehen_zeit = $_POST['gehen_zeit'];
        
        try {
            $stmt1 = $conn->prepare("INSERT INTO zeiterfassung (mitarbeiter_id, uhrzeit, ereignis) VALUES (?, ?, 'Kommen')");
            $stmt1->execute([$mitarbeiter_id, $datum . ' ' . $kommen_zeit]);
            
            
            $stmt2 = $conn->prepare("INSERT INTO zeiterfassung (mitarbeiter_id, uhrzeit, ereignis) VALUES (?, ?, 'Gehen')");
            $stmt2->execute([$mitarbeiter_id, $datum . ' ' . $gehen_zeit]);
            
            echo '<div class="success">✅ Zeiterfassung erfolgreich manuell eingetragen!</div>';
        } catch(PDOException $e) {
            echo '<div style="background:#f8d7da; color:#721c24; padding:10px;">❌ Fehler: ' . $e->getMessage() . '</div>';
        }
    }
    ?>

    <form method="post">
        <div class="form-group">
            <label>Mitarbeiter:</label>
            <select name="mitarbeiter_id" required>
                <option value="">-- Mitarbeiter wählen --</option>
                <?php
                $stmt = $conn->query("SELECT id, vorname, nachname FROM mitarbeiter WHERE aktiv = 1 ORDER BY nachname");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $row['id'] . '">' . $row['vorname'] . ' ' . $row['nachname'] . '</option>';
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Datum:</label>
            <input type="date" name="datum" value="<?= date('Y-m-d') ?>" required>
        </div>
        
        <div class="form-group">
            <label>Kommen Zeit:</label>
            <input type="time" name="kommen_zeit" value="08:00" required>
        </div>
        
        <div class="form-group">
            <label>Gehen Zeit:</label>
            <input type="time" name="gehen_zeit" value="16:30" required>
        </div>
        
        <button type="submit">Zeit manuell eintragen</button>
    </form>
</body>
</html>