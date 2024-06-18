<?php
// Ensure this PHP file is accessed via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if 'league_name' parameter is set
    if (isset($_POST['league_name'])) {
        $league_name = $_POST['league_name'];

        // Connect to database
        $conn = new mysqli('localhost', 'root', '', 'home_affairs');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare SQL statement to fetch clubs based on league
        $sql = "SELECT club_name FROM clubs WHERE league_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $league_name);
        $stmt->execute();
        $result = $stmt->get_result();

        // Collect clubs into an array
        $clubs = [];
        while ($row = $result->fetch_assoc()) {
            $clubs[] = $row['club_name'];
        }

        // Close statement and connection
        $stmt->close();
        $conn->close();

        // Output clubs as options for AJAX response
        if (!empty($clubs)) {
            $output = '<option value="">Select Club</option>';
            foreach ($clubs as $club) {
                $output .= '<option value="' . htmlspecialchars($club) . '">' . htmlspecialchars($club) . '</option>';
            }
            echo $output;
        } else {
            echo '<option value="">No Clubs Found</option>';
        }
    } else {
        echo '<option value="">Error: League name not provided</option>';
    }
} else {
    // Return error if accessed via incorrect method
    echo '<option value="">Error: Invalid Request</option>';
}
?>
