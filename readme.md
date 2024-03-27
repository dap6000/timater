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

### Step 4 - Seed DB with Test Data (Optional)

Load http://localhost:8080/db/seed in your browser to run the seek script.
Or just experiment with the API to create and manipulate the recoreds.

## Why Slim?

I started out doing this project in Laravel. But by the time I had a working
Docker environment and a seeded database the project was approaching 800,000
lines of code. That felt like overkill. I had worked with Slim years ago and
remembered it was pretty lightweight and is well suited for building APIs.
The completed project is less than 25% source lines of code compared to
the false start in Laravel.

## Code Standards

I've set up basic configurations for PHP Code Sniffer, PHP Mess Detector,
Psalm, PHPStan. I've also enabled PSR-12 inspections in PHP Storm. I'm
using PHP 8.3. And I've included a basic .editorconfig file to describe
the code styles I'm using.

## Available Actions

### Initialize Program

This provides a single endpoint to fetch the info needed to launch the software.
It combines getting current settings, available tasks, and active tasks in a
single API call.

Endpoint: `/init`

HTTP Method: GET

Example Request Body: none

Example Response Body:

```json
{
    "settings": {
        "sessionDuration": 30,
        "shortRestDuration": 5,
        "longRestDuration": 20,
        "longRestThreshold": 4,
        "rockBreakingThreshold": 3,
        "useTaskPriority": true,
        "useTaskSize": true,
        "timezone": "America/Chicago"
    },
    "pomodoro": {
        "id": 774,
        "startedAt": "2024-03-25 21:05:52",
        "endedAt": null,
        "breakDuration": 5,
        "timezone": "America/Chicago"
    },
    "active_task": {
        "id": 179,
        "description": "Sint iste quos et repudiandae animi omnis.",
        "priority": "Hot",
        "size": "Big Gulp",
        "status": "In Progress",
        "begunAt": "2024-03-25 21:05:52",
        "completedAt": "2024-03-05 15:52:29",
        "timezone": "America/Chicago"
    },
    "available_tasks": [
        {
            "id": 123,
            "description": "Corrupti nobis quos id aut.",
            "priority": "Urgent",
            "size": "Venti",
            "status": "Paused",
            "begunAt": "2024-02-16 10:47:37",
            "completedAt": "2024-02-16 13:29:37",
            "timezone": "America/Chicago"
        },
        {
            "id": 227,
            "description": "Delectus saepe eos nostrum recusandae id.",
            "priority": "Urgent",
            "size": "Big Gulp",
            "status": "Waiting",
            "begunAt": "2024-03-21 12:39:49",
            "completedAt": null,
            "timezone": "America/Chicago"
        },
        {
            "id": 228,
            "description": "Dolor voluptate praesentium non rem illum id cupiditate.",
            "priority": "Cold",
            "size": "Grande",
            "status": "Waiting",
            "begunAt": "2024-03-22 08:50:31",
            "completedAt": null,
            "timezone": "America/Chicago"
        }
    ]
}
```

### Get Current Settings

Settings are stored in a singleton table with a hard coded 0 ID. If we wanted
to go muli-user with this system we could instead key the table off User ID,
enforcing a single row per user and facilitating easy lookups for any specific
user. I have reservations about growing this system to teams / multiple users.
The appeal of the pomodoro technique is its relative simplicity and ease. Anyone
with a timer and a notepad can do it. If this system gets more cumbersome to use
than the analog alternative then people won't use it. Even if team leaders and
management really want them to.

Endpoint: `/settings/current`

HTTP Method: GET

Example Request Body: none

Example Response Body:

```json
{
    "settings": {
        "session_duration": 25,
        "short_rest_duration": 5,
        "long_rest_duration": 30,
        "long_rest_threshold": 4,
        "rock_breaking_threshold": 4,
        "use_task_priority": true,
        "use_task_size": true,
        "timezone": "America/Chicago"
    }
}
```

### Configure Settings

Settings are user configurable.

Endpoint: `/settings/configure`

HTTP Method: PUT

Example Request Body

```json
{
    "settings": {
        "session_duration": 30,
        "short_rest_duration": 5,
        "long_rest_duration": 20,
        "long_rest_threshold": 4,
        "rock_breaking_threshold": 3,
        "use_task_priority": true,
        "use_task_size": true,
        "timezone": "America/Chicago"
    }
}
```

Example Response Body:

