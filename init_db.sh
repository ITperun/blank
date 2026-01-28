#!/bin/bash

# Start MariaDB in safe mode with --skip-grant-tables
/usr/bin/mysqld_safe --skip-grant-tables &
sleep 5

# Create the database
mysql -u root -e "CREATE DATABASE mydb;"

# Import data into the database
mysql -u root mydb < /tmp/import.sql

# Stop the MariaDB instance started with --skip-grant-tables
mysqladmin shutdown

# Start MariaDB normally
/usr/bin/mysqld_safe &
sleep 5

# Create user and grant privileges
mysql -u root -e "CREATE USER 'admin'@'%' IDENTIFIED BY 'password';"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%' WITH GRANT OPTION;"
mysql -u root -e "FLUSH PRIVILEGES;"

# Optional: Stop MariaDB after initialization (if necessary for the build process)
mysqladmin shutdown