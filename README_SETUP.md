# 🔧 Backend - NMSuperMarket API

Đây là backend API của dự án NMSuperMarket xây dựng bằng **Laravel 13** với **JWT Authentication**.

---

## ⚡ Quick Start

```bash
# 1. Cài dependencies
composer install

# 2. Copy .env
copy .env.example .env

# 3. Sinh keys
php artisan key:generate
php artisan jwt:secret

# 4. Tạo database
mysql -u root -p -e "CREATE DATABASE nmsupermarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Chạy migrations
php artisan migrate

# 6. Chạy server
php artisan serve
# Truy cập: http://localhost:8000
```

---

## 📦 Yêu Cầu

- **PHP**: 8.3+
- **MySQL**: 5.7+
- **Composer**: 2.0+
- **Node.js**: 18+ (cho frontend assets)

---

## 🛠️ Cài Đặt Chi Tiết

### 1. Cài Đặt Composer Dependencies

```bash
composer install
```

### 2. Cấu Hình Environment

```bash
copy .env.example .env
```

**Cấu hình .env:**
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nmsupermarket
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Tạo Database

```bash
mysql -u root -p -e "CREATE DATABASE nmsupermarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Sinh Keys

```bash
# App encryption key
php artisan key:generate

# JWT secret
php artisan jwt:secret
```

### 5. Chạy Migrations

```bash
php artisan migrate
```

### 6. (Tùy Chọn) Seed Data

```bash
php artisan db:seed
```

### 7. Cài NPM Dependencies (Frontend Assets)

```bash
npm install
```

### 8. Build Frontend Assets

```bash
npm run build
```

---

## 🚀 Chạy Server

### Development Mode

```bash
# Chạy tất cả services (server, queue, logs, vite)
composer run dev

# Hoặc chỉ chạy Laravel server
php artisan serve

# Server sẽ chạy tại: http://localhost:8000
```

---

## 📁 Cấu Trúc Dự Án

```
BE_NMSuperMarket/
├── app/
│   ├── Http/
│   │   ├── Controllers/         # API Controllers
│   │   └── Middleware/          # Middleware (JWT, CORS, etc)
│   ├── Models/                  # Database Models
│   ├── Services/                # Business Logic
│   └── Exceptions/              # Custom Exceptions
├── config/
│   ├── auth.php                 # Auth config
│   ├── jwt.php                  # JWT config
│   └── ...
├── database/
│   ├── migrations/              # Database migrations
│   ├── seeders/                 # Seeders
│   └── factories/               # Model factories
├── routes/
│   ├── api.php                  # API routes
│   └── web.php                  # Web routes
├── storage/
│   ├── logs/                    # Log files
│   └── uploads/                 # Uploaded files
├── tests/                       # Unit & Feature tests
├── .env                         # Environment variables
├── .env.example                 # Example .env
├── composer.json                # PHP dependencies
├── package.json                 # Frontend assets
├── vite.config.js              # Vite config
└── artisan                      # Laravel CLI
```

---

## 📚 API Routes

API được định nghĩa trong `routes/api.php`:

```
POST   /api/auth/register         # Đăng ký
POST   /api/auth/login           # Đăng nhập
POST   /api/auth/refresh         # Refresh token
POST   /api/auth/logout          # Đăng xuất

GET    /api/products             # Danh sách sản phẩm
GET    /api/products/:id         # Chi tiết sản phẩm
POST   /api/products             # Tạo sản phẩm (Admin)
PUT    /api/products/:id         # Cập nhật sản phẩm (Admin)
DELETE /api/products/:id         # Xóa sản phẩm (Admin)

GET    /api/orders               # Danh sách đơn hàng
POST   /api/orders               # Tạo đơn hàng
GET    /api/orders/:id           # Chi tiết đơn hàng

POST   /api/chatbot              # Chat with AI
```

---

## 🔐 Authentication (JWT)

Dự án sử dụng **JWT (JSON Web Tokens)** cho authentication.

### Lưu Ý:
- JWT_SECRET được sinh bằng: `php artisan jwt:secret`
- JWT TTL: 60 phút (có thể đổi trong .env)
- Refresh token TTL: 20160 phút (14 ngày)

### Cách Dùng:

```
Header: Authorization: Bearer {token}
```

---

## 📖 Các Lệnh Hữu Ích

```bash
# Migrations
php artisan make:migration create_table_name
php artisan migrate
php artisan migrate:rollback
php artisan migrate:reset
php artisan migrate:refresh

# Models & Controllers
php artisan make:model Product
php artisan make:controller ProductController
php artisan make:model Product -mcr  # Model, Migration, Controller

# Database
php artisan db:seed                  # Run seeders
php artisan tinker                   # Interactive shell

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Testing
php artisan test
php artisan test --filter=TestName

# Logs
tail -f storage/logs/laravel.log

# Queue (nếu sử dụng)
php artisan queue:listen
```

---

## 🐛 Troubleshooting

### 1. "SQLSTATE Connection refused"
- Kiểm tra MySQL đang chạy
- Kiểm tra DB config trong .env

### 2. "JWT_SECRET not defined"
```bash
php artisan jwt:secret
```

### 3. "Base table or view not found"
```bash
php artisan migrate
```

### 4. "Integrity constraint violation"
- Kiểm tra foreign keys
- Hoặc reset: `php artisan migrate:reset && php artisan migrate`

---

## 📝 Environment Variables

```env
APP_NAME=NMSuperMarket
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:xxxxx

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nmsupermarket
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=xxxxx
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256

AI_PROVIDER=groq
GROQ_API_KEY=
```

---

## 🤖 AI Chatbot Integration

Dự án hỗ trợ AI chatbot thông qua:
- **Groq API**
- **OpenRouter API**

Cấu hình trong `.env`:
```env
AI_PROVIDER=groq
GROQ_API_KEY=xxxxx
```

---

## 🧪 Testing

```bash
# Chạy tất cả tests
php artisan test

# Chạy test file cụ thể
php artisan test tests/Feature/AuthTest.php

# Chạy test với filter
php artisan test --filter=test_user_can_login
```

---

**Version**: 1.0  
**Last Updated**: June 2024  
**Backend Documentation**