```json
{
    "settings": {
        "session_duration": 30,
        "short_rest_duration": 5,
        "long_rest_duration": 20,
        "long_rest_threshold": 4,
        "rock_breaking_threshold": 3,
        "use_task_priority": true,
        "use_task_size": true,
        "timezone": "America/Chicago"
    }
}
```

### Create Task

User sets description, priority, and size. Timezone is set from current
settings. Begun at, completed at, and session count stay at defaults.

Endpoint: `/tasks/add`

HTTP Method: GET

Example Request Body:

```json
{
    "task": {
        "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
        "priority": "Urgent",
        "size": "Big Gulp",
        "timezone": "America/Chicago"
    }
}
```

If no `timezone` is provided we will use the currently configured time zone.

Example Response Body:

```json
{
    "task": {
        "id": 245,
        "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
        "priority": "Urgent",
        "size": "Big Gulp",
        "status": "Waiting",
        "begun_at": null,
        "completed_at": null,
        "timezone": "America/Chicago"
    }
}
```

### Edit Task

User can update description, priority, and size.

Endpoint: `/tasks/edit/{id}`

HTTP Method: PUT

Example Request Body

```json
{
    "task": {
        "id": 230,
        "description": "New task description.",
        "priority": "Hot",
        "size": "Grande"
    }
}
```

Example Response Body:

```json
{
    "task": {
        "id": 230,
        "description": "New task description.",
        "priority": "Hot",
        "size": "Grande",
        "status": "Split",
        "begun_at": "2024-03-22 13:01:56",
        "completed_at": null,
        "timezone": "America/Chicago"
    }
}
```

If no `timezone` is provided we will use the currently configured time zone.

### Split Task

This is a process for taking a single task and breaking it into two or more
smaller, more detailed tasks. This is a special case for creating multiple
tasks with a parent task ID passed along with the batch.

- Set parent `status` to "Split"
- Create new tasks for each child record
- Create new splits records with parent and child IDs

Endpoint: `/tasks/split/{id}`

HTTP Method: POST

Example Request Body

```json
{
    "children": [
        {
            "description": "New Child Task 1",
            "priority": "Hot",
            "size": "Grande",
            "timezone": "America/Chicago"
        },
        {
            "description": "New Child Task 2",
            "priority": "Warm",
            "size": "Tall",
            "timezone": "America/Chicago"
        },
        {
            "description": "New Child Task 3",
            "priority": "Cold",
            "size": "Short",
            "timezone": "America/Chicago"
        }
    ]
}
```

Example Response Body:

```json
{
    "parent": {
        "id": 231,
        "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
        "priority": "Urgent",
        "size": "Big Gulp",
        "status": "Split",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
    },
    "children": [
        {
            "id": 247,
            "description": "New Child Task 1",
            "priority": "Hot",
            "size": "Grande",
            "status": "Waiting",
            "begunAt": null,
            "completedAt": null,
            "timezone": "America/Chicago"
        },
        {
            "id": 248,
            "description": "New Child Task 2",
            "priority": "Warm",
            "size": "Tall",
            "status": "Waiting",
            "begunAt": null,
            "completedAt": null,
            "timezone": "America/Chicago"
        },
        {
            "id": 249,
            "description": "New Child Task 3",
            "priority": "Cold",
            "size": "Short",
            "status": "Waiting",
            "begunAt": null,
            "completedAt": null,
            "timezone": "America/Chicago"
        }
    ]
}
```

### List Available Tasks

This endpoint is used to populate the task list.

Endpoint: `/tasks/available`

HTTP Method: GET

Example Request Body none

Example Response Body:

