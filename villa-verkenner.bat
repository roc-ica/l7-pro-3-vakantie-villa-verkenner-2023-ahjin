@echo off
setlocal enabledelayedexpansion

REM Villa Verkenner Windows Management Script

REM Skip using color codes since they don't work reliably on all Windows systems
REM Use prefixes instead

REM Function to print status messages
:print_status
set "type=%~1"
set "message=%~2"

if "%type%"=="GREEN" (
    echo [SUCCESS] %message%
) else if "%type%"=="YELLOW" (
    echo [INFO] %message%
) else if "%type%"=="RED" (
    echo [ERROR] %message%
) else (
    echo %message%
)
goto :eof

REM Check if Docker is installed
:check_dependencies
where docker >nul 2>nul
if %ERRORLEVEL% neq 0 (
    call :print_status RED "Docker is not installed or not in PATH!"
    exit /b 1
)

where docker-compose >nul 2>nul
if %ERRORLEVEL% neq 0 (
    call :print_status RED "Docker Compose is not installed or not in PATH!"
    exit /b 1
)
goto :eof

REM Create required directories
if not exist db_backups mkdir db_backups

REM Start the server
:start
call :check_dependencies
call :print_status YELLOW "Starting Villa Verkenner Docker containers..."
docker-compose up -d
call :print_status GREEN "Containers started successfully!"
call :print_status YELLOW "Access your application at:"
call :print_status GREEN "- Web App: http://localhost:8888"
call :print_status GREEN "- phpMyAdmin: http://localhost:8889"
goto :eof

REM Stop the server
:stop
call :check_dependencies
call :print_status YELLOW "Stopping Villa Verkenner Docker containers..."
docker-compose down
call :print_status GREEN "Containers stopped successfully!"
goto :eof

REM Restart the server
:restart
call :check_dependencies
call :print_status YELLOW "Restarting Villa Verkenner Docker containers..."
docker-compose down
docker-compose up -d
call :print_status GREEN "Containers restarted successfully!"
goto :eof

REM Rebuild the server
:rebuild
call :check_dependencies
call :print_status YELLOW "Rebuilding Villa Verkenner Docker containers..."
docker-compose down
docker-compose up -d --build
call :print_status GREEN "Containers rebuilt successfully!"
goto :eof

REM View logs
:logs
call :check_dependencies
call :print_status YELLOW "Showing Villa Verkenner container logs..."
docker-compose logs
goto :eof

REM Run tests
:test
call :check_dependencies
call :print_status YELLOW "Running PHP tests..."
docker-compose exec webserver composer test
goto :eof

REM MySQL database backup
:backup
call :check_dependencies
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set datestr=%%c%%a%%b)
for /f "tokens=1-2 delims=: " %%a in ('time /t') do (set timestr=%%a%%b)
set "BACKUP_FILE=db_backup_%datestr%_%timestr%.sql"
call :print_status YELLOW "Creating MySQL database backup: %BACKUP_FILE%"

REM Load environment variables
if exist .env (
    for /f "tokens=*" %%a in (.env) do (
        set "line=%%a"
        if "!line:~0,1!" neq "#" (
            set "!line!"
        )
    )
)

docker-compose exec mysql mysqldump -u%username% -p%password% %database% > "db_backups\%BACKUP_FILE%"
if %ERRORLEVEL% equ 0 (
    call :print_status GREEN "Database backup completed! Saved to db_backups\%BACKUP_FILE%"
) else (
    call :print_status RED "Database backup failed!"
)
goto :eof

REM MySQL database restore
:restore
call :check_dependencies
if "%~2"=="" (
    call :print_status RED "You must specify a backup file to restore."
    call :print_status YELLOW "Usage: %0 restore <backup_file>"
    exit /b 1
)

set "BACKUP_FILE=%~2"
if not exist "%BACKUP_FILE%" (
    call :print_status RED "Backup file not found: %BACKUP_FILE%"
    exit /b 1
)

call :print_status YELLOW "Restoring MySQL database from: %BACKUP_FILE%"

REM Load environment variables
if exist .env (
    for /f "tokens=*" %%a in (.env) do (
        set "line=%%a"
        if "!line:~0,1!" neq "#" (
            set "!line!"
        )
    )
)

type "%BACKUP_FILE%" | docker-compose exec -T mysql mysql -u%username% -p%password% %database%
if %ERRORLEVEL% equ 0 (
    call :print_status GREEN "Database restore completed!"
) else (
    call :print_status RED "Database restore failed!"
)
goto :eof

REM MySQL shell
:db_shell
call :check_dependencies
call :print_status YELLOW "Opening MySQL shell..."

REM Load environment variables
if exist .env (
    for /f "tokens=*" %%a in (.env) do (
        set "line=%%a"
        if "!line:~0,1!" neq "#" (
            set "!line!"
        )
    )
)

docker-compose exec mysql mysql -u%username% -p%password% %database%
goto :eof

REM Setup project
:setup
call :print_status YELLOW "Setting up Villa Verkenner project..."

REM Check if .env exists, if not copy from .env.example
if not exist .env (
    if exist .env.example (
        copy .env.example .env
        call :print_status GREEN "Created .env file from .env.example"
    ) else (
        call :print_status RED "No .env.example file found. Please create a .env file manually."
        exit /b 1
    )
)

REM Start the containers
call :start
call :print_status GREEN "Setup completed successfully!"
goto :eof

REM Main script logic
if "%1"=="start" (
    call :start
) else if "%1"=="stop" (
    call :stop
) else if "%1"=="restart" (
    call :restart
) else if "%1"=="rebuild" (
    call :rebuild
) else if "%1"=="logs" (
    call :logs
) else if "%1"=="test" (
    call :test
) else if "%1"=="backup" (
    call :backup
) else if "%1"=="restore" (
    call :restore %*
) else if "%1"=="db_shell" (
    call :db_shell
) else if "%1"=="setup" (
    call :setup
) else (
    echo Usage: %0 {setup^|start^|stop^|restart^|rebuild^|logs^|test^|backup^|restore^|db_shell}
    echo.
    echo Commands:
    echo   setup     - Set up the project (first time setup)
    echo   start     - Start the Docker containers
    echo   stop      - Stop the Docker containers
    echo   restart   - Restart the Docker containers
    echo   rebuild   - Rebuild and start the Docker containers
    echo   logs      - View container logs
    echo   test      - Run PHP tests
    echo   backup    - Create MySQL database backup
    echo   restore   - Restore MySQL database from backup file
    echo   db_shell  - Open MySQL shell
    exit /b 1
)

endlocal 