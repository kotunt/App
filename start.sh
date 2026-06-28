#!/bin/bash
#
# start.sh - Development Environment Startup Script
# This script sets up and runs a local MySQL server and the PHP built-in server.
# It's designed for ephemeral environments like Codespaces or Replit.
#
set -e

# --- Configuration ---
# Get the absolute path of the directory where this script is located.
WORKSPACE_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

# Use a local hidden directory for MySQL data, logs, and run files.
DEV_DATA_DIR="${WORKSPACE_DIR}/.dev-data"
MYSQL_DATA_DIR="${DEV_DATA_DIR}/mysql-data"
MYSQL_RUN_DIR="${DEV_DATA_DIR}/mysql-run"
MYSQL_LOG_DIR="${DEV_DATA_DIR}/mysql-logs"
MYSQL_SOCKET="${MYSQL_RUN_DIR}/mysql.sock"
MYSQL_BASEDIR=$(dirname "$(dirname "$(which mysqld || echo '/usr')")")
DB_PASS="${DB_PASS:-secret}" # Default password is 'secret'

# --- Helper Functions ---
log() {
    echo "[start.sh] $1"
}

cleanup() {
    log "Shutting down services..."
    # Kill the background processes on script exit
    [ -n "$PHP_PID" ] && kill "$PHP_PID" 2>/dev/null
    [ -n "$MYSQL_PID" ] && kill "$MYSQL_PID" 2>/dev/null
    exit 0
}

# Trap script exit signals to run the cleanup function
trap cleanup SIGINT SIGTERM

# Create required directories
log "Creating required directories..."
mkdir -p "$MYSQL_DATA_DIR" "$MYSQL_RUN_DIR" "$MYSQL_LOG_DIR"
mkdir -p "${WORKSPACE_DIR}/logs" "${WORKSPACE_DIR}/uploads" "${WORKSPACE_DIR}/backups"

# Shut down any MySQL instance still holding the data directory lock from a
# previous run (otherwise the new mysqld fails with "Unable to lock ./ibdata1").
if pgrep -f "mysqld .*--datadir=$MYSQL_DATA_DIR" >/dev/null 2>&1; then
    log "Existing MySQL process found, shutting it down..."
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

# Initialize MySQL data directory if not already done
if [ ! -d "$MYSQL_DATA_DIR/mysql" ]; then
    log "Initializing MySQL data directory..."
    mysqld --initialize-insecure \
        --user="$(whoami)" \
        --datadir="$MYSQL_DATA_DIR" \
        --basedir="$MYSQL_BASEDIR" 2>&1
    log "MySQL initialized."
fi

# Clean up stale socket/pid files
rm -f "$MYSQL_SOCKET" "$MYSQL_SOCKET.lock" "$MYSQL_RUN_DIR/mysql.pid"

# Start MySQL server
log "Starting MySQL..."
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
log "Waiting for MySQL to be ready..."
MYSQL_READY=0
for i in $(seq 1 30); do
    if mysqladmin -u root -S "$MYSQL_SOCKET" status >/dev/null 2>&1; then
        MYSQL_READY=1
        log "MySQL ready (no password)."
        break
    fi
    if mysqladmin -u root -p"$DB_PASS" -S "$MYSQL_SOCKET" status >/dev/null 2>&1; then
        MYSQL_READY=1
        log "MySQL ready (with password)."
        break
    fi
    sleep 1
done

if [ "$MYSQL_READY" -eq 0 ]; then
    log "ERROR: MySQL did not start in time."
    cat "$MYSQL_LOG_DIR/error.log" | tail -20
    exit 1
fi

# Setup database and user (idempotent)
log "Setting up database..."
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
TABLE_COUNT=$(mysql -u root -p"$DB_PASS" -sN -S "$MYSQL_SOCKET" thai_2d3d_db -e "SHOW TABLES;" 2>/dev/null | grep -c .)
if [ "$TABLE_COUNT" -eq 0 ]; then
    log "Database is empty. Importing schema..."
    mysql -u root -p"$DB_PASS" -S "$MYSQL_SOCKET" thai_2d3d_db < "${WORKSPACE_DIR}/database/thai_2d3d_db.sql" 2>&1 | grep -v "Warning" || true
    log "Schema imported."
else
    log "Database already has tables, skipping schema import."
fi

# Start PHP built-in server on port 5000
log "Starting PHP server on http://0.0.0.0:5000"
cd "$WORKSPACE_DIR"
PHP_SOCK_PATH="$MYSQL_SOCKET" php -c "${WORKSPACE_DIR}/php.ini" -S 0.0.0.0:5000 -t "$WORKSPACE_DIR" &
PHP_PID=$!

log "All services started. PHP PID: $PHP_PID, MySQL PID: $MYSQL_PID"
log "Press Ctrl+C to stop."

# Wait for any process to exit
wait
