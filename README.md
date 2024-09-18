# Mindfulness

This repository is holds the Mindfulness webapp, built using Laravel.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

## Prerequisites

The following need to be installed before starting running the application:
- PHP >= 8.3
- Composer

Check Laravel's documentation on [project set-up](https://laravel.com/docs/11.x/installation#creating-a-laravel-project) for more information. Both of these can be installed via [Larvel Herd](https://herd.laravel.com/windows).

## Installation and Start-Up

1. Clone the repository:

    ```bash
    git clone https://github.com/uconndxlab/mindfulness.git
    ```

2. Navigate to the project directory:

    ```bash
    cd mindfulness
    ```

3. Install Composer dependencies:

    ```bash
    composer install
    ```

4. Install Node dependencies:
    ```bash
    npm install
    ```

5. Create a copy of the `.env.example` file and rename it to `.env`:

    ```bash
    cp .env.example .env
    ```

6. Generate the application key:

    ```bash
    php artisan key:generate
    ```

7. Run database migrations and seeding:
    ```bash
    php artisan migrate
    php artisan db:seed --class=DatabaseSeeder
    ```
    - If prompted to make a new sqlite database, select yes.

### Running the app:

8. Compile assets, watch for changes, and serve:

    ```bash
    npm run serve
    ```

10. Follow link provided by pervious command to view site, typically `http://localhost:8000`.

## Setting up scheduled reminder emails:
### Mac, Linux

1. Open your terminal.

2. Open the crontab file for editing by running:
    ```bash
    crontab -e
    ```

3. Add the following line to the crontab file to run the Laravel scheduler every minute:
    ```bash
    * * * * * /path/to/php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1
    ```
    - Replace `/path/to/php` with the path to your PHP executable. You can find it by running `which php` in your terminal.
    - Replace `/path/to/your/project` with the path to your Laravel project.

4. Save and exit the crontab file.

5. Verify that the cron job has been added by running:
    ```bash
    crontab -l
    ```

### Windows:

1. Open task scheduler:

    ```bash
    taskschd.msc
    ```
2. Create a New Task:
   - In the Task Scheduler, click on `Create Task` in the right-hand Actions pane.

3. General Tab:
   - Give your task a name, e.g., "Laravel Scheduler".
   - Select "Run whether user is logged on or not".
   - Check "Run with highest privileges".

4. Triggers Tab:
   - Click `New` to create a new trigger.
   - Set the trigger to begin "On a schedule".
   - Set the schedule to "Daily" and repeat the task every 1 minute, indefinitely. Select "One time" if using this in development.
   - Ensure the task is enabled.
   - Note: if using for development, make sure to turn off/disable the task when it is not needed.

5. Actions Tab:
   - Click `New` to create a new action.
   - Set the action to "Start a program".
   - In the "Program/script" field, enter the path to your PHP executable, e.g., `C:\path\to\php.exe`.
   - In the "Add arguments (optional)" field, enter the path to your Laravel `artisan` file followed by `schedule:run`, e.g., `C:\path\to\your\project\artisan schedule:run`.
   - In the "Start in (optional)" field, enter the directory of your Laravel project, e.g., `C:\path\to\your\project`.

6. Conditions Tab:
   - Uncheck "Start the task only if the computer is on AC power" to ensure the task runs even on battery power.

7. Settings Tab:
   - Ensure "Allow task to be run on demand" is checked.
   - Ensure "Run task as soon as possible after a scheduled start is missed" is checked.
   - Ensure "If the task fails, restart every" is set to 1 minute and "Attempt to restart up to" is set to 3 times.
   - Optionally, choose "Stop the stask if it runs longer than:", and select a time for it to shut off after you are done developing.

8. Save the Task:
   - Click `OK` to save the task.
   - You may be prompted to enter your password to create the task.


## Potential Issues:

1. The command `php artisan serve` fails to bind to any ports. [Link to issue](https://github.com/laravel/framework/issues/34229).
     - Solution: in `php.ini` in php installation on local device make the following change:

        `variables_order = 'EGPCS'` <br>
        to <br>
        `variables_order = 'GPCS'`

## License

???
