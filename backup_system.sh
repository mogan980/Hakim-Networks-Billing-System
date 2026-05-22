#!/bin/bash

DATE=$(date +"%Y-%m-%d_%H-%M")
BACKUP_DIR="/home/techbuilder/hakim_backups"

mkdir -p "$BACKUP_DIR"

mysqldump --no-tablespaces -u hakim -p1234 mhakim_billing > "$BACKUP_DIR/mhakim_billing_$DATE.sql"

tar -czf "$BACKUP_DIR/mhakim_hotspot_files_$DATE.tar.gz" \
/var/www/html/mhakim-hotspot \
/var/www/html/mhakim-billing-system

find "$BACKUP_DIR" -type f -mtime +14 -delete
