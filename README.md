# Lapasys - Employee Management System

Lapasys is a comprehensive employee management system built with Laravel and Tailwind CSS. It provides features for managing employees, payroll, loans, and more.

## Features

- **User Management**
  - Employee profiles with biodata
  - Role-based access control
  - Employment history tracking

- **Payroll System**
  - Monthly salary processing
  - Prorated salary calculation
  - Manual salary override
  - Salary history tracking

- **Loan Management**
  - Employee loan tracking
  - Monthly loan deductions
  - Loan installment management
  - Manual loan amount override

- **Modern UI/UX**
  - Built with Tailwind CSS
  - Responsive design
  - Interactive modals
  - Real-time calculations

## Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL/MariaDB
- Laravel 10.x

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/lapasys.git
cd lapasys
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install NPM dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lapasys
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

7. Run migrations:
```bash
php artisan migrate
```

8. Build assets:
```bash
npm run build
```

9. Start the development server:
```bash
php artisan serve
```

## Usage

1. Access the application at `http://localhost:8000`
2. Login with your credentials
3. Navigate through the dashboard to access different features

## Payroll Processing

The system supports three types of salary processing:
1. **Regular Salary**: Full monthly salary
2. **Prorated Salary**: Calculated based on working days
3. **Manual Salary**: Custom amount override

## Loan Management

- Create and track employee loans
- Set installment periods
- Automatic monthly deductions
- Manual loan amount adjustments

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## Acknowledgments

- [Laravel](https://laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)
- [SweetAlert2](https://sweetalert2.github.io)

## Contact

Hasyidan P - [LinkedIn](https://www.linkedin.com/in/hasyidanparamananda/)

Project Link: [https://github.com/senzlord/Lapasys](https://github.com/senzlord/Lapasys)
