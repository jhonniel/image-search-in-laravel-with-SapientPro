#!/bin/bash

# Laravel Development Server Auto-Restart Script
# This script will automatically restart the server if it crashes

PORT=8000
HOST=127.0.0.1
LOG_FILE="server.log"
PID_FILE="server.pid"

# Function to start the server
start_server() {
    echo "Starting Laravel development server..."
    # Set timeout to 300 seconds (5 minutes) to prevent fatal timeout errors
    # Suppress broken pipe errors by:
    # 1. Setting display_errors=0 to suppress error display
    # 2. Setting error_reporting=0 to suppress all error reporting
    # 3. Redirecting stderr to /dev/null (broken pipe errors are harmless)
    php -d max_execution_time=300 \
        -d default_socket_timeout=60 \
        -d display_errors=0 \
        -d error_reporting=0 \
        artisan serve --host=$HOST --port=$PORT > $LOG_FILE 2>/dev/null &
    SERVER_PID=$!
    echo $SERVER_PID > $PID_FILE
    echo "Server started with PID: $SERVER_PID"
    echo "Access at: http://$HOST:$PORT"
    echo "Logs: tail -f $LOG_FILE"
    return $SERVER_PID
}

# Function to stop the server
stop_server() {
    if [ -f $PID_FILE ]; then
        PID=$(cat $PID_FILE)
        if ps -p $PID > /dev/null 2>&1; then
            echo "Stopping server (PID: $PID)..."
            kill $PID 2>/dev/null
            sleep 1
            kill -9 $PID 2>/dev/null
        fi
        rm -f $PID_FILE
    fi
    pkill -f "php artisan serve" 2>/dev/null
    echo "Server stopped"
}

# Trap to handle script exit
trap 'stop_server; exit' INT TERM

# Main loop
while true; do
    if [ -f $PID_FILE ]; then
        PID=$(cat $PID_FILE)
        if ! ps -p $PID > /dev/null 2>&1; then
            echo "$(date): Server crashed, restarting..."
            rm -f $PID_FILE
        fi
    fi
    
    if [ ! -f $PID_FILE ]; then
        start_server
    fi
    
    # Wait 5 seconds before checking again
    sleep 5
done
