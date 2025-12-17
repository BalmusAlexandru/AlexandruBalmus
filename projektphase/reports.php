<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berichte - JustPaper AG</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .overtime-positive { background-color: #ffcccc; }
        .overtime-negative { background-color: #ccffcc; }
        .filter-form { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h2>📈 Arbeitszeitberichte - JustPaper AG</h2>
    <p><a href="index.php">← Zurück zur Zeiterfassung</a> | <a href="admin.php">📊 Admin</a></p>

    <div class="filter-form">
        <form method="get">
            <label>Datum von: <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>"></label>
            <label>Datum bis: <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? date('Y-m-t') ?>"></label>
            <button type="submit">Filter anwenden</button>
        </form>
    </div>

    <h3>⏰ Arbeitszeiten & Überstunden</h3>
    <?php
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-t');

    try {
        $sql = "
        SELECT 
            m.id,
            m.vorname,
            m.nachname,
            m.abteilung,
            SEC_TO_TIME(SUM(
                CASE 
                    WHEN z_next.ereignis = 'Gehen' THEN 
                        TIMESTAMPDIFF(SECOND, z.uhrzeit, z_next.uhrzeit)
                    ELSE 0 
                END
            )) as total_arbeitszeit,
            SEC_TO_TIME(
                SUM(
                    CASE 
                        WHEN z_next.ereignis = 'Gehen' THEN 
                            TIMESTAMPDIFF(SECOND, z.uhrzeit, z_next.uhrzeit)
                        ELSE 0 
                    END
                ) - (8 * 3600 * COUNT(DISTINCT DATE(z.uhrzeit)))
            ) as ueberstunden
        FROM mitarbeiter m
        LEFT JOIN zeiterfassung z ON m.id = z.mitarbeiter_id AND DATE(z.uhrzeit) BETWEEN ? AND ?
        LEFT JOIN zeiterfassung z_next ON 
            z.mitarbeiter_id = z_next.mitarbeiter_id AND 
            z_next.uhrzeit = (
                SELECT MIN(z2.uhrzeit) 
                FROM zeiterfassung z2 
                WHERE z2.mitarbeiter_id = z.mitarbeiter_id 
                AND z2.uhrzeit > z.uhrzeit 
                AND DATE(z2.uhrzeit) = DATE(z.uhrzeit)
            )
        WHERE z.ereignis = 'Kommen'
        GROUP BY m.id, m.vorname, m.nachname, m.abteilung
        ORDER BY m.nachname, m.vorname";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        $berichte = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($berichte) > 0) {
            echo '<table>';
            echo '<tr><th>Name</th><th>Abteilung</th><th>Gesamt Arbeitszeit</th><th>Überstunden</th><th>Arbeitstage</th></tr>';
            
            foreach ($berichte as $bericht) {
                $overtime_class = '';
                if ($bericht['ueberstunden'] && $bericht['ueberstunden'] != '00:00:00') {
                    $overtime_class = (strtotime($bericht['ueberstunden']) > 0) ? 'overtime-positive' : 'overtime-negative';
                }
                
                echo '<tr class="' . $overtime_class . '">';
                echo '<td>' . htmlspecialchars($bericht['vorname'] . ' ' . $bericht['nachname']) . '</td>';
                echo '<td>' . htmlspecialchars($bericht['abteilung']) . '</td>';
                echo '<td>' . ($bericht['total_arbeitszeit'] ?: '00:00:00') . '</td>';
                echo '<td>' . ($bericht['ueberstunden'] ?: '00:00:00') . '</td>';
                
                $days_sql = "SELECT COUNT(DISTINCT DATE(uhrzeit)) as tage FROM zeiterfassung WHERE mitarbeiter_id = ? AND DATE(uhrzeit) BETWEEN ? AND ?";
                $days_stmt = $conn->prepare($days_sql);
                $days_stmt->execute([$bericht['id'], $start_date, $end_date]);
                $days = $days_stmt->fetch(PDO::FETCH_ASSOC);
                
                echo '<td>' . $days['tage'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>Keine Zeiterfassungen im ausgewählten Zeitraum.</p>';
        }
        
    } catch(PDOException $e) {
        echo '<p>Fehler: ' . $e->getMessage() . '</p>';
    }
    ?>

    <h3>📅 Tägliche Details</h3>
    <?php
    try {
        $sql = "
        SELECT 
            m.vorname,
            m.nachname,
            DATE(z.uhrzeit) as tag,
            SEC_TO_TIME(SUM(
                CASE 
                    WHEN z_next.ereignis = 'Gehen' THEN 
                        TIMESTAMPDIFF(SECOND, z.uhrzeit, z_next.uhrzeit)
                    ELSE 0 
                END
            )) as taegliche_zeit
        FROM mitarbeiter m
        LEFT JOIN zeiterfassung z ON m.id = z.mitarbeiter_id AND DATE(z.uhrzeit) BETWEEN ? AND ?
        LEFT JOIN zeiterfassung z_next ON 
            z.mitarbeiter_id = z_next.mitarbeiter_id AND 
            z_next.uhrzeit = (
                SELECT MIN(z2.uhrzeit) 
                FROM zeiterfassung z2 
                WHERE z2.mitarbeiter_id = z.mitarbeiter_id 
                AND z2.uhrzeit > z.uhrzeit 
                AND DATE(z2.uhrzeit) = DATE(z.uhrzeit)
            )
        WHERE z.ereignis = 'Kommen'
        GROUP BY m.vorname, m.nachname, DATE(z.uhrzeit)
        ORDER BY DATE(z.uhrzeit) DESC, m.nachname";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        $taeglich = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($taeglich) > 0) {
            echo '<table>';
            echo '<tr><th>Name</th><th>Datum</th><th>Arbeitszeit</th></tr>';
            foreach ($taeglich as $tag) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($tag['vorname'] . ' ' . $tag['nachname']) . '</td>';
                echo '<td>' . htmlspecialchars($tag['tag']) . '</td>';
                echo '<td>' . ($tag['taegliche_zeit'] ?: '00:00:00') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        
    } catch(PDOException $e) {
        echo '<p>Fehler: ' . $e->getMessage() . '</p>';
    }
    ?>
</body>
</html>