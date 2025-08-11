# Momilove52 - Care Tracker Web Application

A comprehensive web application for managing patient care, built with Bootstrap, PHP, HTML, CSS, JavaScript, and MySQL. This application provides tools for patients, caregivers, and family members to track daily health metrics, medications, appointments, and tasks.

## 🚀 Features

### Core Patient & Caregiver Features
- **User Management**: Patient, caregiver, and family member accounts with role-based permissions
- **Daily Check-ins**: Track mood, energy levels, pain levels, and daily notes
- **Medication Management**: Log medications, track adherence, and set reminders
- **Task Management**: Create and track care-related tasks with priority levels
- **Appointment Scheduling**: Track upcoming medical appointments
- **Dashboard Analytics**: Real-time stats and trends for health metrics
- **Data Export**: JSON export functionality for data portability
- **Comprehensive Error Logging**: Built-in error handling and logging system

### Technical Features
- **Bootstrap 5**: Modern, responsive UI framework
- **PHP 8+ Compatible**: Secure backend with PDO database connections
- **MySQL Database**: Robust data storage with proper relationships
- **CSRF Protection**: Security tokens for form submissions
- **Session Management**: Secure user authentication and session handling
- **API Endpoints**: RESTful API for AJAX operations
- **Mobile Responsive**: Works seamlessly on all device sizes

## 🛠 Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- phpMyAdmin (optional, for database management)

### Database Setup
1. Import the database structure:
   ```sql
   mysql -u root -p < database.sql
   ```

2. Or manually create the database using phpMyAdmin:
   - Database name: `outsrglr_mom`
   - Username: `outsrglr_mom`
   - Password: `born#1852Niptuck`

### Web Server Configuration
1. Clone this repository to your web server directory
2. Ensure the following directories are writable:
   - `logs/`
   - `uploads/`
   - `uploads/documents/`

3. Update database credentials in `config/database.php` if needed

### First Time Setup
1. Navigate to `register.php` to create user accounts
2. Or use the demo credentials in `login.php`:
   - **Patient**: diana@example.com / password
   - **Caregiver**: chance@example.com / password

## 📁 Project Structure

```
Momilove52/
├── api/                    # API endpoints
│   ├── export.php         # Data export functionality
│   ├── medications.php    # Medication management API
│   └── tasks.php          # Task management API
├── config/
│   └── database.php       # Database configuration and connection
├── includes/
│   ├── functions.php      # Core application functions
│   └── error_page.php     # User-friendly error display
├── logs/                  # Application logs (auto-generated)
├── uploads/               # File uploads directory
│   └── documents/         # Patient documents
├── css/                   # Additional stylesheets
├── js/                    # JavaScript files
├── index.php              # Main dashboard
├── login.php              # User authentication
├── register.php           # User registration
├── logout.php             # Logout functionality
├── database.sql           # Database schema and sample data
└── README.md              # This file
```

## 🔧 Configuration

