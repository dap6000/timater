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

### Step 4 - Populate the Database

You can either make use of the API itself or, to save time, there's also
a `timater.sql` file in the project root with some randomly generated
data to play around with. It's not terribly realistic. But it shows how
things behave with a few hundred records. All those records are tied to
user_id 3, by the way.

To facilitate interacting with the API I'm including a `Bruno_Timater.json`
file in the project root. This can be imported into the Bruno API client.
See https://www.usebruno.com/.

## Why Slim?

I started out doing this project in Laravel. But by the time I had a working
Docker environment and a seeded database the project was approaching 800,000
lines of code. That felt like overkill. I had worked with Slim years ago and
remembered it was pretty lightweight and is well suited for building APIs.
The completed project is less than 25% source lines of code compared to
the false start in Laravel.

### Project Structure

#### `config`

Most everything in the `config` directory is adapted from Daniel Opitz's Slim
4 Skeleton project. See https://odan.github.io/slim4-skeleton/. There may be
some leftover oddities in those files because Daniel offers a lot of
scaffolding I'm not using. Although it is mostly done in a way that doesn't
impose code bloat.

#### `data`

MySQL data directory for use by Docker containers.

### `docker`

Contains build context, configuration files, and a `Dockerfile` for each of our
three containers.

### `logs`

Directory for logging to write to.

### `public`

Maps to the web root and contains our "front controller". It's been
deconstructed from the default Slim 4 approach. See `config/bootstrap.php`,
`config/container.php`, `config/middleware.php`, and `config/routes.php`.

### `src`

This contains the bulk of our application logic and maps to the `App` namespace
for PSR-4 auto-loading.

#### `src/Actions`

Actions are invokable and are used as callables in the route definitions. Each
defined route has a companion Action class that does the bulk of the work. This
work involves parsing inputs out of the request body, headers, etc; starting a
database transaction; using Models, Services, and Structs to process the inputs;
then either rolling back the transaction if we hit an error or reporting the
results of our work in JSON format.

#### `src/Data`

This directory contains our database connection builders and our SQL queries
stored as constants in the SQL.php file.

#### `src/Middleware`

This houses any custom middleware we need on top of what is provided by Slim or
any other dependencies we care to bring in.

#### `src/Models`

Our models work with primitive values and Struct classes to move data to and
from the database.

#### `src/Servics`

Services hold business logic that doesn't neatly fit into an Action,
Middleware, Model, or Struct.

#### `src/Structs`

PHP offers more type safety out of the box than JSON or database results, which
mostly work off of strings in arrays. The Struct classes offer ways to pass the
data moving either direction into a representation with tighter type
definitions. They give us some data validation, error checking, and security
improvements.

### `vendor`

