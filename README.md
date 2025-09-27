# E-Commerce Single Vendor Backend (Laravel)

A fully functional backend for a single-vendor e-commerce application, built using **Laravel 10** and **Sanctum** for authentication.

## 🚀 Features
- User Authentication (Signup, Login, Reset Password)
- Admin Authentication with Dashboard
- Product Management (CRUD, Images, Categories, Subcategories)
- Cart & Wishlist
- Orders & Reorders
- Discounts & Coupons
- Chat (User ↔ Admin)
- Notifications
- Ratings & Reviews
- Stripe Payment Gateway Integration
- RESTful API structure for frontend/mobile apps

## 🛠️ Tech Stack
- Laravel 10
- Sanctum (API Authentication)
- MySQL / PostgreSQL
- Stripe Payment Gateway
- Redis / Queue (if configured)

## 📦 Installation
```bash
git clone https://github.com/your-username/e-commerce-single-vendor-backend.git
cd e-commerce-single-vendor-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
