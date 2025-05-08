<?php
session_start();
require_once 'db_connection.php'; // Ensure this function throws exceptions on failure

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['acc_id'];
    $acc_pass = $_POST['acc_pass'];

    try {
        $con = getDatabaseConnection();

        // Debug: Log the input values
        error_log("Login attempt - User ID: " . $user_id . ", Password: " . $acc_pass);

        // Check if user exists in user_prof and use user_id as password
        $stmt = $con->prepare("SELECT user_id, user_fullname 
                             FROM user_prof 
                             WHERE user_id = ? AND user_id = ?");
        $stmt->bind_param("ss", $user_id, $acc_pass);
        $stmt->execute();
        $result = $stmt->get_result();

        // Debug: Log the query result
        error_log("Query result rows: " . $result->num_rows);

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Debug: Log the retrieved data
            error_log("Retrieved data - User ID: " . $row['user_id']);

            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_fullname'] = $row['user_fullname'];

            // Route based on user ID
            if ($row['user_id'] == '2023305122') {
                header("Location: USG-Off_Dash.php");
            } else {
                header("Location: Stud_Dash.php");
            }
            exit();
        } else {
            $login_error = "ERROR: Incorrect Credentials.";
        }

        $stmt->close();
        $con->close();
    } catch (Exception $e) {
        // Log the error message to a file
        error_log("Database error: " . $e->getMessage());

        // Display a generic error message to the user
        $login_error = "An unexpected error occurred. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USG-Login</title>
    <link rel="icon" href="../img/USG-Logo.jpg"/>

    <link rel="stylesheet" href="main.css">
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<style>

@import url('https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

*{
    font-family: "Poppins", sans-serif;
    font-weight: 400;
    font-style: normal;
}
body{
    background: linear-gradient(135deg, rgba(57, 66, 77, 0.5) 0%, rgba(6, 73, 117, 0.9) 100%), url('../img/USG-BG.png');
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
}
.right-box{
    padding: 40px 30px 40px 30px;
}
.log-txt{
    color: #fff;
    font-size: 40px;
    font-weight: 700;
}
.welcome-txt{
    color: #fff;
    font-weight: 500;
}
::placeholder{
    font-size: 16px;
}
.log-btn-txt{
    font-weight: 700;
    background: linear-gradient(to right,rgb(105, 161, 194) 0%,rgb(26, 21, 123) 100%);
}

@media only screen and (max-width: 768px) {
    .box-area{
        margin: 0 10px;
    }
    .left-box {
        height: auto;
        padding: 20px;
        overflow: visible;
    }
    .right-box{
        padding: 20px;
    }
}
.error-message {
    min-height: 2em;
    width: 18rem;
    color: #fff;
    visibility: hidden;
    transition: visibility 0.3s ease;
}

.error-message.visible {
    background-color: #DC143C;
    visibility: visible;
}
.error-msg{
    margin-left: 1rem;
}


</style>

</head>
<body>
    
    <!-- MAIN CONTAINER -->

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
    
    <!-- LOGIN CONTAINER -->

        <div class="row border rounded-5 p-3 bg-white shadow box-area">

    <!-- LEFT BOX -->

            <div class="col-md-6 d-flex justify-content-center align-items-center flex-column left-box">
                <div class="featured-image mb-3 text-center w-100">
                    <img src="../img/USG-Logo2.png" alt="USG Logo" class="img-fluid" style="max-width: 250px; height: auto;">
                </div>
            </div>

    <!-- RIGHT BOX -->

            <div class="col-md-6 rounded-4 right-box" style="background: linear-gradient(140deg, rgba(33, 25, 72, 1) 25%, rgba(249, 166, 2, 1) 60%, rgba(187, 201, 189, 1) 80%);">
                <div class="row align-items-center">
                    <div class="header-text mb-4">
                        <h1 class="log-txt">LOG IN</h1>
                        <h5 class="welcome-txt">Welcome to UNIVERSITY OF STUDENT GOVERNMENT</h5>
                    </div>

                    <form action="USG-Login.php" method="POST">

                        <div class="d-flex align-items-center rounded-2 mb-3 error-message <?php echo !empty($login_error) ? 'visible' : ''; ?>">
                            <div class="error-msg"><?php echo htmlspecialchars($login_error); ?></div>
                        </div>

                        <div class="input-group mb-3">
                            <input type="text" name="acc_id" class="form-control form-control-lg bg-light fs-6" placeholder="ID Number">
                        </div>

                        <div class="input-group mb-3">
                            <input type="password" name="acc_pass" class="form-control form-control-lg bg-light fs-6" placeholder="Password">
                        </div>

                        <div class="input-group mb-5 d-flex">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="formCheck">
                                <label for="form-check" class="form-check-label text-light"><small>Remember Me</small></label>
                            </div>
                        </div>

                        <div class="input-group mb-3">
                            <button type="submit" class="btn btn-lg btn-primary w-100 fs-5 log-btn-txt">LOG IN</button>
                        </div>

                    </form>

                </div>
            </div>
    
        </div>
    </div>

</body>
</html>
