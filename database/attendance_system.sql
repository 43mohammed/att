-- Attendance Management System Database Schema
-- Created: 2026-04-08
-- Version: 1.0.0

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    open_id VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(255),
    email VARCHAR(320) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'instructor', 'student') DEFAULT 'student' NOT NULL,
    login_method VARCHAR(64),
    phone VARCHAR(20),
    avatar VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_signed_in TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email),
    INDEX idx_open_id (open_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Departments Table
-- ============================================
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    head_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (head_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_name (name),
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Courses Table
-- ============================================
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    instructor_id INT NOT NULL,
    department_id INT,
    credits INT DEFAULT 3,
    max_students INT DEFAULT 50,
    absent_threshold INT DEFAULT 15,
    semester VARCHAR(50),
    year INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_instructor_id (instructor_id),
    INDEX idx_department_id (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Classrooms Table
-- ============================================
CREATE TABLE IF NOT EXISTS classrooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    building VARCHAR(100),
    floor INT,
    capacity INT DEFAULT 50,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    radius INT DEFAULT 100,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_building (building)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Schedules Table
-- ============================================
CREATE TABLE IF NOT EXISTS schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    classroom_id INT,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE SET NULL,
    INDEX idx_course_id (course_id),
    INDEX idx_day_of_week (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Enrollments Table
-- ============================================
CREATE TABLE IF NOT EXISTS enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('active', 'dropped', 'completed') DEFAULT 'active',
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id),
    INDEX idx_student_id (student_id),
    INDEX idx_course_id (course_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Attendance Sessions Table
-- ============================================
CREATE TABLE IF NOT EXISTS attendance_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    instructor_id INT NOT NULL,
    classroom_id INT,
    qr_code VARCHAR(255) NOT NULL UNIQUE,
    nfc_code VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    radius INT DEFAULT 100,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'inactive',
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE SET NULL,
    INDEX idx_course_id (course_id),
    INDEX idx_instructor_id (instructor_id),
    INDEX idx_status (status),
    INDEX idx_qr_code (qr_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Attendance Records Table
-- ============================================
CREATE TABLE IF NOT EXISTS attendance_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    session_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'absent',
    method ENUM('qr', 'nfc', 'manual', 'gps') DEFAULT 'manual',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    distance DECIMAL(10, 2),
    notes TEXT,
    checked_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, session_id),
    INDEX idx_student_id (student_id),
    INDEX idx_session_id (session_id),
    INDEX idx_course_id (course_id),
    INDEX idx_status (status),
    INDEX idx_method (method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Notifications Table
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    absence_percentage INT,
    type ENUM('absence_warning', 'info', 'reminder', 'alert') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_course_id (course_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Reports Table
-- ============================================
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    student_id INT,
    report_type ENUM('daily', 'weekly', 'monthly', 'course') DEFAULT 'daily',
    total_sessions INT DEFAULT 0,
    present_count INT DEFAULT 0,
    absent_count INT DEFAULT 0,
    late_count INT DEFAULT 0,
    excused_count INT DEFAULT 0,
    attendance_percentage DECIMAL(5, 2) DEFAULT 0,
    generated_by INT,
    file_path VARCHAR(255),
    report_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_course_id (course_id),
    INDEX idx_student_id (student_id),
    INDEX idx_report_type (report_type),
    INDEX idx_report_date (report_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Audit Logs Table
-- ============================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values LONGTEXT,
    new_values LONGTEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Indexes for Performance
-- ============================================

-- Additional indexes for common queries
CREATE INDEX idx_users_role_active ON users(role, is_active);
CREATE INDEX idx_courses_active ON courses(is_active);
CREATE INDEX idx_enrollments_student_course ON enrollments(student_id, course_id);
CREATE INDEX idx_attendance_records_course_date ON attendance_records(course_id, checked_at);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);

-- ============================================
-- Sample Data (Optional)
-- ============================================

-- Insert sample admin user
INSERT IGNORE INTO users (open_id, name, email, password, role) VALUES
('admin_001', 'Admin User', 'admin@attendance.local', SHA2('password123', 256), 'admin');

-- Insert sample instructor
INSERT IGNORE INTO users (open_id, name, email, password, role) VALUES
('instructor_001', 'Dr. Ahmed Mohammed', 'ahmed@attendance.local', SHA2('password123', 256), 'instructor');

-- Insert sample students
INSERT IGNORE INTO users (open_id, name, email, password, role) VALUES
('student_001', 'Ali Hassan', 'ali@attendance.local', SHA2('password123', 256), 'student'),
('student_002', 'Fatima Ahmed', 'fatima@attendance.local', SHA2('password123', 256), 'student'),
('student_003', 'Mohammed Saeed', 'mohammed@attendance.local', SHA2('password123', 256), 'student');

-- Insert sample department
INSERT IGNORE INTO departments (name, code, description) VALUES
('Computer Science', 'CS', 'Department of Computer Science');

-- Insert sample classroom
INSERT IGNORE INTO classrooms (name, building, floor, capacity, latitude, longitude, radius) VALUES
('Lab 101', 'Building A', 1, 30, 24.7136, 46.6753, 100);

-- Insert sample course
INSERT IGNORE INTO courses (code, name, description, instructor_id, department_id, credits, max_students, absent_threshold, semester, year) VALUES
('CS101', 'Introduction to Programming', 'Learn programming basics', 2, 1, 3, 50, 15, 'Fall', 2026);

-- Insert sample schedule
INSERT IGNORE INTO schedules (course_id, classroom_id, day_of_week, start_time, end_time) VALUES
(1, 1, 'Sunday', '09:00:00', '10:30:00'),
(1, 1, 'Tuesday', '09:00:00', '10:30:00'),
(1, 1, 'Thursday', '09:00:00', '10:30:00');

-- Insert sample enrollments
INSERT IGNORE INTO enrollments (student_id, course_id, status) VALUES
(3, 1, 'active'),
(4, 1, 'active'),
(5, 1, 'active');

-- ============================================
-- Views for Common Queries
-- ============================================

-- Student Attendance Summary View
CREATE OR REPLACE VIEW student_attendance_summary AS
SELECT 
    u.id,
    u.name,
    u.email,
    c.id as course_id,
    c.code,
    c.name as course_name,
    COUNT(ar.id) as total_sessions,
    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
    ROUND((SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) / COUNT(ar.id) * 100), 2) as attendance_percentage
FROM users u
INNER JOIN enrollments e ON u.id = e.student_id
INNER JOIN courses c ON e.course_id = c.id
LEFT JOIN attendance_records ar ON u.id = ar.student_id AND c.id = ar.course_id
WHERE u.role = 'student'
GROUP BY u.id, c.id;

-- Course Attendance Statistics View
CREATE OR REPLACE VIEW course_attendance_statistics AS
SELECT 
    c.id,
    c.code,
    c.name,
    COUNT(DISTINCT e.student_id) as total_students,
    COUNT(DISTINCT ar.student_id) as students_with_records,
    COUNT(DISTINCT ar.session_id) as total_sessions,
    ROUND(AVG(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) * 100, 2) as avg_attendance_percentage
FROM courses c
LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'active'
LEFT JOIN attendance_records ar ON c.id = ar.course_id
GROUP BY c.id;

-- Active Sessions View
CREATE OR REPLACE VIEW active_sessions AS
SELECT 
    s.id,
    s.qr_code,
    s.nfc_code,
    c.code as course_code,
    c.name as course_name,
    u.name as instructor_name,
    s.status,
    s.started_at,
    s.ended_at,
    COUNT(ar.id) as attendance_count
FROM attendance_sessions s
INNER JOIN courses c ON s.course_id = c.id
INNER JOIN users u ON s.instructor_id = u.id
LEFT JOIN attendance_records ar ON s.id = ar.session_id
WHERE s.status IN ('active', 'inactive')
GROUP BY s.id;

-- ============================================
-- Stored Procedures
-- ============================================

-- Calculate Attendance Percentage for a Student in a Course
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS sp_calculate_attendance_percentage(
    IN p_student_id INT,
    IN p_course_id INT,
    OUT p_percentage DECIMAL(5, 2)
)
BEGIN
    SELECT ROUND(
        (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*) * 100),
        2
    ) INTO p_percentage
    FROM attendance_records
    WHERE student_id = p_student_id AND course_id = p_course_id;
END //
DELIMITER ;

-- Generate Attendance Report for a Course
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS sp_generate_course_report(
    IN p_course_id INT,
    IN p_report_date DATE
)
BEGIN
    INSERT INTO reports (course_id, report_type, total_sessions, present_count, absent_count, late_count, attendance_percentage, report_date, generated_by)
    SELECT 
        p_course_id,
        'daily',
        COUNT(DISTINCT session_id),
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END),
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END),
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END),
        ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*) * 100), 2),
        p_report_date,
        1
    FROM attendance_records
    WHERE course_id = p_course_id AND DATE(checked_at) = p_report_date;
END //
DELIMITER ;

-- ============================================
-- End of Database Schema
-- ============================================
