<?php
include("../includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $conn->real_escape_string($_POST['name']);
  $location = $conn->real_escape_string($_POST['location']);
  $device_sensor_id = $conn->real_escape_string($_POST['device_sensor_id']);

  $sql = "INSERT INTO refilling_stations (name, location, device_sensor_id) 
          VALUES ('$name', '$location', '$device_sensor_id')";

  if ($conn->query($sql)) {
    header("Location: stations.php");
    exit();
  } else {
    echo "Error: " . $conn->error;
  }
}
?>
