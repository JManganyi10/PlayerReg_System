<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

function connectDatabase($dbname) {
    $conn = new mysqli('localhost', 'root', '', $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function searchPlayer($national_id) {
    $conn = connectDatabase('home_affairs');

    $sql = "SELECT * FROM persons WHERE national_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $national_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $user;
}

function getClubs() {
    $conn = connectDatabase('home_affairs');

    $sql = "SELECT club_name FROM clubs";
    $result = $conn->query($sql);

    $clubs = [];
    while ($row = $result->fetch_assoc()) {
        $clubs[] = $row['club_name'];
    }

    $conn->close();

    return $clubs;
}

function getLeagues() {
    $conn = connectDatabase('home_affairs');

    $sql = "SELECT league_name FROM leagues";
    $result = $conn->query($sql);

    $leagues = [];
    while ($row = $result->fetch_assoc()) {
        $leagues[] = $row['league_name'];
    }

    $conn->close();

    return $leagues;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="search_player.php">Search Player</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_confirmed_players.php">View Confirmed Players</a>
                    </li>
                </ul>
            </div>
        </nav>

        <h1>Search Player</h1>
        <form method="post" action="search_player.php" class="form-inline justify-content-center">
            <div class="form-group">
                <label for="national_id" class="mr-2">National ID:</label>
                <input type="text" name="national_id" id="national_id" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary ml-2">Search</button>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['national_id'])): ?>
            <?php $user = searchPlayer($_POST['national_id']); ?>
            <?php if ($user): ?>
                <div class="mt-4">
                    <h2>Personn Information</h2>
                    <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
                    <p><strong>Surname:</strong> <?php echo $user['surname']; ?></p>
                    <p><strong>National ID:</strong> <?php echo $user['national_id']; ?></p>
                    <p><strong>Nationality:</strong> <?php echo $user['nationality']; ?></p>
                    <?php if (!empty($user['picture'])): ?>
                        <img src="images/<?php echo $user['picture']; ?>" alt="Player Picture" class="img-thumbnail mt-3" style="max-width: 300px;">
                    <?php endif; ?>
                </div>

                <form method="post" action="send_approval.php" class="mt-4">
                    <input type="hidden" name="national_id" value="<?php echo $user['national_id']; ?>">
                    <div class="form-group">
                        <label for="club">Select Club:</label>
                        <select name="club" id="club" class="form-control" required>
                            <?php $clubs = getClubs(); ?>
                            <?php foreach ($clubs as $club): ?>
                                <option value="<?php echo $club; ?>"><?php echo $club; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="league">Select League:</label>
                        <select name="league" id="league" class="form-control" required>
                            <?php $leagues = getLeagues(); ?>
                            <?php foreach ($leagues as $league): ?>
                                <option value="<?php echo $league; ?>"><?php echo $league; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email">Enter Email Address:</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Confirmation Link</button>
                </form>
            <?php else: ?>
                <p class="text-danger mt-3">No user found with the provided National ID.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
