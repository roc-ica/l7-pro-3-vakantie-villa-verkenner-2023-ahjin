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

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Make the management script executable:
```bash
chmod +x villa-verkenner.sh
```

4. Start the project:
```bash
./villa-verkenner.sh start
```

The application will be available at:
- Web Application: http://localhost:8000

## Available Commands
Use the management script for common operations:
```bash
./villa-verkenner.sh start    # Start the server
./villa-verkenner.sh stop     # Stop the server
./villa-verkenner.sh restart  # Restart the server
./villa-verkenner.sh rebuild  # Rebuild containers
./villa-verkenner.sh logs     # View logs
./villa-verkenner.sh backup   # Backup database
```

For more detailed commands, see `COMMANDS.md`

## Development
- The project uses PHP 8.1 with Apache
- SQLite database is located in `db/` directory
- Frontend files are in `frontend/` directory

## Troubleshooting
If you encounter any issues:
1. Check if Docker is running
2. Try rebuilding the containers: `./villa-verkenner.sh rebuild`
3. Check the logs: `./villa-verkenner.sh logs`

## Team Members
See `taken.yml` for task assignments 