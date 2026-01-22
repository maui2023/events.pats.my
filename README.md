# BeSpoke Events (Event Management System)

Welcome to **BeSpoke Events**, a comprehensive Event Management System (EMS) built under **BeSpoke Sdn Bhd**, led by **Haji Saidy**. This platform is designed to streamline the entire event lifecycle, from ticketing to post-event certification.

## 🚀 Project Overview

BeSpoke Events acts as a one-stop center for event organizers, offering professional-grade tools to manage events, sell tickets, track attendance, and generate reports.

- **Project Lead:** Haji Saidy
- **Developer:** BeSpoke Sdn Bhd
- **Framework:** Laravel 12 + FilamentPHP v4

## ✨ Key Features

### 🎟️ Ticketing & Event Management
- **Comprehensive Event Management:** Create, edit, and publish interactive event pages.
- **Flexible Ticketing:** Support for multiple ticket categories (Free, Paid, VIP).
- **Payment Integration:** Seamless payment processing via ToyyibPay, FPX, Stripe, and SecurePay.

### 📲 Attendance & Check-in
- **QR Scan for Presence:** Each attendee receives a unique QR code. Staff can scan this code for instant check-in and attendance tracking.
- **Real-time Status:** Monitor valid, used, and invalid tickets in real-time.

### 🎓 Certification & Passes
- **Presence Certification:** Automatically generate and distribute certificates of attendance to participants upon successful check-in.
- **Event Pass Photo Boom:** Create personalized event passes and photo opportunities for attendees.

### 📊 Analytics & Reporting
- **Dashboard:** Powered by FilamentPHP, providing real-time statistics on ticket sales, revenue, and attendance.
- **Invoicing:** Automated generation of invoices and receipts.

## 🛠️ Technology Stack

- **Backend:** [Laravel 12](https://laravel.com)
- **Admin Panel:** [FilamentPHP v4](https://filamentphp.com)
- **QR Code Generation:** `simplesoftwareio/simple-qrcode`
- **Frontend:** Blade / Livewire

## ⚙️ Installation

To set up the project locally, follow these steps:

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd events.pats
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Environment Setup**
   Copy the example environment file and configure your database settings:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Migration**
   Run the migrations to set up the database schema:
   ```bash
   php artisan migrate
   ```

5. **Serve the Application**
   Start the local development server:
   ```bash
   php artisan serve
   ```

## 👥 User Roles

- **Organizer:** Manage events, tickets, and view reports.
- **Attendee:** Register, buy tickets, and access event passes.
- **Admin (BeSpoke HQ):** System-wide monitoring and management.

---
*Built with ❤️ by BeSpoke Sdn Bhd*
