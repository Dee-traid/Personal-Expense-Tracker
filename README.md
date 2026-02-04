## Personal Expense Tracker 

A professional-grade Command Line Interface (CLI) application built to help users manage their finances effectively. This project was developed with a primary focus on mastering **Object-Oriented Programming (OOP)** principles and the **Model-View-Controller (MVC)** architectural pattern.

## 🎯 Project Purpose

This project serves as a comprehensive learning journey into backend development. The core objectives were:

* **OOP Mastery**: Implementing Encapsulation, Inheritance, and Polymorphism.
* **MVC Architecture**: Separating business logic (Models) from user interface (Views) and request handling (Controllers).
* **Email Integration**: Leveraging SMTP services to bridge the gap between application logic and real-world user notifications.
* **Data Integrity**: Managing complex relational data using PostgreSQL.

## 🚀 Key Features

* **🔐 User Authentication**: Secure Registration/Login with password recovery and reset functionality.
* **📊 Comprehensive Expense Management**:
* Full CRUD (Create, Read, Update, Delete) for expenses.
* Categorization of spending for better organization.


* **💰 Smart Budgeting**:
* Set monthly limits for specific categories.
* **Automated Alerts**: Instant email notifications via PHPMailer when expenses exceed your defined budget.


* **🔎 Advanced Filtering & Reporting**:
* Filter expenses by Day, Month, or Year.
* Search functionality by category name and date range.
* Statistical analysis (Average spending, Highest/Lowest expenses).


* **📅 Monthly Summaries**: Automated email reports detailing your financial performance.

## 🏗️ Technical Stack

* **Language**: PHP 8.x
* **Pattern**: MVC (Model-View-Controller)
* **Database**: PostgreSQL
* **Dependencies**:
* `vlucas/phpdotenv` (Environment Variable Management)
* `phpmailer/phpmailer` (Email Service)


* **Security**: Environment-based configuration, Password Hashing, and Prepared Statements (SQL Injection prevention).

## 📂 Project Structure

```text
App/
├── core/               # DatabaseHelper & Utility Functions
├── Controllers/        # Auth, Expense, Budget, and Category Logic
├── Models/             # Data Entities (User, Expense, Budget, Category)
├── Services/           # EmailService, EmailHelper, EmailTemplates
├── Views/              
│   ├── Inputs/         # CLI Input Handlers
│   └── UIDisplay.php   # CLI Table and Report Formatting
├── .env                # Sensitive Configuration (Ignored by Git)
└── index.php            # Application Entry Point

```

## 🛠️ Installation & Setup

1. **Clone the repository:**
```bash
git clone https://github.com/Dee-Traid/Personal-Expense-Tracker.git
cd Personal-Expense-Tracker

```


2. **Install Dependencies:**
```bash
composer install

```


3. **Environment Setup:**
Create a `.env` file in the root directory and add your credentials:
```ini
DB_HOST=localhost
DB_NAME=expense_tracker
DB_USER=your_postgres_user
DB_PASS=your_password

SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password

```


4. **Database Migration:**
Execute the SQL queries found in the `Database Schema` section of the documentation to set up your PostgreSQL tables.
5. **Run the Application:**
```bash
php index.php

```



## 📈 Roadmap & Progress

* [x] Week 1-2: Database logic & Authentication system.
* [x] Week 3-4: Expense CRUD & Budget Alert logic.
* [x] Week 5-6: Email Service integration & PHPMailer setup.
* [x] Week 7: Final MVC Refactoring & Documentation.

## 💡 Lessons Learned

During the 8-week development cycle, I explored advanced concepts such as:

* **Static vs. Instance Methods**: Understanding when to use `self::` for utility/global logic vs. `$this->` for object state.
* **Access Modifiers**: Implementing `private` and `protected` to protect data integrity (Encapsulation).
* **API Fundamentals**: Learning the principles of building and consuming services, including security considerations like JWT.

---

**Author**: Dee Traid 

**Goal**: Transitioning from a coding student to a Professional Backend Developer.