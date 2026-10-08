# 📝 To-Do List Web Application

A simple and responsive **To-Do List Web Application** built with PHP and MySQL to help users organize, manage, and track their daily tasks efficiently.

The application supports user accounts, task management, categories, priorities, deadlines, and task completion tracking.

---

## ✨ Features

### 👤 User Management

* User registration
* User login
* User profile
* Password stored using secure password hashing
* Last login tracking

### ✅ Task Management

* Create new tasks
* Edit existing tasks
* Delete tasks
* Mark tasks as completed
* Track pending and completed tasks
* Add task descriptions
* Set task deadlines

### 🏷️ Categories

* Create task categories
* Assign categories to tasks
* Manage categories for each user

### 🚨 Priority System

Tasks can have different priority levels:

* 🟢 **Low**
* 🟡 **Medium**
* 🔴 **High**

### 📱 Responsive Interface

Designed to provide a clean and usable experience across:

* 💻 Desktop
* 📱 Mobile
* 🖥️ Tablet

---

## 🛠️ Tech Stack

| Technology     | Usage                            |
| -------------- | -------------------------------- |
| **HTML5**      | Web page structure               |
| **CSS3**       | Styling and responsive interface |
| **JavaScript** | Client-side interaction          |
| **PHP**        | Backend / server-side logic      |
| **MySQL**      | Database management              |
| **Apache**     | Local web server                 |
| **XAMPP**      | Local development environment    |

---

## 📂 Project Structure

```text
todolist-web/
│
├── actions/
│   └── ...
│
├── assets/
│   └── ...
│
├── config/
│   └── ...
│
├── includes/
│   └── ...
│
├── .htaccess
├── database.sql
├── dashboard.php
├── index.php
├── profile.php
├── register.php
├── tasks.php
└── README.md
```

---

## 🗄️ Database

This project uses **MySQL** with the database:

```text
focuslist_db
```

The database structure includes three main tables:

### `users`

Stores user account information.

```text
id
name
email
password_hash
last_login_at
created_at
updated_at
```

### `categories`

Stores task categories belonging to each user.

```text
id
user_id
name
created_at
updated_at
```

### `tasks`

Stores the user's tasks.

```text
id
user_id
category_id
title
description
priority
status
deadline
completed_at
created_at
updated_at
```

The database relationships ensure that each user's tasks and categories are associated with their account.

---

# 🚀 Getting Started

Follow these steps to run the project locally.

## 📋 Prerequisites

Make sure you have installed:

* [XAMPP](https://www.apachefriends.org/)
* Git
* A modern web browser

---

## 1. Clone the Repository

Open **Command Prompt**, PowerShell, or Git Bash:

```bash
git clone https://github.com/dzcknf/todolist-web.git
```

Navigate into the project directory:

```bash
cd todolist-web
```

---

## 2. Move the Project to XAMPP

Copy the project folder into the XAMPP `htdocs` directory.

Usually:

```text
C:\xampp\htdocs\
```

The final structure should look like:

```text
C:\xampp\htdocs\todolist-web\
```

---

## 3. Start XAMPP

Open **XAMPP Control Panel** and start:

```text
Apache
MySQL
```

Make sure both services are running.

---

## 4. Create the Database

Open:

```text
http://localhost/phpmyadmin
```

Then import the provided:

```text
database.sql
```

The SQL file will automatically create the database:

```text
focuslist_db
```

and its required tables.

---

## 5. Configure the Database Connection

Check the database configuration inside:

```text
config/
```

Make sure the MySQL credentials match your local XAMPP configuration.

The default XAMPP configuration is commonly:

```text
Host     : localhost
Username : root
Password : 
Database : focuslist_db
```

> If you changed your MySQL username or password, update the configuration accordingly.

---

## 6. Run the Application

Open your browser and visit:

```text
http://localhost/todolist-web/
```

You should now see the To-Do List application.

---

# 🧑‍💻 How to Use

### 1. Register

Create a new account through the registration page.

### 2. Login

Log in using your registered email and password.

### 3. Create a Task

Add a task with information such as:

* Task title
* Description
* Category
* Priority
* Deadline

### 4. Manage Tasks

You can:

* Edit tasks
* Delete tasks
* Change task status
* Mark tasks as completed

### 5. Organize Tasks

Use categories, priorities, and deadlines to organize your tasks more efficiently.

---

# 🔐 Security

This project includes several basic security practices, including:

* Password hashing
* User-specific task ownership
* Database foreign-key relationships
* Session-based authentication
* MySQL constraints
* Cascading relationships for user data

> This project is intended primarily for learning and local development. Additional security hardening is recommended before deploying it to a production environment.

---

# 🎯 Project Goals

This project was created as a practical project for learning and implementing:

* PHP web development
* MySQL database design
* CRUD operations
* Authentication
* Session management
* Relational database relationships
* Front-end development
* Responsive web design

---

# 🔮 Future Improvements

Possible improvements for future versions:

* [ ] Dark mode
* [ ] Task search
* [ ] Task filtering
* [ ] Task sorting
* [ ] Better dashboard statistics
* [ ] Task notifications
* [ ] Password reset
* [ ] Email verification
* [ ] More advanced user settings
* [ ] Deployment to a public server
* [ ] REST API
* [ ] Progressive Web App (PWA) support

---

# 📌 Project Status

🟢 **Active Development**

The project is still being developed and improved. Features and interface may change in future updates.

---

# 👨‍💻 Author

GitHub: [@dzcknf](https://github.com/dzcknf)

---

# ⭐ Support

If you find this project useful or interesting, consider giving the repository a ⭐ on GitHub.

---

## 📄 License

This project is available for educational and personal development purposes.
