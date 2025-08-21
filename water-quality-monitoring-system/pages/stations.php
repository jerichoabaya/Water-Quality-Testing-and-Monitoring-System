<?php 
include("../includes/db.php");

$sql = "SELECT * FROM refilling_stations ORDER BY name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stations - Water Quality Monitor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body { background: #0e1117; color: #fff; }
    .navbar {
      background-color: #1f2733;
      box-shadow: 0 2px 10px rgba(0, 198, 255, 0.2);
    }
    .navbar-brand, .nav-link, .btn {
      color: #fff;
    }
    .navbar-nav .nav-link:hover {
      color: #00c6ff;
    }
    .container { margin-top: 40px; }
    .card {
      background-color: #1f2733;
      color: #fff;
      border: 1px solid #00c6ff;
      border-radius: 15px;
    }
    .card:hover { box-shadow: 0 0 15px #00c6ff; cursor: pointer; }
    .add-btn { background-color: #00c6ff; border: none; }
    .add-btn:hover { background-color: #00a3cc; }
    .delete-btn { background-color: #dc3545; border: none; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><i class="fas fa-tint"></i> Water Quality Monitor System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="stations.php"><i class="fas fa-building"></i> Stations</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="login.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Water Refilling Stations</h2>
    <button class="btn add-btn" data-bs-toggle="modal" data-bs-target="#addStationModal"><i class="fas fa-plus"></i> Add Station</button>
  </div>

  <div class="row">
    <?php while($station = $result->fetch_assoc()): ?>
      <div class="col-md-4 mb-4">
        <a href="dashboard.php?station_id=<?= $station['id'] ?>" class="card p-3 text-decoration-none text-white">
          <h5><i class="fas fa-building"></i> <?= htmlspecialchars($station['name']) ?></h5>
          <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($station['location']) ?></p>
          <p><i class="fas fa-microchip"></i> Sensor ID: <?= htmlspecialchars($station['device_sensor_id']) ?></p>
        </a>
        <form method="POST" action="delete_station.php" onsubmit="return confirm('Are you sure you want to delete this station?');">
          <input type="hidden" name="id" value="<?= $station['id'] ?>">
          <button class="btn btn-sm delete-btn mt-2"><i class="fas fa-trash"></i> Delete</button>
        </form>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<div class="modal fade" id="addStationModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="add_station.php">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Add Station</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-dark text-white">
        <div class="mb-3">
          <label class="form-label">Station Name</label>
          <input type="text" class="form-control" name="name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Location</label>
          <input type="text" class="form-control" name="location" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Device Sensor ID</label>
          <input type="text" class="form-control" name="device_sensor_id" required>
        </div>
      </div>
      <div class="modal-footer bg-dark">
        <button type="submit" class="btn btn-success">Add Station</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
