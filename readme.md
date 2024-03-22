# Timater - A Pomodoro Timer

API first (and likely API only) design for a timer and basic task tracker for use
with the pomodoro technique. See https://todoist.com/productivity-methods/pomodoro-technique

## Setup

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