<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $national_id = $_POST['national_id'];

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
    } else {
        echo "No data found";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Display Info</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
    <?php if (isset($user)): ?>
        <h2>Person Information</h2>
        <p>Name: <?php echo $user['name']; ?></p>
        <p>Surname: <?php echo $user['surname']; ?></p>
        <p>National ID: <?php echo $user['national_id']; ?></p>
        <p>Nationality: <?php echo $user['nationality']; ?></p>
        <img src="images/<?php echo $user['picture']; ?>" alt="Profile Picture">
        
        <form method="post" action="display_info.php">
            <input type="hidden" name="national_id" value="<?php echo $user['national_id']; ?>">
            Email: <input type="email" name="email" required>
            <br>
            <input type="submit" name="send_email" value="Send Confirmation Email">
        </form>

        <?php
        if (isset($_POST['send_email'])) {
            $email = $_POST['email'];
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;

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
        }
        ?>
    <?php endif; ?>
</body>
</html>
