CREATE TABLE IF NOT EXISTS leaderboard (
    idno VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    total_sessions INT DEFAULT 0,
    total_points INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_points (total_points DESC),
    INDEX idx_sessions (total_sessions DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 