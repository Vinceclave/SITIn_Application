-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Delete data from all tables except users in the correct order
-- First delete from tables that reference other tables
DELETE FROM lab_points;
DELETE FROM sit_in;
DELETE FROM feedback;
DELETE FROM laboratory_messages;
DELETE FROM announcements;
DELETE FROM leaderboard;
DELETE FROM student_session;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Create or update student_session entries for all students
INSERT INTO student_session (idno, session, points)
SELECT idno, 30, 0 FROM users WHERE role = 'Student' AND idno IS NOT NULL
ON DUPLICATE KEY UPDATE session = 30, points = 0;

-- Update leaderboard with fresh data
INSERT INTO leaderboard (idno, full_name, total_sessions, total_points)
SELECT 
    u.idno, 
    CONCAT(u.firstname, ' ', u.lastname) AS full_name,
    0 AS total_sessions,
    0 AS total_points
FROM 
    users u
WHERE 
    u.role = 'Student' AND u.idno IS NOT NULL
ON DUPLICATE KEY UPDATE 
    full_name = VALUES(full_name),
    total_sessions = 0,
    total_points = 0;

-- Show confirmation
SELECT 'Database reset successful. All students now have 30 sessions.' AS message; 