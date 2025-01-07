# PHP-projects
# User Management System

A robust and flexible User Management System built using PHP and MySQL, featuring role-based access control (RBAC), CRUD operations, and dynamic permission management.

---

## Features

- **User Management**: Add, edit, delete, and view user details.
- **Role Management**: Assign roles to users with module-based permissions.
- **Permission Control**: Define hierarchical permissions (view, add, edit, delete) for each module.
- **Authentication**: Secure login and session-based authentication.
- **Dynamic Rights Check**: Only users with appropriate rights can access specific actions.
- **File Uploads**: Supports profile picture uploads for users.
- **Pagination**: Manage listings with pagination for improved performance.
- **Validation**: Comprehensive validation for forms and input fields.
- **Responsive Design**: Optimized for desktop and mobile devices.

---

## Table of Contents

1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Database Schema](#database-schema)
4. [Usage](#usage)
5. [Directory Structure](#directory-structure)
6. [Helper Functions](#helper-functions)
7. [Contributing](#contributing)
8. [License](#license)

---

## Installation

1. **Clone the Repository**  
   ```bash
   git clone https://github.com/your-repo/user-management-system.git
   cd user-management-system
   ```

2. **Set Up the Database**  
   - Import the `database.sql` file into your MySQL server.
   - Ensure the required tables are created (`users`, `roles`, `modules`, `rights`, etc.).

3. **Configure Database Connection**  
   Edit the `common/dbconnections.php` file:
   ```php
   $host = 'localhost';
   $dbname = 'your_database_name';
   $username = 'your_database_user';
   $password = 'your_database_password';
   ```

4. **Run the Application**  
   - Place the project folder in your local server directory (e.g., `htdocs` for WAMP/XAMPP).
   - Access the application via `http://localhost/user-management-system`.

---

## Configuration

- **Base URL**: Define your application's base URL in a configuration file (e.g., `common/helpers.php`):
  ```php
  define('BASE_URL', 'http://localhost/user-management-system');
  define('IMAGE_URL', BASE_URL . '/uploads/images/profile/');
  ```

- **Folder Permissions**: Ensure `uploads/images/profile/` is writable for image uploads.

---

## Database Schema

### Tables Overview

1. **Users** (`users`)  
   - Stores user information (name, email, password, status, etc.).

2. **Roles** (`roles`)  
   - Defines roles for users (e.g., Admin, Editor, Viewer).

3. **Modules** (`modules`)  
   - Represents application modules (e.g., Dashboard, Users, Roles).

4. **Rights** (`rights`)  
   - Lists rights (view, add, edit, delete) available for each module.

5. **Role-Module Permissions** (`roles_modules_permissions`)  
   - Links roles to modules with permissions.

6. **Role-Permission Rights** (`roles_modules_permissions_rights`)  
   - Links specific rights to role-module permissions.

---

## Usage

### User Management
1. **Add User**: Navigate to `Users > Add User`.
2. **Edit User**: Click "Edit" in the user listing.
3. **Delete User**: Click "Delete" (if you have the delete right).

### Role Management
1. **Add Role**: Navigate to `Roles > Add Role`. Assign permissions hierarchically.
2. **Edit Role**: Modify role details and permissions.
3. **Delete Role**: Remove roles (restricted if associated with users).

### Rights Management
- Permissions are assigned during role creation/editing in a hierarchical view (Modules → Rights).

---

## Directory Structure

```
user-management-system/
├── common/
│   ├── dbconnections.php       # Database connection
│   ├── helpers.php             # Utility functions
│   ├── middleware.php          # Authentication middleware
├── uploads/
│   └── images/
│       └── profile/            # User profile pictures
├── users/
│   ├── users_add.php           # Add user
│   ├── users_edit.php          # Edit user
│   ├── users_delete.php        # Delete user
│   ├── users_detail.php        # View user details
│   ├── users_listing.php       # List users
├── roles/
│   ├── roles_add.php           # Add role
│   ├── roles_edit.php          # Edit role
│   ├── roles_delete.php        # Delete role
│   ├── roles_detail.php        # View role details
│   ├── roles_listing.php       # List roles
├── index.php                   # Dashboard
└── README.md                   # Documentation
```

---

## Helper Functions

**Rights Checking**
```php
hasAddRight($userId, $moduleId, $pdo);
hasEditRight($userId, $moduleId, $pdo);
hasDeleteRight($userId, $moduleId, $pdo);
```

**Folder Management**
```php
createFolderIfNotExists($path);
```

**Validation**
```php
isValidEmail($email);
isValidPhoneNumber($phone);
```

---

## Contributing

Contributions are welcome! Please follow these steps:
1. Fork the repository.
2. Create a new feature branch.
3. Submit a pull request.

---

## License

This project is licensed under the [MIT License](LICENSE).

