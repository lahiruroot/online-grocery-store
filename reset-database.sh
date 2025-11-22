#!/bin/bash

# =====================================================
# DATABASE RESET SCRIPT FOR DOCKER
# =====================================================
# This script resets the grocery_store database
# Usage: ./reset-database.sh
# =====================================================

# Database container name/ID
DB_CONTAINER="grocery-store-db"

# Database credentials (adjust if needed)
DB_USER="root"
DB_PASSWORD="root"
DB_NAME="grocery_store"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}  GROCERY STORE DATABASE RESET${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# Check if container is running
if ! docker ps | grep -q "$DB_CONTAINER"; then
    echo -e "${RED}Error: Database container '$DB_CONTAINER' is not running!${NC}"
    echo "Please start your Docker container first."
    exit 1
fi

echo -e "${GREEN}✓ Database container found${NC}"
echo ""

# Ask for confirmation
read -p "This will DROP ALL TABLES and recreate the schema. Continue? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Operation cancelled."
    exit 0
fi

echo ""
echo -e "${YELLOW}Resetting database...${NC}"

# Execute reset script
docker exec -i "$DB_CONTAINER" mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < sql/reset-database.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database reset successfully!${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Create an admin user using: sql/create-admin.sql"
    echo "2. Or use: create-admin.php"
else
    echo -e "${RED}✗ Error resetting database${NC}"
    exit 1
fi

