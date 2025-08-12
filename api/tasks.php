<?php
/**
 * Tasks API endpoints
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $taskManager = new TaskManager();
    $userId = $_SESSION['user_id'] ?? 2; // Default to caregiver
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get tasks
        $status = $_GET['status'] ?? null;
        $tasks = $taskManager->getUserTasks($userId, $status);
        echo json_encode(['success' => true, 'data' => $tasks]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!verifyCSRFToken($input['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        switch ($input['action'] ?? '') {
            case 'create_task':
                $data = [
                    'patient_id' => (int)$input['patient_id'],
                    'assigned_to' => (int)$input['assigned_to'],
                    'created_by' => $userId,
                    'title' => sanitizeInput($input['title']),
                    'description' => sanitizeInput($input['description'] ?? ''),
                    'priority' => sanitizeInput($input['priority'] ?? 'medium'),
                    'due_date' => $input['due_date'] ?? null,
                    'category' => sanitizeInput($input['category'] ?? '')
                ];
                
                $id = $taskManager->createTask($data);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Task created successfully']);
                break;
                
            case 'update_status':
                $result = $taskManager->updateTaskStatus(
                    (int)$input['task_id'],
                    sanitizeInput($input['status']),
                    $userId
                );
                echo json_encode(['success' => true, 'message' => 'Task status updated successfully']);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>