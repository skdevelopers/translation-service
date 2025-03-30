# Translation Service

A Laravel 12 API-driven Translation Management Service with token-based authentication (Sanctum), optimized for high performance and scalability.
## Author
Salman@everestbuys.com Senior Software Engineer DevSps.
github.com/skdevelopers
## Features
- CRUD operations for translations
- Search and export endpoints
- Token-based authentication with Laravel Sanctum
- Comprehensive test suite using PHPUnit
- Artisan commands for migrations, seeding, and testing

## Setup Instructions
Clone the repository by running "git clone https://github.com/skdevelopers/translation-service.git" in your terminal, then change into the project directory.

Install Dependencies:

Run "composer install" to install PHP dependencies.

If your project uses JavaScript assets, run "npm install" followed by "npm run dev" to install JavaScript dependencies and compile assets.

Environment Configuration:

Copy the example environment file by running "cp .env.example .env".

Generate the application key with "php artisan key:generate".

Update your .env file as needed with your database credentials, cache settings, and other configuration values.

Database Setup:

Run the database migrations using "php artisan migrate".

Seed the database with initial data (for testing scalability) using "php artisan db:seed --class=TranslationSeeder".

Clear and Cache Configurations (Optional for Optimization):

Clear any existing configuration cache by running "php artisan config:clear" and then rebuild the cache with "php artisan config:cache".

Also, clear the route cache with "php artisan route:clear" and then cache the routes using "php artisan route:cache".

Running the Test Suite:

Ensure everything is working by running "php artisan test". This command will run all unit and feature tests, including those for the TranslationController.

Start the Development Server:

Launch the application locally by running "php artisan serve". Your application should now be running at http://localhost:8000.

Final Steps in PhpStorm:

Open your project in PhpStorm. PhpStorm will automatically detect the Git version control.

Use the Git tool window to review, commit, and push your changes. For example, commit your changes and then right-click on your repository to select "Git > Push" to upload your commits to GitHub.

Finally, verify on GitHub (at github.com/skdevelopers/translation-service) that all files, including this README, are present.

Following these steps ensures that your Laravel project is properly set up, thoroughly tested, and ready to be run on any new computer.

Explanation & Testing for Large Datasets
Docker Setup:

The Dockerfile builds your Laravel application using PHP 8.4 with FPM. It installs necessary PHP extensions and Composer, sets the working directory, and installs dependencies.

The docker-compose.yml file defines two services: the app (Laravel) and db (MySQL). This configuration ensures that your application runs in an isolated environment and can be easily scaled.

PHPDocs:
All controllers and test files include PHPDoc annotations to clearly document method parameters, return types, and overall functionality in compliance with PSR-12 standards.

Testing with 100,000 Records:
Our feature test for export (see testExportPerformance() in the TranslationControllerTest) is configured to seed the database with 100,000 records. This allows you to measure performance (ensuring the export completes in under 0.5 seconds) and validate that the export endpoint can handle large datasets efficiently.

Running Tests:
Once your Docker containers are up and running (using docker-compose up -d), you can run your tests from within the container (or by connecting to the container):

docker-compose exec app php artisan test
This command will execute your unit and feature tests, including those that seed 100,000 records, ensuring your application's performance meets the criteria.