Where our Composer managed dependencies are built and auto-loaded from.


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
    "data": {
        "settings": {
            "userId": 3,
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
            "id": 782,
            "userId": 3,
            "startedAt": "2024-03-25 21:05:52",
            "endedAt": null,
            "breakDuration": 5,
            "timezone": "America/Chicago"
        },
        "active_task": null,
        "available_tasks": [
            {
                "id": 123,
                "userId": 3,
                "description": "Ad corporis iste incidunt officia qui error.",
                "priority": "Warm",
                "size": "Big Gulp",
                "status": "Paused",
                "begunAt": "2024-02-14 14:56:15",
                "completedAt": "2024-02-15 11:06:15",
                "timezone": "America/Chicago"
            },
            {
                "id": 256,
                "userId": 3,
                "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
                "priority": "Urgent",
                "size": "Big Gulp",
                "status": "Waiting",
                "begunAt": null,
                "completedAt": null,
                "timezone": "America/Chicago"
            },
            {
                "id": 257,
                "userId": 3,
                "description": "New Child Task 1",
                "priority": "Hot",
                "size": "Grande",
                "status": "Waiting",
                "begunAt": null,
                "completedAt": null,
                "timezone": "America/Chicago"
            },
            {
                "id": 258,
                "userId": 3,
                "description": "New Child Task 2",
                "priority": "Warm",
                "size": "Tall",
                "status": "Waiting",
                "begunAt": null,
                "completedAt": null,
                "timezone": "America/Chicago"
            },
            {
                "id": 259,
                "userId": 3,
                "description": "New Child Task 3",
                "priority": "Cold",
                "size": "Short",
                "status": "Waiting",
                "begunAt": null,
                "completedAt": null,
                "timezone": "America/Chicago"
            },
            {
                "id": 260,
                "userId": 3,
                "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
                "priority": "Urgent",
                "size": "Big Gulp",
                "status": "Waiting",
                "begunAt": null,
                "completedAt": null,
                "timezone": "America/Chicago"
            }
        ]
    }
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
  "data": {
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
  "data": {
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
  "data": {
    "task": {
      "id": 284,
      "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
      "priority": "Urgent",
      "size": "Big Gulp",
      "status": "Waiting",
      "begun_at": null,
      "completed_at": null,
      "timezone": "America/Chicago"
    }
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
  "data": {
    "task": {
      "id": 230,
      "description": "New task description.",
      "priority": "Hot",
      "size": "Grande",
      "status": "Completed",
      "begun_at": "2024-03-15 09:05:52",
      "completed_at": "2024-03-15 10:03:52",
      "timezone": "America/Chicago"
    }
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
  "data": {
    "parent": {
      "id": 239,
      "userId": 3,
      "description": "Dicta iure hic facere.",
      "priority": "Warm",
      "size": "Grande",
      "status": "Split",
      "begunAt": "2024-03-19 10:50:43",
      "completedAt": "2024-03-19 12:56:43",
      "timezone": "America/Chicago"
    },
    "children": [
      {
        "id": 257,
        "userId": 3,
        "description": "New Child Task 1",
        "priority": "Hot",
        "size": "Grande",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 258,
        "userId": 3,
        "description": "New Child Task 2",
        "priority": "Warm",
        "size": "Tall",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 259,
        "userId": 3,
        "description": "New Child Task 3",
        "priority": "Cold",
        "size": "Short",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 261,
        "userId": 3,
        "description": "New Child Task 1",
        "priority": "Hot",
        "size": "Grande",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 262,
        "userId": 3,
        "description": "New Child Task 2",
        "priority": "Warm",
        "size": "Tall",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 263,
        "userId": 3,
        "description": "New Child Task 3",
        "priority": "Cold",
        "size": "Short",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 265,
        "userId": 3,
        "description": "New Child Task 1",
        "priority": "Hot",
        "size": "Grande",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 266,
        "userId": 3,
        "description": "New Child Task 2",
        "priority": "Warm",
        "size": "Tall",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 267,
        "userId": 3,
        "description": "New Child Task 3",
        "priority": "Cold",
        "size": "Short",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 277,
        "userId": 3,
        "description": "New Child Task 1",
        "priority": "Hot",
        "size": "Grande",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 278,
        "userId": 3,
        "description": "New Child Task 2",
        "priority": "Warm",
        "size": "Tall",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 279,
        "userId": 3,
        "description": "New Child Task 3",
        "priority": "Cold",
        "size": "Short",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 280,
        "userId": 3,
        "description": "New Child Task 1",
        "priority": "Hot",
        "size": "Grande",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 281,
        "userId": 3,
        "description": "New Child Task 2",
        "priority": "Warm",
        "size": "Tall",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 282,
        "userId": 3,
        "description": "New Child Task 3",
        "priority": "Cold",
        "size": "Short",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 285,
        "userId": 3,
        "description": "New Child Task 1",
        "priority": "Hot",
        "size": "Grande",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 286,
        "userId": 3,
        "description": "New Child Task 2",
        "priority": "Warm",
        "size": "Tall",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 287,
        "userId": 3,
        "description": "New Child Task 3",
        "priority": "Cold",
        "size": "Short",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 288,
        "userId": 3,
        "description": "New Child Task 1",
        "priority": "Hot",
        "size": "Grande",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 289,
        "userId": 3,
        "description": "New Child Task 2",
        "priority": "Warm",
        "size": "Tall",
        "status": "Waiting",
        "begunAt": null,
        "completedAt": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 290,
        "userId": 3,
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
  "data": {
    "tasks": [
      {
        "id": 123,
        "description": "Ad corporis iste incidunt officia qui error.",
        "priority": "Warm",
        "size": "Big Gulp",
        "status": "Paused",
        "begun_at": "2024-02-14 14:56:15",
        "completed_at": "2024-02-15 11:06:15",
        "timezone": "America/Chicago"
      },
      {
        "id": 256,
        "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
        "priority": "Urgent",
        "size": "Big Gulp",
        "status": "Waiting",
        "begun_at": null,
        "completed_at": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 257,
        "description": "New Child Task 1",
        "priority": "Hot",
        "size": "Grande",
        "status": "Waiting",
        "begun_at": null,
        "completed_at": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 258,
        "description": "New Child Task 2",
        "priority": "Warm",
        "size": "Tall",
        "status": "Waiting",
        "begun_at": null,
        "completed_at": null,
        "timezone": "America/Chicago"
      },
      {
        "id": 259,
        "description": "New Child Task 3",
        "priority": "Cold",
        "size": "Short",
        "status": "Waiting",
        "begun_at": null,
        "completed_at": null,
        "timezone": "America/Chicago"
      }
    ]
  }
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

Endpoint: `/task/assign/{id}`

HTTP Method: POST

Example Request Body

```json
{
    "id": 284,
    "time": "2024-03-25 16:05:52",
    "timezone": "America/Chicago"
}
```

Example Response Body:

```json
{
    "data": {
        "task": {
            "id": 284,
            "userId": 3,
            "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
            "priority": "Urgent",
            "size": "Big Gulp",
            "status": "In Progress",
            "begunAt": "2024-03-25 21:05:52",
            "completedAt": null,
            "timezone": "America\/Chicago"
        }
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

Endpoint: `/tasks/pause/{id}`

HTTP Method: POST

Example Request Body

```json
{
    "id": 284,
    "time": "2024-03-25 16:05:52",
    "timezone": "America/Chicago"
}
```

Example Response Body:

```json
{
    "data": {
        "task": {
            "id": 284,
            "userId": 3,
            "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
            "priority": "Urgent",
            "size": "Big Gulp",
            "status": "Paused",
            "begunAt": "2024-03-25 21:05:52",
            "completedAt": null,
            "timezone": "America/Chicago"
        }
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
    "id": 284,
    "time": "2024-03-25 16:05:52",
    "timezone": "America/Chicago"
}
```

Example Response Body:

```json
{
  "data": {
    "task": {
      "id": 284,
      "userId": 3,
      "description": "Detention block A A-twenty-three. I'm afraid she's scheduled to be terminated. Oh, no!",
      "priority": "Urgent",
      "size": "Big Gulp",
      "status": "Completed",
      "begunAt": "2024-03-25 21:05:52",
      "completedAt": "2024-03-25 21:05:52",
      "timezone": "America/Chicago"
    }
  }
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
    "data": {
        "pomodoro": {
            "id": 782,
            "userId": 3,
            "startedAt": "2024-03-25 21:05:52",
            "endedAt": null,
            "breakDuration": 5,
            "timezone": "America/Chicago"
        }
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
    "data": {
        "break": {
            "duration": 5
        }
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
    "data": {
        "message": "Goodbye!"
    }
}
```

## Reporting

Reports take a start date and an end date then gather their info within the
specified range. Users with the admin role (identified by API key) can specify
a user ID in the URL and generate reports for other users. Standard users can
only run reports on their own data.

### View Tasks Over Threshold

See tasks that took longer than the configured number of pomodoro sessions to complete.
This report can be used to better learn to spot tasks that need to be split.

Endpoint: `/reports/tasks/long` or `/reports/tasks/long/{id}}`

Method: GET

Sample request body: none

Sample response body:
```json
{
  "data": {
    "report": [
      {
        "taskId": 179,
        "sessionCount": 6,
        "description": "Minus voluptatem perspiciatis laborum quaerat.",
        "priority": "Urgent",
        "size": "Big Gulp",
        "status": "In Progress",
        "begunAt": "2024-02-28 15:31:14",
        "completedAt": "2024-02-29 10:11:14",
        "timezone": "America/Chicago"
      }
    ]
  }
}
```

### View Splits

Review tasks that have been split along with their resulting child tasks. May be useful
for seeing improvement over time with "rightsizing" tasks.

Endpoint: `/reports/tasks/splits` or `/reports/tasks/splits/{id}}`

Method: GET

Sample request body: none

Sample response body:
```json
{
  "data": {
    "report": [
      {
        "split": {
          "num_new_tasks": 3,
          "parent": {
            "id": 231,
            "userId": 3,
            "description": "Quidem enim qui voluptas et doloribus.",
            "priority": "Warm",
            "size": "Short",
            "status": "Split",
            "begunAt": "2024-03-15 10:03:58",
            "completedAt": "2024-03-15 10:13:58",
            "timezone": "America/Chicago"
          },
          "children": [
            {
              "id": 257,
              "userId": 3,
              "description": "New Child Task 1",
              "priority": "Hot",
              "size": "Grande",
              "status": "Waiting",
              "begunAt": null,
              "completedAt": null,
              "timezone": "America/Chicago"
            },
            {
              "id": 258,
              "userId": 3,
              "description": "New Child Task 2",
              "priority": "Warm",
              "size": "Tall",
              "status": "Waiting",
              "begunAt": null,
              "completedAt": null,
              "timezone": "America/Chicago"
            },
            {
              "id": 259,
              "userId": 3,
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
      }
    ]
  }
}
```

### View Paused Tasks

Tasks paused for an excessive amount of time

Endpoint: `/reports/tasks/pauses` or `/reports/tasks/pauses/{id}}`

Method: GET

Sample request body: none

Sample response body:
```json
{
  "data": {
    "report": [
      {
        "taskId": 123,
        "description": "Ad corporis iste incidunt officia qui error.",
        "priority": "Warm",
        "size": "Big Gulp",
        "status": "Paused",
        "begunAt": "2024-02-14 14:56:15",
        "completedAt": "2024-02-15 11:06:15",
        "timezone": "America/Chicago",
        "totalPauses": 5,
        "totalSeconds": 600,
        "totalMinutes": 10,
        "totalHours": 0.16666667
      },
      {
        "taskId": 179,
        "description": "Minus voluptatem perspiciatis laborum quaerat.",
        "priority": "Urgent",
        "size": "Big Gulp",
        "status": "In Progress",
        "begunAt": "2024-02-28 15:31:14",
        "completedAt": "2024-02-29 10:11:14",
        "timezone": "America/Chicago",
        "totalPauses": 5,
        "totalSeconds": 2580,
        "totalMinutes": 43,
        "totalHours": 0.71666667
      }
    ]
  }
}
```

### View Metrics by Priority

See times from created/modified to beginning work, completed work, and time on task (in
pomodoro sessions and minutes) by priority level. Ideally higher priority items should
have lower average time to beginning work. All times reported in seconds.

Endpoint: `/reports/metrics/priority` or `/reports/metrics/priority/{id}}`

Method: GET

Sample request body:
```json
{
    "start": "2024-01-25",
    "end": "2024-03-25",
    "timezone": "America/Chicago"
}
```

Sample response body:
```json
{
  "data": [
    {
      "priority": "Urgent",
      "avg_create_to_start": "2703537.1915",
      "avg_create_to_complete": "2685307.4043",
      "avg_begun_to_completed": "18229.7872",
      "avg_session_count": "1.0000",
      "max_create_to_start": 5325675,
      "max_create_to_complete": 5314035,
      "max_begun_to_completed": 240480,
      "max_session_count": 1,
      "min_create_to_start": 468138,
      "min_create_to_complete": 467778,
      "min_begun_to_completed": 300,
      "min_session_count": 1
    },
    {
      "priority": "Hot",
      "avg_create_to_start": "2714543.1887",
      "avg_create_to_complete": "2630156.6792",
      "avg_begun_to_completed": "84386.5094",
      "avg_session_count": "1.0000",
      "max_create_to_start": 5313993,
      "max_create_to_complete": 5310753,
      "max_begun_to_completed": 3405565,
      "max_session_count": 1,
      "min_create_to_start": 399519,
      "min_create_to_complete": 96431,
      "min_begun_to_completed": 300,
      "min_session_count": 1
    },
    {
      "priority": "Cold",
      "avg_create_to_start": "2996104.3111",
      "avg_create_to_complete": "2976464.3111",
      "avg_begun_to_completed": "19640.0000",
      "avg_session_count": "1.0000",
      "max_create_to_start": 5310697,
      "max_create_to_complete": 5302597,
      "max_begun_to_completed": 245400,
      "max_session_count": 1,
      "min_create_to_start": 402530,
      "min_create_to_complete": 401990,
      "min_begun_to_completed": 300,
      "min_session_count": 1
    },
    {
      "priority": "Warm",
      "avg_create_to_start": "2808229.5556",
      "avg_create_to_complete": "2770244.2222",
      "avg_begun_to_completed": "37985.3333",
      "avg_session_count": "1.0000",
      "max_create_to_start": 5302588,
      "max_create_to_complete": 5228188,
      "max_begun_to_completed": 246900,
      "max_session_count": 1,
      "min_create_to_start": 380282,
      "min_create_to_complete": 373562,
      "min_begun_to_completed": 300,
      "min_session_count": 1
    }
  ]
}
```

### View Metrics by Size

See times from created/modified to beginning work, completed work, and time on task (in
pomodoro sessions and minutes) by task size. Ideally the relationship between task size
and time on task should be clear. All times reported in seconds.

Endpoint: `/reports/metrics/size` or `/reports/metrics/size/{id}}`

Method: GET

Sample request body:
```json
{
    "start": "2024-01-25",
    "end": "2024-03-25",
    "timezone": "America/Chicago"
}
```

Sample response body:
```json
{
  "data": [
    {
      "size": "Venti",
      "avg_create_to_start": "2890092.9643",
      "avg_create_to_complete": "2839570.8214",
      "avg_begun_to_completed": "50522.1429",
      "avg_session_count": "1.0000",
      "max_create_to_start": 5325675,
      "max_create_to_complete": 5314035,
      "max_begun_to_completed": 242400,
      "max_session_count": 1,
      "min_create_to_start": 399519,
      "min_create_to_complete": 386079,
      "min_begun_to_completed": 5700,
      "min_session_count": 1
    },
    {
      "size": "Tall",
      "avg_create_to_start": "2889406.6458",
      "avg_create_to_complete": "2882076.6458",
      "avg_begun_to_completed": "7330.0000",
      "avg_session_count": "1.0000",
      "max_create_to_start": 5313993,
      "max_create_to_complete": 5310753,
      "max_begun_to_completed": 59400,
      "max_session_count": 1,
      "min_create_to_start": 401982,
      "min_create_to_complete": 399522,
      "min_begun_to_completed": 1200,
      "min_session_count": 1
    },
    {
      "size": "Grande",
      "avg_create_to_start": "2609698.5714",
      "avg_create_to_complete": "2600813.4286",
      "avg_begun_to_completed": "8885.1429",
      "avg_session_count": "1.0000",
      "max_create_to_start": 5310697,
      "max_create_to_complete": 5302597,
      "max_begun_to_completed": 63000,
      "max_session_count": 1,
      "min_create_to_start": 380282,
      "min_create_to_complete": 373562,
      "min_begun_to_completed": 3000,
      "min_session_count": 1
    },
    {
      "size": "Big Gulp",
      "avg_create_to_start": "2903499.9706",
      "avg_create_to_complete": "2835902.9118",
      "avg_begun_to_completed": "67597.0588",
      "avg_session_count": "1.0000",
      "max_create_to_start": 5302588,
      "max_create_to_complete": 5228188,
      "max_begun_to_completed": 250500,
      "max_session_count": 1,
      "min_create_to_start": 550206,
      "min_create_to_complete": 481206,
      "min_begun_to_completed": 10500,
      "min_session_count": 1
    },
    {
      "size": "Short",
      "avg_create_to_start": "2721322.0000",
      "avg_create_to_complete": "2642506.7778",
      "avg_begun_to_completed": "78815.2222",
      "avg_session_count": "1.0000",
      "max_create_to_start": 4970395,
      "max_create_to_complete": 4969495,
      "max_begun_to_completed": 3405565,
      "max_session_count": 1,
      "min_create_to_start": 402530,
      "min_create_to_complete": 96431,
      "min_begun_to_completed": 300,
      "min_session_count": 1
    }
  ]
}
```

# TODO

- OAuth2?
- csrf?
- JWT?
- Tests?
- Logging?
- Rate limits?
- JSON Schema?
