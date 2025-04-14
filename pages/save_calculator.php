<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    require_once('../includes/db.php');

    try {
        // Sanitize and retrieve form data
        $user_id = $_SESSION['user_id'];
        $car_travel = intval($_POST['car_travel']);
        $public_transport = intval($_POST['public_transport']);
        $flights = intval($_POST['flights']);
        $electricity = intval($_POST['electricity']);
        $natural_gas = intval($_POST['natural_gas']);
        $meat_consumption = intval($_POST['meat_consumption']);
        $local_food = intval($_POST['local_food']);

        // Insert data into the database using PDO
        $sql = "INSERT INTO co2_calculations (user_id, car_travel, public_transport, flights, electricity, natural_gas, meat_consumption, local_food, created_at)
                VALUES (:user_id, :car_travel, :public_transport, :flights, :electricity, :natural_gas, :meat_consumption, :local_food, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':car_travel' => $car_travel,
            ':public_transport' => $public_transport,
            ':flights' => $flights,
            ':electricity' => $electricity,
            ':natural_gas' => $natural_gas,
            ':meat_consumption' => $meat_consumption,
            ':local_food' => $local_food
        ]);

        $_SESSION['success_message'] = "CO2 calculation saved successfully!";
        header("Location: co2.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error saving calculation: " . $e->getMessage();
        header("Location: co2.php");
        exit();
    }
} else {
    header("Location: co2.php");
    exit();
}
?>