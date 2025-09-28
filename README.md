# 🏡 Property Booking API

A **Laravel 10 REST API** for property listings, availability management, and guest bookings with an **Admin dashboard** for management.  
It supports **authentication, role-based access, availability checks, booking requests, status updates, and queued email notifications**.

---

### Centralized Exception Handling

We are using a custom exception handler located in:

-   All exceptions are managed in this file.
-   No need to add `try/catch` blocks in every controller or service method.
-   The handler ensures consistent JSON error responses for APIs.

## 🚀 Features

-   **Admin Panel APIs**
    -   Manage properties (CRUD)
    -   Manage availabilities (set date ranges)
    -   Manage bookings (approve, reject, confirm)
-   **Guest APIs**
    -   Browse properties
    -   Check availability
    -   Create bookings
-   **Authentication**
    -   Admin login/logout with Laravel Sanctum
-   **Email Notifications**
    -   Booking status change notifications sent via **Mailtrap + Queue**
-   **Database Seeders**
    -   Admin user seeder
    -   Demo properties with availability

---

## 🛠️ Tech Stack

-   **Laravel 10** (Framework)
-   **Sanctum** (API Authentication)
-   **MySQL** (Database)
-   **Mailtrap** (Email Testing)
-   **Laravel Queue** (Email jobs)

---

## ⚙️ Installation

```bash
# 1. Clone repository
git clone https://github.com/your-username/property-booking-api.git
cd property-booking-api

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Configure database in .env
DB_DATABASE=property-test
DB_USERNAME=root
DB_PASSWORD=

# 5. Generate app key
php artisan key:generate

# 6. Run migrations
php artisan migrate

# 7. Run seeders
php artisan db:seed

# 8. Base Url
http://localhost/"{your-directory}"/property-test-backend/api/
```

---

## 📩 Queue Setup (Emails)

Emails are sent when **booking status changes**.

```bash
# Run queue worker
php artisan queue:work
```

✅ Uses **Mailtrap SMTP** (already configured in `.env`).

---

## 🔑 Default Admin

Seeder creates an admin user:

```
Email: lewis@mailinator.com
Password: Admin@123
```

---

## 🌱 Seeded Data

-   **2 Properties** (Beach Villa & Mountain Cabin)
-   Each property has **availability ranges**
-   Ready to test guest bookings

---

## 📡 API Endpoints

### Guest APIs (`/api/guest/`)

| Method | Endpoint                 | Description            |
| ------ | ------------------------ | ---------------------- |
| GET    | `/guest/properties`      | List all properties    |
| GET    | `/guest/properties/{id}` | Show single property   |
| POST   | `/guest/bookings`        | Create booking request |

📌 Example **Booking Payload**

```json
{
    "property_id": 1,
    "guest_name": "John Doe",
    "guest_email": "john@example.com",
    "start_date": "2025-10-01",
    "end_date": "2025-10-05"
}
```

---

### Admin APIs (`/api/admin/`)

| Method | Endpoint                            | Description                          |
| ------ | ----------------------------------- | ------------------------------------ |
| POST   | `/admin/login`                      | Login admin                          |
| POST   | `/admin/logout`                     | Logout admin                         |
| GET    | `/admin/properties`                 | List properties                      |
| POST   | `/admin/properties`                 | Create property                      |
| PUT    | `/admin/properties/{id}`            | Update property                      |
| GET    | `/admin/properties/{id}`            | Show property                        |
| DELETE | `/admin/properties/{id}`            | Delete property                      |
| GET    | `/admin/availability/{property_id}` | List availability for property       |
| POST   | `/admin/availability`               | Add availability                     |
| GET    | `/admin/bookings`                   | List bookings (filterable by status) |
| PATCH  | `/admin/bookings/{id}/status`       | Update booking status                |

📌 Example **Update Booking Status Payload**

```json
{
    "status": "confirmed"
}
```

📌 Booking status options:

-   `pending`
-   `confirmed`
-   `rejected`

✅ Changing status triggers **email notification** to guest.

---

## 🧪 Authentication

Admin APIs are protected with **Sanctum**.

1. Login → Receive token.
2. Send token in headers:

```
Authorization: Bearer {token}
```

---

## 📬 Emails

-   Triggered when booking status is **confirmed/rejected**
-   Uses **Mailtrap** (check your Mailtrap inbox)

---

## 🔍 Filters

-   Admin bookings endpoint supports filtering:

```
GET /api/admin/bookings?status=confirmed
```

---

## 👨‍💻 Author

Developed by **Abdul Basit**
