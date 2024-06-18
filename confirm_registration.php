<?php
session_start();

function connectDatabase($dbname) {
    $conn = new mysqli('localhost', 'root', '', $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function updatePlayerStatus($national_id, $club_name, $league_name) {
    $conn = connectDatabase('home_affairs');

    // Retrieve user details from the persons table
    $sql = "SELECT name, surname, national_id, picture FROM persons WHERE national_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $national_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Insert or update the player_status table
        $sql = "INSERT INTO player_status (name, surname, national_id, picture, SAFA_id, club_name, league_name, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Approved')
                ON DUPLICATE KEY UPDATE 
                name = VALUES(name),
                surname = VALUES(surname),
                picture = VALUES(picture),
                SAFA_id = VALUES(SAFA_id),
                status = 'Approved'";
        
        $stmt = $conn->prepare($sql);
        $SAFA_id = rand(1000, 9999); // Example: Generate a random SAFA_id (you should have a proper method for this)
        $stmt->bind_param('ssssiss', $user['name'], $user['surname'], $user['national_id'], $user['picture'], $SAFA_id, $club_name, $league_name);
        
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    return $user ? true : false;
}

// Check if token and necessary parameters are provided in URL
if (isset($_GET['token'], $_GET['national_id'], $_GET['club'], $_GET['league'])) {
    $token = $_GET['token'];
    $national_id = $_GET['national_id'];
    $club_name = $_GET['club'];
    $league_name = $_GET['league'];

    // Verify token (for simplicity, checking against session stored token)
    if ($token === $_SESSION['confirmation_token']) {
        // Update player status
        $updated = updatePlayerStatus($national_id, $club_name, $league_name);
        
        if ($updated) {
            echo "Registration confirmed successfully!";
        } else {
            echo "Failed to confirm registration. User not found.";
        }
    } else {
        echo "Invalid token or token expired.";
    }
} else {
    echo "Token not provided or missing parameters.";
}
?>
