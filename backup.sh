#!/bin/bash

# TEC Sunday School Audio App - Backup Script
# Usage: ./backup.sh

set -e

# Configuration
BACKUP_DIR="/var/backups/tec-sunday-school"
APP_DIR="/var/www/tec-sunday-school"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

print_status "Starting backup process..."

# 1. Database Backup
print_status "Backing up database..."
if [ -f "$APP_DIR/database/database.sqlite" ]; then
    cp "$APP_DIR/database/database.sqlite" "$BACKUP_DIR/database_$DATE.sqlite"
    print_status "SQLite database backed up"
else
    print_warning "SQLite database not found"
fi

# 2. Environment Configuration Backup
print_status "Backing up configuration..."
if [ -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env" "$BACKUP_DIR/env_$DATE.txt"
    print_status "Environment configuration backed up"
fi

# 3. Storage Directory Backup
print_status "Backing up storage directory..."
if [ -d "$APP_DIR/storage" ]; then
    tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" -C "$APP_DIR" storage/
    print_status "Storage directory backed up"
fi

# 4. Application Files Backup (excluding vendor and node_modules)
print_status "Backing up application files..."
tar -czf "$BACKUP_DIR/app_$DATE.tar.gz" \
    -C "$APP_DIR" \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    .

print_status "Application files backed up"

# 5. Create backup manifest
print_status "Creating backup manifest..."
cat > "$BACKUP_DIR/manifest_$DATE.txt" << EOF
TEC Sunday School Audio App Backup
Date: $(date)
Backup ID: $DATE

Files included:
- database_$DATE.sqlite (Database)
- env_$DATE.txt (Environment configuration)
- storage_$DATE.tar.gz (Storage directory)
- app_$DATE.tar.gz (Application files)

Backup location: $BACKUP_DIR
EOF

# 6. Cleanup old backups
print_status "Cleaning up old backups (older than $RETENTION_DAYS days)..."
find "$BACKUP_DIR" -name "*_*.sqlite" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "*_*.txt" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "*_*.tar.gz" -mtime +$RETENTION_DAYS -delete

# 7. Calculate backup sizes
print_status "Backup summary:"
echo "📁 Backup directory: $BACKUP_DIR"
echo "📅 Backup date: $DATE"
echo "💾 Backup sizes:"

if [ -f "$BACKUP_DIR/database_$DATE.sqlite" ]; then
    echo "   Database: $(du -h "$BACKUP_DIR/database_$DATE.sqlite" | cut -f1)"
fi

if [ -f "$BACKUP_DIR/storage_$DATE.tar.gz" ]; then
    echo "   Storage: $(du -h "$BACKUP_DIR/storage_$DATE.tar.gz" | cut -f1)"
fi

if [ -f "$BACKUP_DIR/app_$DATE.tar.gz" ]; then
    echo "   Application: $(du -h "$BACKUP_DIR/app_$DATE.tar.gz" | cut -f1)"
fi

echo "   Total: $(du -sh "$BACKUP_DIR" | cut -f1)"

print_status "Backup completed successfully! 🎉"

# Optional: Upload to remote storage
# Uncomment and configure for your backup storage solution
# print_status "Uploading to remote storage..."
# rsync -av "$BACKUP_DIR/" user@backup-server:/backups/tec-sunday-school/
# aws s3 sync "$BACKUP_DIR/" s3://your-backup-bucket/tec-sunday-school/
