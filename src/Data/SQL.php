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
}
