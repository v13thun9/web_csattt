#!/bin/bash
DB_FILE="/var/www/html/database/main.sqlite"
SCHEMA_FILE="/var/www/html/database/schema.sql"

if [ ! -f "$DB_FILE" ] || [ ! $(sqlite3 "$DB_FILE" ".tables" | grep users) ]; then
    echo "Khởi tạo database..."
    rm -f "$DB_FILE"
    sqlite3 "$DB_FILE" < "$SCHEMA_FILE"
    if [ $? -ne 0 ]; then
        echo "Lỗi khi tạo bảng từ schema.sql!" >&2
        cat "$SCHEMA_FILE"
        exit 1
    fi
    chown www-data:www-data "$DB_FILE"
else
    echo "Database đã tồn tại và đã có bảng users."
fi

# Chạy Apache ở foreground
apache2-foreground 