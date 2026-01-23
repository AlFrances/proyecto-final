<?php
include 'connect.php';
session_start();

/* ---------------- SIGN UP ---------------- */
if (isset($_POST['signUp'])) {

    $firstName = $_POST['fName'];
    $lastName  = $_POST['lName'];
    $email     = $_POST['email'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        echo "Email Address Already Exists!";
    } else {

        // Insert user
        $insertQuery = $conn->prepare(
            "INSERT INTO users (firstName, lastName, email, password)
             VALUES (?, ?, ?, ?)"
        );
        $insertQuery->bind_param("ssss", $firstName, $lastName, $email, $password);

        if ($insertQuery->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

/* ---------------- SIGN IN ---------------- */
if (isset($_POST['signIn'])) {

    $email    = $_POST['email'];
    $password = $_POST['password'];

    $sql = $conn->prepare(
        "SELECT firstName, lastName, email, password FROM users WHERE email = ?"
    );
    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {

            $_SESSION['email']     = $row['email'];
            $_SESSION['firstName'] = $row['firstName'];
            $_SESSION['lastName']  = $row['lastName'];

            header("Location: homepage.php");
            exit();
        }
    }

    echo "Incorrect Email or Password";
}
?>
