CREATE TABLE settings (
    id INT UNIQUE,
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
    use_task_size BOOLEAN NOT NULL
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
    use_task_size
)
VALUES (1, 25, 5, 30, 4, 4, 1, 1)
;

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    priority SET('Cold', 'Warm', 'Hot', 'Urgent') DEFAULT 'Warm',
    size SET('Short', 'Tall', 'Grande', 'Venti', 'Big Gulp') DEFAULT 'Tall',
    status SET('Waiting', 'In Progress', 'Completed', 'Split') DEFAULT 'Waiting',
    begun_at DATETIME NOT NULL,
    completed_at DATETIME NOT NULL,
    timezone VARCHAR(32) NOT NULL,
    session_count INT UNSIGNED NOT NULL,
    INDEX i_tasks_begun_at (begun_at),
    INDEX i_tasks_completed_at (completed_at)
);

CREATE TABLE pomodoro_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    started_at DATETIME NOT NULL,
    ended_at DATETIME NOT NULL,
    timezone VARCHAR(32) NOT NULL,
    INDEX i_ps_started_at (started_at),
    INDEX i_ps_ended_at (ended_at)
);

CREATE TABLE splits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED NOT NULL,
    child_id INT UNSIGNED NOT NULL,
    CONSTRAINT FOREIGN KEY fk_splits_parent (parent_id) REFERENCES tasks.id,
    CONSTRAINT FOREIGN KEY fk_splits_child (child_id) REFERENCES tasks.id
);
