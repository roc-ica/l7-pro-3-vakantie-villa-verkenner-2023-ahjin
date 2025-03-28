# Villa Verkenner Project

## Prerequisites
- Docker
- Docker Compose
- Git

## Quick Start
1. Clone the repository:
```bash
git clone [repository-url]
cd [project-directory]
```

### Linux/macOS:
2. Make the management script executable:
```bash
chmod +x villa-verkenner.sh
```

3. Set up and start the project:
```bash
./villa-verkenner.sh start
```

### Windows:
2. Set up and start the project using the batch script:
```cmd
villa-verkenner.bat setup
```

The application will be available at:
- Web Application: http://localhost:8888
- phpMyAdmin: http://localhost:8889

## Available Commands

### Linux/macOS:
```bash
./villa-verkenner.sh start    # Start the server
./villa-verkenner.sh stop     # Stop the server
./villa-verkenner.sh restart  # Restart the server
./villa-verkenner.sh rebuild  # Rebuild containers
./villa-verkenner.sh logs     # View logs
./villa-verkenner.sh backup   # Backup database
./villa-verkenner.sh restore  # Restore database backup
./villa-verkenner.sh db_shell # Access MySQL shell
```

### Windows:
```cmd
villa-verkenner.bat setup     # First-time setup
villa-verkenner.bat start     # Start the server
villa-verkenner.bat stop      # Stop the server
villa-verkenner.bat restart   # Restart the server
villa-verkenner.bat rebuild   # Rebuild containers
villa-verkenner.bat logs      # View logs
villa-verkenner.bat backup    # Backup database
villa-verkenner.bat restore   # Restore database backup
villa-verkenner.bat db_shell  # Access MySQL shell
```

For more detailed commands, see `COMMANDS.md`

## Development
- The project uses PHP 8.1 with Apache
- MySQL 8.0 database with phpMyAdmin
- Frontend files are in `frontend/` directory

## Troubleshooting
If you encounter any issues:
1. Check if Docker is running
2. Try rebuilding the containers: `./villa-verkenner.sh rebuild` or `villa-verkenner.bat rebuild`
3. Check the logs: `./villa-verkenner.sh logs` or `villa-verkenner.bat logs`

## Team Members
See `taken.yml` for task assignments 