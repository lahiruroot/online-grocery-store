#!/bin/bash

# =====================================================
# CLEAR ALL DATA SCRIPT FOR DOCKER
# =====================================================
# This script clears all data but keeps the schema
# Usage: ./clear-data.sh
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
echo -e "${YELLOW}  CLEAR ALL DATABASE DATA${NC}"
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
read -p "This will DELETE ALL DATA from all tables. Continue? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Operation cancelled."
    exit 0
fi

echo ""
echo -e "${YELLOW}Clearing all data...${NC}"

# Execute clear script
docker exec -i "$DB_CONTAINER" mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < sql/clear-all-data.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ All data cleared successfully!${NC}"
    echo ""
    echo "Note: Table structure is preserved. You can now add new data."
else
    echo -e "${RED}✗ Error clearing data${NC}"
    exit 1
fi

