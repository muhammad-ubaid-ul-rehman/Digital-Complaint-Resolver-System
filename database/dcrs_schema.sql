-- ============================================================
--  DCRS — Digital Complaint Resolver System
--  Database Schema  |  MySQL 5.7+
--  File: dcrs_schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS dcrs_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE dcrs_db;

-- ─────────────────────────────────────────────
--  TABLE: users
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  user_id      INT          NOT NULL AUTO_INCREMENT,
  name         VARCHAR(100) NOT NULL,
  email        VARCHAR(150) NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,            -- bcrypt hash
  role         ENUM('student','admin','resolver') NOT NULL DEFAULT 'student',
  department   VARCHAR(100)  DEFAULT NULL,
  phone        VARCHAR(20)   DEFAULT NULL,
  is_active    TINYINT(1)    NOT NULL DEFAULT 1,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  INDEX idx_email (email),
  INDEX idx_role  (role)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  TABLE: complaints
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS complaints (
  complaint_id   INT          NOT NULL AUTO_INCREMENT,
  complaint_code VARCHAR(20)  NOT NULL UNIQUE,   -- e.g. CMP-0001
  student_id     INT          NOT NULL,
  resolver_id    INT               DEFAULT NULL,
  assigned_by    INT               DEFAULT NULL,  -- admin who assigned
  title          VARCHAR(255) NOT NULL,
  description    TEXT         NOT NULL,
  category       ENUM('Academic','IT','Library','Cafeteria',
                      'Hostel','Transport','Finance','Other')
                              NOT NULL DEFAULT 'Other',
  priority       ENUM('Low','Medium','High','Critical')
                              NOT NULL DEFAULT 'Medium',
  status         ENUM('Pending','Assigned','In Progress',
                      'Resolved','Closed')
                              NOT NULL DEFAULT 'Pending',
  remarks        TEXT              DEFAULT NULL,
  progress       TINYINT UNSIGNED  NOT NULL DEFAULT 0
                              CHECK (progress BETWEEN 0 AND 100),
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
  resolved_at    DATETIME          DEFAULT NULL,
  PRIMARY KEY (complaint_id),
  INDEX idx_student    (student_id),
  INDEX idx_resolver   (resolver_id),
  INDEX idx_status     (status),
  INDEX idx_priority   (priority),
  INDEX idx_category   (category),
  CONSTRAINT fk_complaint_student
    FOREIGN KEY (student_id)  REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_complaint_resolver
    FOREIGN KEY (resolver_id) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_complaint_assigner
    FOREIGN KEY (assigned_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  TABLE: progress_updates
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS progress_updates (
  update_id           INT          NOT NULL AUTO_INCREMENT,
  complaint_id        INT          NOT NULL,
  updated_by          INT          NOT NULL,
  update_text         TEXT         NOT NULL,
  progress_percentage TINYINT UNSIGNED NOT NULL DEFAULT 0
                      CHECK (progress_percentage BETWEEN 0 AND 100),
  old_status          VARCHAR(20)  DEFAULT NULL,
  new_status          VARCHAR(20)  DEFAULT NULL,
  created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (update_id),
  INDEX idx_complaint_updates (complaint_id),
  CONSTRAINT fk_update_complaint
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
  CONSTRAINT fk_update_user
    FOREIGN KEY (updated_by)   REFERENCES users(user_id)      ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  TABLE: notifications
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
  notification_id INT          NOT NULL AUTO_INCREMENT,
  user_id         INT          NOT NULL,
  complaint_id    INT               DEFAULT NULL,
  type            ENUM('submitted','assigned','updated',
                       'resolved','closed','comment')
                               NOT NULL DEFAULT 'updated',
  message         TEXT         NOT NULL,
  is_read         TINYINT(1)   NOT NULL DEFAULT 0,
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (notification_id),
  INDEX idx_notif_user     (user_id),
  INDEX idx_notif_read     (user_id, is_read),
  CONSTRAINT fk_notif_user
    FOREIGN KEY (user_id)      REFERENCES users(user_id)      ON DELETE CASCADE,
  CONSTRAINT fk_notif_complaint
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  TABLE: sessions  (server-side session store)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sessions (
  session_id   VARCHAR(128) NOT NULL,
  user_id      INT          NOT NULL,
  ip_address   VARCHAR(45)  DEFAULT NULL,
  user_agent   TEXT         DEFAULT NULL,
  last_activity DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (session_id),
  INDEX idx_session_user (user_id),
  CONSTRAINT fk_session_user
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  SEED DATA — demo accounts (password = "password123")
--  Hash generated with: password_hash('password123', PASSWORD_BCRYPT)
-- ============================================================

INSERT INTO users (name, email, password, role, department) VALUES
  ('Admin Officer',   'admin@university.edu',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'admin',    'Administration'),
  ('Ali Hassan',      'ali@university.edu',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'student',  'Computer Science'),
  ('Sara Ahmed',      'sara@university.edu',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'student',  'Business Administration'),
  ('Usman Khan',      'usman@university.edu',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'student',  'Electrical Engineering'),
  ('Dr. Fatima Malik','fatima@university.edu',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'resolver', 'IT & Services'),
  ('Mr. Tariq Butt',  'tariq@university.edu',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'resolver', 'Academic Affairs');

-- Seed complaints
INSERT INTO complaints
  (complaint_code, student_id, resolver_id, assigned_by,
   title, description, category, priority, status, remarks, progress, resolved_at)
VALUES
  ('CMP-0001', 2, 5, 1,
   'Library books overdue policy unclear',
   'The library policy regarding overdue books is confusing and contradicts the student handbook. Students are being fined incorrectly.',
   'Library','High','Resolved',
   'Policy document has been updated and published on the student portal.',
   100, '2025-01-15 14:30:00'),

  ('CMP-0002', 3, 5, 1,
   'Wi-Fi connectivity issues in Block C',
   'Wi-Fi drops every 30 minutes in Block C classrooms, severely affecting online exams and e-learning sessions.',
   'IT','Critical','In Progress',
   'Network team is investigating. New router has been ordered.',
   60, NULL),

  ('CMP-0003', 2, NULL, NULL,
   'Cafeteria food quality declining',
   'Food quality in the main cafeteria has dropped significantly over the past month. Portions are smaller and hygiene is questionable.',
   'Cafeteria','Medium','Pending',
   NULL, 0, NULL),

  ('CMP-0004', 3, 6, 1,
   'Exam schedule conflict for CS final year',
   'Two major exams (Database Systems and Software Engineering) are scheduled on the same day for CS final year students.',
   'Academic','Critical','Assigned',
   'Escalated to the examination committee for rescheduling.',
   20, NULL),

  ('CMP-0005', 4, 6, 1,
   'Hostel room 204 broken tap',
   'Water tap in room 204, Boys Hostel Block A has been leaking for 2 weeks. Maintenance requests have been ignored.',
   'Hostel','Low','Resolved',
   'Repaired by the maintenance team on 2025-01-10.',
   100, '2025-01-10 10:00:00');

-- Seed progress updates
INSERT INTO progress_updates
  (complaint_id, updated_by, update_text, progress_percentage, old_status, new_status)
VALUES
  (1, 1,  'Complaint received and assigned to IT & Services resolver.', 10,  'Pending',     'Assigned'),
  (1, 5,  'Reviewing the library policy document with the librarian.',  40,  'Assigned',    'In Progress'),
  (1, 5,  'Policy updated. Circular sent to all students via portal.',  100, 'In Progress', 'Resolved'),
  (2, 1,  'Assigned to IT department for investigation.',               10,  'Pending',     'Assigned'),
  (2, 5,  'Issue confirmed. Hardware replacement ordered.',             60,  'Assigned',    'In Progress'),
  (4, 1,  'Escalated to the academic committee.',                       20,  'Pending',     'Assigned'),
  (5, 1,  'Assigned to maintenance resolver.',                          10,  'Pending',     'Assigned'),
  (5, 6,  'Tap replaced. Issue fully resolved.',                        100, 'In Progress', 'Resolved');

-- Seed notifications
INSERT INTO notifications (user_id, complaint_id, type, message, is_read) VALUES
  (2, 1, 'resolved',  'Your complaint CMP-0001 has been resolved.', 1),
  (3, 2, 'updated',   'CMP-0002: Progress updated to 60%. Hardware ordered.', 0),
  (3, 4, 'assigned',  'CMP-0004 has been assigned to a resolver.', 0),
  (4, 5, 'resolved',  'Your complaint CMP-0005 has been resolved.', 1),
  (5, 2, 'assigned',  'Complaint CMP-0002 has been assigned to you.', 0),
  (6, 4, 'assigned',  'Complaint CMP-0004 has been assigned to you.', 0),
  (1, 3, 'submitted', 'New complaint CMP-0003 submitted by Sara Ahmed.', 0);
