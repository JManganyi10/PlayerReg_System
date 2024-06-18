<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $national_id = $_POST['national_id'];
    $email = $_POST['email'];

    $conn = new mysqli('localhost', 'root', '', 'home_affairs');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM persons WHERE national_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $national_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($user) {
        $_SESSION['user'] = $user;
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;

        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'juniormanganyi96@gmail.com';
            $mail->Password = 'fhwksyrsgvzefdfo';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('juniormanganyi96@gmail.com');
            $mail->addAddress($email);
            $mail->isHTML(true);

            $mail->Subject = 'Confirmation Link';
            $mail->Body = "Hello player, your OTP is $otp. Use it to confirm your registration.";

            $mail->send();

            echo "<script>
                alert('OTP sent successfully');
                document.location.href = 'enter_otp.php';
            </script>";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "No data found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Page</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
    <form method="post" action="index.php">
        National ID: <input type="text" name="national_id" required>
        <br>
        Email: <input type="email" name="email" required>
        <br>
        <input type="submit" value="Search">
    </form>
</body>
</html>

