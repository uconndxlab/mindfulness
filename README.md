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

8. Compile assets and watch for changes:

    ```bash
    npm run dev
    ```

9. Serve the application:

    ```bash
    php artisan serve
    ```

10. Follow link provided by pervious command to view site, typically `http://localhost:8000`.

## Potential Issues:

1. The command `php artisan serve` fails to bind to any ports. [Link to issue](https://github.com/laravel/framework/issues/34229).
     - Solution: in `php.ini` in php installation on local device make the following change:

        `variables_order = 'EGPCS'` <br>
        to <br>
        `variables_order = 'GPCS'`

## License

???