### Database Configuration
Edit `config/database.php` to update database settings:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'outsrglr_mom');
define('DB_USER', 'outsrglr_mom');
define('DB_PASS', 'born#1852Niptuck');
```

### Application Settings
Key configuration options in `config/database.php`:
- `SESSION_LIFETIME`: Session timeout (default: 1 hour)
- `MAX_FILE_SIZE`: Maximum file upload size (default: 10MB)
- `LOG_LEVEL`: Logging verbosity (DEBUG, INFO, WARNING, ERROR, CRITICAL)

## 📊 Database Schema

The application uses a comprehensive database schema with the following main tables:

- **users**: Patient, caregiver, and family member accounts
- **daily_checkins**: Daily mood, energy, and pain tracking
- **medications**: Medication information and prescriptions
- **medication_logs**: Medication adherence tracking
- **tasks**: Care-related task management
- **appointments**: Medical appointment scheduling
- **symptoms**: Symptom tracking and monitoring
- **vitals**: Health vitals recording (blood pressure, heart rate, etc.)
- **documents**: Secure document storage
- **care_notes**: Care provider notes and observations
- **contacts**: Emergency and medical contacts
- **error_logs**: Application error logging

## 🔐 Security Features

- **Password Hashing**: Bcrypt with configurable cost
- **CSRF Protection**: Token-based form security
- **Input Sanitization**: All user inputs are sanitized
- **SQL Injection Prevention**: Prepared statements
- **Session Security**: Secure session management
- **Error Handling**: Comprehensive error logging without exposing sensitive data

## 🎯 Usage

### For Patients
1. **Daily Check-ins**: Record daily mood, energy, and pain levels
2. **Medication Tracking**: Log when medications are taken
3. **Appointment Management**: View upcoming medical appointments
4. **Health Monitoring**: Track symptoms and vitals over time

### For Caregivers
1. **Task Management**: Create and track care-related tasks
2. **Patient Monitoring**: View patient check-ins and medication adherence
3. **Care Coordination**: Manage multiple patients and tasks
4. **Reporting**: Generate reports on patient progress

### For Family Members
1. **Monitoring**: View patient status and progress
2. **Communication**: Access shared care notes
3. **Support**: Track caregiver workload and stress levels

## 🛡 Error Handling & Logging

The application includes comprehensive error handling:

- **Database Errors**: Automatic logging with user-friendly messages
- **Application Errors**: Stack trace logging for debugging
- **User Errors**: Validation messages and helpful feedback
- **System Monitoring**: Performance and usage tracking

Logs are stored in the `logs/` directory and can be monitored for:
- Application errors
- Security events
- User activities
- Performance issues

## 🚀 API Documentation

### Medication API (`/api/medications.php`)
- `GET`: Retrieve patient medications
- `POST`: Add new medication or log medication taken

### Task API (`/api/tasks.php`)
- `GET`: Retrieve user tasks
- `POST`: Create new task or update task status

### Export API (`/api/export.php`)
- `GET`: Export user data as JSON

## 🎨 Customization

The application uses CSS custom properties for easy theming:
```css
:root {
  --bg: #0f1115;
  --panel: #151822;
  --accent: #6c5ce7;
  --success: #2ecc71;
  --danger: #ff6b6b;
}
```

## 📱 Mobile Support

The application is fully responsive and includes:
- Touch-friendly buttons and controls
- Optimized layouts for small screens
- Fast loading on mobile networks
- Offline-capable features (planned)

## 🔄 Future Enhancements

Planned features include:
- Real-time notifications
- Telehealth integration
- Advanced analytics and reporting
- Mobile app development
- API integrations with healthcare systems
- Multi-language support
- Advanced security features

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database exists and user has proper permissions

2. **Permission Errors**
   - Ensure `logs/` and `uploads/` directories are writable
   - Check file permissions (755 for directories, 644 for files)

3. **Session Issues**
   - Check PHP session configuration
   - Ensure session directory is writable
   - Verify session timeout settings

### Debug Mode
Enable debug mode by setting `LOG_LEVEL` to 'DEBUG' in `config/database.php`

## 📞 Support

For support and questions:
- Check the error logs in `logs/` directory
- Review database connection settings
- Ensure all prerequisites are met
- Verify file permissions

## 🤝 Contributing

This is a care management application designed for personal use. Please ensure any modifications maintain the security and privacy standards required for healthcare-related data.

## 📄 License

This project is intended for personal and educational use. Please respect privacy and security requirements when handling healthcare data.

---

**Note**: This application handles sensitive healthcare information. Please ensure compliance with relevant privacy laws and regulations in your jurisdiction.

## 💡 Additional Tab Ideas

Based on the comprehensive feature list, here are additional tabs that could be added to enhance the application:

### Patient Tabs
- **🔬 Vitals Tracking**: Blood pressure, heart rate, temperature, oxygen saturation
- **😴 Sleep Log**: Bedtime, wake time, sleep quality tracking
- **🍽️ Nutrition**: Meal tracking, calorie counting, hydration monitoring
- **🏃 Activity/Exercise**: Physical therapy exercises, mobility tracking
- **🧠 Mental Health**: Mood tracking, anxiety/depression assessments
- **💉 Vaccinations**: Vaccination records and reminders
- **🤧 Allergies**: Allergy information and emergency plans
- **📊 Reports**: Health trend analysis and progress reports
- **🎯 Goals**: Health goals and milestone tracking
- **📱 Devices**: Integration with health monitoring devices

### Caregiver Tabs
- **👥 Multi-Patient**: Overview of all patients under care
- **⚠️ Alerts**: Critical alerts and notifications
- **📝 Incident Reports**: Fall reports, emergency incidents
- **🔄 Handover**: Shift change notes and communication
- **📋 Care Plans**: Customizable care plan templates
- **🩹 Wound Care**: Photo tracking and dressing change logs
- **💊 PRN Medications**: As-needed medication tracking
- **🚗 Transportation**: Transport planning and scheduling
- **📞 Communications**: Messaging hub for team coordination
- **📈 Analytics**: Caregiver performance and patient outcomes

### Family Member Tabs
- **👁️ Monitor**: Read-only view of patient status
- **💬 Messages**: Communication with care team
- **📅 Shared Calendar**: Family involvement in appointments
- **📊 Progress**: Patient progress reports
- **🆘 Emergency**: Emergency contacts and procedures
- **💰 Insurance**: Insurance information and claims
- **🏥 Providers**: Healthcare provider directory
- **📚 Education**: Educational resources for families