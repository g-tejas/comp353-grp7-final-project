# Installation Guide

This guide will help you set up both the frontend (PHP) and backend (MariaDB) components of the application.

## macOS Installation

### Frontend Setup (PHP)
1. Install PHP using Homebrew:
```bash
brew install php
```

2. Start the PHP development server:
```bash
php -S 127.0.0.1:8000
```

### Backend Setup (MariaDB)
1. Install MariaDB using Homebrew:
```bash
brew install mariadb
```

2. Start the MariaDB service:
```bash
brew services start mariadb
```

3. Create and set up the database:
```bash
# Connect to MariaDB
mysql -u root

# Create the database
CREATE DATABASE cosn;

# Select the database
USE cosn;

# Import the database schema
source backend/ProjectDatabase.sql
```

### Accessing the Application
1. Open your web browser
2. Navigate to: `http://127.0.0.1:8000`

## Linux Installation

### Frontend Setup (PHP)
1. Install PHP and required extensions:
```bash
# For Ubuntu/Debian
sudo apt update
sudo apt install php php-mysql

# For Fedora
sudo dnf install php php-mysqlnd
```

2. Start the PHP development server:
```bash
php -S 127.0.0.1:8000
```

### Backend Setup (MariaDB)
1. Install MariaDB:
```bash
# For Ubuntu/Debian
sudo apt update
sudo apt install mariadb-server

# For Fedora
sudo dnf install mariadb-server
```

2. Start and enable the MariaDB service:
```bash
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

3. Create and set up the database:
```bash
# Connect to MariaDB
sudo mysql -u root

# Create the database
CREATE DATABASE cosn;

# Select the database
USE cosn;

# Import the database schema
source backend/ProjectDatabase.sql
```

### Accessing the Application
1. Open your web browser
2. Navigate to: `http://127.0.0.1:8000`

## Troubleshooting

### Common Issues
1. **MariaDB Connection Issues**
   - Ensure MariaDB service is running
   - Check database credentials in `includes/dbh.inc.php`

2. **PHP Server Issues**
   - Make sure port 8000 is not in use
   - Try a different port if needed: `php -S 127.0.0.1:8080`

3. **Database Import Errors**
   - Ensure you're in the correct directory when running the source command
   - Check file permissions for ProjectDatabase.sql

### Security Notes
- For production deployment, configure proper authentication for MariaDB
- Update database credentials in configuration files
- Use appropriate firewall rules
- Configure PHP for production use 