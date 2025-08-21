<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notifications - Water Quality Monitor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <style>
    body {
      background: #0e1117;
      color: white;
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar {
      background-color: #1f2733;
      box-shadow: 0 2px 10px rgba(0, 198, 255, 0.2);
    }
    .nav-link, .navbar-brand {
      color: white;
    }
    .nav-link:hover {
      color: #00c6ff;
    }
    .container {
      margin-top: 30px;
    }
    .alert-card {
      background: #1f2733;
      border-left: 5px solid #dc3545;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(255, 0, 0, 0.2);
    }
    .alert-card h5 {
      color: #00c6ff;
    }
    .parameter-name {
      color: #ffc107;
    }
    .fa-exclamation-triangle {
      color: #dc3545;
      margin-right: 10px;
    }
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
          <a class="nav-link active" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
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
  <h3 class="mb-4 text-center">⚠️ Parameter Alerts</h3>

  <!-- Alert 1 -->
  <div class="alert-card">
    <h5><i class="fas fa-exclamation-triangle"></i> Juan's Water Station</h5>
    <p>
      <span class="parameter-name">Lead</span> level is <strong>0.015 mg/L</strong> — exceeds safe limit of <strong>0.010 mg/L</strong>.
    </p>
    <p>Date: 2025-04-12 | Time: 10:45 AM</p>
  </div>

  <!-- Alert 2 -->
  <div class="alert-card">
    <h5><i class="fas fa-exclamation-triangle"></i> Clarise's H20</h5>
    <p>
      <span class="parameter-name">Total Disolved Solids</span> level is <strong>12 mg/L</strong> — exceeds safe limit of <strong>10 mg/L</strong>.
    </p>
    <p>Date: 2025-04-12 | Time: 10:40 AM</p>
  </div>

  <!-- Alert 3 -->
  <div class="alert-card">
    <h5><i class="fas fa-exclamation-triangle"></i> Clarise's H20</h5>
    <p>
      <span class="parameter-name">Nitrate</span> level is <strong>60 mg/L</strong> — exceeds safe limit of <strong>50 mg/L</strong>.
    </p>
    <p>Date: 2025-04-12 | Time: 10:40 AM</p>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
