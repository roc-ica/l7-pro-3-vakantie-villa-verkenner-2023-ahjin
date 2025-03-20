# Villa Verkenner - Command Reference

## Docker Compose Commands

### Server Management
```bash
# Start server in background
docker-compose up -d

# Stop server and remove containers
docker-compose down

# Rebuild and start server
docker-compose up -d --build

# View running containers
docker-compose ps

# View all container logs
docker-compose logs

# View specific container logs (e.g., webserver)
docker-compose logs webserver
```

## Dependency Management
```bash
# Install PHP dependencies
docker-compose exec webserver composer install

# Update dependencies
docker-compose exec webserver composer update

# Run PHP unit tests
docker-compose exec webserver composer test

# Run code style checker
docker-compose exec webserver composer lint

# Automatically fix code style issues
docker-compose exec webserver composer fix
```

## SQLite Database Management
```bash
# Access SQLite database directly
docker-compose exec webserver sqlite3 /var/www/html/db/villaverkenner.sqlite

# Create a backup of the SQLite database
docker-compose exec webserver cp /var/www/html/db/villaverkenner.sqlite /var/www/html/db/villaverkenner_backup.sqlite

# Restore a backup
docker-compose exec webserver cp /var/www/html/db/villaverkenner_backup.sqlite /var/www/html/db/villaverkenner.sqlite
```

## PHP and Apache Management
```bash
# Check PHP version
docker-compose exec webserver php -v

# Restart Apache service
docker-compose exec webserver service apache2 restart

# Run PHP script
docker-compose exec webserver php your_script.php
```

## Development Utilities
```bash
# Generate autoload files
docker-compose exec webserver composer dump-autoload

# Clear PHP opcache
docker-compose exec webserver php -r 'opcache_reset();'
```

## Docker System Management
```bash
# Remove all containers, networks, volumes
docker-compose down -v

# Prune unused Docker resources
docker system prune -a

# Remove all unused images
docker image prune -a

# List all Docker volumes
docker volume ls

# Remove specific volume
docker volume rm volume_name
```

## Debugging and Monitoring
```bash
# Show resource usage of containers
docker stats

# Inspect a specific container
docker-compose inspect webserver

# View container environment variables
docker-compose exec webserver env

# Check container network configuration
docker network inspect villa_verkenner_network
```

## Application Access
- **Web Application**: http://localhost:8000

## Troubleshooting Workflow
1. Check container status: `docker-compose ps`
2. View logs: `docker-compose logs`
3. Rebuild containers: `docker-compose up -d --build`

## Common Issues and Solutions
- **Port already in use**: 
  - Stop conflicting services
  - Change port mappings in `docker-compose.yml`

- **Permission issues**:
  ```bash
  # Fix file permissions
  docker-compose exec webserver chown -R www-data:www-data /var/www/html
  ```

- **Composer memory limit**:
  ```bash
  # Increase memory limit
  docker-compose exec webserver php -d memory_limit=-1 /usr/bin/composer install
  ```

## Quick Start Script
Create a `villa-verkenner.sh` in your project root:

```bash
#!/bin/bash

# Villa Verkenner Management Script

case "$1" in
    start)
        docker-compose up -d
        ;;
    stop)
        docker-compose down
        ;;
    restart)
        docker-compose down
        docker-compose up -d
        ;;
    rebuild)
        docker-compose up -d --build
        ;;
    logs)
        docker-compose logs
        ;;
    test)
        docker-compose exec webserver composer test
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|rebuild|logs|test}"
        exit 1
esac

exit 0
```

Make it executable:
```bash
chmod +x villa-verkenner.sh
```

Usage:
```bash
./villa-verkenner.sh start
./villa-verkenner.sh stop
./villa-verkenner.sh restart
```

## Contributing
- Always pull latest changes before starting work
- Use feature branches
- Run tests before committing
- Follow coding standards

## Notes
- Keep `.env` file secure and out of version control
- Regularly backup your SQLite database
- Monitor container resource usage 