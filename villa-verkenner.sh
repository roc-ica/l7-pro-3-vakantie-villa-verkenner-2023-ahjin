#!/bin/bash

# Villa Verkenner Management Script

# Color codes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print status messages
print_status() {
    case "$1" in
        success)
            echo -e "${GREEN}$2${NC}"
            ;;
        warning)
            echo -e "${YELLOW}$2${NC}"
            ;;
        error)
            echo -e "${RED}$2${NC}"
            ;;
        *)
            echo "$2"
            ;;
    esac
}

# Check if Docker and Docker Compose are installed
check_dependencies() {
    if ! command -v docker &> /dev/null; then
        print_status error "Docker is not installed!"
        exit 1
    fi

    if ! command -v docker-compose &> /dev/null; then
        print_status error "Docker Compose is not installed!"
        exit 1
    fi
}

# Start the server
start() {
    check_dependencies
    print_status warning "Starting Villa Verkenner Docker containers..."
    docker-compose up -d
    print_status success "Containers started successfully!"
    print_status warning "Access your application at:"
    print_status success "- Web App: http://localhost:8000"
}

# Stop the server
stop() {
    check_dependencies
    print_status warning "Stopping Villa Verkenner Docker containers..."
    docker-compose down
    print_status success "Containers stopped successfully!"
}

# Restart the server
restart() {
    check_dependencies
    print_status warning "Restarting Villa Verkenner Docker containers..."
    docker-compose down
    docker-compose up -d
    print_status success "Containers restarted successfully!"
}

# Rebuild the server
rebuild() {
    check_dependencies
    print_status warning "Rebuilding Villa Verkenner Docker containers..."
    docker-compose down
    docker-compose up -d --build
    print_status success "Containers rebuilt successfully!"
}

# View logs
logs() {
    check_dependencies
    print_status warning "Showing Villa Verkenner container logs..."
    docker-compose logs
}

# Run tests
test() {
    check_dependencies
    print_status warning "Running PHP tests..."
    docker-compose exec webserver composer test
}

# SQLite database backup
backup() {
    check_dependencies
    BACKUP_FILE="db_backup_$(date +"%Y%m%d_%H%M%S").sqlite"
    print_status warning "Creating SQLite database backup: $BACKUP_FILE"
    docker-compose exec webserver cp /var/www/html/db/villaverkenner.sqlite "/var/www/html/db/$BACKUP_FILE"
    print_status success "Database backup completed!"
}

# Main script logic
case "$1" in
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    rebuild)
        rebuild
        ;;
    logs)
        logs
        ;;
    test)
        test
        ;;
    backup)
        backup
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|rebuild|logs|test|backup}"
        exit 1
esac

exit 0 