# Class Diagram
## Attendance Management System Architecture

```mermaid
classDiagram
    class User {
        -int id
        -string openId
        -string name
        -string email
        -string password
        -enum role
        -timestamp created_at
        -timestamp updated_at
        +authenticate()
        +changePassword()
        +updateProfile()
        +getRole()
    }

    class Course {
        -int id
        -string code
        -string name
        -string description
        -int instructor_id
        -int credits
        -int max_students
        -int absent_threshold
        +createSession()
        +getEnrollments()
        +generateReport()
        +getAttendanceStats()
    }

    class Enrollment {
        -int id
        -int student_id
        -int course_id
        -enum status
        -timestamp enrolled_at
        +getAttendanceRecords()
        +calculateAttendancePercentage()
        +getStudentStats()
    }

    class AttendanceSession {
        -int id
        -int course_id
        -int instructor_id
        -string qr_code
        -string nfc_code
        -float latitude
        -float longitude
        -int radius
        -enum status
        -timestamp started_at
        -timestamp ended_at
        +generateQRCode()
        +generateNFCCode()
        +startSession()
        +endSession()
        +recordAttendance()
        +getAttendanceList()
    }

    class AttendanceRecord {
        -int id
        -int student_id
        -int session_id
        -int course_id
        -enum status
        -string method
        -float latitude
        -float longitude
        -float distance
        -timestamp checked_at
        +verifyLocation()
        +calculateDistance()
        +validateQRCode()
        +validateNFCCode()
        +saveToLocal()
        +syncToServer()
    }

    class Notification {
        -int id
        -int user_id
        -int course_id
        -string title
        -string message
        -int absence_percentage
        -enum type
        -boolean read
        +send()
        +markAsRead()
        +getUnreadCount()
        +sendPushNotification()
    }

    class Report {
        -int id
        -int course_id
        -int student_id
        -string report_type
        -int total_sessions
        -int present_count
        -int absent_count
        -int late_count
        -float attendance_percentage
        +generatePDF()
        +generateExcel()
        +generateCSV()
        +getStatistics()
        +exportReport()
    }

    class AuditLog {
        -int id
        -int user_id
        -string action
        -string table_name
        -int record_id
        -string old_values
        -string new_values
        -string ip_address
        -string user_agent
        +logAction()
        +getHistory()
        +trackChanges()
    }

    class AuthController {
        -User user
        +login()
        +register()
        +logout()
        +changePassword()
        +updateProfile()
        +verifyEmail()
    }

    class DashboardController {
        -User user
        +getAdminDashboard()
        +getInstructorDashboard()
        +getStudentDashboard()
        +getStatistics()
        +getRecentActivity()
    }

    class AttendanceController {
        -AttendanceSession session
        -AttendanceRecord record
        +recordAttendance()
        +scanQRCode()
        +scanNFC()
        +verifyLocation()
        +getAttendanceList()
        +editRecord()
        +deleteRecord()
    }

    class SessionController {
        -AttendanceSession session
        +createSession()
        +updateSession()
        +deleteSession()
        +startSession()
        +endSession()
        +getActiveSessions()
        +generateQRCode()
    }

    class ReportController {
        -Report report
        +generateDailyReport()
        +generateWeeklyReport()
        +generateMonthlyReport()
        +exportPDF()
        +exportExcel()
        +getStatistics()
    }

    class QRCodeScanner {
        -video element
        -canvas element
        -boolean isScanning
        +start()
        +stop()
        +scan()
        +processQRCode()
        +submitData()
    }

    class NFCScanner {
        -boolean isSupported
        +start()
        +stop()
        +read()
        +processNFCData()
        +submitData()
    }

    class GPSLocation {
        -float latitude
        -float longitude
        +getLocation()
        +calculateDistance()
        +verifyLocation()
        +checkRadius()
    }

    class IndexedDBStorage {
        -IDBDatabase db
        +init()
        +save()
        +getAll()
        +get()
        +delete()
        +clear()
    }

    class ServiceWorker {
        -Cache cache
        -MessageChannel channel
        +install()
        +activate()
        +fetch()
        +sync()
        +push()
        +message()
    }

    class NotificationService {
        -Notification notification
        +requestPermission()
        +sendNotification()
        +sendPushNotification()
        +handleNotificationClick()
    }

    %% Relationships
    User "1" --> "*" Course : teaches
    User "1" --> "*" Enrollment : enrolls
    User "1" --> "*" AttendanceSession : creates
    User "1" --> "*" AttendanceRecord : records
    User "1" --> "*" Notification : receives
    User "1" --> "*" AuditLog : performs
    
    Course "1" --> "*" Enrollment : has
    Course "1" --> "*" AttendanceSession : has
    Course "1" --> "*" Report : generates
    
    Enrollment "1" --> "*" AttendanceRecord : tracks
    
    AttendanceSession "1" --> "*" AttendanceRecord : contains
    
    AttendanceRecord "1" --> "*" Notification : triggers
    
    AuthController --> User
    DashboardController --> User
    AttendanceController --> AttendanceSession
    AttendanceController --> AttendanceRecord
    SessionController --> AttendanceSession
    ReportController --> Report
    
    AttendanceRecord --> QRCodeScanner
    AttendanceRecord --> NFCScanner
    AttendanceRecord --> GPSLocation
    
    IndexedDBStorage --> AttendanceRecord
    ServiceWorker --> IndexedDBStorage
    NotificationService --> Notification
```

