CREATE DATABASE stroke_dbm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stroke_dbm;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Patients table (basic patient info)
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    medical_history TEXT,
    doctor_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);

-- Patient Records table (tracking patient status per visit)
CREATE TABLE patient_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    exam_date DATE NOT NULL,
    scan_image VARCHAR(255),
    ai_result ENUM('affected', 'not_affected'),
    doctor_confirm ENUM('yes', 'no', 'pending') DEFAULT 'pending',
    notes TEXT,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Articles table
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    image_path VARCHAR(255),
    category_id INT,
    uploaded_by INT,
    published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Tags table
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Article-Tags relationship (many-to-many)
CREATE TABLE article_tags (
    article_id INT,
    tag_id INT,
    FOREIGN KEY (article_id) REFERENCES articles(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id),
    PRIMARY KEY (article_id, tag_id)
);

-- Complaints table (new)
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT,
    complaint_text TEXT NOT NULL,
    response TEXT,
    status ENUM('pending', 'resolved') DEFAULT 'pending',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    responded_at DATETIME,
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);
--Add shared_cases table
CREATE TABLE shared_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    record_id INT,
    shared_by INT,
    shared_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (record_id) REFERENCES patient_records(id),
    FOREIGN KEY (shared_by) REFERENCES users(id)
);

--Add case_comments table
CREATE TABLE case_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shared_case_id INT,
    doctor_id INT,
    comment_text TEXT NOT NULL,
    commented_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shared_case_id) REFERENCES shared_cases(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);
-- Insert admin user
INSERT INTO users (username, password, role) VALUES ('admin', 'admin123', 'admin');
INSERT INTO users (username, password, role) VALUES ('doctor1', 'doc123', 'doctor');

-- Insert sample categories
INSERT INTO categories (name) VALUES ('Stroke Diagnosis'), ('Treatment Protocols'), ('Research Papers');

-- Insert sample tags
INSERT INTO tags (name) VALUES ('stroke'), ('diagnosis'), ('treatment'), ('brain'), ('AI');

-- Insert sample articles
INSERT INTO articles (title, description, file_path, image_path, category_id, uploaded_by) VALUES
('Stroke Diagnosis Guide', 'A comprehensive guide on diagnosing strokes.', 'assets/uploads/stroke_guide.pdf', 'assets/uploads/stroke_image1.jpg', 1, 1),
('Treatment Protocols 2023', 'Latest protocols for stroke treatment.', 'assets/uploads/treatment_2023.pdf', 'assets/uploads/treatment_image.jpg', 2, 1),
('AI in Stroke Research', 'How AI improves stroke diagnosis.', 'assets/uploads/ai_research.pdf', NULL, 3, 1);

-- Insert article-tag relationships
INSERT INTO article_tags (article_id, tag_id) VALUES
(1, 1), (1, 2), (2, 3), (2, 1), (3, 4), (3, 5);

-- Insert sample patients
INSERT INTO patients (name, age, medical_history, doctor_id) VALUES
('John Doe', 55, 'Hypertension, smoking', 2),
('Jane Smith', 62, 'Diabetes', 2),
('Michael Brown', 45, 'No history', 2);

-- Insert sample patient records
INSERT INTO patient_records (patient_id, exam_date, scan_image, ai_result, doctor_confirm, notes) VALUES
(1, '2025-01-01', 'assets/uploads/scan1.jpg', 'affected', 'yes', 'Patient showed improvement'),
(1, '2025-01-15', 'assets/uploads/scan2.jpg', 'not_affected', 'yes', 'Stable condition'),
(2, '2025-01-02', NULL, NULL, 'pending', 'Awaiting scan');

-- Insert sample complaints
INSERT INTO complaints (doctor_id, complaint_text) VALUES
(2, 'System is slow during peak hours'),
(2, 'Need more training resources');