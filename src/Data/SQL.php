<?php

declare(strict_types=1);

namespace App\Data;

/**
 * This class houses all the SQL strings used in the app as constants.
 */
class SQL
{
    public const string ALLUSERS = 'SELECT * FROM users;';

    public const string GETUSERBYID = 'SELECT * FROM users WHERE id = :id;';

    public const string GETUSERBYKEY = '
        SELECT * FROM users WHERE api_key = :api_key;
    ';

    public const string CURRENTSETTINGS = '
        SELECT * FROM settings WHERE user_id = :user_id;
    ';
    public const string EDITSETTINGS = '
        UPDATE settings
            SET
                session_duration = :session_duration,
                short_rest_duration = :short_rest_duration,
                long_rest_duration = :long_rest_duration,
                long_rest_threshold = :long_rest_threshold,
                rock_breaking_threshold = :rock_breaking_threshold,
                use_task_priority = :use_task_priority,
                use_task_size = :use_task_size,
                timezone = :timezone
            WHERE
                user_id = :user_id
            ;';
    public const string AVAILBLETASKS = '
        SELECT *
        FROM tasks
        WHERE
            status IN ("Waiting", "Paused")
            AND
            user_id = :user_id;
    ';
    public const string GETTASK = '
        SELECT *
        FROM tasks
        WHERE
            id = :id
            AND
            user_id = :user_id;
    ';
    public const string CREATETASK = '
        INSERT INTO tasks (
            user_id,
            description,
            priority,
            size,
            timezone
        ) VALUES (
            :user_id,
            :description,
            :priority,
            :size,
            :timezone
        );
    ';
    public const string CREATETASKBATCHPARTIAL = '
        INSERT INTO tasks (
            user_id,
            description,
            priority,
            size,
            timezone
        ) VALUES
    ';

