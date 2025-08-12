<?php
/**
 * Main Dashboard for Momilove52 Care Tracker
 */
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Initialize managers
$userManager = new UserManager();
$checkinManager = new CheckinManager();
$medicationManager = new MedicationManager();
$taskManager = new TaskManager();
$appointmentManager = new AppointmentManager();
$analyticsManager = new AnalyticsManager();

// For demo purposes, we'll use sample user IDs
// In production, this would come from session after login
$currentUserId = $_SESSION['user_id'] ?? 1; // Diana (patient)
$caregiverId = 2; // Chance (caregiver)

try {
    // Get current user info
    $currentUser = $userManager->getUserById($currentUserId);
    if (!$currentUser) {
        throw new Exception("User not found");
    }
    
    // Get analytics data
    $patientStats = $analyticsManager->getPatientStats($currentUserId);
    $caregiverStats = $analyticsManager->getCaregiverStats($caregiverId);
    
    // Get recent data
    $recentCheckins = $checkinManager->getCheckinHistory($currentUserId, 5);
    $todayCheckin = $checkinManager->getTodayCheckin($currentUserId);
    $medications = $medicationManager->getPatientMedications($currentUserId);
    $medicationLogs = $medicationManager->getMedicationLogs($currentUserId, 10);
    $tasks = $taskManager->getUserTasks($caregiverId);
    $upcomingAppointments = $appointmentManager->getUpcomingAppointments($currentUserId, 5);
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error = "Unable to load dashboard data. Please try again.";
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'save_checkin':
                    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                        $checkinData = [
                            'checkin_date' => sanitizeInput($_POST['checkin_date']),
                            'mood' => (int)($_POST['mood'] ?? 0),
                            'energy_level' => (int)($_POST['energy_level'] ?? 0),
                            'pain_level' => (int)($_POST['pain_level'] ?? 0),
                            'stress_level' => (int)($_POST['stress_level'] ?? 0),
                            'care_load' => (int)($_POST['care_load'] ?? 0),
                            'notes' => sanitizeInput($_POST['notes'] ?? '')
                        ];
                        
                        $checkinManager->saveCheckin($currentUserId, $checkinData);
                        $success = "Check-in saved successfully!";
                        
                        // Refresh data
                        $todayCheckin = $checkinManager->getTodayCheckin($currentUserId);
                        $recentCheckins = $checkinManager->getCheckinHistory($currentUserId, 5);
                    }
                    break;
                    
                case 'log_medication':
                    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                        $medicationManager->logMedicationTaken(
                            (int)$_POST['medication_id'],
                            $currentUserId,
                            sanitizeInput($_POST['status']),
                            sanitizeInput($_POST['notes'] ?? ''),
                            $currentUserId
                        );
                        $success = "Medication logged successfully!";
                        $medicationLogs = $medicationManager->getMedicationLogs($currentUserId, 10);
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
    :root{
      --bg: #0f1115;
      --panel: #151822;
      --panel-2: #181b24;
      --card: #1b1f2b;
      --muted: #9aa3b2;
      --text: #e8ecf1;
      --accent: #6c5ce7;
      --accent-2: #8f84ff;
      --success: #2ecc71;
      --danger: #ff6b6b;
      --warning: #f1c40f;
      --shadow: 0 10px 24px rgba(0,0,0,.35), 0 2px 6px rgba(0,0,0,.25);
      --radius-lg: 16px;
      --radius-md: 12px;
      --radius-sm: 10px;
      --ring: 0 0 0 2px rgba(108,92,231,.45), 0 8px 24px rgba(108,92,231,.25);
    }
    
    body{
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
      background: radial-gradient(1200px 800px at 10% -10%, rgba(108,92,231,.08), transparent 60%),
                  radial-gradient(1200px 800px at 100% 0%, rgba(108,92,231,.06), transparent 60%),
                  var(--bg);
      color: var(--text);
      line-height: 1.45;
      min-height: 100vh;
    }
    
    .app{
      max-width: 1280px;
      margin: 28px auto 64px;
      padding: 0 20px;
    }
    
    .panel{
      background: linear-gradient(180deg, var(--panel), var(--panel-2));
      border: 1px solid rgba(255,255,255,.06);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow);
      padding: 18px;
    }
    
    .stat{
      background: linear-gradient(180deg, #1b2030, #161b24);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 14px;
      padding: 16px;
      min-height: 86px;
    }
    
    .stat .label{ color: var(--muted); font-size: 13px; }
    .stat .value{ font-size: 22px; font-weight: 700; letter-spacing: .2px; }
    
    .tab{
      border: 1px solid rgba(255,255,255,.08);
      padding: 8px 12px;
      border-radius: 999px;
      font-size: 13px;
      color: var(--text);
      background: #161b25;
      cursor: pointer;
      text-decoration: none;
    }
    
    .tab.active{
      background: linear-gradient(180deg, rgba(108,92,231,.35), rgba(108,92,231,.18));
      border-color: rgba(108,92,231,.55);
      box-shadow: var(--ring);
    }
    
    .card{
      background: linear-gradient(180deg, #171b25, #131722);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: var(--radius-lg);
      padding: 16px;
      margin-top: 14px;
    }
    
    .form-control, .form-select {
      background: #0f1320;
      border: 1px solid rgba(255,255,255,.08);
      color: var(--text);
      border-radius: 12px;
    }
    
    .form-control:focus, .form-select:focus {
      background: #0f1320;
      border-color: rgba(108,92,231,.55);
      box-shadow: var(--ring);
      color: var(--text);
    }
    
    .btn-primary {
      background: linear-gradient(180deg, var(--accent), #5b4bd1);
      border: 1px solid rgba(108,92,231,.55);
      color: white;
    }
    
    .btn-primary:hover {
      background: linear-gradient(180deg, #5b4bd1, var(--accent));
      border-color: rgba(108,92,231,.75);
    }
    
    .emoji{
      width: 38px; height: 38px;
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,.08);
      background: #0f1320;
      cursor: pointer;
      font-size: 20px;
      transition: .15s ease;
    }
    
    .emoji.active, .emoji:hover {
      border-color: rgba(108,92,231,.55);
      box-shadow: var(--ring);
      transform: translateY(-1px);
    }
    
    .table-dark {
      --bs-table-bg: transparent;
      --bs-table-border-color: rgba(255,255,255,.06);
    }
    
    .table-dark td, .table-dark th {
      border-color: rgba(255,255,255,.06);
    }
    
    .pill-num{
      min-width: 24px; height: 24px;
      display: inline-grid; place-items: center;
      border-radius: 999px;
      background: rgba(108,92,231,.25);
      border: 1px solid rgba(108,92,231,.5);
      font-weight: 700; font-size: 12px;
      color: #d9d9ff;
      padding: 0 6px;
    }
    
    .alert-success {
      background: rgba(46, 204, 113, 0.1);
      border: 1px solid rgba(46, 204, 113, 0.3);
      color: #2ecc71;
    }
    
    .alert-danger {
      background: rgba(255, 107, 107, 0.1);
      border: 1px solid rgba(255, 107, 107, 0.3);
      color: #ff6b6b;
    }
    </style>
</head>

<body>
    <div class="app">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="bg-dark rounded-circle p-3 me-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="#87a2ff" stroke-width="1.4"/>
                        <path d="M3 21a9 9 0 0 1 18 0" stroke="#87a2ff" stroke-width="1.4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="mb-0"><?php echo $currentUser['first_name']; ?> Care Binder 
                        <span class="badge bg-primary">Private</span>
                    </h1>
                    <small class="text-muted">
                        Patient: <strong><?php echo $currentUser['first_name']; ?></strong> · 
                        Last login: <?php echo date('M j, Y g:i A'); ?>
                    </small>
                </div>
            </div>
            <div>
                <a href="api/export.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-download"></i> Export Data
                </a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Patient Panel -->
            <div class="col-lg-6">
                <section class="panel">
                    <!-- Stats Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="stat">
                                <div class="label">Next appointment</div>
                                <div class="value">
                                    <?php if ($patientStats['next_appointment']): ?>
                                        <?php echo date('M j', strtotime($patientStats['next_appointment']['appointment_date'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat">
                                <div class="label">Avg pain (7d)</div>
                                <div class="value">
                                    <?php if ($patientStats['avg_pain']): ?>
                                        <?php echo $patientStats['avg_pain']; ?>/10
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat">
                                <div class="label">Open symptoms</div>
                                <div class="value"><?php echo $patientStats['open_symptoms']; ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat">
                                <div class="label">Med compliance</div>
                                <div class="value">
                                    <span class="pill-num"><?php echo $patientStats['medication_compliance']; ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-pills mb-3" id="patient-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link tab active" id="checkin-tab" data-bs-toggle="pill" data-bs-target="#checkin" role="tab">
                                😊 Daily Check-In
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link tab" id="meds-tab" data-bs-toggle="pill" data-bs-target="#meds" role="tab">
                                💊 Medications
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link tab" id="appointments-tab" data-bs-toggle="pill" data-bs-target="#appointments" role="tab">
                                📅 Appointments
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="patient-content">
                        <!-- Daily Check-in Tab -->
                        <div class="tab-pane fade show active" id="checkin" role="tabpanel">
                            <div class="card">
                                <h5 class="card-title mb-3">Daily Check-In</h5>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="save_checkin">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="checkin_date" class="form-control" 
                                                   value="<?php echo $todayCheckin['checkin_date'] ?? date('Y-m-d'); ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Mood</label>
                                            <div class="d-flex gap-2">
                                                <?php 
                                                $moods = [1 => '😞', 2 => '😐', 3 => '😊', 4 => '🤗'];
                                                $currentMood = $todayCheckin['mood'] ?? 3;
                                                foreach ($moods as $value => $emoji): 
                                                ?>
                                                    <button type="button" class="btn emoji <?php echo $currentMood == $value ? 'active' : ''; ?>" 
                                                            data-mood="<?php echo $value; ?>"><?php echo $emoji; ?></button>
                                                <?php endforeach; ?>
                                                <input type="hidden" name="mood" value="<?php echo $currentMood; ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Energy (0-10)</label>
                                            <input type="number" name="energy_level" class="form-control" min="0" max="10" 
                                                   value="<?php echo $todayCheckin['energy_level'] ?? 5; ?>">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Pain (0-10)</label>
                                            <input type="number" name="pain_level" class="form-control" min="0" max="10" 
                                                   value="<?php echo $todayCheckin['pain_level'] ?? 0; ?>">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Notes</label>
                                            <textarea name="notes" class="form-control" rows="3" 
                                                      placeholder="How are you feeling today?"><?php echo $todayCheckin['notes'] ?? ''; ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary">Save Check-In</button>
                                    </div>
                                </form>
                                
                                <!-- Recent Check-ins -->
                                <div class="mt-4">
                                    <h6 class="mb-3">Recent Check-ins</h6>
                                    <div class="table-responsive">
                                        <table class="table table-dark table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Mood</th>
                                                    <th>Energy</th>
                                                    <th>Pain</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($recentCheckins)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-muted text-center">No check-ins yet</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($recentCheckins as $checkin): ?>
                                                        <tr>
                                                            <td><?php echo date('M j', strtotime($checkin['checkin_date'])); ?></td>
                                                            <td><?php echo $moods[$checkin['mood']] ?? '—'; ?></td>
                                                            <td><?php echo $checkin['energy_level'] ?? '—'; ?></td>
                                                            <td><?php echo $checkin['pain_level'] ?? '—'; ?></td>
                                                            <td class="text-truncate" style="max-width: 150px;">
                                                                <?php echo $checkin['notes'] ? htmlspecialchars($checkin['notes']) : '—'; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medications Tab -->
                        <div class="tab-pane fade" id="meds" role="tabpanel">
                            <div class="card">
                                <h5 class="card-title mb-3">Medication Management</h5>
                                
                                <!-- Current Medications -->
                                <h6 class="mb-3">Current Medications</h6>
                                <div class="row g-2 mb-4">
                                    <?php if (empty($medications)): ?>
                                        <div class="col-12">
                                            <div class="text-muted text-center py-4">
                                                No medications recorded yet.
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($medications as $med): ?>
                                            <div class="col-md-6">
                                                <div class="card border">
                                                    <div class="card-body p-3">
                                                        <h6 class="card-title mb-1"><?php echo htmlspecialchars($med['medication_name']); ?></h6>
                                                        <p class="card-text small mb-2">
                                                            <?php echo htmlspecialchars($med['dosage'] . ' - ' . $med['frequency']); ?>
                                                        </p>
                                                        <form method="POST" class="d-flex gap-2">
                                                            <input type="hidden" name="action" value="log_medication">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="medication_id" value="<?php echo $med['id']; ?>">
                                                            
                                                            <button type="submit" name="status" value="taken" class="btn btn-success btn-sm">Taken</button>
                                                            <button type="submit" name="status" value="skipped" class="btn btn-warning btn-sm">Skipped</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Recent Medication Logs -->
                                <h6 class="mb-3">Recent Medication Log</h6>
                                <div class="table-responsive">
                                    <table class="table table-dark table-sm">
                                        <thead>
                                            <tr>
                                                <th>Medication</th>
                                                <th>Status</th>
                                                <th>Time</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($medicationLogs)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-muted text-center">No medication logs yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($medicationLogs as $log): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($log['medication_name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $log['status'] === 'taken' ? 'success' : ($log['status'] === 'skipped' ? 'warning' : 'danger'); ?>">
                                                                <?php echo ucfirst($log['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M j, g:i A', strtotime($log['taken_at'])); ?></td>
                                                        <td class="text-truncate" style="max-width: 100px;">
                                                            <?php echo $log['notes'] ? htmlspecialchars($log['notes']) : '—'; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Appointments Tab -->
                        <div class="tab-pane fade" id="appointments" role="tabpanel">
                            <div class="card">
                                <h5 class="card-title mb-3">Upcoming Appointments</h5>
                                
                                <?php if (empty($upcomingAppointments)): ?>
                                    <div class="text-muted text-center py-4">
                                        No upcoming appointments scheduled.
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($upcomingAppointments as $appointment): ?>
                                            <div class="list-group-item bg-transparent border-secondary">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($appointment['title']); ?></h6>
                                                    <small><?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?></small>
                                                </div>
                                                <p class="mb-1"><?php echo htmlspecialchars($appointment['doctor_name'] ?? 'Doctor TBD'); ?></p>
                                                <small class="text-muted"><?php echo htmlspecialchars($appointment['location'] ?? 'Location TBD'); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Caregiver Panel -->
            <div class="col-lg-6">
                <section class="panel">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-dark rounded-circle p-2 me-3">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M12 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" transform="translate(0,5)" stroke="#87a2ff" stroke-width="1.4"/>
                                <path d="M3 22c0-4.418 4.03-8 9-8s9 3.582 9 8" stroke="#87a2ff" stroke-width="1.4"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="mb-0">Caregiver Dashboard</h5>
                            <small class="text-muted">Care recipient: <strong><?php echo $currentUser['first_name']; ?></strong></small>
                        </div>
                    </div>

                    <!-- Caregiver Stats -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="stat">
                                <div class="label">Open tasks</div>
                                <div class="value"><?php echo $caregiverStats['open_tasks']; ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat">
                                <div class="label">Patients</div>
                                <div class="value"><?php echo $caregiverStats['patient_count']; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks List -->
                    <div class="card">
                        <h5 class="card-title mb-3">Task Management</h5>
                        
                        <?php if (empty($tasks)): ?>
                            <div class="text-muted text-center py-4">
                                No tasks assigned yet.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($tasks, 0, 5) as $task): ?>
                                    <div class="list-group-item bg-transparent border-secondary">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($task['title']); ?></h6>
                                            <span class="badge bg-<?php echo $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($task['priority']); ?>
                                            </span>
                                        </div>
                                        <p class="mb-1 small"><?php echo htmlspecialchars($task['description'] ?? ''); ?></p>
                                        <small class="text-muted">
                                            Patient: <?php echo htmlspecialchars($task['patient_first_name'] . ' ' . $task['patient_last_name']); ?>
                                            <?php if ($task['due_date']): ?>
                                                · Due: <?php echo date('M j', strtotime($task['due_date'])); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mood selector
        document.querySelectorAll('.emoji[data-mood]').forEach(emoji => {
            emoji.addEventListener('click', function() {
                // Remove active class from all mood emojis
                document.querySelectorAll('.emoji[data-mood]').forEach(e => e.classList.remove('active'));
                
                // Add active class to clicked emoji
                this.classList.add('active');
                
                // Update hidden input
                document.querySelector('input[name="mood"]').value = this.dataset.mood;
            });
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                }
            });
        });
    </script>
</body>
</html>