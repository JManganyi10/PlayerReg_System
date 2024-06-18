<?php
session_start();

// Database connection parameters
$servername = 'localhost';
$username = 'root';  // Replace with your actual database username
$password = '';  // Replace with your actual database password
$dbname = 'home_affairs';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch approved players with club and league names
function getApprovedPlayers($conn) {
    $sql = "SELECT ps.*, c.club_name, l.league_name 
            FROM player_status ps 
            INNER JOIN clubs c ON ps.club_name = c.club_name
            INNER JOIN leagues l ON ps.league_name = l.league_name
            WHERE ps.status = 'Approved'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $players = [];
        while ($row = $result->fetch_assoc()) {
            $players[] = $row;
        }
        return $players;
    } else {
        return [];
    }
}

// Get approved players
$approvedPlayers = getApprovedPlayers($conn);

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Confirmed Players</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>View Confirmed Players</h1>
        
        <?php if (!empty($approvedPlayers)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>National ID</th>
                        <th>Name</th>
                        <th>Club</th>
                        <th>League</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approvedPlayers as $player): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($player['national_id']); ?></td>
                            <td><?php echo htmlspecialchars($player['name']); ?></td>
                            <td><?php echo htmlspecialchars($player['club_name']); ?></td>
                            <td><?php echo htmlspecialchars($player['league_name']); ?></td>
                            <td><?php echo htmlspecialchars($player['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No approved players found.</p>
        <?php endif; ?>

        <!-- Back Button -->
        <a href="search_player.php" class="btn btn-primary">Back to Home</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
