# Timater - A Pomodoro Timer

API first (and likely API only) design for a timer and basic task tracker for use
with the pomodoro technique. See https://todoist.com/productivity-methods/pomodoro-technique

## Setup

## Step 0 - Build Dependencies with Composer

This project ships with a `composer.phar` file in the project root in case
your host machine doesn't have Composer installed globally. To use the phar
file run:

`$ php composer.phar install`

To use your local Composer (if available) run:

`$ composer install`

### Step 1 - .env File

Take a look at `example.env` in the project root. It's set up with the details
needed to get up and running with the Docker Compose based local development
environment this project ships with. You don't have to use Docker. If you
run a local web server (or the one built into PHP) and a local instance of
MySQL you can set the `MYSQL_HOST` environment variable to point to wherever
you need.

### Step 2 - Docker

This project ships with a basic containerized local environment with Docker and
Docker Compose. If your host machine has those dependencies set up you should
be able to get up and running with:

```
$ docker compose build
…
$ docker compose up
…
```

Check the output of both commands for any errors. If you don't see any errors,
at this point you should be able to get a response from http://0.0.0.0:8080/
Or replace 8080 with whatever your value is for `APP_EXPOSED_PORT`.

### Step 3 - Build Schema

This project ships with an instance of PHP MyAdmin for convenience. You are of
course welcome to use whatever MySQL client you prefer. You can connect to
the containerized PHP MyAdmin instance at http://0.0.0.0:8081. Replace 8081
with the value you used for `PHPMYADMIN_EXPOSED_PORT` if you changed it. The
`MYSQL_ROOT_PASSWORD` is also set in the .env file. Connect as root to the
MySQL client of your choice. Then execute the `schema.sql` file found in the
root of this project.

### Step 4 - Seed DB with Test Data


## Available Actions

### Get Current Settings

Settings are stored in a singleton table with a hard coded 0 ID. If we wanted
to go muli-user with this system we could instead key the table off User ID,
enforcing a single row per user and facilitating easy lookups for any specific
user. I have reservations about growing this system to teams / multiple users.
The appeal of the pomodoro technique is its relative simplicity and ease. Anyone
with a timer and a notepad can do it. If this system gets more cumbersome to use
than the analog alternative then people won't use it. Even if team leaders and
management really want them to.

### Configure Settings

```
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
;
```

### Create Task

User sets description, priority, and size. Timezone is set from current
settings. Begun at, completed at, and session count stay at defaults.

### Edit Task

User can update description, priority, and size.

### Split Task

This is a process for taking a single task and breaking it into two or more
smaller, more detailed tasks. This is a special case for creating multiple
tasks with a parent task ID passed along with the batch.

 - Set parent `status` to "Split"
 - Create new tasks for each child record
 - Create new splits records with parent and child IDs

### Start Pomodoro Session

This may begin with a roll over task, a task new to this session that was
assigned before starting the session, or no session. We always create a new
`pomodoro_sessions` row. Then:

 - If rollover task: task->session_count++ and save.
 - If new task: process as new assignment as below.
 - If no task: take no action, wait for assignment event / API call.

### List Available Tasks

```
SELECT * FROM tasks WHERE status IN ("Waiting", "Paused");
```

### Assign Task to Pomodoro Session

I imagine a drag and drop UI with a single task slot beneath the timer UI.
Dragging a task into the slot assigns it to the current pomodoro session
(if any). Or if the assignment happens during a break period, the task
is queued to be assigned to the next newly created session.

Assignment involves:
 - Set `status` to 'In Progress'
   - If `status` was "Paused" set `resumed_at` in associated `pauses` record
 - Set `begun_at` to now if currently NULL, leave any existing non-null values
 - Increment `session_count`

There is no actual FK relationship between tasks and sessions. If we need
to sort out such a relationship, we can ues the `begun_at`, `completed_at`,
`started_at`, and `ended_at` columns.

### Pause Task

Ideally we don't switch tasks when doing the pomodoro technique. But life
is rarely ideal. If we don't allow for this sort of feature, sub-optimal
though it may be, users will resort to paper and different timer. Maybe
it becomes clear the current task is blocked. Or too big and should be split.
Or something urgent comes up and needs to take priority without waiting
for the next pomodoro session. In any case, we need to account for this.

Pausing a task involves:
 - Set `status` to 'Paused'
 - Decrement `session_count`
 - If ID matches any current `pauses` records, set its `resumed_at` to now (handles Paused Active case)
 - Created record in `pauses` table with `task_id`, `paused_at`, `timezone`

### Complete Task

The active task should have some sort of UI such as a button to mark it as
completed. The back end changes involve:

 - Set `status` to 'Completed'
 - Set `completed_at` to now

### End Pomodoro Session

The UI can immediately start the break timer from the from `break_duration` value.
Backend processing involves setting an `ended_at` value on the record.

### Quit

This ends the pomodoro session without starting a break timer. Use case is meetings,
lunch breaks, end of day, or anything that stops work but isn't beholden to pomodoro
break duration rules. The backend processes follow the same logic for ending the
pomodoro session. Any currently active tasks get treated as a special case of pause:

 - Set `status` to 'Paused Active'
 - Created record in `pauses` table with `task_id`, `paused_at`, `timezone`

## Reporting

Reports take a start date and an end date then gather their info within the
specified range.

### View Tasks Over Threshold

See tasks that took longer than the configured number of pomodoro sessions to complete.
This report can be used to better learn to spot tasks that need to be split.

### View Metrics by Priority

See times from created/modified to beginning work, completed work, and time on task (in
pomodoro sessions and minutes) by priority level. Ideally higher priority items should
have lower average time to beginning work.

### View Metrics by Size

See times from created/modified to beginning work, completed work, and time on task (in
pomodoro sessions and minutes) by task size. Ideally the relationship between task size
and time on task should be clear.

### View Splits

Review tasks that have been split along with their resulting child tasks. May be useful
for seeing improvement over time with "rightsizing" tasks.

### View Paused Tasks

Tasks paused for an excessive amount of time