---

## Class Descriptions

### Model Classes

#### User
Represents system users with different roles (admin, instructor, student).
- **Attributes:** id, openId, name, email, password, role, timestamps
- **Methods:** authenticate, changePassword, updateProfile, getRole

#### Course
Represents academic courses with attendance settings.
- **Attributes:** id, code, name, description, instructor_id, credits, max_students, absent_threshold
- **Methods:** createSession, getEnrollments, generateReport, getAttendanceStats

#### Enrollment
Tracks student enrollment in courses.
- **Attributes:** id, student_id, course_id, status, enrolled_at
- **Methods:** getAttendanceRecords, calculateAttendancePercentage, getStudentStats

#### AttendanceSession
Represents a single attendance session with QR/NFC codes.
- **Attributes:** id, course_id, instructor_id, qr_code, nfc_code, location, radius, status, timestamps
- **Methods:** generateQRCode, generateNFCCode, startSession, endSession, recordAttendance, getAttendanceList

#### AttendanceRecord
Individual attendance records with GPS verification.
- **Attributes:** id, student_id, session_id, course_id, status, method, location, distance, checked_at
- **Methods:** verifyLocation, calculateDistance, validateQRCode, validateNFCCode, saveToLocal, syncToServer

#### Notification
System notifications for absence warnings and reminders.
- **Attributes:** id, user_id, course_id, title, message, absence_percentage, type, read
- **Methods:** send, markAsRead, getUnreadCount, sendPushNotification

#### Report
Generated attendance reports in multiple formats.
- **Attributes:** id, course_id, student_id, report_type, statistics, attendance_percentage
- **Methods:** generatePDF, generateExcel, generateCSV, getStatistics, exportReport

#### AuditLog
Tracks all system changes and user actions.
- **Attributes:** id, user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent
- **Methods:** logAction, getHistory, trackChanges

---

### Controller Classes

#### AuthController
Handles user authentication and profile management.
- **Methods:** login, register, logout, changePassword, updateProfile, verifyEmail

#### DashboardController
Manages dashboard views for different roles.
- **Methods:** getAdminDashboard, getInstructorDashboard, getStudentDashboard, getStatistics, getRecentActivity

#### AttendanceController
Handles attendance recording and verification.
- **Methods:** recordAttendance, scanQRCode, scanNFC, verifyLocation, getAttendanceList, editRecord, deleteRecord

