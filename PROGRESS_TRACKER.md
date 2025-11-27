# MoneyFlow Project - Progress Tracker

## 📚 Project Overview

Building a peer-to-peer money transfer API similar to Tikkie/Venmo with Laravel, focusing on preventing race conditions in concurrent money transfers.

---

## ✅ Completed Tasks

### 🐳 Docker Setup (COMPLETED)

-   [x] Created `docker-compose.yml` with all services (PHP, Nginx, MySQL, Redis, Mailpit)
-   [x] Created `Dockerfile` for PHP 8.3 container
-   [x] Created `docker/nginx/default.conf` for Nginx configuration
-   [x] Created `docker/php/php.ini` for PHP settings
-   [x] Created `docker/php/www.conf` for PHP-FPM pool configuration
-   [x] Created `docker/php/docker-entrypoint.sh` for permission handling
-   [x] Created `docker/mysql/my.cnf` to ensure InnoDB engine (critical for transactions)
-   [x] Created `.dockerignore` to exclude unnecessary files
-   [x] Fixed permission issues with PHP-FPM
-   [x] All containers running successfully

**Key Files Created:**

-   `docker-compose.yml`
-   `Dockerfile`
-   `docker/nginx/default.conf`
-   `docker/php/php.ini`
-   `docker/php/www.conf`
-   `docker/php/docker-entrypoint.sh`
-   `docker/mysql/my.cnf`
-   `.dockerignore`

**Learning Points:**

-   ✅ Docker networking (services communicate via service names)
-   ✅ PHP-FPM master-worker architecture
-   ✅ Volume persistence for database data
-   ✅ InnoDB requirement for transactions

---

### 🔐 Week 1 - Day 1: Authentication Setup (COMPLETED)

-   [x] Installed Laravel Sanctum package
-   [x] Published Sanctum migrations and configuration
-   [x] Ran migrations (created `personal_access_tokens` table)
-   [x] Added `HasApiTokens` trait to User model
-   [x] Created `routes/api.php` for API routes
-   [x] Configured API routes in `bootstrap/app.php`
-   [x] Created `AuthController` with register and login methods
-   [x] Implemented Register endpoint (`POST /api/register`)
-   [x] Implemented Login endpoint (`POST /api/login`)
-   [x] Added proper validation for both endpoints
-   [x] Tested endpoints successfully with Postman

**Key Files Created/Modified:**

-   `app/Models/User.php` - Added HasApiTokens trait
-   `routes/api.php` - API routes configuration
-   `bootstrap/app.php` - Added API routes
-   `app/Http/Controllers/AuthController.php` - Authentication logic
-   `POSTMAN_TESTING_GUIDE.md` - Testing instructions

**Learning Points:**

-   ✅ Laravel Sanctum for token-based authentication
-   ✅ Password hashing with `Hash::make()` and `Hash::check()`
-   ✅ Request validation rules
-   ✅ HTTP status codes (200, 201, 422)
-   ✅ Token creation with `createToken()`

**Endpoints Working:**

-   ✅ `POST /api/register` - Creates new user
-   ✅ `POST /api/login` - Returns authentication token

---

### 🗄️ Week 1 - Day 3: Database Migrations (COMPLETED)

-   [x] Create wallets table migration
    -   [x] Design schema (id, user_id, balance DECIMAL(10,2), currency)
    -   [x] Add foreign key constraint to users table
    -   [x] Add indexes for performance
-   [x] Create transactions table migration
    -   [x] Design schema (id, uuid, sender_wallet_id, recipient_wallet_id, amount, status, description)
    -   [x] Add foreign keys and indexes
    -   [x] Understand UUID for idempotency
-   [x] Run migrations successfully
-   [x] Tables created in database (wallets and transactions)

**Key Files Created:**

-   `database/migrations/2025_11_27_004010_create_wallets_table.php` - Wallets table schema
-   `database/migrations/2025_11_27_004659_create_transactions_table.php` - Transactions table schema

**Learning Points:**

