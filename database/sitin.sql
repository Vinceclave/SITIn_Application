DROP TABLE IF EXISTS announcements;

CREATE TABLE announcements (
    announce_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_name VARCHAR(255) NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    message TEXT NOT NULL
);