#### SessionController
Manages attendance sessions.
- **Methods:** createSession, updateSession, deleteSession, startSession, endSession, getActiveSessions, generateQRCode

#### ReportController
Generates and exports reports.
- **Methods:** generateDailyReport, generateWeeklyReport, generateMonthlyReport, exportPDF, exportExcel, getStatistics

---

### Service Classes

#### QRCodeScanner
Handles QR code scanning from camera.
- **Attributes:** video element, canvas element, isScanning flag
- **Methods:** start, stop, scan, processQRCode, submitData

#### NFCScanner
Handles NFC tag reading.
- **Attributes:** isSupported flag
- **Methods:** start, stop, read, processNFCData, submitData

#### GPSLocation
Handles GPS location verification.
- **Attributes:** latitude, longitude
- **Methods:** getLocation, calculateDistance, verifyLocation, checkRadius

#### IndexedDBStorage
Handles local data storage for offline support.
- **Attributes:** IDBDatabase instance
- **Methods:** init, save, getAll, get, delete, clear

#### ServiceWorker
Handles PWA functionality and offline support.
- **Attributes:** Cache instance, MessageChannel
- **Methods:** install, activate, fetch, sync, push, message

#### NotificationService
Handles push notifications and alerts.
- **Attributes:** Notification instance
- **Methods:** requestPermission, sendNotification, sendPushNotification, handleNotificationClick

---

## Design Patterns Used

| Pattern | Usage |
|---------|-------|
| **MVC** | Model-View-Controller architecture |
| **Repository** | Data access abstraction |
| **Service** | Business logic encapsulation |
| **Singleton** | Database and cache instances |
| **Observer** | Event handling and notifications |
| **Factory** | Object creation |
| **Strategy** | Multiple attendance verification methods |

---

## Architecture Layers

```
┌─────────────────────────────────────┐
│     Presentation Layer (Views)      │
│  HTML/CSS/JavaScript UI Components  │
└─────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────┐
│    Application Layer (Controllers)  │
│  Business Logic & Request Handling  │
└─────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────┐
│      Domain Layer (Models)          │
│  Core Business Entities & Rules     │
└─────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────┐
│    Data Access Layer (Repository)   │
│  Database Operations & Queries      │
└─────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────┐
│    Infrastructure Layer (Services)  │
│  External Services & Utilities      │
└─────────────────────────────────────┘
```

---

## Interaction Flow

### Attendance Recording Flow
```
Student → QRCodeScanner → AttendanceRecord → GPSLocation → Verification
                                                    ↓
                                            AttendanceController
                                                    ↓
                                            IndexedDBStorage
                                                    ↓
                                            ServiceWorker (Sync)
                                                    ↓
                                            Server Database
                                                    ↓
                                            Notification Service
```

### Report Generation Flow
```
ReportController → Report Model → Database Query → Data Aggregation
                                                    ↓
                                            Statistics Calculation
                                                    ↓
                                            PDF/Excel Generation
                                                    ↓
                                            File Export
```

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 10+ |
| **Database** | SQLite/MySQL |
| **Frontend** | HTML5, CSS3, JavaScript ES6+ |
| **PWA** | Service Worker, Web APIs |
| **QR Code** | QR Code Scanner Library |
| **NFC** | Web NFC API |
| **GPS** | Geolocation API |
| **Storage** | IndexedDB |
| **Notifications** | Push API |

---

## Security Considerations

✅ **Authentication:** Secure login with password hashing
✅ **Authorization:** Role-based access control (RBAC)
✅ **Data Validation:** Input validation and sanitization
✅ **CSRF Protection:** Token-based CSRF protection
✅ **Audit Trail:** Complete audit logging
✅ **GPS Verification:** Location-based verification
✅ **QR Code Security:** Unique codes per session
✅ **Encryption:** Data encryption in transit and at rest
