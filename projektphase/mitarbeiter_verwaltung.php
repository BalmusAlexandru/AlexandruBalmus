<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitarbeiter Verwaltung - JustPaper AG</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .inactive { background-color: #f8d7da; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <h2>👥 Mitarbeiter Verwaltung - JustPaper AG</h2>
    <p>
        <a href="index.php">← Zurück zur Zeiterfassung</a> | 
        <a href="admin.php">📊 Admin</a> |
        <a href="reports.php">📈 Berichte</a>
    </p>

    <div style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; margin: 20px 0;">
        <h3>➕ Neuen Mitarbeiter hinzufügen</h3>
        
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="' . $_SESSION['message_type'] . '">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        
        <form method="post" action="mitarbeiter_speichern.php">
            <div class="form-group">
                <label for="vorname">Vorname:</label>
                <input type="text" id="vorname" name="vorname" required>
            </div>
            
            <div class="form-group">
                <label for="nachname">Nachname:</label>
                <input type="text" id="nachname" name="nachname" required>
            </div>
            
            <div class="form-group">
                <label for="rfid_code">RFID-Code:</label>
                <input type="text" id="rfid_code" name="rfid_code" required>
                <small>Muss eindeutig sein (z.B. RFID005, RFID006, ...)</small>
            </div>
            
            <div class="form-group">
                <label for="abteilung">Abteilung:</label>
                <input type="text" id="abteilung" name="abteilung" required>
            </div>
            
            <button type="submit">Mitarbeiter hinzufügen</button>
        </form>
    </div>

    <h3>📋 Alle Mitarbeiter</h3>
    <?php
    try {
        $sql = "SELECT id, vorname, nachname, rfid_code, abteilung, aktiv FROM mitarbeiter ORDER BY nachname, vorname";
        $stmt = $conn->query($sql);
        $mitarbeiter = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($mitarbeiter) > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Name</th><th>RFID-Code</th><th>Abteilung</th><th>Status</th><th>Aktion</th></tr>';
            
            foreach ($mitarbeiter as $ma) {
                $row_class = $ma['aktiv'] ? '' : 'inactive';
                $status = $ma['aktiv'] ? '🟢 Aktiv' : '🔴 Inaktiv';
                
                echo '<tr class="' . $row_class . '">';
                echo '<td>' . htmlspecialchars($ma['id']) . '</td>';
                echo '<td>' . htmlspecialchars($ma['vorname'] . ' ' . $ma['nachname']) . '</td>';
                echo '<td>' . htmlspecialchars($ma['rfid_code']) . '</td>';
                echo '<td>' . htmlspecialchars($ma['abteilung']) . '</td>';
                echo '<td>' . $status . '</td>';
                echo '<td>';
                if ($ma['aktiv']) {
                    echo '<a href="mitarbeiter_aktion.php?action=deactivate&id=' . $ma['id'] . '">🔴 Deaktivieren</a>';
                } else {
                    echo '<a href="mitarbeiter_aktion.php?action=activate&id=' . $ma['id'] . '">🟢 Aktivieren</a>';
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>Keine Mitarbeiter gefunden.</p>';
        }
        
    } catch(PDOException $e) {
        echo '<p>Fehler: ' . $e->getMessage() . '</p>';
    }
    ?>
</body>
</html>