```json
{
    "tasks": [
        {
            "id": 227,
            "description": "Delectus saepe eos nostrum recusandae id.",
            "priority": "Urgent",
            "size": "Big Gulp",
            "status": "Waiting",
            "begun_at": "2024-03-21 12:39:49",
            "completed_at": null,
            "timezone": "America/Chicago"
        },
        {
            "id": 228,
            "description": "Dolor voluptate praesentium non rem illum id cupiditate.",
            "priority": "Cold",
            "size": "Grande",
            "status": "Waiting",
            "begun_at": "2024-03-22 08:50:31",
            "completed_at": null,
            "timezone": "America/Chicago"
        },
        {
            "id": 229,
            "description": "…"
        }
    ]
}
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
- Update `active_task`
- Create `pomodoro_tasks` record

Endpoint: `/task/assign/230`

HTTP Method: POST

Example Request Body

```json
{
    "id": 123,
    "time": "2024-03-25 16:05:52",
    "timezone": "America/Chicago"
}
```

Example Response Body:

```json
{
    "task": {
        "id": 123,
        "description": "Corrupti nobis quos id aut.",
        "priority": "Urgent",
        "size": "Venti",
        "status": "In Progress",
        "begunAt": "2024-02-16 10:47:37",
        "completedAt": "2024-02-16 13:29:37",
        "timezone": "America/Chicago"
    }
}
```

### Pause Task

Ideally we don't switch tasks when doing the pomodoro technique. But life
is rarely ideal. If we don't allow for this sort of feature, sub-optimal
though it may be, users will resort to paper and different timer. Maybe
it becomes clear the current task is blocked. Or too big and should be split.
Or something urgent comes up and needs to take priority without waiting
for the next pomodoro session. In any case, we need to account for this.

Pausing a task involves:

- Set `status` to 'Paused'
- Set pomodoro_tasks.unassigned_at in associated record
- Created record in `pauses` table with `task_id`, `paused_at`, `timezone`

Endpoint: `/tasks/pause/123`

HTTP Method: POST

Example Request Body

```json
{
    "id": 123,
    "time": "2024-03-25 16:05:52",
    "timezone": "America/Chicago"
}
```

Example Response Body:

```json
{
    "task": {
        "id": 123,
        "description": "Corrupti nobis quos id aut.",
        "priority": "Urgent",
        "size": "Venti",
        "status": "Paused",
        "begunAt": "2024-02-16 10:47:37",
        "completedAt": "2024-02-16 13:29:37",
        "timezone": "America/Chicago"
    }
}
```

### Complete Task

The active task should have some sort of UI such as a button to mark it as
completed. The back end changes involve:

- Set `status` to 'Completed'
- Set `completed_at` to now

Endpoint: `/tasks/complete/{id}`

HTTP Method: POST

Example Request Body

```json
{
    "id": 124,
    "time": "2024-03-25 16:05:52",
    "timezone": "America/Chicago"
}
```

Example Response Body:

```json
{
    "message": "Slim Application Error",
    "exception": [
        {
            "type": "Exception",
            "code": 0,
            "message": "ID mismatch!",
            "file": "/var/www/slim_app/src/Handlers/CompleteTask.php",
            "line": 20
        }
    ]
}
```

### Start Pomodoro Session

This may begin with a roll over task, a task new to this session that was
assigned before starting the session, or no task. We always create a new
`pomodoro_sessions` row. Then:

- If rollover task: Unpause task.
- If new task: process as new assignment.
- If no task: take no action, wait for assignment event / API call.

Endpoint: `/pomodoro/start`

HTTP Method: POST

Example Request Body

```json
{
    "pomodoro": {
        "break_duration": 5,
        "started_at": "2024-03-25 16:05:52",
        "timezone": "America/Chicago"
    },
    "task": {
        "id": 125
    }
}
```

If no `break_duration` is provided, the API will default to the currently configured
short duration. If you want to take a long break, you have to specify it in the request.
If no `timezone` is provided, we will also use the currently configured time zone.
If is possible to begin a session without a task. But the value passed should reflect
the state of the UI.

Example Response Body:

```json
{
    "pomodoro": {
        "id": 772,
        "startedAt": "2024-03-25 21:05:52",
        "endedAt": null,
        "breakDuration": 5,
        "timezone": "America/Chicago"
    }
}
```

### End Pomodoro Session

The UI can immediately start the break timer from the from `break_duration` value.
Backend processing involves setting an `ended_at` value on the record.

Endpoint: `/pomodoro/end`

HTTP Method: POST

Example Request Body

```json
{
    "ended_at": "2024-03-25 16:05:52",
    "timezone": "America/Chicago"
}
```

Example Response Body:

```json
{
    "break": {
        "duration": 5
    }
}
```

### Quit

This ends the pomodoro session without starting a break timer. Use case is meetings,
lunch breaks, end of day, or anything that stops work but isn't beholden to pomodoro
break duration rules. The backend processes follow the same logic for ending the
pomodoro session. Any currently active tasks get treated as a pause:

- Created record in `pauses` table with `task_id`, `paused_at`, `timezone`

Endpoint: `http://localhost:8080/pomodoro/quit`

HTTP Method: POST

Example Request Body

```json
{
    "ended_at": "2024-03-25 16:05:52",
    "timezone": "America/Chicago"
}
```

Example Response Body:

```json
{
    "message": "Goodbye!"
}
```

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

# TODO

- Update example JSON request and response blocks
- Implement csrf
- Build reports
- Dependency injection?
- Tests?
