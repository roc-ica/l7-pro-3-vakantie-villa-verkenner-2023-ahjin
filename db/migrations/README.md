# Database Migrations System

This directory contains database migration files that are used to manage the structure and data of the database.

## How to Use

1. **Create a Migration File**: 
   - Name your file with a prefix number and a descriptive name, e.g., `001_create_admin_table.sql`, `002_add_test_villas.sql`
   - The numbering ensures migrations are applied in the correct order

2. **Migration File Format**:
   ```sql
   -- Migration: [filename]
   -- Description: [description of what this migration does]
   -- Created: [date/time]

   -- Your SQL statements here...
   ```

3. **Apply Migrations**:
   - Run `php db/apply_migrations.php` from the project root
   - Only new migrations will be applied, previously applied migrations are skipped
   - Each migration is executed in a transaction to ensure all-or-nothing application

## Existing Migrations

- **001_create_admin_table.sql**: Creates the admin and login_activity tables for user authentication
- **002_add_test_villas.sql**: Adds test data for villas, features, locations, and images
- **003_update_villa_images_table.sql**: Updates the villa_images table with additional functionality including is_main column, indexes, and helper procedures

## Troubleshooting

If you encounter an error while running migrations:

1. Check the error message
2. Fix the migration file
3. Run the migration script again (`php db/apply_migrations.php`)

Note: The migration system keeps track of which migrations have been applied in the `migrations` table. 