-   ✅ DECIMAL(10,2) for money (never use FLOAT/DOUBLE)
-   ✅ Foreign key constraints with cascade delete
-   ✅ Indexes for query performance (user_id, sender_wallet_id, recipient_wallet_id, status)
-   ✅ UUID field for idempotency (prevents duplicate transaction processing)
-   ✅ Enum field for transaction status (pending, completed, failed)
-   ✅ Database schema design principles

### 👁️ Week 1 - Day 2: Wallet Creation with Observers (COMPLETED)

-   [x] Understand the Observer pattern concept
-   [x] Create UserObserver class
-   [x] Implement automatic wallet creation on user registration
-   [x] Register observer in Laravel service provider
-   [x] Test wallet auto-creation successfully

**Key Files Created/Modified:**

-   `app/Models/Wallet.php` - Wallet model with fillable fields
-   `app/Observers/UserObserver.php` - Observer that creates wallet on user creation
-   `app/Providers/AppServiceProvider.php` - Registered UserObserver

**Learning Points:**

-   ✅ Observer pattern - automatic event handling
-   ✅ Laravel model events (created, updated, deleted)
-   ✅ Centralized logic for automatic wallet creation
-   ✅ Service provider boot() method for registering observers
-   ✅ Separation of concerns (observer handles side effects)

**How It Works:**

-   When a user is created → Observer fires automatically
-   Observer creates a wallet with balance 0.00 EUR
-   Works everywhere (API, commands, seeders)

---

## 🔄 In Progress

_Nothing currently in progress_

---

## 📋 Upcoming Tasks

### Week 1 - Day 4: Model Relationships

-   [ ] Define User → Wallet relationship (hasOne)
-   [ ] Define Wallet → User relationship (belongsTo)
-   [ ] Define Transaction relationships (belongsTo sender/recipient wallets)
-   [ ] Install Pest PHP testing framework
-   [ ] Write first test: "User gets wallet on registration"

### Week 2 - Days 5-10: The Critical Part (Race Conditions)

-   [ ] Day 5: Understand race conditions conceptually
-   [ ] Day 6: Learn database transactions (ACID properties)
-   [ ] Day 7: Implement pessimistic locking (`lockForUpdate()`)
-   [ ] Day 8: Create transfer API endpoint
-   [ ] Day 9: Create transaction history endpoints
-   [ ] Day 10: Testing and validation

---

## 🎓 Concepts Learned So Far

### Docker & Infrastructure

-   Docker Compose orchestration
-   PHP-FPM architecture (master/worker processes)
-   Nginx reverse proxy configuration
-   MySQL InnoDB engine requirements
-   Volume persistence

### Laravel Fundamentals

-   Laravel 12 project structure
-   API-only application setup
-   Sanctum authentication
-   Request validation
-   Controllers and routing
-   Database migrations and schema design
-   Observer pattern for model events
-   Service providers and boot() method

### Security & Best Practices

-   Password hashing (never store plain text)
-   Token-based authentication
-   HTTP status codes
-   Input validation

---

## 📝 Notes & Reminders

-   **Important**: Always use InnoDB engine for MySQL (required for transactions)
-   **Important**: Never use FLOAT/DOUBLE for money - use DECIMAL(10,2)
-   **Important**: All money amounts stored as DECIMAL with 2 decimal places
-   Docker containers accessible at `http://localhost:8000`
-   Mailpit email testing UI: `http://localhost:8025`

---

## 🔍 Quick Reference

### Useful Commands

```bash
# Start Docker containers
docker-compose up -d

# Check container status
docker-compose ps

# View logs
docker-compose logs -f app

# Run Laravel commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan route:list

# Access container shell
docker-compose exec app bash
```

### API Endpoints (Current)

-   `POST /api/register` - Register new user
-   `POST /api/login` - Login and get token

---

## 🎯 Next Session Goal

**Start Day 4: Model Relationships**

-   Define User → Wallet relationship (hasOne)
-   Define Wallet → User relationship (belongsTo)
-   Define Transaction relationships
-   Install Pest PHP testing framework
-   Write first test

---

_Last Updated: After Day 2 - Wallet Creation with Observers_
