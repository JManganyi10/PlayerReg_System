<?php
session_start();

function connectDatabase($dbname) {
    $conn = new mysqli('localhost', 'root', '', $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function insertRegisteredPlayer($user) {
    $safa_id = 'SAFA' . rand(100000, 999999);

    $conn = connectDatabase('home_affairs');
    $stmt = $conn->prepare("INSERT INTO registered_players (name, surname, national_id, nationality, safa_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $user['name'], $user['surname'], $user['national_id'], $user['nationality'], $safa_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];
    if ($entered_otp == $_SESSION['otp']) {
        insertRegisteredPlayer($_SESSION['user']);
        header("Location: success.php");
        exit();
    } else {
        echo "Invalid OTP";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enter OTP</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Enter OTP</h1>
        <form method="post" action="enter_otp.php" class="form-inline justify-content-center">
            <div class="form-group">
                <label for="otp" class="mr-2">OTP:</label>
                <input type="text" name="otp" id="otp" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary ml-2">Submit</button>
        </form>
        <a href="index.php" class="btn btn-primary mt-3">Back to Home</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
