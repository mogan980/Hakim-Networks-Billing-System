#!/bin/bash

DATE=$(date +"%Y-%m-%d_%H-%M-%S")

BASE="/var/www/html/mhakim-billing-system"
BACKUP_DB="$BASE/backups/database"
BACKUP_SYS="$BASE/backups/system"
BACKUP_LOG="$BASE/backups/logs/backup.log"

mkdir -p "$BACKUP_DB" "$BACKUP_SYS" "$BASE/backups/logs"

echo "[$DATE] Backup started" >> "$BACKUP_LOG"

sudo mysqldump mhakim_billing > "$BACKUP_DB/db_$DATE.sql"

tar -czf "$BACKUP_SYS/system_$DATE.tar.gz" \
  --exclude="$BASE/backups" \
  --exclude="$BASE/vendor" \
  "$BASE"

find "$BACKUP_DB" -type f -mtime +7 -delete
find "$BACKUP_SYS" -type f -mtime +7 -delete

echo "[$DATE] Backup completed" >> "$BACKUP_LOG"
