<?php
include_once 'connection.php';

function sanitize_input($input)
{
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

function execute_query($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    return $result;
}

function get_admin_by_username($username)
{
    $username = sanitize_input($username);
    $query = "SELECT * FROM admin WHERE username = '$username'";
    return execute_query($query);
}

function insert_switch($name, $ip_address, $area, $status = 'up')
{
    $name = sanitize_input($name);
    $ip_address = sanitize_input($ip_address);
    $area = sanitize_input($area);
    $status = sanitize_input($status);

    $query = "INSERT INTO switch (name, ip_address, area, status) 
              VALUES ('$name', '$ip_address', '$area', '$status')";
    return execute_query($query);
}

function get_months_years_from_switches()
{
    global $conn;

    $query = "SELECT DISTINCT DATE_FORMAT(created_at, '%M %Y') AS month_year 
              FROM switch ORDER BY created_at DESC";
    $result = execute_query($query);

    $months_years = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $months_years[] = $row['month_year'];
    }

    return $months_years;
}

function get_switches_by_month_year($month_year)
{
    global $conn;

    $query = "SELECT * FROM switch WHERE DATE_FORMAT(created_at, '%M %Y') = '$month_year'";
    return execute_query($query);
}

function get_all_switches()
{
    $query = "SELECT * FROM switch";
    return execute_query($query);
}

function update_switch_status($id, $status, $last_down = null)
{
    $id = sanitize_input($id);
    $status = sanitize_input($status);
    $last_down_query = $last_down ? ", last_down = '$last_down'" : "";

    $query = "UPDATE switch 
              SET status = '$status' $last_down_query, updated_at = NOW() 
              WHERE id = $id";
    return execute_query($query);
}

function delete_switch($id)
{
    $id = sanitize_input($id);
    $query = "DELETE FROM switch WHERE id = $id";
    return execute_query($query);
}
