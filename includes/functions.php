<?php
/**
 * Core Functions for Momilove52 Care Tracker
 */

require_once __DIR__ . '/../config/database.php';

/**
 * User management functions
 */
class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate user
     */
    public function login($email, $password) {
        try {
            $sql = "SELECT id, username, email, password_hash, first_name, last_name, role FROM users WHERE email = ? AND is_active = 1";
            $user = $this->db->fetchOne($sql, [$email]);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['username'] = $user['username'];
                
                // Log successful login
                $this->logActivity($user['id'], 'login', 'User logged in successfully');
                
                return $user;
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("Login failed: " . $e->getMessage());
        }
    }
    
    /**
     * Register new user
     */
    public function register($userData) {
        try {
            $this->db->beginTransaction();
            
            // Hash password
            $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT, ['cost' => BCRYPT_COST]);
            
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role, phone, date_of_birth) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $userId = $this->db->insert($sql, [
                $userData['username'],
                $userData['email'],
                $passwordHash,
                $userData['first_name'],
                $userData['last_name'],
                $userData['role'] ?? 'patient',
                $userData['phone'] ?? null,
                $userData['date_of_birth'] ?? null
            ]);
            
            $this->db->commit();
            
            $this->logActivity($userId, 'register', 'New user registered');
            
            return $userId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $sql = "SELECT id, username, email, first_name, last_name, role, phone, date_of_birth, created_at FROM users WHERE id = ? AND is_active = 1";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        try {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, [
                $data['first_name'],
                $data['last_name'],
                $data['phone'],
                $userId
            ]);
            
            $this->logActivity($userId, 'profile_update', 'User profile updated');
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Profile update failed: " . $e->getMessage());
        }
    }
    
    /**
     * Log user activity
     */
    private function logActivity($userId, $action, $description) {
        try {
            $sql = "INSERT INTO care_notes (patient_id, created_by, note_type, title, content) VALUES (?, ?, 'system', ?, ?)";
            $this->db->execute($sql, [$userId, $userId, $action, $description]);
        } catch (Exception $e) {
            // Activity logging failure shouldn't break the main operation
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}

/**
 * Daily check-in management
 */
class CheckinManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Save daily check-in
     */
    public function saveCheckin($userId, $data) {
        try {
            $sql = "INSERT INTO daily_checkins (user_id, checkin_date, mood, energy_level, pain_level, stress_level, care_load, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    mood = VALUES(mood), 
                    energy_level = VALUES(energy_level), 
                    pain_level = VALUES(pain_level), 
                    stress_level = VALUES(stress_level), 
                    care_load = VALUES(care_load), 
                    notes = VALUES(notes), 
                    updated_at = NOW()";
            
            $this->db->execute($sql, [
                $userId,
                $data['checkin_date'],
                $data['mood'] ?? null,
                $data['energy_level'] ?? null,
                $data['pain_level'] ?? null,
                $data['stress_level'] ?? null,
                $data['care_load'] ?? null,
                $data['notes'] ?? null
            ]);
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to save check-in: " . $e->getMessage());
        }
    }
    
    /**
     * Get check-in history
     */
    public function getCheckinHistory($userId, $limit = 30) {
        $sql = "SELECT * FROM daily_checkins WHERE user_id = ? ORDER BY checkin_date DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
    
    /**
     * Get today's check-in
     */
    public function getTodayCheckin($userId) {
        $sql = "SELECT * FROM daily_checkins WHERE user_id = ? AND checkin_date = CURDATE()";
        return $this->db->fetchOne($sql, [$userId]);
    }
}

/**
 * Medication management
 */
class MedicationManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get patient medications
     */
    public function getPatientMedications($patientId) {
        $sql = "SELECT * FROM medications WHERE patient_id = ? AND is_active = 1 ORDER BY medication_name";
        return $this->db->fetchAll($sql, [$patientId]);
    }
    
    /**
     * Add new medication
     */
    public function addMedication($data) {
        try {
            $sql = "INSERT INTO medications (patient_id, medication_name, dosage, frequency, prescribing_doctor, start_date, end_date, instructions, side_effects) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            return $this->db->insert($sql, [
                $data['patient_id'],
                $data['medication_name'],
                $data['dosage'],
                $data['frequency'],
                $data['prescribing_doctor'] ?? null,
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['instructions'] ?? null,
                $data['side_effects'] ?? null
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to add medication: " . $e->getMessage());
        }
    }
    
    /**
     * Log medication taken
     */
    public function logMedicationTaken($medicationId, $patientId, $status = 'taken', $notes = null, $administeredBy = null) {
        try {
            $sql = "INSERT INTO medication_logs (medication_id, patient_id, taken_at, status, notes, administered_by) 
                    VALUES (?, ?, NOW(), ?, ?, ?)";
            
            return $this->db->insert($sql, [
                $medicationId,
                $patientId,
                $status,
                $notes,
                $administeredBy
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to log medication: " . $e->getMessage());
        }
    }
    
    /**
     * Get medication log history
     */
    public function getMedicationLogs($patientId, $limit = 50) {
        $sql = "SELECT ml.*, m.medication_name, m.dosage, u.first_name, u.last_name 
                FROM medication_logs ml 
                JOIN medications m ON ml.medication_id = m.id 
                LEFT JOIN users u ON ml.administered_by = u.id 
                WHERE ml.patient_id = ? 
                ORDER BY ml.taken_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$patientId, $limit]);
    }
}

