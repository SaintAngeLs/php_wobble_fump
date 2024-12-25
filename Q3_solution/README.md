# Q3: MySQL Table Schema and Queries for User Activity Analysis

This document outlines the solution for analyzing user activity based on the provided CSV file. The tasks include creating a table schema, writing queries to find the most active users, and retrieving the most recent activity for each user.

---


## 1. MySQL Table Schema

The proposed schema to store the dataset is as follows:

```sql
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
```

This schema includes:
- `user_id`: The unique identifier for each user.
- `download_id`: The unique identifier for each download.
- `ts`: The timestamp of the activity.
- `rev`: Revision number.
- `source_app`: The application source of the activity.
- `server`: The server associated with the activity.

---

## 2. Query: Top 10 Most Active Users in the Last 180 Days

This query retrieves the top 10 users with the highest activity count within the last 180 days:

```sql
SELECT user_id,  COUNT(*) AS activity_count
FROM 
    user_activity
WHERE 
    ts >= DATE_SUB(NOW(), INTERVAL 180 DAY)
GROUP BY user_id ORDER BY activity_count DESC
LIMIT 10;
```

### Example Output:
```bash
user_id activity_count
567890  1
123456  1
345123  1
```

---

## 3. Query: Most Recent Activity for Each User

This query retrieves the most recent activity for each user:

```sql
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

```

### Example Output:
```bash


user_id download_id     ts                      rev     source_app      server
123456  6003932         2024-08-26 01:02:20     1       app3    user@host1
234567  9400696         2022-01-31 09:50:04     0       app1    user@host1
345123  282392405       2024-09-13 10:07:32     0       app2    user@host1
345678  34343955        2023-11-16 13:43:18     1       app2    user@host2
567890  43726887        2024-09-18 12:12:56     1       app1    user@host1
987123  74415349        2023-01-15 23:02:09     0       app1    user@host3
987333  282099767       2024-04-19 07:20:16     0       app3    user@host2
```
---

## Steps to Run the Solution

1. **Setup the Database and Table**:
   - Save the schema and data loading commands in a file `setup_user_activity.sql`.
   - Run the script using:
     ```bash
     mysql -u user_with_access -p < setup_user_activity.sql
     ```

2. **Run Queries**:
   - Execute the provided queries in your MySQL client to get the results:
     ```bash
     mysql -u user_with_access -p
     USE user_activity_db;
     ```

3. **Expected Results**:
   - The first query lists the top 10 users by activity in the last 180 days.
   - The second query lists the most recent activity for each user.

