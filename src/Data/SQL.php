<?php

namespace App\Data;

class SQL
{
    const string CURRENTSETTINGS = 'SELECT * FROM settings WHERE id = :id;';
    const string EDITSETTINGS = '
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
                id = :id
            ;';
    const string AVAILBLETASKS = 'SELECT * FROM tasks WHERE status IN ("Waiting", "Paused");';
    const string GETTASK = 'SELECT * FROM tasks WHERE id = :id;';
    const string CREATETASK = 'INSERT INTO tasks (
            description,
            priority,
            size,
            status,
            timezone
        ) VALUES (
            :description,
            :priority,
            :size,
            :status,
            :timezone
        );';
    const string CREATETASKBATCHPARTIAL = 'INSERT INTO tasks (
            description,
            priority,
            size,
            status,
            timezone
        ) VALUES ';

    public static function createTaskValuesRow(int $n): string {
        return "(
            :description$n,
            :priority$n,
            :size$n,
            :status$n,
            :timezone$n
        )";
    }

    const string GETTASKBATCHPARTIAL = 'SELECT * FROM tasks WHERE ';

    public static function getTaskWhereRow(int $n): string {
        return "(
            description = :description$n
            AND
            priority = :priority$n
            AND
            size = :size$n
            AND
            status = :status$n
            AND
            timezone = :timezone$n
        )";
    }
    const string EDITTASK = 'UPDATE tasks
		SET
			description = :description,
			priority = :priority,
			size = :size
		WHERE
			id = :id;';
    const string UPDATETASKSTATUS = 'UPDATE tasks SET status = :status WHERE id = :id;';
    const string CREATEPOMODOROSESSION = 'INSERT INTO pomodoro_sessions (
            started_at,
            break_duration,
            timezone
        )
        VALUES (
            :started_at,
            :break_duration,
            :timezone
        );';
    const string ENDPOMODORO = 'UPDATE pomodoro_sessions SET ended_at = :ended_at WHERE ended_at IS NULL';
    const string UPSERTACTIVETASK = 'INSERT INTO active_task (
            id,
            task_id
        )
        VALUES (
            :active_task_id,
            :task_id0
        )
        ON DUPLICATE KEY UPDATE task_id = :task_id1;';
    const string RESUMETASK = 'UPDATE pauses SET resumed_at = :resume_at, timezone = :timezone WHERE task_id = :task_id AND resumed_at IS NULL;';
    const string BEGINTASK = 'UPDATE tasks SET begun_at = :begun_at, status = "In Progress", timezone = :timezone WHERE id = :task_id;';
    const string CREATEPOMODOROTASK = 'INSERT INTO pomodoro_tasks (
		pomodoro_session_id,
		task_id,
		assigned_at,
		timezone
	) VALUES (
		:pomodoro_session_id,
		:task_id,
		:assigned_at,
		:timezone
	);';
    const string UNASSIGNTASK = 'UPDATE pomodoro_tasks SET unassigned_at = :unassigned_at WHERE pomodoro_session_id = :pomodoro_session_id AND task_id = :task_id AND unassigned_at IS NULL;';
    const string CREATEPAUSE = 'INSERT INTO pauses (task_id, paused_at, timezone) VALUES (:task_id, :paused_at, :timezone);';
    const string COMPLETETASK = 'UPDATE tasks SET status = "Completed", completed_at = :completed_at WHERE id = :id;';
    const string GETACTIVETASK = 'SELECT * FROM tasks WHERE id IN (SELECT task_id FROM active_task WHERE id = :id);';
    const string ENDSESSION = 'UPDATE pomodoro_sessions SET ended_at = :ended_at WHERE id = :id;';
    const string CREATESPLITBATCHPARTIAL = 'INSERT INTO splits (parent_id, child_id) VALUES ';
    public static function createSplitValuesRow(int $n): string {
        return "(
            :parent_id$n,
            :child_id$n
        )";
    }
    const string GETCURRENTPOMODOROSESSION = 'SELECT * FROM pomodoro_sessions WHERE ended_at IS NULL;';
}