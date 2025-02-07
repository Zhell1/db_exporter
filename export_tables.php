<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the form data
    $host = $_POST['host'];
    $user = $_POST['user'];
    $dbname = $_POST['dbname'];
    $password = $_POST['password'];
    $keyword = $_POST['keyword'];

    // Establish connection to MySQL
    $conn = new mysqli($host, $user, $password, $dbname);

    // Check if the connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to find tables matching the keyword
    $tables = [];
    $sql = "SHOW TABLES LIKE '$keyword'";
    $result = $conn->query($sql);

    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    // Step 2: Display the tables for selection
    if (count($tables) > 0) {
        echo "<form action='export_tables.php' method='POST'>";
        echo "<h3>Select tables to export:</h3>";
        echo "<input type='hidden' name='host' value='$host'>";
        echo "<input type='hidden' name='user' value='$user'>";
        echo "<input type='hidden' name='dbname' value='$dbname'>";
        echo "<input type='hidden' name='password' value='$password'>";
        echo "<input type='hidden' name='keyword' value='$keyword'>";

        echo "<button type='button' onclick='selectAllTables()'>Select All</button>";
        echo "<button type='button' onclick='deselectAllTables()'>Deselect All</button><br><br>";

        foreach ($tables as $table) {
            echo "<input type='checkbox' name='tables[]' value='$table' id='$table'>
                  <label for='$table'>$table</label><br>";
        }

        echo "<br><button type='submit' name='export' value='true'>Export Selected Tables</button>";
        echo "</form>";
    } else {
        echo "No tables found matching the keyword '$keyword'.";
    }

    // Close the connection
    $conn->close();
}
?>

<script>
    // JavaScript function to select/deselect all checkboxes
    function selectAllTables() {
        const checkboxes = document.querySelectorAll("input[name='tables[]']");
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
    }

    function deselectAllTables() {
        const checkboxes = document.querySelectorAll("input[name='tables[]']");
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    }
</script>
