<?php 
// dashboard.php
// Copy-paste ready. Requires includes/db.php to provide $conn (mysqli).

include("../includes/db.php");

$station_id = isset($_GET['station_id']) ? (int)$_GET['station_id'] : null;
$station = null;

// Handle AJAX save request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_autotest') {
  header('Content-Type: application/json; charset=utf-8');

  // Read and sanitize inputs
  $sid = isset($_POST['station_id']) ? (int)$_POST['station_id'] : 0;
  $mode = isset($_POST['mode']) ? $_POST['mode'] : 'hourly';
  $interval_hours = isset($_POST['interval_hours']) && $_POST['interval_hours'] !== '' ? (int)$_POST['interval_hours'] : null;
  $interval_days = isset($_POST['interval_days']) && $_POST['interval_days'] !== '' ? (int)$_POST['interval_days'] : null;
  $interval_months = isset($_POST['interval_months']) && $_POST['interval_months'] !== '' ? (int)$_POST['interval_months'] : null;
  $day_of_month = isset($_POST['day_of_month']) && $_POST['day_of_month'] !== '' ? (int)$_POST['day_of_month'] : null;
  $time_of_day = isset($_POST['time_of_day']) && $_POST['time_of_day'] !== '' ? $_POST['time_of_day'] : null;
  $enabled = isset($_POST['enabled']) && ($_POST['enabled'] === '1' || $_POST['enabled'] === 'true') ? 1 : 0;

  if (!$sid) {
    echo json_encode(['success' => false, 'message' => 'No station id provided.']);
    exit;
  }

  // Create table if not exists
  $createSql = "CREATE TABLE IF NOT EXISTS station_autotest_settings (
    station_id INT PRIMARY KEY,
    mode VARCHAR(10) NOT NULL,
    interval_hours INT DEFAULT NULL,
    interval_days INT DEFAULT NULL,
    interval_months INT DEFAULT NULL,
    day_of_month INT DEFAULT NULL,
    time_of_day TIME DEFAULT NULL,
    enabled TINYINT(1) DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
  $conn->query($createSql);

  // Insert or update
  $sql = "INSERT INTO station_autotest_settings
    (station_id, mode, interval_hours, interval_days, interval_months, day_of_month, time_of_day, enabled)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      mode = VALUES(mode),
      interval_hours = VALUES(interval_hours),
      interval_days = VALUES(interval_days),
      interval_months = VALUES(interval_months),
      day_of_month = VALUES(day_of_month),
      time_of_day = VALUES(time_of_day),
      enabled = VALUES(enabled)";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
  }
  // Bind - use nulls appropriately
  $stmt->bind_param(
    'isiiiissi',
    $sid,
    $mode,
    $interval_hours,
    $interval_days,
    $interval_months,
    $day_of_month,
    $time_of_day,
    $enabled
  );
  $exec = $stmt->execute();
  if ($exec) {
    echo json_encode(['success' => true, 'message' => 'Settings saved.']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Save failed: ' . $stmt->error]);
  }
  $stmt->close();
  exit;
}

// Normal page load: fetch station
if ($station_id) {
  $sql = "SELECT * FROM refilling_stations WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $station_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $station = $result->fetch_assoc();
  $stmt->close();
}

// Load saved auto-test settings for this station (if any)
$settings = [
  'mode' => 'hourly',
  'interval_hours' => 1,
  'interval_days' => 1,
  'daily_time' => '',
  'interval_months' => 1,
  'day_of_month' => 1,
  'monthly_time' => '',
  'enabled' => 0
];

