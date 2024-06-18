<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Function to establish database connection
function connectDatabase($dbname) {
    $conn = new mysqli('localhost', 'root', '', $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Function to generate a random token
function generateToken() {
    return bin2hex(random_bytes(32));
}

// Function to fetch player status from database
function getPlayerStatus($national_id) {
    $conn = connectDatabase('home_affairs');

    $sql = "SELECT * FROM player_status WHERE national_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param('s', $national_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $player_status = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $player_status;
}

// Function to update player status in the database
function updatePlayerStatus($national_id, $new_club_name, $new_league_name) {
    $conn = connectDatabase('home_affairs');

    $existingPlayer = getPlayerStatus($national_id);

    if (!$existingPlayer) {
        echo "No existing player found with national_id: $national_id";
        return false;
    }

    echo "Current values for national_id $national_id - Club: " . $existingPlayer['club_name'] . ", League: " . $existingPlayer['league_name'] . "<br>";
    echo "New values - Club: $new_club_name, League: $new_league_name<br>";

    $sql = "UPDATE player_status SET club_name = ?, league_name = ? WHERE national_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param('sss', $new_club_name, $new_league_name, $national_id);
    $stmt->execute();

    if ($stmt->affected_rows <= 0) {
        echo "No rows affected. Error: (" . $stmt->errno . ") " . $stmt->error;
        return false;
    }

    $stmt->close();
    $conn->close();

    return true;
}

// Main script starts here
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["email"])) {
    $national_id = $_POST['national_id'];
    $club = $_POST['club'];
    $league = $_POST['league'];
    $email = $_POST['email'];

    if (isset($_POST['confirm_action'])) {
        $confirmAction = $_POST['confirm_action'];

        if ($confirmAction === 'modify') {
            // Update player status
            $updateResult = updatePlayerStatus($national_id, $club, $league);
            if (!$updateResult) {
                die("Failed to update player status. <a href='javascript:history.back()'>Go back</a>");
            }

            // Generate a unique token
            $token = generateToken();

            // Store the token and other details in the session
            $_SESSION['confirmation_token'] = $token;
            $_SESSION['email'] = $email;
            $_SESSION['national_id'] = $national_id;
            $_SESSION['club'] = $club;
            $_SESSION['league'] = $league;

            // Example email content
            $subject = 'Approval Link for Player Registration';
            $message = "Dear player, please click the following link to confirm your registration: 
                        <a href='localhost/reg/confirm_registration.php?token=$token&national_id=$national_id&club=$club&league=$league'>Confirm Registration</a>";

            // Send email using PHPMailer
            $mail = new PHPMailer(true);

            try {
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'juniormanganyi96@gmail.com'; // Your Gmail address
                $mail->Password = 'fhwksyrsgvzefdfo'; // Your Gmail password
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                // Sender and recipient settings
                $mail->setFrom('juniormanganyi96@gmail.com');
                $mail->addAddress($email);
                $mail->isHTML(true);

                // Email content
                $mail->Subject = $subject;
                $mail->Body = $message;

                // Send email
                $mail->send();

                // Display success message and redirect
                echo "<script>
                        alert('Email sent successfully');
                        window.location.href = 'index.php';
                      </script>";

            } catch (Exception $e) {
                // Handle errors
                echo "<script>
                        alert('Email could not be sent. Error: {$mail->ErrorInfo}');
                        window.location.href = 'index.php';
                      </script>";
            }

        } else {
            // User cancelled, redirect to index or appropriate page
            header("Location: index.php");
            exit();
        }
    } else {
        // Fetch existing player status
        $existingPlayer = getPlayerStatus($national_id);

        if ($existingPlayer) {
            // Display a form for user confirmation
            echo "<form id='confirmForm' action='send_approval.php' method='post'>
                    <input type='hidden' name='national_id' value='$national_id'>
                    <input type='hidden' name='club' value='$club'>
                    <input type='hidden' name='league' value='$league'>
                    <input type='hidden' name='email' value='$email'>
                    <input type='hidden' name='confirm_action' id='confirmAction'>
                  </form>";

            // JavaScript to submit the form based on user action
            echo "<script>
                    var confirmMsg = confirm('Player status already exists. Do you want to proceed with modifying?');
                    if (confirmMsg) {
                        document.getElementById('confirmAction').value = 'modify';
                    } else {
                        document.getElementById('confirmAction').value = 'cancel';
                    }
                    document.getElementById('confirmForm').submit();
                  </script>";
            exit(); // Stop further execution
        }

        // If player status does not exist, proceed with creating new entry
        // Generate a unique token
        $token = generateToken();

        // Store the token and other details in the session
        $_SESSION['confirmation_token'] = $token;
        $_SESSION['national_id'] = $national_id;
        $_SESSION['club'] = $club;
        $_SESSION['league'] = $league;

        // Example email content
        $subject = 'Approval Link for Player Registration';
        $message = "Dear player, please click the following link to confirm your registration: 
                    <a href='localhost/reg/confirm_registration.php?token=$token&national_id=$national_id&club=$club&league=$league'>Confirm Registration</a>";

        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'juniormanganyi96@gmail.com'; // Your Gmail address
            $mail->Password = 'fhwksyrsgvzefdfo'; // Your Gmail password
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            // Sender and recipient settings
            $mail->setFrom('juniormanganyi96@gmail.com');
            $mail->addAddress($email);
            $mail->isHTML(true);

            // Email content
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Send email
            $mail->send();

            // Display success message and redirect
            echo "<script>
                    alert('Email sent successfully');
                    window.location.href = 'index.php';
                  </script>";

        } catch (Exception $e) {
            // Handle errors
            echo "<script>
                    alert('Email could not be sent. Error: {$mail->ErrorInfo}');
                    window.location.href = 'index.php';
                  </script>";
        }
    }
} else {
    // If accessed directly without proper POST data, redirect to index or appropriate page
    header("Location: index.php");
    exit();
}
?>
