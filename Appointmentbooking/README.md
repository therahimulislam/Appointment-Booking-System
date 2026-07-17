# CarePlus — Doctor Appointment Booking System

CarePlus is a comprehensive web-based platform designed to connect patients with top medical professionals for easy, secure, and hassle-free appointment booking. The system supports 10+ medical specialties and features a fully integrated payment gateway for consultation fees.

## ✨ Features

- **User Authentication**: Secure user registration, login, and profile management.
- **Doctor Directory**: Browse and select doctors across multiple specialties (Cardiology, Pediatrics, Neurology, etc.).
- **Appointment Booking**: Select a preferred date and time for consultations.
- **Payment Integration**: Secure online payment processing via a **Payment Gateway** (Sandbox & Production modes).
- **User Dashboard**: View, manage, and cancel upcoming or past appointments.
- **Responsive UI**: A modern, glassmorphism-inspired design with mobile responsiveness and built-in Dark/Light mode toggle.

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3 (Vanilla), JavaScript
- **Backend**: PHP 8.x
- **Database**: MySQL
- **Payment Gateway**: Payment Gateway JS SDK & API

## 📋 Prerequisites

To run this project locally, you will need:
- A local server environment like **XAMPP**, **WAMP**, or **MAMP**.
- PHP 7.4 or higher (8.x recommended).
- MySQL.
- A Payment Gateway Merchant Account (Sandbox/Test account for development).

## 🚀 Installation & Setup

1. **Clone the Repository**
   Clone or download the project and place it in your server's root directory (e.g., `htdocs` for XAMPP, `www` for WAMP).

2. **Database Configuration**
   - Open your MySQL management tool (e.g., phpMyAdmin at `http://localhost/phpmyadmin`).
   - You don't need to manually create the database. Simply import the provided `database.sql` file. It will automatically create the `appointment_db` database, necessary tables (`users`, `doctors`, `bookings`), and populate dummy doctor data.

3. **Environment Setup**
   - In the root folder of the project, copy the `.env.example` file and rename it to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Open the `.env` file and update your configuration details:
     ```env
     # Payment Gateway Credentials
     CF_APP_ID=YOUR_PAYMENT_APP_ID
     CF_SECRET_KEY=YOUR_PAYMENT_SECRET_KEY
     
     # Environment: sandbox | production
     CF_ENV=sandbox
     
     # App URL
     APP_BASE_URL=http://localhost/project/Appointmentbooking
     
     # Consultation Fee (INR)
     CONSULTATION_FEE=100
     
     # Database Credentials
     DB_HOST=localhost
     DB_USER=root
     DB_PASS=
     DB_NAME=appointment_db
     ```

4. **Run the Application**
   - Start **Apache** and **MySQL** from your XAMPP/WAMP control panel.
   - Open your web browser and navigate to the project URL:
     ```text
     http://localhost/project/Appointmentbooking
     ```

## 📂 Project Structure

```text
├── index.php                # Landing page
├── login.php                # User login page
├── signup.php               # User registration page
├── logout.php               # User logout handler
├── dashboard.php            # User dashboard
├── profile.php              # User profile management
├── book.php                 # Appointment booking interface
├── appointments.php         # User's appointment history
├── edit_appointment.php     # Appointment modification logic
├── cancel_appointments.php  # Appointment cancellation logic
├── db_connect.php           # Database connection handling
├── config.php               # Environment variable loader
├── initiate_payment.php     # Payment initialization
├── payment_return.php       # Payment success/failure handler
├── payment_webhook.php      # Webhook listener for Payment Gateway
├── database.sql             # SQL schema and seed data
├── style.css                # Main stylesheet
├── mobile.css               # Mobile responsive styles
├── .env.example             # Example environment variables
└── README.md                # Project documentation
```

## 🔒 Security Notes

- **Never commit your `.env` file** to a public version control system. It contains sensitive keys and database credentials.
- Ensure your `session.gc_maxlifetime` and cookie parameters are configured securely as defined in the application entry points.

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the issues page if you want to contribute.

## 📝 License

This project is open-source and available under the [MIT License](LICENSE).
