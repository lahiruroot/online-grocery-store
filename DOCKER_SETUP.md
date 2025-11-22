# Docker Setup Instructions

This project is now configured to run with Docker and MySQL.

## Prerequisites

- Docker installed on your system
- Docker Compose installed

## Quick Start

1. **Build and start the containers:**
   ```bash
   docker-compose up -d
   ```

2. **Access the application:**
   - Web application: http://localhost:8080
   - MySQL database: localhost:3306

3. **View logs:**
   ```bash
   docker-compose logs -f
   ```

4. **Stop the containers:**
   ```bash
   docker-compose down
   ```

5. **Stop and remove volumes (clean slate):**
   ```bash
   docker-compose down -v
   ```

## Database Configuration

The database is automatically initialized with the schema from `sql/schema.sql` when the MySQL container starts for the first time.

**Default Database Credentials:**
- Host: `db` (internal Docker network) or `localhost` (from host)
- Database: `grocery_store`
- Root User: `root`
- Root Password: `rootpassword`
- User: `grocery_user`
- User Password: `grocery_password`

## Creating Admin User

To create an admin user, you have two options:

### Option 1: Using the Web Interface (Recommended)
1. Access: http://localhost:8080/create-admin.php
2. Fill in the admin details and click "Create Admin User"
3. **Important:** Delete `create-admin.php` file after creating the admin for security!

### Option 2: Using SQL Script
1. Connect to the database:
   ```bash
   docker-compose exec db mysql -u root -prootpassword grocery_store
   ```
2. Run the admin creation script:
   ```sql
   source /docker-entrypoint-initdb.d/create-admin.sql
   ```
   Or manually:
   ```sql
   INSERT INTO users (name, email, password, role) 
   VALUES ('Admin', 'admin@GroceryKing.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
   ```
   Default password: `admin123` (change after first login!)

### Option 3: Direct Database Access
```bash
# Connect to MySQL
docker-compose exec db mysql -u root -prootpassword grocery_store

# Create admin user (replace with your own password hash)
INSERT INTO users (name, email, password, role) 
VALUES ('Admin User', 'admin@example.com', '$2y$10$YOUR_HASHED_PASSWORD', 'admin');
```

## Customizing Configuration

You can customize the database credentials by editing the `docker-compose.yml` file or by creating a `.env` file with your preferred values.

## Troubleshooting

1. **Port already in use:**
   - If port 8080 is in use, change it in `docker-compose.yml` under `web` service `ports` section
   - If port 3306 is in use, change it in `docker-compose.yml` under `db` service `ports` section

2. **Database connection issues:**
   - Make sure the `db` service is healthy before the `web` service starts
   - Check logs: `docker-compose logs db`

3. **Permission issues:**
   - The `public/uploads` directory needs write permissions for file uploads
   - If you encounter permission issues, run: `chmod -R 755 public/uploads`

## Development

The application files are mounted as volumes, so changes to PHP files will be reflected immediately without rebuilding the container.

To rebuild the containers after changes to Dockerfile:
```bash
docker-compose up -d --build
```

## Updating Docker

### After Making Code Changes

Since your PHP files are mounted as volumes, most code changes are reflected immediately. However, if you need to:

1. **Restart containers to apply changes:**
   ```bash
   docker-compose restart
   ```

2. **Restart a specific service:**
   ```bash
   docker-compose restart web    # Restart web server
   docker-compose restart db      # Restart database
   ```

### After Changing Dockerfile or docker-compose.yml

1. **Rebuild and restart containers:**
   ```bash
   docker-compose up -d --build
   ```

2. **Force rebuild without cache:**
   ```bash
   docker-compose build --no-cache
   docker-compose up -d
   ```

### After Database Schema Changes

1. **If you modified `sql/schema.sql` and want to reinitialize the database:**
   ```bash
   # Stop containers and remove volumes (WARNING: This deletes all data!)
   docker-compose down -v
   
   # Start fresh
   docker-compose up -d
   ```

2. **If you want to keep existing data and just apply new changes:**
   - Connect to the database and run your SQL manually, or
   - Use a migration script

### Updating Docker Images

1. **Pull latest MySQL image:**
   ```bash
   docker-compose pull db
   docker-compose up -d db
   ```

2. **Update all images:**
   ```bash
   docker-compose pull
   docker-compose up -d
   ```

### Common Update Commands

```bash
# View running containers
docker-compose ps

# View logs
docker-compose logs -f

# Stop all containers
docker-compose stop

# Start stopped containers
docker-compose start

# Stop and remove containers (keeps volumes)
docker-compose down

# Stop and remove everything including volumes (deletes data!)
docker-compose down -v

# Rebuild specific service
docker-compose build web
docker-compose up -d web

# Execute command in running container
docker-compose exec web php -v
docker-compose exec db mysql -u root -prootpassword grocery_store
```

### After Configuration Changes

If you modified `docker-compose.yml` or environment variables:

```bash
# Recreate containers with new configuration
docker-compose up -d --force-recreate
```

