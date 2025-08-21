<?php
include("../includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Disable foreign key checks temporarily if needed
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    $stmt = $conn->prepare("DELETE FROM refilling_stations WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        header("Location: stations.php");
        exit();
    } else {
        echo "Error deleting station: " . $stmt->error;
    }
} else {
    echo "Invalid request.";
}
?>
