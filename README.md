# PuremedPharmacy - Pharmacy Management System

A Laravel-based pharmacy management system with features for medicine inventory, purchases, and customer notifications.

## Features

- User and Admin Authentication
- Medicine Inventory Management
- Purchase Processing System
- Email Notifications
- Payment Proof Upload
- Chat Support System
- Pickup Verification System

## Requirements

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL/MariaDB
- Laravel 10.x
- Gmail Account (for notifications)

## Quick Installation

1. Clone the repository
   ```bash
   git clone https://github.com/YourUsername/PuremedPharmacy.git
   cd PuremedPharmacy
   ```

2. Install PHP dependencies
   ```bash
   composer install
   ```

3. Install Node.js dependencies
   ```bash
   npm install
   ```

4. Environment Setup
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure your `.env` file:
   - Set your database credentials (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
   - Configure mail settings for Gmail:
     ```
     MAIL_MAILER=smtp
     MAIL_HOST=smtp.gmail.com
     MAIL_PORT=587
     MAIL_USERNAME=your-email@gmail.com
     MAIL_PASSWORD=your-app-password
     MAIL_ENCRYPTION=tls
     MAIL_FROM_ADDRESS=your-email@gmail.com
     MAIL_FROM_NAME="${APP_NAME}"
     ```

6. Database Setup
   ```bash
   php artisan migrate --seed
   ```

7. Storage Setup
   ```bash
   php artisan storage:link
   ```

8. Build Assets
   ```bash
   npm run build
   ```

9. Start the Development Server
   ```bash
   php artisan serve
   ```

The application will be available at `http://localhost:8000`

## Default Admin Credentials
- Email: admin@gmail.com
- Password: password

## Production Deployment Notes

1. Set `APP_ENV=production` and `APP_DEBUG=false` in your `.env` file
2. Configure proper mail settings
3. Set up proper database credentials
4. Configure proper file permissions
5. Set up a proper web server (Apache/Nginx)

## Troubleshooting

If you encounter any issues:

1. Clear application cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

2. Regenerate autoload files:
   ```bash
   composer dump-autoload
   ```

3. Check storage permissions:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

## Contributing

Please read our [Contributing Guide](CONTRIBUTING.md) before submitting a Pull Request.

## License

This project is licensed under the [MIT License](LICENSE).