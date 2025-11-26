# Docker Setup Guide for MoneyFlow

## 📚 What We've Built

### Files Created

1. **`docker-compose.yml`** - Orchestrates all services (PHP, Nginx, MySQL, Redis, Mailpit)
2. **`Dockerfile`** - Defines the PHP 8.3 container with all required extensions
3. **`docker/nginx/default.conf`** - Nginx configuration for serving Laravel
4. **`docker/php/php.ini`** - PHP settings (memory, upload limits, etc.)
5. **`docker/php/www.conf`** - PHP-FPM pool configuration (runs workers as non-root user)
6. **`docker/php/docker-entrypoint.sh`** - Entrypoint script that sets Laravel directory permissions
7. **`docker/mysql/my.cnf`** - **CRITICAL**: Ensures InnoDB is default (required for transactions)
8. **`.dockerignore`** - Prevents unnecessary files from being copied into Docker

---

## 🎯 Key Concepts Explained

### Why MySQL with InnoDB?

Your money transfer project needs **InnoDB** because:

-   ✅ **Transactions**: All-or-nothing operations (prevents partial transfers)
-   ✅ **Row-level locking**: `lockForUpdate()` requires InnoDB
-   ✅ **ACID compliance**: Guarantees data integrity during concurrent requests

### How Services Communicate

In Docker, services communicate using **service names** (defined in docker-compose.yml):

-   Laravel connects to MySQL using hostname: `mysql` (not `localhost`)
-   Nginx connects to PHP-FPM using hostname: `app` (not `localhost`)
-   All services share the `moneyflow_network` network

### PHP-FPM Architecture & Permissions

**Why this matters:** PHP-FPM has a master-worker architecture:

-   **Master process** (runs as root) - Manages worker processes
-   **Worker processes** (run as `moneyflow` user) - Execute your PHP code

This is configured in `docker/php/www.conf`:

-   Security: PHP code runs as non-root user
-   Permissions: Entrypoint script ensures Laravel directories are writable
-   Communication: Uses TCP port 9000 for Docker inter-container networking

---

## ⚙️ Next Steps: Environment Configuration

### Step 1: Create Your `.env` File

You need to create a `.env` file in your project root with these values:

```env
APP_NAME=MoneyFlow
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration (matches docker-compose.yml)
DB_CONNECTION=mysql
DB_HOST=mysql          # ← Service name from docker-compose.yml
DB_PORT=3306
DB_DATABASE=moneyflow
DB_USERNAME=moneyflow_user
DB_PASSWORD=moneyflow_password

# Redis Configuration
REDIS_HOST=redis       # ← Service name from docker-compose.yml
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration (Mailpit)
MAIL_MAILER=smtp
MAIL_HOST=mailpit      # ← Service name from docker-compose.yml
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@moneyflow.test"
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 2: Generate Application Key

After creating `.env`, you'll need to generate Laravel's encryption key.

---

## 🚀 How to Start Your Docker Environment

### Build and Start All Containers

```bash
docker-compose up -d --build
```

**What this does:**

-   `up` - Starts all services
-   `-d` - Runs in "detached" mode (background)
-   `--build` - Rebuilds the PHP image (only needed first time or after Dockerfile changes)

### Check Container Status

```bash
docker-compose ps
```

All containers should show "Up" status.

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f mysql
```

---

## ✅ Verification Steps

### 1. Check MySQL is Using InnoDB

```bash
docker-compose exec mysql mysql -u root -prootpassword -e "SHOW ENGINES;"
```

You should see `InnoDB` with `DEFAULT` in the Support column.

### 2. Access Your Application

-   **Laravel App**: http://localhost:8000
-   **Mailpit (Email Testing)**: http://localhost:8025

### 3. Verify Database Connection

Once containers are running, you can run Laravel commands:

```bash
# Access PHP container shell
docker-compose exec app bash

# Run Laravel artisan commands (from outside container)
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:status
```

---

## 📝 Common Commands

### Start Services

```bash
docker-compose up -d
```

### Stop Services

```bash
docker-compose down
```

### Stop and Remove Volumes (⚠️ Deletes database data)

```bash
docker-compose down -v
```

### Rebuild PHP Container (after Dockerfile changes)

```bash
docker-compose build app
docker-compose up -d
```

### Execute Commands in Containers

```bash
# Run Laravel commands
docker-compose exec app php artisan migrate

# Access PHP container shell
docker-compose exec app bash

# Access MySQL shell
docker-compose exec mysql mysql -u moneyflow_user -pmoneyflow_password moneyflow
```

---

## 🐛 Troubleshooting

### Port Already in Use

If port 8000, 3306, or 6379 is already in use:

1. Check what's using the port:

    ```bash
    netstat -ano | findstr :8000
    ```

2. Stop the conflicting service, OR
3. Change the port in `docker-compose.yml` (e.g., `"8001:80"` instead of `"8000:80"`)

### Permission Errors on Windows

Docker Desktop for Windows handles file permissions automatically. If you see permission errors:

-   Ensure Docker Desktop is running
-   Try running Docker as Administrator

### MySQL Won't Start

Check MySQL logs:

```bash
docker-compose logs mysql
```

Common issue: Volume permissions. Delete the volume and recreate:

```bash
docker-compose down -v
docker-compose up -d
```

### PHP-FPM Permission Errors

If you see errors like "failed to open error_log: Permission denied":

-   This is already fixed! The entrypoint script (`docker-entrypoint.sh`) automatically sets correct permissions when containers start
-   PHP-FPM master runs as root, workers run as `moneyflow` user (configured in `www.conf`)
-   If issues persist, check logs: `docker-compose logs app`

### Container Build Failures

If Docker build fails:

```bash
# Clean rebuild (removes all cached layers)
docker-compose build --no-cache app

# Then start again
docker-compose up -d
```

---

## 🎓 Learning Checkpoints

Before moving to Week 1 (Day 1), make sure you understand:

1. ✅ **Why we use Docker**: Consistent environment across machines
2. ✅ **Service communication**: Services use service names, not localhost
3. ✅ **InnoDB requirement**: Essential for your transaction-based money transfers
4. ✅ **Volume persistence**: Database data persists even when containers stop
5. ✅ **PHP-FPM permissions**: Master process (root) manages workers (non-root user)
6. ✅ **Entrypoint scripts**: Run automatically when containers start to set up permissions

---

## 📖 Next Steps in Your Journey

Once Docker is running:

1. **Create `.env` file** with the configuration above
2. **Generate Laravel key**: `docker-compose exec app php artisan key:generate`
3. **Run migrations**: `docker-compose exec app php artisan migrate`
4. **Install dependencies**: `docker-compose exec app composer install`

Then you'll be ready to start **Week 1: Day 1 - Authentication Setup**!

---

## 🔒 Security Notes

The passwords in `docker-compose.yml` are for **development only**. For production:

-   Use environment variables in docker-compose.yml
-   Never commit `.env` to git (it's already in `.gitignore`)
-   Use strong, randomly generated passwords
