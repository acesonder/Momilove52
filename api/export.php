<?php
/**
 * Export API endpoint
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set headers for JSON download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="care_data_export_' . date('Y-m-d') . '.json"');

try {
    // For demo purposes, use user ID 1 (Diana)
    $userId = $_SESSION['user_id'] ?? 1;
    
    $userManager = new UserManager();
    $checkinManager = new CheckinManager();
    $medicationManager = new MedicationManager();
    $taskManager = new TaskManager();
    
    // Get user data
    $user = $userManager->getUserById($userId);
    if (!$user) {
        throw new Exception("User not found");
    }
    
    // Prepare export data
    $exportData = [
        'export_date' => date('Y-m-d H:i:s'),
        'user_info' => [
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'daily_checkins' => $checkinManager->getCheckinHistory($userId, 100),
        'medications' => $medicationManager->getPatientMedications($userId),
        'medication_logs' => $medicationManager->getMedicationLogs($userId, 100),
        'tasks' => $taskManager->getUserTasks($userId)
    ];
    
    // Remove sensitive data
    unset($exportData['user_info']['email']);
    
    echo json_encode($exportData, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Export failed: ' . $e->getMessage()]);
}
?>