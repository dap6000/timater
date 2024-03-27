DROP DATABASE IF EXISTS timater;
CREATE DATABASE timater;
USE timater;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    email VARCHAR(64) NOT NULL,
    role SET('Standard', 'Admin'),
    api_key CHAR(64) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (api_key),
    INDEX i_users_username (username)
);

INSERT INTO users (
    username,
    email,
    role,
    api_key
)
VALUES
(
    'egirauld0',
    'egirauld0@fake.example.com',
    'Admin',
    'e06036d41fc45e4be7e06f71a9a7a038eb900c3c21869df67b1743181c4f2f05'
),
(
    'lcake3',
    'lcake3@fake.example.com',
    'Standard',
    '8c9cacb2e0222d965003db39e605cf561c8faca3ab31238bcbbf01852bc28dbb'
),
(
    'dharpo',
    'dharpo0@fake.example.com',
    'Standard',
    'b49c2637ab9cc2de060fb0d8b5201d1f2afe5844b077b0011c52b057fa197755'
);


CREATE TABLE settings (
    user_id INT UNSIGNED NOT NULL,
    # Length for a single pomodoro session in minutes.
    session_duration INT UNSIGNED NOT NULL,
    # Length for a single short break between pomodoro session in minutes.
    short_rest_duration INT UNSIGNED NOT NULL,
    # Length for longer breaks after every Nth session.
    long_rest_duration INT UNSIGNED NOT NULL,
    # Every Nth pomodoro session triggers a long rest.
    long_rest_threshold INT UNSIGNED NOT NULL,
    # Tasks that take this number of sessions or greater to complete are flagged for review.
    # The goal is to get better at breaking larger tasks into manageable chunks.
    rock_breaking_threshold INT UNSIGNED NOT NULL,
    use_task_priority BOOLEAN NOT NULL,
    use_task_size BOOLEAN NOT NULL,
    timezone VARCHAR(32) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_settings_user (user_id) REFERENCES users(id)
);

INSERT INTO settings
(
    user_id,
    session_duration,
    short_rest_duration,
    long_rest_duration,
    long_rest_threshold,
    rock_breaking_threshold,
    use_task_priority,
    use_task_size,
    timezone
)
VALUES
(1, 25, 5, 30, 4, 4, 1, 1, 'America/Chicago'),
(2, 25, 5, 30, 4, 4, 1, 1, 'America/Chicago'),
(3, 25, 5, 30, 4, 4, 1, 1, 'America/Chicago')
;

CREATE TABLE tasks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL,
    priority SET('Cold', 'Warm', 'Hot', 'Urgent') DEFAULT 'Warm',
    size SET('Short', 'Tall', 'Grande', 'Venti', 'Big Gulp') DEFAULT 'Tall',
    status SET('Waiting', 'In Progress', 'Completed', 'Split', 'Paused') DEFAULT 'Waiting',
    begun_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    timezone VARCHAR(32) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_ps_user (user_id) REFERENCES users(id),
    INDEX i_tasks_begun_at (begun_at),
    INDEX i_tasks_completed_at (completed_at)
);

CREATE TABLE pomodoro_sessions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    started_at DATETIME NOT NULL,
    ended_at DATETIME DEFAULT NULL,
    break_duration INT UNSIGNED NOT NULL,
    timezone VARCHAR(32) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_ps_user (user_id) REFERENCES users(id),
    INDEX i_ps_started_at (started_at),
    INDEX i_ps_ended_at (ended_at)
);

CREATE TABLE splits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    parent_id INT UNSIGNED NOT NULL,
    child_id INT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_splits_user (user_id) REFERENCES users(id),
    FOREIGN KEY fk_splits_parent (parent_id) REFERENCES tasks(id),
    FOREIGN KEY fk_splits_child (child_id) REFERENCES tasks(id)
);

CREATE TABLE pauses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    task_id INT UNSIGNED NOT NULL,
    paused_at DATETIME NOT NULL,
    resumed_at DATETIME DEFAULT NULL,
    timezone VARCHAR(32) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_pauses_user (user_id) REFERENCES users(id),
    FOREIGN KEY fk_pauses_parent (task_id) REFERENCES tasks(id)
);

CREATE TABLE active_task (
    user_id INT UNSIGNED NOT NULL UNIQUE PRIMARY KEY,
    task_id INT UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_active_task_user (user_id) REFERENCES users(id),
    FOREIGN KEY fk_active_task_task (task_id) REFERENCES tasks(id)
);

INSERT INTO active_task (user_id) VALUES (1), (2), (3);

CREATE TABLE pomodoro_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    pomodoro_session_id INT UNSIGNED NOT NULL,
    task_id INT UNSIGNED NOT NULL,
    assigned_at DATETIME NOT NULL,
    unassigned_at DATETIME DEFAULT NULL,
    timezone VARCHAR(32) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_pt_user (user_id) REFERENCES users(id),
    FOREIGN KEY fk_pt_ps (pomodoro_session_id) REFERENCES pomodoro_sessions(id),
    FOREIGN KEY fk_pt_task (task_id) REFERENCES tasks(id)
);

DROP USER IF EXISTS 'timater'@'%';
CREATE USER 'timater'@'%' IDENTIFIED BY 'timater1234';

GRANT SELECT ON *.* TO 'timater'@'%';

GRANT ALL ON `timater`.* TO 'timater'@'%';

FLUSH PRIVILEGES;