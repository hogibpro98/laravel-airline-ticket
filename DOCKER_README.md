# Airline Ticket System - Docker Deployment Guide

## Tổng quan

Dự án này sử dụng Docker Compose để triển khai ứng dụng với các services sau:

- **PostgreSQL 16 Alpine** - Cơ sở dữ liệu quan hệ
- **Redis 7 Alpine** - Cache và session storage
- **PHP 8.4 FPM Alpine** - Backend Laravel
- **Nginx 1.25 Alpine** - Web server

## Yêu cầu hệ thống

- Docker Engine 20.10+
- Docker Compose 2.0+
- 2GB RAM tối thiểu
- 10GB dung lượng ổ cứng

## Cài đặt

### 1. Chuẩn bị môi trường

```bash
# Copy file environment
cp .env.docker.example .env

# Chỉnh sửa file .env với thông tin của bạn
nano .env
```

### 2. Tạo APP_KEY cho Laravel

```bash
# Tạo key tạm thời
cd laravel
cp .env.example .env
docker run --rm -v $(pwd):/app composer:2.7 composer install --no-dev --optimize-autoloader
docker run --rm -v $(pwd):/app php:8.4-cli-alpine php artisan key:generate --show
```

Sao chép APP_KEY vừa tạo vào file `.env` ở thư mục gốc.

### 3. Build và khởi chạy containers

```bash
# Build images
docker-compose build

# Khởi động services
docker-compose up -d

# Kiểm tra trạng thái
docker-compose ps
```

### 4. Cài đặt Laravel

```bash
# Chạy migrations
docker-compose exec laravel php artisan migrate

# Tạo storage link
docker-compose exec laravel php artisan storage:link

# Clear và cache lại
docker-compose exec laravel php artisan config:cache
docker-compose exec laravel php artisan route:cache
docker-compose exec laravel php artisan view:cache
```

## Sử dụng

### Truy cập ứng dụng

- **Laravel API**: http://localhost
- **PostgreSQL**: localhost:5432
- **Redis**: localhost:6379

### Các lệnh thường dùng

```bash
# Xem logs
docker-compose logs -f laravel
docker-compose logs -f nginx

# Restart services
docker-compose restart

# Dừng services
docker-compose down

# Dừng và xóa volumes (cẩn thận - sẽ mất dữ liệu!)
docker-compose down -v

# Chạy artisan commands
docker-compose exec laravel php artisan migrate
docker-compose exec laravel php artisan tinker
docker-compose exec laravel php artisan queue:work

# Chạy composer
docker-compose exec laravel composer install
docker-compose exec laravel composer update

# Truy cập bash container
docker-compose exec laravel sh
docker-compose exec postgres sh

# Backup database
docker-compose exec postgres pg_dump -U airline_user airline_db > backup.sql

# Restore database
docker-compose exec -T postgres psql -U airline_user airline_db < backup.sql
```

## Development Mode

Để chạy ở chế độ development với Xdebug:

```bash
# Sửa trong docker-compose.yml, thay đổi target build:
# target: development

# Rebuild
docker-compose build laravel
docker-compose up -d
```

## Production Checklist

- [ ] Đặt `APP_ENV=production` trong `.env`
- [ ] Đặt `APP_DEBUG=false`
- [ ] Tạo APP_KEY mạnh
- [ ] Thay đổi mật khẩu database và redis
- [ ] Cấu hình SSL/TLS cho Nginx
- [ ] Thiết lập backup tự động
- [ ] Cấu hình monitoring và logging
- [ ] Giới hạn tài nguyên container
- [ ] Cấu hình firewall

## Bảo mật

### SSL/TLS Configuration

Để bật HTTPS, thêm certificate vào `nginx/ssl/` và cập nhật `nginx/conf.d/laravel.conf`:

```nginx
server {
    listen 443 ssl http2;
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    # ... rest of config
}
```

### Hardening

1. Đổi mật khẩu mặc định
2. Sử dụng secrets thay vì environment variables
3. Giới hạn kết nối database
4. Cấu hình rate limiting

## Troubleshooting

### Container không khởi động

```bash
# Xem logs chi tiết
docker-compose logs laravel

# Kiểm tra health status
docker-compose ps
```

### Permission errors

```bash
# Fix permissions
docker-compose exec laravel chown -R www:www /var/www/html/storage
docker-compose exec laravel chmod -R 775 /var/www/html/storage
```

### Database connection errors

```bash
# Kiểm tra PostgreSQL đang chạy
docker-compose exec postgres pg_isready -U airline_user

# Kiểm tra credentials trong .env
docker-compose exec laravel cat .env | grep DB_
```

## Cấu trúc thư mục

```
.
├── docker-compose.yml          # Docker Compose configuration
├── .env                        # Environment variables
├── laravel/                    # Laravel application
│   ├── Dockerfile             # PHP-FPM Dockerfile
│   ├── .dockerignore          # Docker ignore file
│   └── docker/
│       └── php/               # PHP configurations
│           ├── php.ini
│           ├── php-dev.ini
│           └── opcache.ini
└── nginx/                      # Nginx configuration
    ├── nginx.conf             # Main nginx config
    └── conf.d/
        └── laravel.conf       # Laravel site config
```

## Monitoring

### Health Checks

```bash
# Nginx health check
curl http://localhost/health

# PostgreSQL
docker-compose exec postgres pg_isready

# Redis
docker-compose exec redis redis-cli ping
```

### Resource Usage

```bash
# Xem CPU và RAM usage
docker stats

# Xem disk usage
docker system df
```

## Backup và Restore

### Database Backup

```bash
# Manual backup
docker-compose exec postgres pg_dump -U airline_user airline_db > backup_$(date +%Y%m%d).sql

# Automated backup script
./scripts/backup.sh
```

### Volume Backup

```bash
# Backup all volumes
docker run --rm -v airline_postgres_data:/data -v $(pwd)/backups:/backup alpine tar czf /backup/postgres_data.tar.gz -C /data .
```

## Support

Nếu gặp vấn đề, vui lòng:

1. Kiểm tra logs: `docker-compose logs`
2. Xem documentation Laravel và Docker
3. Tạo issue trên repository

## License

MIT License
