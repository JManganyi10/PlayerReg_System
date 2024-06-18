<?php
session_start();

function connectDatabase($dbname) {
    $conn = new mysqli('localhost', 'root', '', $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function insertRegisteredPlayer($national_id, $club, $league) {
    $conn = connectDatabase('home_affairs');

    $sql = "SELECT * FROM persons WHERE national_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $national_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $sql = "INSERT INTO registered_players (name, surname, national_id, nationality, club, league) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssss', $user['name'], $user['surname'], $user['national_id'], $user['nationality'], $club, $league);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
}

if (isset($_GET['national_id']) && isset($_GET['club']) && isset($_GET['league'])) {
    $national_id = $_GET['national_id'];
    $club = $_GET['club'];
    $league = $_GET['league'];

    insertRegisteredPlayer($national_id, $club, $league);

    echo "Registration approved successfully.";
    header("Location: success.php");
    exit();
} else {
    echo "Invalid approval link.";
}
?>