if ($station_id) {
  // ensure table exists (safe)
  $conn->query("CREATE TABLE IF NOT EXISTS station_autotest_settings (
    station_id INT PRIMARY KEY,
    mode VARCHAR(10) NOT NULL,
    interval_hours INT DEFAULT NULL,
    interval_days INT DEFAULT NULL,
    interval_months INT DEFAULT NULL,
    day_of_month INT DEFAULT NULL,
    time_of_day TIME DEFAULT NULL,
    enabled TINYINT(1) DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $sql = "SELECT * FROM station_autotest_settings WHERE station_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $station_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    $settings['mode'] = $row['mode'] ?? 'hourly';
    $settings['interval_hours'] = $row['interval_hours'] ?? 1;
    $settings['interval_days'] = $row['interval_days'] ?? 1;
    $settings['daily_time'] = $row['time_of_day'] ?? '';
    $settings['interval_months'] = $row['interval_months'] ?? 1;
    $settings['day_of_month'] = $row['day_of_month'] ?? 1;
    $settings['monthly_time'] = $row['time_of_day'] ?? '';
    $settings['enabled'] = (int)$row['enabled'];
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Water Quality Monitor</title>

  <!-- Bootstrap & Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

  <style>
    body { background: #0e1117; font-family: 'Segoe UI', sans-serif; color: #fff; }
    .navbar { background-color: #1f2733; box-shadow: 0 2px 10px rgba(0, 198, 255, 0.2); }
    .navbar-brand, .nav-link, .btn { color: #fff; }
    .navbar-nav .nav-link:hover { color: #00c6ff; }

    .container { background: #1f2733; border-radius: 20px; padding: 30px; margin-top: 30px; box-shadow: 0 0 30px rgba(0, 198, 255, 0.1); }

    /* Header row (Start+Settings | Center info | Results) */
    .header-bar { gap: 16px; }
    .header-bar .middle { flex: 1 1 520px; min-width: 320px; }
    .header-bar .btn { white-space: nowrap; }
    .btn-gear { width: 60px; }

    .station-name { font-size: 22px; font-weight: bold; color: #00c6ff; text-align: center; }
    .station-address { font-size: 14px; color: #ccc; text-align: center; margin-bottom: 4px; }
    .sensor-id { text-align: center; }
    .sensor-id p { margin: 0; }
    .timestamp { text-align: center; font-size: 14px; color: #aaa; margin-top: 6px; }

    .status-label { display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 20px; font-weight: bold; margin: 0 auto 6px auto; padding: 6px 20px; border: 2px solid black; border-radius: 30px; background: transparent; color: white; }
    .status-dot { display: inline-block; width: 14px; height: 14px; border-radius: 50%; }
    .status-online .status-dot { background: #28a745; }  /* green dot */
    .status-offline .status-dot { background: #dc3545; } /* red dot */

    /* Gauges (copied from your design) */
    .gauge {
      position: relative;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: conic-gradient(
        from 220deg,
        #00c6ff 0deg,
        #28a745 70deg,
        #ffc107 140deg,
        #dc3545 210deg,
        #dc3545 280deg,
        transparent 280deg,
        transparent 360deg
      );
      border: 10px solid rgb(46, 42, 42);
      box-shadow: inset 0 0 10px #000, 0 0 20px #000;
    }
    .gauge::after {
      content: "";
      position: absolute;
      top: 35px; left: 35px; right: 35px; bottom: 35px;
      background: #1e1e1e;
      border-radius: 50%;
      z-index: 1;
    }
    .gauge::before {
      content: "";
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      border-radius: 50%;
      border: 12px solid #1e1e1e;
      z-index: 2;
    }
    .needle { position: absolute; width: 4px; height: 120px; background: #ccc; bottom: 50%; left: 50%; transform-origin: bottom center; transform: rotate(-130deg); z-index: 2; }
    .center-dot { position: absolute; width: 20px; height: 20px; background: #333; border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 3; }
    .lcd-display { position: absolute; top: 70%; left: 50%; transform: translateX(-50%); padding: 0 12px; background: #0E0F1A; color: #00FFCC; font-family: 'Courier New', monospace; font-size: 36px; font-weight: bold; border-radius: 10px; border: 2px solid rgb(0, 157, 255); box-shadow: 0 0 10px #00A3FF, inset 0 0 5px #004d40, 0 0 20px rgba(0, 255, 204, 0.3); z-index: 4; transition: all 0.4s ease-in-out; }
    .status-svg { position: absolute; top: 0; left: 0; width: 290px; height: 290px; pointer-events: none; z-index: 4; }
    .arc-text { font-size: 14px; font-weight: bold; letter-spacing: 1px; fill: white; }
    .safe-text { fill: #00c6ff; } .neutral-text { fill: #28a745; } .warning-text { fill: #ffc107; } .fail-text { fill: #dc3545; }
    .gauge-label { margin-top: 10px; color: white; font-size: 32px; text-align: center; font-weight: bold; animation: glow 1s infinite alternate; }
    @keyframes glow { from { text-shadow: 0 0 5px #00f7ff, 0 0 10px #00e0ff; } to { text-shadow: 0 0 15px #00f7ff, 0 0 25px #00e0ff; } }

    /* small responsive tweaks */
    @media (max-width: 700px) {
      .gauge { width: 220px; height: 220px; }
      .lcd-display { font-size: 28px; }
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#"><i class="fas fa-tint"></i> Water Quality Testing & Monitoring System</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
          <li class="nav-item"><a class="nav-link" href="stations.php"><i class="fas fa-building"></i> Stations</a></li>
          <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
          <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <!-- ONE ROW: Start+Settings | Center Info | Results -->
    <div class="header-bar d-flex justify-content-between align-items-start flex-wrap mb-4">
      <!-- Left: Start Testing + small Settings button -->
      <div class="btn-group">
        <button id="startBtn" class="btn btn-primary btn-lg px-4" style="background:#00c6ff; border:none; font-weight:bold;">
          <i class="fas fa-play"></i> Start Testing
        </button>
        <button id="openSettingsBtn" class="btn btn-secondary btn-lg btn-gear" style="background:#005f7f; border:none;"
                data-bs-toggle="modal" data-bs-target="#settingsModal" title="Auto Test Settings">
          <i class="fas fa-cog"></i>
        </button>
      </div>

      <!-- Center: Station Info + Date/Time -->
      <div class="middle text-center mx-auto">
        <?php if ($station): ?>
          <div class="status-label status-online">ONLINE <span class="status-dot"></span></div>
          <div class="station-name"><?= htmlspecialchars($station['name']) ?></div>
          <div class="station-address"><?= htmlspecialchars($station['location']) ?></div>
          <div class="sensor-id"><p><strong>Sensor ID:</strong> <?= htmlspecialchars($station['device_sensor_id']) ?></p></div>
        <?php else: ?>
          <div class="status-label status-offline">OFFLINE <span class="status-dot"></span></div>
          <div class="station-name">No Station Selected</div>
          <div class="station-address">—</div>
          <div class="sensor-id"><p class="text-warning m-0">Please Select Station First!</p></div>
        <?php endif; ?>
        <div class="timestamp">Date: <span id="date"></span> | Time: <span id="time"></span></div>
      </div>

      <!-- Right: Test Results Button -->
      <button class="btn btn-success btn-lg px-4" data-bs-toggle="modal" data-bs-target="#resultsModal" style="background:#28a745; border:none; font-weight:bold;">
        <i class="fas fa-clipboard-list"></i> Test Results
      </button>
    </div>

   <!-- RESULTS MODAL -->
<div class="modal fade" id="resultsModal" tabindex="-1" aria-labelledby="resultsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="background:#1f2733; color:white; border-radius:15px;">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="resultsModalLabel">
          <i class="fas fa-clipboard-check"></i> Water Quality Test Results
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- TEST RUNS LIST (default view) -->
        <div id="testListView">
          <h6>Available Test Runs</h6>
          <div class="table-responsive">
            <table class="table table-dark table-striped">
              <thead>
                <tr><th>Date</th><th>Time</th><th>Action</th></tr>
              </thead>
              <tbody id="testRunsTable">
                <!-- Example static rows; should be fetched from DB -->
                <tr>
                  <td>2025-08-21</td>
                  <td>10:30 AM</td>
                  <td><button class="btn btn-sm btn-info view-test" data-id="1">View</button></td>
                </tr>
                <tr>
                  <td>2025-08-20</td>
                  <td>02:15 PM</td>
                  <td><button class="btn btn-sm btn-info view-test" data-id="2">View</button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- DETAILED RESULTS (hidden until clicked) -->
        <div id="testDetailView" class="d-none">
          <button class="btn btn-sm btn-secondary mb-3" id="backToList"><i class="fas fa-arrow-left"></i> Back to List</button>
          <h6>Test Details</h6>
          <div class="table-responsive">
            <table class="table table-dark table-striped">
              <thead>
                <tr><th>Parameter</th><th>Value</th><th>Status</th></tr>
              </thead>
              <tbody id="resultsTable">
                <!-- Will be loaded dynamically -->
              </tbody>
            </table>
          </div>
          <div class="text-end">
            <a href="#" id="downloadResult" class="btn btn-success"><i class="fas fa-download"></i> Download</a>
          </div>
        </div>

      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Handle switching between list and detail
  document.addEventListener("click", function(e) {
    if (e.target.classList.contains("view-test")) {
      const testId = e.target.getAttribute("data-id");

      // Hide list, show detail
      document.getElementById("testListView").classList.add("d-none");
      document.getElementById("testDetailView").classList.remove("d-none");

      // Example: load static values (replace with AJAX call to PHP)
      const sampleResults = {
        1: [
          { param: "Color", value: "5 TCU", status: "Safe" },
          { param: "pH", value: "7.2", status: "Safe" },
          { param: "Turbidity", value: "0.8 NTU", status: "Safe" },
          { param: "TDS", value: "120 ppm", status: "Warning" },
          { param: "Residual Chlorine", value: "0.4 mg/L", status: "Safe" },
          { param: "Lead", value: "0.01 mg/L", status: "Safe" },
          { param: "Cadmium", value: "0.002 mg/L", status: "Safe" },
          { param: "Arsenic", value: "0.005 mg/L", status: "Safe" },
          { param: "Nitrate", value: "8 mg/L", status: "Safe" }
        ],
        2: [
          {param: "Color", value: "7 TCU", status: "Safe"},
          {param: "pH", value: "6.8", status: "Safe"},
          { param: "Turbidity", value: "0.8 NTU", status: "Safe" },
          { param: "TDS", value: "120 ppm", status: "Warning" },
          { param: "Residual Chlorine", value: "0.4 mg/L", status: "Safe" },
          { param: "Lead", value: "0.01 mg/L", status: "Safe" },
          { param: "Cadmium", value: "0.002 mg/L", status: "Safe" },
          { param: "Arsenic", value: "0.005 mg/L", status: "Safe" },
          {param: "Nitrate", value: "50 mg/L", status: "Warning"}
        ]
      };

      const tableBody = document.getElementById("resultsTable");
      tableBody.innerHTML = "";
      (sampleResults[testId] || []).forEach(r => {
        let badgeClass = r.status === "Safe" ? "bg-info" :
                         r.status === "Warning" ? "bg-warning" : "bg-danger";
        tableBody.innerHTML += `<tr>
          <td>${r.param}</td>
          <td>${r.value}</td>
          <td><span class="badge ${badgeClass}">${r.status}</span></td>
        </tr>`;
      });

      // Download link (could point to server-side file)
      document.getElementById("downloadResult").setAttribute("href", "download.php?test_id=" + testId);
    }
  });

  // Back to list button
  document.getElementById("backToList").addEventListener("click", function() {
    document.getElementById("testDetailView").classList.add("d-none");
    document.getElementById("testListView").classList.remove("d-none");
  });
</script>


    <!-- SETTINGS MODAL -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1f2733; color:white; border-radius:15px;">
          <div class="modal-header border-0">
            <h5 class="modal-title" id="settingsModalLabel"><i class="fas fa-cog"></i> Auto Test Settings</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="autoTestForm">
              <div class="mb-3">
                <label class="form-label">Choose Frequency</label>
                <select id="frequencySelect" class="form-select">
                  <option value="hourly">Every X Hours</option>
                  <option value="daily">Every X Days</option>
                  <option value="monthly">Every X Months</option>
                </select>
              </div>

              <!-- Hourly -->
              <div class="mb-3 freq-option" id="hourlyOption">
                <label class="form-label">Every how many hours?</label>
                <input id="hourlyHours" type="number" class="form-control" min="1" value="<?= htmlspecialchars($settings['interval_hours']) ?>">
              </div>

              <!-- Daily -->
              <div class="mb-3 freq-option d-none" id="dailyOption">
                <label class="form-label">Every how many days?</label>
                <input id="dailyDays" type="number" class="form-control mb-2" min="1" value="<?= htmlspecialchars($settings['interval_days']) ?>">
                <label class="form-label">At what time?</label>
                <input id="dailyTime" type="time" class="form-control" value="<?= htmlspecialchars($settings['daily_time']) ?>">
              </div>

              <!-- Monthly -->
              <div class="mb-3 freq-option d-none" id="monthlyOption">
                <label class="form-label">Every how many months?</label>
                <input id="monthlyMonths" type="number" class="form-control mb-2" min="1" value="<?= htmlspecialchars($settings['interval_months']) ?>">
                <label class="form-label">On what day of the month?</label>
                <input id="monthlyDay" type="number" class="form-control mb-2" min="1" max="31" value="<?= htmlspecialchars($settings['day_of_month']) ?>">
                <label class="form-label">At what time?</label>
                <input id="monthlyTime" type="time" class="form-control" value="<?= htmlspecialchars($settings['monthly_time']) ?>">
              </div>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="autoEnable" <?= $settings['enabled'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="autoEnable">Enable automatic testing</label>
              </div>

              <div id="settingsAlert" class="alert d-none" role="alert"></div>

              <div class="d-grid">
                <button type="button" id="saveAutoTest" class="btn btn-primary">Save Settings</button>
              </div>
            </form>
          </div>
          <div class="modal-footer border-0">
            <small class="text-muted">Saved per station (if station selected) so IoT/scheduler can use it.</small>
          </div>
        </div>
      </div>
    </div>

    <!-- GAUGES -->
    <div class="d-flex flex-wrap justify-content-center text-center" style="gap: 40px;">
      <?php
        $parameters = ['pH', 'Color', 'Turbidity', 'Arsenic', 'Residual Chlorine', 'Lead', 'Cadmium', 'Total Disolved Solids', 'Nitrate'];
        foreach ($parameters as $param):
          $id = strtolower(str_replace(' ', '-', $param));
      ?>
      <div class="mb-5">
        <div class="gauge">
          <div class="needle" id="needle-<?= $id ?>"></div>
          <div class="center-dot"></div>
          <div class="lcd-display" id="<?= $id ?>-value">0.00</div>

          <svg class="status-svg" viewBox="0 0 350 350">
            <defs>
              <path id="arc-safe-<?= $id ?>" d="M 75 230 A 125 125 0 0 1 140 60" />
              <path id="arc-neutral-<?= $id ?>" d="M 75 120 A 125 125 0 0 1 180 60" />
              <path id="arc-warning-<?= $id ?>" d="M 195 70 A 125 125 0 0 1 280 140" />
              <path id="arc-fail-<?= $id ?>" d="M 275 170 A 100 100 0 0 1 255 240" />
            </defs>
            <text class="arc-text safe-text"><textPath href="#arc-safe-<?= $id ?>">SAFE</textPath></text>
            <text class="arc-text neutral-text"><textPath href="#arc-neutral-<?= $id ?>">NEUTRAL</textPath></text>
            <text class="arc-text warning-text"><textPath href="#arc-warning-<?= $id ?>">WARNING</textPath></text>
            <text class="arc-text fail-text"><textPath href="#arc-fail-<?= $id ?>">FAILED</textPath></text>
          </svg>
        </div>
        <div class="gauge-label"><?= htmlspecialchars($param) ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
      <img src="parameters.png" alt="Water Quality Parameters" class="img-fluid" style="max-width: 1000px;">
    </div>
  </div> <!-- /.container -->

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Live date/time
    function updateTime() {
      const now = new Date();
      document.getElementById('time').textContent = now.toLocaleTimeString();
      document.getElementById('date').textContent = now.toLocaleDateString();
    }
    setInterval(updateTime, 1000);
    updateTime();

    // Frequency UI logic
    const frequencySelect = document.getElementById('frequencySelect');
    const hourlyOption = document.getElementById('hourlyOption');
    const dailyOption = document.getElementById('dailyOption');
    const monthlyOption = document.getElementById('monthlyOption');

    function updateOptions() {
      hourlyOption.classList.add('d-none');
      dailyOption.classList.add('d-none');
      monthlyOption.classList.add('d-none');
      if (!frequencySelect) return;
      if (frequencySelect.value === 'hourly') hourlyOption.classList.remove('d-none');
      if (frequencySelect.value === 'daily') dailyOption.classList.remove('d-none');
      if (frequencySelect.value === 'monthly') monthlyOption.classList.remove('d-none');
    }

    if (frequencySelect) {
      frequencySelect.value = <?= json_encode($settings['mode']) ?>;
      updateOptions();
      frequencySelect.addEventListener('change', updateOptions);
    }

    // Save settings via AJAX to this same file
    document.getElementById('saveAutoTest').addEventListener('click', function () {
      const alertEl = document.getElementById('settingsAlert');
      alertEl.classList.add('d-none');

      const mode = document.getElementById('frequencySelect').value;
      const hourlyHours = document.getElementById('hourlyHours').value;
      const dailyDays = document.getElementById('dailyDays').value;
      const dailyTime = document.getElementById('dailyTime').value;
      const monthlyMonths = document.getElementById('monthlyMonths').value;
      const monthlyDay = document.getElementById('monthlyDay').value;
      const monthlyTime = document.getElementById('monthlyTime').value;
      const enabled = document.getElementById('autoEnable').checked ? '1' : '0';

      const payload = new FormData();
      payload.append('action', 'save_autotest');
      payload.append('mode', mode);
      payload.append('interval_hours', hourlyHours);
      payload.append('interval_days', dailyDays);
      payload.append('time_of_day', mode === 'daily' ? dailyTime : (mode === 'monthly' ? monthlyTime : ''));
      payload.append('interval_months', monthlyMonths);
      payload.append('day_of_month', monthlyDay);
      payload.append('enabled', enabled);
      <?php if ($station_id): ?>
        payload.append('station_id', <?= json_encode($station_id) ?>);
      <?php else: ?>
        // no station selected -- fallback to localStorage
      <?php endif; ?>

      // If no station, save to localStorage and show message
      <?php if (!$station_id): ?>
        // Save locally
        const local = {
          mode, hourlyHours: parseInt(hourlyHours||0,10), dailyDays: parseInt(dailyDays||0,10),
          dailyTime, monthlyMonths: parseInt(monthlyMonths||0,10), monthlyDay: parseInt(monthlyDay||0,10),
          monthlyTime, enabled: !!(enabled === '1')
        };
        localStorage.setItem('autoTestSettings', JSON.stringify(local));
        alertEl.classList.remove('d-none');
        alertEl.classList.remove('alert-danger');
        alertEl.classList.add('alert-success');
        alertEl.textContent = 'Settings saved locally (no station selected).';
        return;
      <?php else: ?>
        // proceed to POST to server
      <?php endif; ?>

      const btn = this;
      btn.disabled = true;
      btn.textContent = 'Saving...';

      fetch(location.href, { method: 'POST', body: payload })
        .then(r => r.json())
        .then(data => {
          if (data && data.success) {
            alertEl.classList.remove('d-none');
            alertEl.classList.remove('alert-danger');
            alertEl.classList.add('alert-success');
            alertEl.textContent = data.message || 'Saved.';
            // close modal after brief delay
            setTimeout(() => {
              const modalEl = document.getElementById('settingsModal');
              const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
              modal.hide();
            }, 700);
          } else {
            alertEl.classList.remove('d-none');
            alertEl.classList.remove('alert-success');
            alertEl.classList.add('alert-danger');
            alertEl.textContent = (data && data.message) ? data.message : 'Save failed.';
          }
        })
        .catch(err => {
          alertEl.classList.remove('d-none');
          alertEl.classList.remove('alert-success');
          alertEl.classList.add('alert-danger');
          alertEl.textContent = 'Network error: ' + err;
        })
        .finally(() => {
          btn.disabled = false;
          btn.textContent = 'Save Settings';
        });
    });

    // If no server settings & localStorage exist, prefill modal when opened
    document.getElementById('openSettingsBtn').addEventListener('click', function () {
      <?php if (!$station_id): ?>
        const saved = JSON.parse(localStorage.getItem('autoTestSettings') || '{}');
        if (Object.keys(saved).length) {
          document.getElementById('frequencySelect').value = saved.mode || 'hourly';
          document.getElementById('hourlyHours').value = saved.hourlyHours || 1;
          document.getElementById('dailyDays').value = saved.dailyDays || 1;
          document.getElementById('dailyTime').value = saved.dailyTime || '';
          document.getElementById('monthlyMonths').value = saved.monthlyMonths || 1;
          document.getElementById('monthlyDay').value = saved.monthlyDay || 1;
          document.getElementById('monthlyTime').value = saved.monthlyTime || '';
          document.getElementById('autoEnable').checked = !!saved.enabled;
          updateOptions();
        }
      <?php endif; ?>
    });

    // OPTIONAL: Start Testing click handler (placeholder)
    document.getElementById('startBtn').addEventListener('click', function () {
      // Hook here to trigger an actual test (AJAX to server or websockets)
      // For now we show a tiny visual cue:
      this.classList.add('btn-success');
      this.classList.remove('btn-primary');
      setTimeout(() => {
        this.classList.remove('btn-success');
        this.classList.add('btn-primary');
      }, 800);
    });
  </script>
</body>
</html>
