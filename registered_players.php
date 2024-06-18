<?php
session_start();

function connectDatabase($dbname) {
    $conn = new mysqli('localhost', 'root', '', $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function displayRegisteredPlayers() {
    $conn = connectDatabase('home_affairs');
    $sql = "SELECT name, surname, national_id, nationality, safa_id FROM registered_players";
    $result = $conn->query($sql);

    echo '<table class="table">';
    echo '<thead><tr><th>Name</th><th>Surname</th><th>National ID</th><th>Nationality</th><th>SAFA ID</th></tr></thead>';
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['surname']) . '</td>';
        echo '<td>' . htmlspecialchars($row['national_id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nationality']) . '</td>';
        echo '<td>' . htmlspecialchars($row['safa_id']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registered Players</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Registered Players</h1>
        <?php displayRegisteredPlayers(); ?>
        <a href="index.php" class="btn btn-primary mt-3">Back to Home</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
