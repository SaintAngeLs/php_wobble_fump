-- Setup the database and table
CREATE DATABASE IF NOT EXISTS user_activity_db;

USE user_activity_db;

DROP TABLE IF EXISTS user_activity;

CREATE TABLE user_activity (
    user_id BIGINT NOT NULL,
    download_id BIGINT NOT NULL,
    ts DATETIME NOT NULL,
    rev TINYINT NOT NULL,
    source_app VARCHAR(50) NOT NULL,
    server VARCHAR(100) NOT NULL,
    PRIMARY KEY (user_id, download_id) 
);

LOAD DATA LOCAL INFILE './user_downloads.csv'
INTO TABLE user_activity
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"' 
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(user_id, download_id, ts, rev, source_app, server);

-- Retrieve up to 10 most active users in the last 180 days
SELECT user_id,  COUNT(*) AS activity_count
FROM 
    user_activity
WHERE 
    ts >= DATE_SUB(NOW(), INTERVAL 180 DAY)
GROUP BY user_id ORDER BY activity_count DESC
LIMIT 10;

-- Retrieve the latest activity for each user_id
SELECT ua1.user_id,  ua1.download_id, ua1.ts, ua1.rev, ua1.source_app, ua1.server
FROM 
    user_activity ua1
INNER JOIN (
    SELECT 
        user_id, MAX(ts) AS latest_ts
    FROM 
        user_activity
    GROUP BY user_id
) ua2 ON ua1.user_id = ua2.user_id AND ua1.ts = ua2.latest_ts;
