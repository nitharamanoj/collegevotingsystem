<div align="center">
  <img src="logo.png" alt="College Voting System Logo" width="100" />
  <h1>🗳️ College Voting System</h1>
  <p><strong>A secure, web-based digital election platform built for colleges</strong></p>

  ![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
  ![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
  ![PHPMailer](https://img.shields.io/badge/PHPMailer-6.x-EA4335?style=for-the-badge&logo=gmail&logoColor=white)
  ![FPDF](https://img.shields.io/badge/FPDF-PDF_Reports-FF6B6B?style=for-the-badge)
  ![Composer](https://img.shields.io/badge/Composer-Dependency_Manager-885630?style=for-the-badge&logo=composer&logoColor=white)
  ![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

</div>

---

## 📋 Description

**College Voting System** is a full-featured, secure online voting platform designed for college elections. It supports student and admin portals, live election scheduling, candidate management, OTP-based email verification, and PDF report generation — all in a clean, responsive interface.

---

## ✨ Features

### 🔐 Authentication & Security
- Dual login portal — **Student** (by Student ID) and **Admin** (by Email)
- Passwords hashed with `password_hash()` (bcrypt)
- Session-based access control on every page
- OTP email verification via Gmail SMTP (PHPMailer)

### 🗳️ Voting & Elections
- Create, schedule, and manage multiple elections
- Add candidates with photos and department info
- One-vote-per-student enforcement
- Live election status (active / upcoming / closed)

### 📊 Admin Dashboard
- Real-time vote analytics and charts
- Student roster management (add / edit / delete)
- Candidate management with photo uploads
- Election timeline scheduling

### 📄 Reports & Exports
- PDF result reports generated with FPDF
- Per-election result breakdown
- Downloadable invoice-style reports

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.x (procedural + OOP) |
| **Database** | MySQL 8.0 / MariaDB |
| **Email** | PHPMailer 6.x + Gmail SMTP |
| **PDF Generation** | FPDF Library |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript |
| **Dependency Mgmt** | Composer |
| **Web Server** | Apache (XAMPP / WAMP) |

---

## 🚀 Setup Guide

### Prerequisites
- XAMPP / WAMP with PHP 8.x and MySQL
- [Composer](https://getcomposer.org/) installed globally
- A Gmail account with an [App Password](https://myaccount.google.com/apppasswords) enabled

---

### 1. Clone the Repository

```bash
git clone https://github.com/nitharamanoj/collegevotingsystem.git
cd collegevotingsystem
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure the Database

1. Start **Apache** and **MySQL** in XAMPP/WAMP
2. Open [phpMyAdmin](http://localhost/phpmyadmin)
3. Create a new database named `votesystem`
4. Import the schema:

```bash
# Import the SQL schema (ask the project owner for votesystem.sql)
mysql -u root -p votesystem < votesystem.sql
```

### 4. Configure Database Connection

```bash
# Copy the example config
cp config.example.php config.php
```

Edit `config.php` with your database credentials:

```php
$host   = "localhost";
$user   = "root";        // your DB username
$pass   = "";            // your DB password
$dbname = "votesystem";
```

### 5. Configure Email (PHPMailer)

```bash
# Copy the example mail config
cp mail_config.example.php mail_config.php
```

Edit `mail_config.php` with your Gmail credentials:

```php
$mail->Username = 'your_email@gmail.com';
$mail->Password = 'your_gmail_app_password'; // NOT your login password
```

> ⚠️ **Important:** Generate a Gmail [App Password](https://myaccount.google.com/apppasswords) — do not use your regular Gmail password.

### 6. Run the Application

Place the project folder inside your web server root:
- **XAMPP**: `C:/xampp/htdocs/votesystem/`
- **WAMP**: `C:/wamp64/www/votesystem/`

Then open your browser:
```
http://localhost/votesystem/
```

**Default Admin Credentials** (created automatically on first run):
- Email: `admin@gmail.com`
- Password: `1234`

> 🔒 Change the default admin password immediately after first login!

---

## 📁 Folder Structure

```
collegevotingsystem/
│
├── 📄 index.php                  # Login page (student + admin)
├── 📄 config.example.php         # DB config template (copy → config.php)
├── 📄 mail_config.example.php    # Mail config template (copy → mail_config.php)
├── 📄 composer.json              # Composer dependencies
│
├── 👨‍💼 Admin Panel
│   ├── admin_dashboard.php       # Admin home + stats
│   ├── admin_analytics.php       # Vote analytics & charts
│   ├── admin_candidates.php      # Candidate list
│   ├── admin_elections.php       # Election management
│   ├── admin_students.php        # Student roster
│   ├── admin_schedule.php        # Election schedule
│   └── admin_settings.php        # System settings
│
├── 🎓 Student Portal
│   ├── student_dashboard.php     # Student home
│   ├── student_login.php         # Student login
│   ├── student_results.php       # View election results
│   ├── vote.php                  # Cast vote page
│   └── submit_vote.php           # Vote submission handler
│
├── ➕ CRUD Operations
│   ├── add_candidate.php / edit_candidate.php / delete_candidate.php
│   ├── add_election.php  / edit_election.php  / delete_election.php
│   └── add_student.php   / edit_student.php   / delete_student.php
│
├── 🔧 Utilities
│   ├── sidebar.php               # Shared admin sidebar
│   ├── logout.php                # Session logout
│   ├── update_election_status.php
│   └── logo.png                  # App logo
│
├── 📦 Libraries (auto-installed by Composer)
│   ├── PHPMailer/                # Email library
│   └── fpdf/                     # PDF generation
│
├── 📁 uploads/                   # Candidate photos (runtime, not tracked)
├── 📁 invoices/                  # Generated PDFs (runtime, not tracked)
└── 📁 screenshots/               # Project screenshots
```

---

## 🔒 Security Notes

- **`config.php`** and **`mail_config.php`** are in `.gitignore` — **never commit these files**
- Email App Passwords are not your Gmail login password — generate them from [Google Account Security](https://myaccount.google.com/apppasswords)
- The `uploads/` and `invoices/` directories are excluded from version control
- The database `.sql` file should be shared securely (not committed to public repos)
- Default admin password (`1234`) **must be changed** immediately after setup
- All SQL queries use **prepared statements** to prevent SQL injection

---

## 📸 Screenshots

> Screenshots showcase the main pages of the application.

<details>
<summary>Click to expand screenshots</summary>

| Login Page | Admin Dashboard |
|:----------:|:---------------:|
| *Coming soon* | *Coming soon* |

| Student Portal | Analytics |
|:--------------:|:---------:|
| *Coming soon* | *Coming soon* |

</details>

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Commit your changes: `git commit -m "Add: your feature description"`
4. Push to the branch: `git push origin feature/your-feature-name`
5. Open a **Pull Request**

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

<div align="center">
  <p>Made with ❤️ by <a href="https://github.com/nitharamanoj">nitharamanoj</a></p>
  <p>⭐ Star this repo if you found it helpful!</p>
</div>
