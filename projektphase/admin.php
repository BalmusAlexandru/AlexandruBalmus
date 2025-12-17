<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - JustPaper AG</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .present { background-color: #d4edda; }
        .absent { background-color: #f8d7da; }
        .stats { display: flex; gap: 20px; margin: 20px 0; }
        .stat-box { border: 1px solid #ccc; padding: 15px; border-radius: 5px; background: #f9f9f9; }
        .nav { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ccc; }
    </style>
</head>
<body>
    <h2>📊 Admin-Bereich - JustPaper AG</h2>
    
    <!-- NAVIGATION -->
    <div class="nav">
        <a href="index.php">← Zurück zur Zeiterfassung</a> | 
        <a href="mitarbeiter_verwaltung.php">👥 Mitarbeiter</a> |
        <a href="reports.php">📈 Berichte</a>
    </div>

    <!-- STATISTICS -->
    <div class="stats">
        <div class="stat-box">
            <h3>📈 Heute</h3>
            <?php
            try {
                $stmt = $conn->query("SELECT COUNT(DISTINCT mitarbeiter_id) as count FROM zeiterfassung WHERE DATE(uhrzeit) = CURDATE()");
                $today = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p><strong>Eingestempelt heute:</strong> " . $today['count'] . " Mitarbeiter</p>";
            } catch (Exception $e) {
                echo "<p><strong>Fehler:</strong> " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
        
        <div class="stat-box">
            <h3>👥 Gesamt</h3>
            <?php
            try {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM mitarbeiter WHERE aktiv = TRUE");
                $total = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p><strong>Aktive Mitarbeiter:</strong> " . $total['total'] . "</p>";
            } catch (Exception $e) {
                echo "<p><strong>Fehler:</strong> " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>

    <!-- CURRENTLY PRESENT EMPLOYEES -->
    <h3>🟢 Aktuell anwesende Mitarbeiter</h3>
    <?php
    try {
        $sql = "
        SELECT m.vorname, m.nachname, m.abteilung, z.uhrzeit 
        FROM mitarbeiter m
        INNER JOIN (
            SELECT mitarbeiter_id, MAX(uhrzeit) as latest_time 
            FROM zeiterfassung 
            GROUP BY mitarbeiter_id
        ) latest ON m.id = latest.mitarbeiter_id
        INNER JOIN zeiterfassung z ON latest.mitarbeiter_id = z.mitarbeiter_id AND latest.latest_time = z.uhrzeit
        WHERE z.ereignis = 'Kommen'
        ORDER BY z.uhrzeit DESC";
        
        $stmt = $conn->query($sql);
        $anwesende = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($anwesende) > 0) {
            echo '<table>';
            echo '<tr><th>Name</th><th>Abteilung</th><th>Eingestempelt um</th></tr>';
            foreach ($anwesende as $ma) {
                echo '<tr class="present">';
                echo '<td>' . htmlspecialchars($ma['vorname'] . ' ' . $ma['nachname']) . '</td>';
                echo '<td>' . htmlspecialchars($ma['abteilung']) . '</td>';
                echo '<td>' . htmlspecialchars($ma['uhrzeit']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>Derzeit sind keine Mitarbeiter anwesend.</p>';
        }
        
    } catch(PDOException $e) {
        echo '<p><strong>Fehler bei Abfrage:</strong> ' . $e->getMessage() . '</p>';
        echo '<p><em>Möglicherweise existieren noch keine Zeiterfassungs-Daten.</em></p>';
    }
    ?>

    <!-- RECENT TIME ENTRIES -->
    <h3>📋 Letzte Zeiterfassungen</h3>
    <?php
    try {
        $sql = "
        SELECT m.vorname, m.nachname, z.ereignis, z.uhrzeit 
        FROM zeiterfassung z 
        JOIN mitarbeiter m ON z.mitarbeiter_id = m.id 
        ORDER BY z.uhrzeit DESC 
        LIMIT 10";
        
        $stmt = $conn->query($sql);
        $erfassungen = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($erfassungen) > 0) {
            echo '<table>';
            echo '<tr><th>Name</th><th>Ereignis</th><th>Uhrzeit</th></tr>';
            foreach ($erfassungen as $e) {
                $color = $e['ereignis'] == 'Kommen' ? '#d4edda' : '#f8d7da';
                echo '<tr style="background-color: ' . $color . '">';
                echo '<td>' . htmlspecialchars($e['vorname'] . ' ' . $e['nachname']) . '</td>';
                echo '<td>' . htmlspecialchars($e['ereignis']) . '</td>';
                echo '<td>' . htmlspecialchars($e['uhrzeit']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>Noch keine Zeiterfassungen vorhanden.</p>';
        }
        
    } catch(PDOException $e) {
        echo '<p><strong>Fehler:</strong> ' . $e->getMessage() . '</p>';
    }
    ?>
    
    <!-- DEBUG INFO (temporarily) -->
    <div style="margin-top: 30px; padding: 15px; background: #f0f0f0; border: 1px solid #ccc;">
        <h4>Debug Info:</h4>
        <?php
        echo "Session Status: " . session_status() . "<br>";
        echo "Session ID: " . session_id() . "<br>";
        echo "Database Connection: " . ($conn ? "OK" : "FAILED") . "<br>";
        
        // Test simple query
        try {
            $test = $conn->query("SELECT 1 as test");
            echo "Simple Query Test: OK<br>";
        } catch (Exception $e) {
            echo "Simple Query Test: FAILED - " . $e->getMessage() . "<br>";
        }
        ?>
    </div>
</body>

</html>
