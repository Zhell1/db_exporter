<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tables'])) {
    $host = $_POST['host'];
    $user = $_POST['user'];
    $dbname = $_POST['dbname'];
    $password = $_POST['password'];
    $tables = $_POST['tables']; // Array of selected tables

    // Connect to MySQL
    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Suggest filename (e.g., "your_database_2024-02-07.sql")
    $suggestedFilename = $dbname . "_" . date("Y-m-d") . ".sql";

    // Start SQL dump header
    $sqlDump = "-- phpMyAdmin SQL Dump\n";
    $sqlDump .= "-- Host: $host\n";
    $sqlDump .= "-- Database: `$dbname`\n";
    $sqlDump .= "-- Generation Time: " . date("Y-m-d H:i:s") . "\n";
    $sqlDump .= "--\n\nSET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        // Export structure
        $sqlDump .= "--\n-- Table structure for table `$table`\n--\n";
        $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
        
        $result = $conn->query("SHOW CREATE TABLE `$table`");
        if ($row = $result->fetch_assoc()) {
            $sqlDump .= $row['Create Table'] . ";\n\n";
        }

        // Export data
        $result = $conn->query("SELECT * FROM `$table`");
        if ($result->num_rows > 0) {
            $sqlDump .= "--\n-- Dumping data for table `$table`\n--\n";
            $sqlDump .= "INSERT INTO `$table` (";

            // Get column names
            $columns = [];
            $columnsResult = $conn->query("SHOW COLUMNS FROM `$table`");
            while ($colRow = $columnsResult->fetch_assoc()) {
                $columns[] = "`" . $colRow['Field'] . "`";
            }
            $sqlDump .= implode(", ", $columns) . ") VALUES\n";

            // Batch insert values
            $batch = [];
            while ($row = $result->fetch_assoc()) {
                $values = array_map(function ($value) use ($conn) {
                    if ($value === null) return 'NULL';
                    return "'" . $conn->real_escape_string($value) . "'";
                }, array_values($row));
                $batch[] = "(" . implode(", ", $values) . ")";
            }

            $sqlDump .= implode(",\n", $batch) . ";\n\n";
        }
    }

    $conn->close();
} else {
    die("No tables selected for export.");
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Export</title>
</head>
<body>

    <h2>SQL Export</h2>
    <label for="filename">Suggested Filename:</label>
    <input type="text" id="filename" value="<?= htmlspecialchars($suggestedFilename) ?>" size="40"><br><br>

    <label for="sqlOutput">SQL Dump:</label><br>
    <button onclick="copyToClipboard()">Copy to Clipboard</button><br>
    <textarea id="sqlOutput" rows="20" cols="100"><?= htmlspecialchars($sqlDump) ?></textarea><br>

    <script>
        function copyToClipboard() {
            let textarea = document.getElementById("sqlOutput");
            textarea.select();
            document.execCommand("copy");
            alert("SQL copied to clipboard!");
        }
    </script>

</body>
</html>
