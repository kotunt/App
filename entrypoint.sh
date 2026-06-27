#!/bin/sh

# Exit immediately if a command exits with a non-zero status.
set -e

# Load the crontab file
crontab /etc/cron/crontab

# Start the cron daemon in the foreground
# The -f flag is important to keep the container running
exec crond -f -l 8