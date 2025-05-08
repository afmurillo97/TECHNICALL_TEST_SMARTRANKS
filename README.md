# Laravel API REST Project

This is a RESTful API project built with Laravel, featuring:
- Swagger/OpenAPI documentation
- Database migrations and seeders
- Factory patterns for testing
- MySQL database integration

## Requirements
- PHP >= 8.1
- Composer
- MySQL
- Laravel 10.x

## Installation

1. Clone the repository
```bash
git clone [your-repository-url]
```

2. Install dependencies
```bash
composer install
```

3. Copy .env.example to .env and configure your environment
```bash
cp .env.example .env
```

4. Generate application key
```bash
php artisan key:generate
```

5. Configure your database in .env file

6. Run migrations
```bash
php artisan migrate
```

## Development

To start the development server:
```bash
php artisan serve
```

## API Documentation

API documentation will be available at `/api/documentation` after setting up Swagger.

## License

[Your chosen license]
