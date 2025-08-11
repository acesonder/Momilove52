<?php
/**
 * Medication API endpoints
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $medicationManager = new MedicationManager();
    $userId = $_SESSION['user_id'] ?? 1;
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get medications
        $medications = $medicationManager->getPatientMedications($userId);
        echo json_encode(['success' => true, 'data' => $medications]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!verifyCSRFToken($input['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        switch ($input['action'] ?? '') {
            case 'add_medication':
                $data = [
                    'patient_id' => $userId,
                    'medication_name' => sanitizeInput($input['medication_name']),
                    'dosage' => sanitizeInput($input['dosage']),
                    'frequency' => sanitizeInput($input['frequency']),
                    'prescribing_doctor' => sanitizeInput($input['prescribing_doctor'] ?? ''),
                    'start_date' => $input['start_date'] ?? null,
                    'instructions' => sanitizeInput($input['instructions'] ?? '')
                ];
                
                $id = $medicationManager->addMedication($data);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Medication added successfully']);
                break;
                
            case 'log_medication':
                $id = $medicationManager->logMedicationTaken(
                    (int)$input['medication_id'],
                    $userId,
                    sanitizeInput($input['status']),
                    sanitizeInput($input['notes'] ?? ''),
                    $userId
                );
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Medication logged successfully']);
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