/**
 * Task management
 */
class TaskManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get tasks for user
     */
    public function getUserTasks($userId, $status = null) {
        $sql = "SELECT t.*, 
                       p.first_name as patient_first_name, p.last_name as patient_last_name,
                       a.first_name as assigned_first_name, a.last_name as assigned_last_name,
                       c.first_name as creator_first_name, c.last_name as creator_last_name
                FROM tasks t
                JOIN users p ON t.patient_id = p.id
                JOIN users a ON t.assigned_to = a.id
                JOIN users c ON t.created_by = c.id
                WHERE (t.assigned_to = ? OR t.created_by = ? OR t.patient_id = ?)";
        
        $params = [$userId, $userId, $userId];
        
        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY t.priority DESC, t.due_date ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create new task
     */
    public function createTask($data) {
        try {
            $sql = "INSERT INTO tasks (patient_id, assigned_to, created_by, title, description, priority, due_date, category) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            return $this->db->insert($sql, [
                $data['patient_id'],
                $data['assigned_to'],
                $data['created_by'],
                $data['title'],
                $data['description'] ?? null,
                $data['priority'] ?? 'medium',
                $data['due_date'] ?? null,
                $data['category'] ?? null
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to create task: " . $e->getMessage());
        }
    }
    
    /**
     * Update task status
     */
    public function updateTaskStatus($taskId, $status, $userId) {
        try {
            $completedAt = ($status === 'completed') ? 'NOW()' : 'NULL';
            
            $sql = "UPDATE tasks SET status = ?, completed_at = $completedAt, updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, [$status, $taskId]);
            
            // Log the task update
            $this->logTaskActivity($taskId, $userId, "Task status changed to: $status");
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to update task: " . $e->getMessage());
        }
    }
    
    /**
     * Log task activity
     */
    private function logTaskActivity($taskId, $userId, $activity) {
        try {
            $sql = "INSERT INTO care_notes (patient_id, created_by, note_type, title, content) 
                    SELECT patient_id, ?, 'task', 'Task Activity', ? 
                    FROM tasks WHERE id = ?";
            $this->db->execute($sql, [$userId, $activity, $taskId]);
        } catch (Exception $e) {
            error_log("Failed to log task activity: " . $e->getMessage());
        }
    }
}

/**
 * Appointment management
 */
class AppointmentManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get upcoming appointments
     */
    public function getUpcomingAppointments($patientId, $limit = 10) {
        $sql = "SELECT * FROM appointments 
                WHERE patient_id = ? AND appointment_date >= NOW() AND status = 'scheduled' 
                ORDER BY appointment_date ASC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$patientId, $limit]);
    }
    
    /**
     * Create new appointment
     */
    public function createAppointment($data) {
        try {
            $sql = "INSERT INTO appointments (patient_id, title, description, doctor_name, location, appointment_date, duration_minutes, type, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            return $this->db->insert($sql, [
                $data['patient_id'],
                $data['title'],
                $data['description'] ?? null,
                $data['doctor_name'] ?? null,
                $data['location'] ?? null,
                $data['appointment_date'],
                $data['duration_minutes'] ?? 60,
                $data['type'] ?? 'routine',
                $data['created_by']
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to create appointment: " . $e->getMessage());
        }
    }
}

/**
 * Statistics and analytics
 */
class AnalyticsManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get patient dashboard stats
     */
    public function getPatientStats($patientId) {
        try {
            $stats = [];
            
            // Next appointment
            $nextAppt = $this->db->fetchOne(
                "SELECT appointment_date, title FROM appointments 
                 WHERE patient_id = ? AND appointment_date >= NOW() AND status = 'scheduled' 
                 ORDER BY appointment_date ASC LIMIT 1", 
                [$patientId]
            );
            $stats['next_appointment'] = $nextAppt;
            
            // Average pain (last 7 days)
            $avgPain = $this->db->fetchOne(
                "SELECT AVG(pain_level) as avg_pain FROM daily_checkins 
                 WHERE user_id = ? AND checkin_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND pain_level IS NOT NULL", 
                [$patientId]
            );
            $stats['avg_pain'] = $avgPain['avg_pain'] ? round($avgPain['avg_pain'], 1) : null;
            
            // Open symptoms
            $openSymptoms = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM symptoms 
                 WHERE patient_id = ? AND resolved_date IS NULL", 
                [$patientId]
            );
            $stats['open_symptoms'] = $openSymptoms['count'];
            
            // Medication compliance (last 7 days)
            $compliance = $this->db->fetchOne(
                "SELECT 
                    COUNT(CASE WHEN status = 'taken' THEN 1 END) * 100.0 / COUNT(*) as compliance_rate
                 FROM medication_logs 
                 WHERE patient_id = ? AND taken_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)", 
                [$patientId]
            );
            $stats['medication_compliance'] = $compliance['compliance_rate'] ? round($compliance['compliance_rate']) : 0;
            
            return $stats;
            
        } catch (Exception $e) {
            throw new Exception("Failed to get patient stats: " . $e->getMessage());
        }
    }
    
    /**
     * Get caregiver dashboard stats
     */
    public function getCaregiverStats($caregiverId) {
        try {
            $stats = [];
            
            // Open tasks
            $openTasks = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM tasks 
                 WHERE assigned_to = ? AND status IN ('pending', 'in_progress')", 
                [$caregiverId]
            );
            $stats['open_tasks'] = $openTasks['count'];
            
            // Patients under care
            $patients = $this->db->fetchOne(
                "SELECT COUNT(DISTINCT patient_id) as count FROM tasks 
                 WHERE assigned_to = ?", 
                [$caregiverId]
            );
            $stats['patient_count'] = $patients['count'];
            
            return $stats;
            
        } catch (Exception $e) {
            throw new Exception("Failed to get caregiver stats: " . $e->getMessage());
        }
    }
}

/**
 * Document management
 */
class DocumentManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Upload document
     */
    public function uploadDocument($patientId, $uploadedBy, $file, $category, $description = null) {
        try {
            // Validate file
            if (!$this->validateFile($file)) {
                throw new Exception("Invalid file type or size");
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filePath = UPLOAD_DIR . 'documents/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception("Failed to save file");
            }
            
            // Save to database
            $sql = "INSERT INTO documents (patient_id, uploaded_by, document_name, file_path, file_type, file_size, category, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $documentId = $this->db->insert($sql, [
                $patientId,
                $uploadedBy,
                $file['name'],
                $filePath,
                $extension,
                $file['size'],
                $category,
                $description
            ]);
            
            return $documentId;
            
        } catch (Exception $e) {
            throw new Exception("Document upload failed: " . $e->getMessage());
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return false;
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_FILE_TYPES)) {
            return false;
        }
        
        return true;
    }
}
?>