    /**
     * @param int $n
     * @return string
     */
    public static function createTaskValuesRow(int $n): string
    {
        return "(
            :user_id$n,
            :description$n,
            :priority$n,
            :size$n,
            :timezone$n
        )";
    }

    public const string GETTASKBATCHPARTIAL = 'SELECT * FROM tasks WHERE ';

    /**
     * @param int $n
     * @return string
     */
    public static function getTaskWhereRow(int $n): string
    {
        return "(
            user_id = :user_id$n
            AND
            description = :description$n
            AND
            priority = :priority$n
            AND
            size = :size$n
            AND
            timezone = :timezone$n
        )";
    }

    public const string EDITTASK = '
        UPDATE tasks
        SET
            description = :description,
            priority = :priority,
            size = :size
        WHERE
            id = :id
            AND
            user_id = :user_id;
    ';
    public const string UPDATETASKSTATUS = '
        UPDATE tasks
        SET status = :status
        WHERE
            id = :id
            AND
            user_id = :user_id;
    ';
    public const string CREATEPOMODOROSESSION = '
        INSERT INTO pomodoro_sessions (
            user_id,
            started_at,
            break_duration,
            timezone
        )
        VALUES (
            :user_id,
            :started_at,
            :break_duration,
            :timezone
        );
    ';
    public const string ENDPOMODORO = '
        UPDATE pomodoro_sessions
        SET ended_at = :ended_at
        WHERE
            ended_at IS NULL
            AND
            user_id = :user_id;
    ';
    public const string UPSERTACTIVETASK = '
        INSERT INTO active_task (
            user_id,
            task_id
        )
        VALUES (
            :user_id,
            :task_id0
        )
        ON DUPLICATE KEY UPDATE task_id = :task_id1;
    ';
    public const string RESUMETASK = '
        UPDATE pauses
        SET
            resumed_at = :resume_at,
            timezone = :timezone
        WHERE
            task_id = :task_id
            AND
            user_id = :user_id
            AND
            resumed_at IS NULL;
    ';
    public const string BEGINTASK = '
        UPDATE tasks
        SET
            begun_at = COALESCE(begun_at, :begun_at),
            status = "In Progress",
            timezone = :timezone
        WHERE
            id = :id
            AND
            user_id = :user_id;
    ';
    public const string CREATEPOMODOROTASK = '
        INSERT INTO pomodoro_tasks (
            user_id,
            pomodoro_session_id,
            task_id,
            assigned_at,
            timezone
        ) VALUES (
            :user_id,
            :pomodoro_session_id,
            :task_id,
            :assigned_at,
            :timezone
        );
    ';
    public const string UNASSIGNTASK = '
        UPDATE pomodoro_tasks
        SET unassigned_at = :unassigned_at
        WHERE
            user_id = :user_id
            AND
            pomodoro_session_id = :pomodoro_session_id
            AND
            task_id = :task_id
            AND
            unassigned_at IS NULL;
    ';
    public const string CREATEPAUSE = '
        INSERT INTO pauses (
            user_id,
            task_id,
            paused_at,
            timezone
        )
        VALUES (
            :user_id,
            :task_id,
            :paused_at,
            :timezone
        );
    ';
    public const string COMPLETETASK = '
        UPDATE tasks
        SET
            status = "Completed",
            completed_at = :completed_at
        WHERE
            id = :id
            AND
            user_id = :user_id;
    ';
    public const string GETACTIVETASK = '
        SELECT *
        FROM tasks
        WHERE
            id IN (
                SELECT task_id
                FROM active_task
                WHERE user_id = :user_id
            );
    ';
    public const string CREATESPLITBATCHPARTIAL = '
        INSERT INTO splits (
            user_id,
            parent_id,
            child_id
        )
        VALUES
    ';

    /**
     * @param int $n
     * @return string
     */
    public static function createSplitValuesRow(int $n): string
    {
        return "
        (
            :user_id$n,
            :parent_id$n,
            :child_id$n
        )";
    }

    public const string GETCURRENTPOMODOROSESSION = '
        SELECT *
        FROM pomodoro_sessions
        WHERE
            ended_at IS NULL
            AND
            user_id = :user_id;
    ';

    public const string LONGTASKREPORT = '
        SELECT
            task_id,
            sessions,
            description,
            priority,
            size,
            status,
            begun_at,
            completed_at,
            timezone
        FROM
            tasks
            JOIN (
                SELECT
                    task_id,
                    COUNT(DISTINCT pomodoro_session_id) AS sessions
                FROM
                    pomodoro_tasks
                WHERE
                    user_id = :user_id
                GROUP BY task_id
                HAVING sessions > :threshold
            ) pt ON (tasks.id = pt.task_id)
        ;
    ';

    public const string REPORTPAUSES = '
        SELECT
            tasks.id as task_id,
            tasks.description,
            tasks.priority,
            tasks.`size`,
            tasks.status,
            tasks.begun_at,
            tasks.completed_at,
            tasks.timezone,
            COUNT(p.id) AS total_pauses,
            SUM(pause_seconds) AS total_seconds,
            SUM(pause_seconds) / 60 AS total_minutes,
            SUM(pause_seconds) / 60 / 60 AS total_hours
        FROM
            tasks
            JOIN (
                SELECT
                    task_id,
                    user_id,
                    pauses.id,
                    UNIX_TIMESTAMP(resumed_at) - UNIX_TIMESTAMP(paused_at) AS pause_seconds
                FROM pauses
                WHERE user_id = :user_id
            ) p ON (tasks.id = p.task_id)
        GROUP BY tasks.id;
    ';

    public const string SPLITSREPORT = '
        SELECT
            JSON_OBJECT(
            	"id", parents.id,
            	"user_id", parents.user_id,
            	"description", parents.description,
            	"status", parents.status,
            	"priority", parents.priority,
            	"size", parents.size,
            	"begun_at", COALESCE(parents.begun_at, ""),
            	"completed_at", COALESCE(parents.completed_at, ""),
            	"timezone", parents.timezone
            ) AS old_task,
            COUNT(children.id) as num_new_tasks,
            CONCAT(
                "[",
                GROUP_CONCAT(
                    JSON_OBJECT(
                        "id", children.id,
                        "user_id", children.user_id,
                        "description", children.description,
                        "status", children.status,
                        "priority", children.priority,
                        "size", children.size,
                        "begun_at", COALESCE(children.begun_at, ""),
                        "completed_at", COALESCE(children.completed_at, ""),
                        "timezone", children.timezone
                    )
                ),
                "]"
            )  AS new_tasks
        FROM
            splits
            JOIN tasks as parents ON (
                splits.parent_id = parents.id
                AND
                splits.user_id = parents.user_id
                AND
                parents.status = "Split"
            )
            JOIN tasks as children ON (
                splits.child_id = children.id
                AND
                splits.user_id = children.user_id
            )
        WHERE
            splits.user_id = :user_id
        GROUP BY parents.id
        ;
    ';

    public const string METRICSBYSIZE = '
        SELECT
            size,
            AVG(create_to_start) AS avg_create_to_start,
            AVG(create_to_complete) AS avg_create_to_complete,
            AVG(begun_to_completed) AS avg_begun_to_completed,
            AVG(begun_to_completed) AS avg_begun_to_completed,
            AVG(session_count) AS avg_session_count,
            MAX(create_to_start) AS max_create_to_start,
            MAX(create_to_complete) AS max_create_to_complete,
            MAX(begun_to_completed) AS max_begun_to_completed,
            MAX(begun_to_completed) AS max_begun_to_completed,
            MAX(session_count) AS max_session_count,
            MIN(create_to_start) AS min_create_to_start,
            MIN(create_to_complete) AS min_create_to_complete,
            MIN(begun_to_completed) AS min_begun_to_completed,
            MIN(begun_to_completed) AS min_begun_to_completed,
            MIN(session_count) AS min_session_count
        FROM (
            SELECT
                tasks.id,
                tasks.status,
                tasks.size,
                tasks.priority,
                UNIX_TIMESTAMP(tasks.created_at) - UNIX_TIMESTAMP(begun_at) AS create_to_start,
                UNIX_TIMESTAMP(tasks.created_at) - UNIX_TIMESTAMP(completed_at) AS create_to_complete,
                UNIX_TIMESTAMP(completed_at) - UNIX_TIMESTAMP(begun_at) AS begun_to_completed,
                COUNT(DISTINCT pomodoro_tasks.pomodoro_session_id) AS session_count
            FROM
                tasks
                JOIN pomodoro_tasks ON (tasks.id = pomodoro_tasks.task_id AND tasks.user_id = pomodoro_tasks.user_id)
            WHERE
                tasks.user_id = :user_id
                AND
                tasks.begun_at BETWEEN :report_start AND :report_end
                AND
                tasks.status = "Completed"
                AND
                tasks.begun_at IS NOT NULL
                AND
                tasks.completed_at IS NOT NULL
            GROUP BY tasks.id
        ) t
        GROUP BY size;
    ';
    public const string METRICSBYPRIORITY = '
        SELECT
            priority,
            AVG(create_to_start) AS avg_create_to_start,
            AVG(create_to_complete) AS avg_create_to_complete,
            AVG(begun_to_completed) AS avg_begun_to_completed,
            AVG(begun_to_completed) AS avg_begun_to_completed,
            AVG(session_count) AS avg_session_count,
            MAX(create_to_start) AS max_create_to_start,
            MAX(create_to_complete) AS max_create_to_complete,
            MAX(begun_to_completed) AS max_begun_to_completed,
            MAX(begun_to_completed) AS max_begun_to_completed,
            MAX(session_count) AS max_session_count,
            MIN(create_to_start) AS min_create_to_start,
            MIN(create_to_complete) AS min_create_to_complete,
            MIN(begun_to_completed) AS min_begun_to_completed,
            MIN(begun_to_completed) AS min_begun_to_completed,
            MIN(session_count) AS min_session_count
        FROM (
            SELECT
                tasks.id,
                tasks.status,
                tasks.size,
                tasks.priority,
                UNIX_TIMESTAMP(tasks.created_at) - UNIX_TIMESTAMP(begun_at) AS create_to_start,
                UNIX_TIMESTAMP(tasks.created_at) - UNIX_TIMESTAMP(completed_at) AS create_to_complete,
                UNIX_TIMESTAMP(completed_at) - UNIX_TIMESTAMP(begun_at) AS begun_to_completed,
                COUNT(DISTINCT pomodoro_tasks.pomodoro_session_id) AS session_count
            FROM
                tasks
                JOIN pomodoro_tasks ON (tasks.id = pomodoro_tasks.task_id AND tasks.user_id = pomodoro_tasks.user_id)
            WHERE
                tasks.user_id = :user_id
                AND
                tasks.begun_at BETWEEN :report_start AND :report_end
                AND
                tasks.status = "Completed"
                AND
                tasks.begun_at IS NOT NULL
                AND
                tasks.completed_at IS NOT NULL
            GROUP BY tasks.id
        ) t
        GROUP BY priority;
    ';
}
