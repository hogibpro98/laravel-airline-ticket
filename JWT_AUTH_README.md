# JWT Authentication & Spatie Permission - API Documentation

## 📦 Đã cài đặt

- ✅ JWT-Auth (tymon/jwt-auth) - Token-based authentication
- ✅ Spatie Permission (spatie/laravel-permission) - Roles & Permissions management

## 🔐 Roles & Permissions

### Roles mặc định:

1. **admin** - Toàn quyền
2. **manager** - Quản lý flights, bookings, tickets, reports  
3. **agent** - Tạo và quản lý bookings, tickets
4. **user** - Xem flights, tạo bookings cơ bản

### Permissions:

**User Management:**
- view users, create users, edit users, delete users

**Flight Management:**
- view flights, create flights, edit flights, delete flights

**Booking Management:**
- view bookings, create bookings, edit bookings, delete bookings, cancel bookings

**Ticket Management:**
- view tickets, create tickets, edit tickets, delete tickets, print tickets

**Report Management:**
- view reports, export reports

## 🚀 API Endpoints

### Base URL
```
http://localhost/api
```

### Authentication Endpoints

#### 1. Register
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "bearer",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "roles": ["user"],
        "permissions": ["view flights", "view bookings", "create bookings", "view tickets"]
    }
}
```

#### 2. Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:** Same as Register

#### 3. Get User Info
```http
GET /api/auth/me
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "roles": ["user"],
        "permissions": ["view flights", "view bookings", ...]
    }
}
```

#### 4. Refresh Token
```http
POST /api/auth/refresh
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "access_token": "new_token_here...",
        "token_type": "bearer",
        "expires_in": 3600,
        "user": {...},
        "roles": [...],
        "permissions": [...]
    }
}
```

#### 5. Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Successfully logged out"
}
```

## 🔧 Sử dụng trong Code

### Kiểm tra Permission

```php
// Trong Controller
if ($request->user()->can('create flights')) {
    // User có permission
}

// Hoặc dùng middleware
Route::middleware(['permission:create flights'])->group(function () {
    Route::post('/flights', [FlightController::class, 'store']);
});
```

### Kiểm tra Role

```php
// Trong Controller
if ($request->user()->hasRole('admin')) {
    // User là admin
}

// Hoặc dùng middleware
Route::middleware(['role:admin'])->group(function () {
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```

### Assign Role cho User

```php
use App\Models\User;

$user = User::find(1);
$user->assignRole('manager');

// Hoặc nhiều roles
$user->assignRole(['manager', 'agent']);
```

### Give Permission trực tiếp

```php
$user->givePermissionTo('edit flights');
$user->givePermissionTo(['edit flights', 'delete flights']);
```

### Revoke Permission

```php
$user->revokePermissionTo('delete flights');
```

## 🧪 Test với cURL

### Register
```bash
curl -X POST http://localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### Get User Info (với token)
```bash
curl -X GET http://localhost/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 🛡️ Middleware có sẵn

```php
// routes/api.php

// Chỉ user đã đăng nhập
Route::middleware('auth:api')->group(function () {
    // Routes...
});

// Chỉ admin
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Routes...
});

// Chỉ user có permission cụ thể
Route::middleware(['auth:api', 'permission:create flights'])->group(function () {
    // Routes...
});

// Kết hợp nhiều điều kiện
Route::middleware(['auth:api', 'role:admin|manager'])->group(function () {
    // Routes...
});
```

## 🔑 JWT Configuration

File: `config/jwt.php`

Các cấu hình quan trọng:
- **ttl**: 60 (minutes) - Thời gian sống của token
- **refresh_ttl**: 20160 (minutes = 2 weeks) - Thời gian refresh token
- **algo**: HS256 - Thuật toán mã hóa

## 📝 Environment Variables

Đã được tự động thêm vào `.env`:

```env
JWT_SECRET=vzbljEI5X9auBJ6HDqJ6TCPhXRRyezJxtYZ9PFbJ2cwJmA1QgGx6RDU1K7GLfKFQ
```

## 🎯 Next Steps

1. Tạo các Controllers cho Flight, Booking, Ticket
2. Áp dụng middleware permissions cho các routes
3. Tạo API documentation chi tiết hơn
4. Implement React frontend với JWT authentication
5. Add rate limiting cho API endpoints

## 📚 Tài liệu tham khảo

- [JWT-Auth Documentation](https://jwt-auth.readthedocs.io/)
- [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission/)
- [Laravel API Authentication](https://laravel.com/docs/11.x/sanctum)
