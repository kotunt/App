#!/bin/bash
set -e

MYSQL_DATA_DIR="/home/runner/mysql-data"
MYSQL_RUN_DIR="/home/runner/mysql-run"
MYSQL_LOG_DIR="/home/runner/mysql-logs"
MYSQL_SOCKET="$MYSQL_RUN_DIR/mysql.sock"
MYSQL_BASEDIR=$(dirname $(dirname $(which mysqld)))
DB_PASS="${DB_PASS:-secret}"

# Create required directories
mkdir -p "$MYSQL_DATA_DIR" "$MYSQL_RUN_DIR" "$MYSQL_LOG_DIR"
mkdir -p /home/runner/workspace/logs /home/runner/workspace/uploads /home/runner/workspace/backups

# Initialize MySQL data directory if not already done
if [ ! -d "$MYSQL_DATA_DIR/mysql" ]; then
    echo "[start.sh] Initializing MySQL data directory..."
    mysqld --initialize-insecure \
        --user=$(whoami) \
        --datadir="$MYSQL_DATA_DIR" \
        --basedir="$MYSQL_BASEDIR" 2>&1
    echo "[start.sh] MySQL initialized."
fi

# Shut down any MySQL instance still holding the data directory lock from a
# previous run (otherwise the new mysqld fails with "Unable to lock ./ibdata1").
if pgrep -f "mysqld .*--datadir=$MYSQL_DATA_DIR" >/dev/null 2>&1; then
    echo "[start.sh] Existing MySQL process found, shutting it down..."
    mysqladmin -u root -S "$MYSQL_SOCKET" shutdown >/dev/null 2>&1 \
        || mysqladmin -u root -p"$DB_PASS" -S "$MYSQL_SOCKET" shutdown >/dev/null 2>&1 \
        || pkill -f "mysqld .*--datadir=$MYSQL_DATA_DIR" || true
    for i in $(seq 1 15); do
        pgrep -f "mysqld .*--datadir=$MYSQL_DATA_DIR" >/dev/null 2>&1 || break
        sleep 1
    done
    # Force kill if still alive
    pkill -9 -f "mysqld .*--datadir=$MYSQL_DATA_DIR" >/dev/null 2>&1 || true
fi

# Clean up stale socket/pid files
rm -f "$MYSQL_SOCKET" "$MYSQL_SOCKET.lock" "$MYSQL_RUN_DIR/mysql.pid"

# Start MySQL server
echo "[start.sh] Starting MySQL..."
mysqld \
    --user=$(whoami) \
    --datadir="$MYSQL_DATA_DIR" \
    --socket="$MYSQL_SOCKET" \
    --pid-file="$MYSQL_RUN_DIR/mysql.pid" \
    --log-error="$MYSQL_LOG_DIR/error.log" \
    --port=3306 \
    --basedir="$MYSQL_BASEDIR" \
    --mysqlx=OFF \
    --bind-address=127.0.0.1 &

MYSQL_PID=$!

# Wait for MySQL to be ready (no password yet on fresh start, or with password on restart)
echo "[start.sh] Waiting for MySQL to be ready..."
MYSQL_READY=0
for i in $(seq 1 30); do
    if mysqladmin -u root -S "$MYSQL_SOCKET" status >/dev/null 2>&1; then
        MYSQL_READY=1
        echo "[start.sh] MySQL ready (no password)."
        break
    fi
    if mysqladmin -u root -p"$DB_PASS" -S "$MYSQL_SOCKET" status >/dev/null 2>&1; then
        MYSQL_READY=1
        echo "[start.sh] MySQL ready (with password)."
        break
    fi
    sleep 1
done

if [ "$MYSQL_READY" -eq 0 ]; then
    echo "[start.sh] ERROR: MySQL did not start in time."
    cat "$MYSQL_LOG_DIR/error.log" | tail -20
    exit 1
fi

# Setup database and user (idempotent)
echo "[start.sh] Setting up database..."
mysql -u root -S "$MYSQL_SOCKET" -e "
    CREATE DATABASE IF NOT EXISTS thai_2d3d_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';
    ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
" 2>/dev/null || mysql -u root -p"$DB_PASS" -S "$MYSQL_SOCKET" -e "
    CREATE DATABASE IF NOT EXISTS thai_2d3d_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    FLUSH PRIVILEGES;
" 2>&1

# Import schema if tables don't exist yet
TABLE_COUNT=$(mysql -u root -p"$DB_PASS" -S "$MYSQL_SOCKET" thai_2d3d_db -e "SHOW TABLES;" 2>/dev/null | wc -l)
if [ "$TABLE_COUNT" -lt 2 ]; then
    echo "[start.sh] Importing database schema..."
    mysql -u root -p"$DB_PASS" -S "$MYSQL_SOCKET" thai_2d3d_db < /home/runner/workspace/thai_2d3d_db.sql 2>&1 | grep -v "Warning" || true
    echo "[start.sh] Schema imported."
else
    echo "[start.sh] Database already has tables, skipping schema import."
fi

# Start PHP built-in server on port 5000
echo "[start.sh] Starting PHP server on port 5000..."
cd /home/runner/workspace
PHP_SOCK_PATH="$MYSQL_SOCKET" php -c /home/runner/workspace/php.ini -S 0.0.0.0:5000 -t /home/runner/workspace &
PHP_PID=$!

echo "[start.sh] All services started. PHP PID: $PHP_PID, MySQL PID: $MYSQL_PID"

# Wait for any process to exit
wait $PHP_PID
