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
    print_status success "- Web App: http://localhost:${WEB_PORT:-8888}"
    print_status success "- phpMyAdmin: http://localhost:${PMA_PORT:-8889}"
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

# MySQL database backup
backup() {
    check_dependencies
    BACKUP_FILE="db_backup_$(date +"%Y%m%d_%H%M%S").sql"
    print_status warning "Creating MySQL database backup: $BACKUP_FILE"
    
    # Load environment variables
    if [ -f .env ]; then
        export $(grep -v '^#' .env | xargs)
    fi
    
    docker-compose exec mysql mysqldump -u${username:-villa_user} -p${password:-securepassword} ${database:-villa_verkenner} > "db_backups/$BACKUP_FILE"
    
    if [ $? -eq 0 ]; then
        print_status success "Database backup completed! Saved to db_backups/$BACKUP_FILE"
    else
        print_status error "Database backup failed!"
    fi
}

# MySQL database restore
restore() {
    check_dependencies
    
    if [ -z "$2" ]; then
        print_status error "You must specify a backup file to restore."
        print_status warning "Usage: $0 restore <backup_file>"
        exit 1
    fi
    
    BACKUP_FILE="$2"
    
    if [ ! -f "$BACKUP_FILE" ]; then
        print_status error "Backup file not found: $BACKUP_FILE"
        exit 1
    fi
    
    print_status warning "Restoring MySQL database from: $BACKUP_FILE"
    
    # Load environment variables
    if [ -f .env ]; then
        export $(grep -v '^#' .env | xargs)
    fi
    
    cat "$BACKUP_FILE" | docker-compose exec -T mysql mysql -u${username:-villa_user} -p${password:-securepassword} ${database:-villa_verkenner}
    
    if [ $? -eq 0 ]; then
        print_status success "Database restore completed!"
    else
        print_status error "Database restore failed!"
    fi
}

# MySQL shell
db_shell() {
    check_dependencies
    print_status warning "Opening MySQL shell..."
    
    # Load environment variables
    if [ -f .env ]; then
        export $(grep -v '^#' .env | xargs)
    fi
    
    docker-compose exec mysql mysql -u${username:-villa_user} -p${password:-securepassword} ${database:-villa_verkenner}
}

# Create db_backups directory if it doesn't exist
mkdir -p db_backups

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
    restore)
        restore "$@"
        ;;
    db_shell)
        db_shell
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|rebuild|logs|test|backup|restore|db_shell}"
        echo ""
        echo "Commands:"
        echo "  start     - Start the Docker containers"
        echo "  stop      - Stop the Docker containers"
        echo "  restart   - Restart the Docker containers"
        echo "  rebuild   - Rebuild and start the Docker containers"
        echo "  logs      - View container logs"
        echo "  test      - Run PHP tests"
        echo "  backup    - Create MySQL database backup"
        echo "  restore   - Restore MySQL database from backup file"
        echo "  db_shell  - Open MySQL shell"
        exit 1
esac

exit 0 