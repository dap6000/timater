DROP DATABASE IF EXISTS timater;
CREATE DATABASE timater;
USE timater;

CREATE TABLE settings (
    id INT UNIQUE PRIMARY KEY,
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
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings
(
    id,
    session_duration,
    short_rest_duration,
    long_rest_duration,
    long_rest_threshold,
    rock_breaking_threshold,
    use_task_priority,
    use_task_size,
    timezone
)
VALUES (0, 25, 5, 30, 4, 4, 1, 1, 'America/Chicago')
;

CREATE TABLE tasks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    priority SET('Cold', 'Warm', 'Hot', 'Urgent') DEFAULT 'Warm',
    size SET('Short', 'Tall', 'Grande', 'Venti', 'Big Gulp') DEFAULT 'Tall',
    status SET('Waiting', 'In Progress', 'Completed', 'Split', 'Paused') DEFAULT 'Waiting',
    begun_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    timezone VARCHAR(32) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX i_tasks_begun_at (begun_at),
    INDEX i_tasks_completed_at (completed_at)
);

CREATE TABLE pomodoro_sessions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    started_at DATETIME NOT NULL,
    ended_at DATETIME DEFAULT NULL,
    break_duration INT UNSIGNED NOT NULL,
    timezone VARCHAR(32) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX i_ps_started_at (started_at),
    INDEX i_ps_ended_at (ended_at)
);

CREATE TABLE splits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED NOT NULL,
    child_id INT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_splits_parent (parent_id) REFERENCES tasks(id),
    FOREIGN KEY fk_splits_child (child_id) REFERENCES tasks(id)
);

CREATE TABLE pauses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED NOT NULL,
    paused_at DATETIME NOT NULL,
    resumed_at DATETIME DEFAULT NULL,
    timezone VARCHAR(32) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_pauses_parent (task_id) REFERENCES tasks(id)
);

CREATE TABLE active_task (
    id INT UNIQUE PRIMARY KEY,
    task_id INT UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_active_task (task_id) REFERENCES tasks(id)
);

INSERT INTO active_task (id) VALUES (0);

CREATE TABLE pomodoro_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pomodoro_session_id INT UNSIGNED NOT NULL,
    task_id INT UNSIGNED NOT NULL,
    assigned_at DATETIME NOT NULL,
    unassigned_at DATETIME DEFAULT NULL,
    timezone VARCHAR(32) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_pt_ps (pomodoro_session_id) REFERENCES pomodoro_sessions(id),
    FOREIGN KEY fk_pt_task (task_id) REFERENCES tasks(id)
);

DROP USER IF EXISTS 'timater'@'%';
CREATE USER 'timater'@'%' IDENTIFIED BY 'timater1234';

GRANT SELECT ON *.* TO 'timater'@'%';

# TODO Can we just use GRANT ALL here?
GRANT ALL
#    CREATE ROUTINE,
#        LOCK TABLES,
#        CREATE TEMPORARY TABLES,
#        SHOW VIEW,
#        EXECUTE,
#        GRANT OPTION,
#        REFERENCES,
#        SELECT,
#        DROP,
#        TRIGGER,
#        CREATE,
#        EVENT,
#        ALTER ROUTINE,
#        INSERT,
#        UPDATE,
#        CREATE VIEW,
#        ALTER,
#        INDEX,
#        DELETE
        ON `timater`.* TO 'timater'@'%';

FLUSH PRIVILEGES;