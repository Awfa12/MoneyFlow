# 💰 MoneyFlow - P2P Money Transfer API

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg)](https://docker.com)
[![Testing](https://img.shields.io/badge/Tests-Pest%20PHP-green.svg)](https://pestphp.com)

A production-ready peer-to-peer money transfer API built with Laravel, featuring robust race condition prevention, database transactions, and comprehensive security measures. This project demonstrates advanced backend engineering concepts including concurrent transaction handling, pessimistic locking, and atomic operations.

---

## 📋 Table of Contents

-   [Overview](#-overview)
-   [Key Features](#-key-features)
-   [Tech Stack](#-tech-stack)
-   [Architecture Highlights](#-architecture-highlights)
-   [Installation](#-installation)
-   [API Documentation](#-api-documentation)
-   [Database Schema](#-database-schema)
-   [Testing](#-testing)
-   [Security Features](#-security-features)
-   [Key Concepts Demonstrated](#-key-concepts-demonstrated)
-   [Project Structure](#-project-structure)
-   [Future Enhancements](#-future-enhancements)

---

## 🎯 Overview

MoneyFlow is a RESTful API for peer-to-peer money transfers, similar to Venmo or Tikkie. Built with Laravel 12, it handles concurrent money transfers safely using database transactions and pessimistic locking to prevent race conditions and ensure data integrity.

**Key Problem Solved:** Preventing double-spending and ensuring accurate balances when multiple users transfer money simultaneously.

---

## ✨ Key Features

-   🔐 **Secure Authentication** - Token-based authentication using Laravel Sanctum
-   💸 **Money Transfers** - Send money between users with race condition prevention
-   📊 **Transaction History** - View all transactions (sent & received) with pagination
-   🔒 **Race Condition Prevention** - Database transactions + pessimistic locking
-   💰 **Automatic Wallet Creation** - Wallets automatically created on user registration
-   🧪 **Comprehensive Testing** - Feature tests covering all endpoints and security
-   🐳 **Docker Setup** - Fully containerized development environment
-   📝 **RESTful API** - Clean, intuitive API design

---

## 🛠 Tech Stack

### Backend

-   **Framework:** Laravel 12
-   **Language:** PHP 8.3
-   **Authentication:** Laravel Sanctum
-   **Database:** MySQL 8.0 (InnoDB engine)
-   **Testing:** Pest PHP

### Infrastructure

-   **Containerization:** Docker & Docker Compose
-   **Web Server:** Nginx
-   **PHP Runtime:** PHP-FPM
-   **Cache/Sessions:** Redis
-   **Email Testing:** Mailpit

### Development Tools

-   **Testing Framework:** Pest PHP
-   **Code Quality:** PHPUnit

---

## 🏗 Architecture Highlights

### Race Condition Prevention

The core challenge in financial systems is handling concurrent requests safely. MoneyFlow solves this using:

1. **Database Transactions** - Ensures atomicity (all-or-nothing operations)
2. **Pessimistic Locking** - Prevents concurrent access with `lockForUpdate()`
3. **ACID Compliance** - Guarantees data integrity

```php
// Example: Transfer Service with locking
DB::transaction(function () {
    $sender = Wallet::where('id', $id)->lockForUpdate()->first();
    $recipient = Wallet::where('id', $id)->lockForUpdate()->first();

    // Safe balance checks and updates
    if ($sender->balance >= $amount) {
        $sender->decrement('balance', $amount);
        $recipient->increment('balance', $amount);
    }
});
```

### Service Layer Pattern

Business logic is separated into services for maintainability:

-   `TransferService` - Handles all money transfer logic
-   Controllers remain thin and delegate to services

### Observer Pattern

Automatic wallet creation when users register using Laravel Observers:

```php
// UserObserver automatically creates wallet on user creation
public function created(User $user): void
{
    Wallet::create([
        'user_id' => $user->id,
        'balance' => 0.00,
        'currency' => 'EUR',
    ]);
}
```

---

## 🚀 Installation

### Prerequisites

-   Docker & Docker Compose
-   Git

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd MoneyFlow
```

### Step 2: Configure Environment

Copy the environment file:

```bash
cp .env.example .env
```

Update `.env` with your database credentials (already configured for Docker):

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=moneyflow
DB_USERNAME=moneyflow_user
DB_PASSWORD=moneyflow_password
```

### Step 3: Start Docker Containers

```bash
docker-compose up -d
```

This starts:

-   **PHP-FPM** container (port 9000)
-   **Nginx** web server (port 8000)
-   **MySQL** database (port 3306)
-   **Redis** cache (port 6379)
-   **Mailpit** email testing (ports 1025, 8025)

### Step 4: Install Dependencies

```bash
docker-compose exec app composer install
```

### Step 5: Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

### Step 6: Run Migrations

```bash
docker-compose exec app php artisan migrate
```

### Step 7: Verify Installation

Visit `http://localhost:8000/api/register` or check:

```bash
docker-compose exec app php artisan route:list
```

---

## 📚 API Documentation

### Base URL

```
http://localhost:8000/api
```

### Authentication

Most endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

### Endpoints

#### 1. Register User

**POST** `/api/register`

Register a new user account.

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:** `201 Created`

```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

---

#### 2. Login

**POST** `/api/login`

Authenticate and receive access token.

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:** `200 OK`

```json
{
    "message": "Logged in successfully",
    "token": "1|abcdef123456...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

---

#### 3. Transfer Money

**POST** `/api/transfers` 🔒

Transfer money to another user.

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Request Body:**

```json
{
    "recipient_id": 2,
    "amount": 50.0,
    "description": "Payment for services"
}
```

**Response:** `200 OK`

```json
{
    "message": "Transfer completed successfully",
    "transaction": {
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "amount": "50.00",
        "recipient": "Jane Smith",
        "description": "Payment for services"
    },
    "new_balance": 450.0
}
```

**Error Responses:**

-   `400 Bad Request` - Insufficient funds
-   `422 Unprocessable Entity` - Validation errors or self-transfer

---

#### 4. List Transactions

**GET** `/api/transactions` 🔒

Get all transactions for authenticated user (paginated).

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Response:** `200 OK`

```json
{
    "data": [
        {
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "type": "sent",
            "amount": "50.00",
            "other_party": "Jane Smith",
            "other_party_email": "jane@example.com",
            "description": "Payment for services",
            "status": "completed",
            "created_at": "2025-01-15T10:30:00+00:00"
        }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
}
```

**Query Parameters:**

-   `page` - Page number (default: 1)

---

#### 5. View Single Transaction

**GET** `/api/transactions/{uuid}` 🔒

Get details of a specific transaction.

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Response:** `200 OK`

```json
{
    "data": {
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "type": "sent",
        "amount": "50.00",
        "sender": {
            "name": "John Doe",
            "email": "john@example.com"
        },
        "recipient": {
            "name": "Jane Smith",
            "email": "jane@example.com"
        },
        "description": "Payment for services",
        "status": "completed",
        "created_at": "2025-01-15T10:30:00+00:00",
        "updated_at": "2025-01-15T10:30:00+00:00"
    }
}
```

**Error Responses:**

-   `404 Not Found` - Transaction not found or unauthorized

---

## 🗄 Database Schema

### Users Table

```sql
- id (bigint, primary key)
- name (string)
- email (string, unique)
- password (hashed)
- created_at, updated_at
```

### Wallets Table

```sql
- id (bigint, primary key)
- user_id (foreign key → users.id, cascade delete)
- balance (decimal(10,2), default 0.00)
- currency (string(3), default 'EUR')
- created_at, updated_at
```

### Transactions Table

```sql
- id (bigint, primary key)
- uuid (uuid, unique) - For idempotency
- sender_wallet_id (foreign key → wallets.id)
- recipient_wallet_id (foreign key → wallets.id)
- amount (decimal(10,2))
- status (enum: pending, completed, failed)
- description (text, nullable)
- created_at, updated_at
```

**Key Design Decisions:**

-   `DECIMAL(10,2)` for money (never FLOAT/DOUBLE for precision)
-   UUID for transaction idempotency
-   InnoDB engine for ACID transactions and row-level locking
-   Indexes on foreign keys and status for performance

---

## 🧪 Testing

### Run All Tests

```bash
docker-compose exec app php vendor/bin/pest
```

### Run Specific Test File

```bash
docker-compose exec app php vendor/bin/pest tests/Feature/TransactionHistoryTest.php
```

### Test Coverage

The project includes comprehensive feature tests covering:

-   ✅ Authentication (register, login)
-   ✅ Authorization (users can only access their own data)
-   ✅ Money transfers with race condition prevention
-   ✅ Transaction history listing
-   ✅ Single transaction viewing
-   ✅ Edge cases (insufficient funds, self-transfers, etc.)

**Test Files:**

-   `tests/Feature/WalletCreationTest.php` - Wallet auto-creation
-   `tests/Feature/TransactionHistoryTest.php` - Transaction endpoints

---

## 🔒 Security Features

### Authentication & Authorization

-   Token-based authentication with Laravel Sanctum
-   Protected routes require valid Bearer tokens
-   Users can only access their own transactions

### Data Protection

-   Passwords hashed using bcrypt
-   SQL injection prevention (Eloquent ORM)
-   Input validation on all endpoints
-   Self-transfer prevention

### Financial Security

-   Atomic transactions ensure data consistency
-   Pessimistic locking prevents race conditions
-   Balance validation before transfers
-   UUID-based idempotency prevents duplicate processing

### Best Practices

-   Environment variables for sensitive data
-   Non-root user in Docker containers
-   Proper HTTP status codes
-   Error messages don't leak sensitive information

---

## 🎓 Key Concepts Demonstrated

This project showcases advanced backend engineering concepts:

### 1. **Race Condition Prevention**

-   Understanding concurrent access problems
-   Implementing pessimistic locking with `lockForUpdate()`
-   Database transactions for atomicity

### 2. **Database Transactions (ACID)**

-   Atomicity - All or nothing operations
-   Consistency - Data always valid
-   Isolation - Concurrent transactions don't interfere
-   Durability - Committed changes persist

### 3. **Eager Loading**

-   Preventing N+1 query problems
-   Optimizing database queries with `with()`
-   Performance optimization techniques

### 4. **Service Layer Pattern**

-   Separation of concerns
-   Business logic in services, not controllers
-   Maintainable and testable code

### 5. **Observer Pattern**

-   Automatic wallet creation on user registration
-   Event-driven architecture
-   Laravel model events

### 6. **API Design**

-   RESTful principles
-   Proper HTTP status codes
-   Consistent response formatting
-   Pagination for large datasets

### 7. **Testing**

-   Feature tests with Pest PHP
-   Testing authentication and authorization
-   Database refresh for test isolation

---

## 📁 Project Structure

```
MoneyFlow/
├── app/
│   ├── Exceptions/
│   │   └── InsufficientFundsException.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       ├── TransferController.php
│   │       └── TransactionController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Wallet.php
│   │   └── Transaction.php
│   ├── Observers/
│   │   └── UserObserver.php
│   ├── Services/
│   │   └── TransferService.php
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_wallets_table.php
│   │   └── create_transactions_table.php
│   └── factories/
│       └── UserFactory.php
├── routes/
│   └── api.php
├── tests/
│   └── Feature/
│       ├── WalletCreationTest.php
│       └── TransactionHistoryTest.php
├── docker/
│   ├── nginx/
│   ├── php/
│   └── mysql/
├── docker-compose.yml
├── Dockerfile
└── README.md
```

---

## 🚧 Future Enhancements

Potential improvements for production:

-   [ ] Email notifications for transfers
-   [ ] Multi-currency support
-   [ ] Transaction webhooks
-   [ ] Rate limiting
-   [ ] API documentation with Scribe
-   [ ] Request logging and monitoring
-   [ ] Background job processing for heavy operations
-   [ ] Caching for frequently accessed data
-   [ ] GraphQL API alternative
-   [ ] Mobile app integration

---

## 🤝 Contributing

This is a portfolio project. Contributions and suggestions are welcome!

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new features
5. Submit a pull request

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👨‍💻 Author

Built as a portfolio project to demonstrate advanced Laravel and backend engineering skills.

**Key Highlights:**

-   Production-ready code architecture
-   Advanced concurrency handling
-   Comprehensive test coverage
-   Security best practices
-   Clean, maintainable codebase

---

## 🙏 Acknowledgments

-   Laravel framework and community
-   Pest PHP for excellent testing experience
-   Docker for containerization

---

<div align="center">

**⭐ If you found this project interesting, please consider giving it a star!**

Built with ❤️ using Laravel

</div>
