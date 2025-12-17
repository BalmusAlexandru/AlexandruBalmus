<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>JustPaper AG - Zeiterfassung</title>
    <style>
        body { font-family: Arial; max-width: 500px; margin: 50px auto; padding: 20px; }
        .container { border: 1px solid #ccc; padding: 20px; border-radius: 5px; }
        input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔒 JustPaper AG - Zeiterfassung</h2>
        <?php
session_start();
if (isset($_SESSION['message'])) {
    echo '<div style="padding: 10px; margin: 10px 0; border-radius: 3px; background: ' . 
         ($_SESSION['message_type'] == 'success' ? '#d4edda' : '#f8d7da') . '; color: ' .
         ($_SESSION['message_type'] == 'success' ? '#155724' : '#721c24') . ';">' . 
         $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
        <p>Bitte RFID-Karte scannen oder Code eingeben:</p>
        
        <form action="login_process.php" method="post">
            <input type="text" name="rfid_code" placeholder="RFID-Code eingeben..." required>
            <button type="submit">Zeit erfassen</button>
        </form>
        
        <hr>
        <p><strong>Test-Codes:</strong><br>RFID001, RFID002, RFID003</p>
        <p><a href="admin.php">📊 Admin-Bereich</a></p>
    </div>
</body>
</html>