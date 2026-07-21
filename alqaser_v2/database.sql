
CREATE DATABASE IF NOT EXISTS alqaser_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE alqaser_db;


CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100)        NOT NULL,
    email       VARCHAR(150)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,
    role        ENUM('admin','student') DEFAULT 'student',
    phone       VARCHAR(20),
    created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)        NOT NULL,
    description TEXT,
    created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE courses (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    category_id   INT              NOT NULL,
    title         VARCHAR(200)     NOT NULL,
    description   TEXT,
    duration      VARCHAR(50),
    seats         INT              DEFAULT 30,
    image         VARCHAR(255),
    status        ENUM('active','inactive') DEFAULT 'active',
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE enrollments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT              NOT NULL,
    course_id   INT              NOT NULL,
    status      ENUM('pending','approved','rejected') DEFAULT 'pending',
    enrolled_at TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,

    UNIQUE KEY unique_enrollment (user_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE attendance (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT              NOT NULL,
    attendance_date DATE             NOT NULL,
    status          ENUM('present','absent','late') DEFAULT 'present',
    recorded_by     INT,
    notes           VARCHAR(255),
    created_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,

    UNIQUE KEY unique_attendance (user_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



INSERT INTO users (full_name, email, password, role, phone) VALUES
('مسؤول النظام', 'adminbaraa@alqaser.ps',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'admin', '0595970941');

INSERT INTO categories (name, description) VALUES
('تقنية المعلومات', 'دورات في البرمجة والشبكات'),
('التسويق الرقمي', 'دورات في السوشيال ميديا والإعلان'),
('التصميم الجرافيكي', 'دورات في فوتوشوب وإليستريتور');

INSERT INTO courses (category_id, title, description, duration, seats) VALUES
(1, 'تطوير مواقع الويب بـ PHP', 'تعلم PHP وMySQL من الصفر', '4 أسابيع', 25),
(1, 'أساسيات الشبكات', 'دورة في أساسيات الشبكات وبروتوكولات الإنترنت', '3 أسابيع', 20),
(2, 'إدارة السوشيال ميديا', 'كيفية إدارة حسابات التواصل الاجتماعي', '2 أسبوع', 30);