<?php
include_once 'functions.php';

$name = "Admin Switch";
$username = "admin-switch";
$password = "admin-switch2024";

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$check_query = "SELECT * FROM admin WHERE username = '$username'";
$result = execute_query($check_query);

if (mysqli_num_rows($result) > 0) {
    echo "Admin with username '$username' already exists.";
} else {
    $insert_query = "INSERT INTO admin (name, username, password, created_at) 
                     VALUES ('$name', '$username', '$hashed_password', NOW())";
    if (execute_query($insert_query)) {
        echo "Admin '$name' added successfully.";
    } else {
        echo "Failed to add admin: " . mysqli_error($conn);
    }
}
