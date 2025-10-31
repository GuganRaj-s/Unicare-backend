<?php
// Database connection details (replace with your own)
$host = 'localhost';
$user = 'unicareNewUser';
$pass = '*=RXr}sr8P3F';
$db = 'dbUnicareNew';

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Basic authentication for security
$valid_user = 'client';
$valid_pass = 'securepassword'; // Replace with a strong password
if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] != $valid_user || 
    $_SERVER['PHP_AUTH_PW'] != $valid_pass) {
    header('WWW-Authenticate: Basic realm="Restricted Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Access denied.';
    exit;
}

// Get all tables in the database
$tables_query = "SHOW TABLES";
$tables_result = $conn->query($tables_query);

?>
<!DOCTYPE html>
<html>
<head>
    <title>All Database Tables</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .table-container { margin-bottom: 40px; }
    </style>
</head>
<body>
    <h1>All Tables in Database: <?php echo $db; ?></h1>
    <?php
    // Loop through each table
    if ($tables_result->num_rows > 0) {
        while ($table_row = $tables_result->fetch_array()) {
            $table_name = $table_row[0];
            echo "<div class='table-container'>";
            echo "<h2>Table: " . htmlspecialchars($table_name) . "</h2>";

            // Query to fetch all records from the current table
            $sql = "SELECT * FROM `$table_name`";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                echo "<table>";
                // Display column headers
                echo "<thead><tr>";
                $fields = $result->fetch_fields();
                foreach ($fields as $field) {
                    echo "<th>" . htmlspecialchars($field->name) . "</th>";
                }
                echo "</tr></thead><tbody>";

                // Display table rows
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No records found in table " . htmlspecialchars($table_name) . "</p>";
            }
            echo "</div>";
        }
    } else {
        echo "<p>No tables found in the database.</p>";
    }

    // Close the connection
    $conn->close();
    ?>
</body>
</html>