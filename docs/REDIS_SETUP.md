# Redis Setup Guide

This guide explains how to install and configure Redis for caching and rate limiting in your Laravel application.

## Why Use Redis?

Redis provides significant performance benefits over file-based caching:

- ⚡ **10-100x faster** than file cache
- 🔄 **Atomic operations** for rate limiting
- 📊 **Better concurrency** handling
- 🚀 **Distributed caching** support
- 💾 **Memory-efficient** data structures

## Installation

### macOS (using Homebrew)

```bash
# Install Redis server
brew install redis

# Start Redis (and restart at login)
brew services start redis

# Or run Redis in foreground (for testing)
redis-server
```

### Ubuntu/Debian

```bash
# Install Redis server
sudo apt update
sudo apt install redis-server

# Start Redis
sudo systemctl start redis-server

# Enable Redis to start on boot
sudo systemctl enable redis-server
```

### Verify Redis is Running

```bash
redis-cli ping
# Should return: PONG
```

## Install PHP Redis Extension

### macOS

```bash
# Install via PECL
pecl install redis

# Add extension to php.ini
echo "extension=redis.so" >> $(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")

# Restart PHP (if using PHP-FPM)
brew services restart php
```

### Ubuntu/Debian

```bash
# Install PHP Redis extension
sudo apt install php-redis

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm  # Adjust version if needed
```

### Verify PHP Extension

```bash
php -m | grep redis
# Should return: redis
```

## Configure Laravel to Use Redis

### 1. Update `.env` File

```env
# Cache Configuration
CACHE_STORE=redis

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_TIMEOUT=2000
```

### 2. Clear Laravel Caches

**Note:** No code changes needed! The `RateLimitService` automatically uses whatever cache store is configured in `.env`.

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

### 3. Test Redis Connection

```bash
php artisan tinker

# In tinker:
Cache::put('test', 'hello', 60);
Cache::get('test');
# Should return: "hello"
```

## Redis Configuration Options

### Multiple Redis Connections

You can configure multiple Redis connections in `config/database.php`:

```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),

    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => 0,
    ],

    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => 1,  // Separate database for cache
    ],

    'queue' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => 2,  // Separate database for queues
    ],
],
```

### Redis Security (Production)

For production environments, secure your Redis instance:

**1. Set a password:**

Edit `/opt/homebrew/etc/redis.conf` (macOS) or `/etc/redis/redis.conf` (Linux):

```conf
requirepass your_strong_password_here
```

**2. Bind to localhost only:**

```conf
bind 127.0.0.1 ::1
```

**3. Update `.env`:**

```env
REDIS_PASSWORD=your_strong_password_here
```

**4. Restart Redis:**

```bash
# macOS
brew services restart redis

# Ubuntu/Debian
sudo systemctl restart redis-server
```

## Performance Tuning

### Redis Memory Optimization

Edit Redis config file:

```conf
# Set max memory (e.g., 256MB)
maxmemory 256mb

# Eviction policy (remove least recently used keys when memory is full)
maxmemory-policy allkeys-lru
```

### Laravel Cache Optimization

**Use cache tags for better organization:**

```php
Cache::tags(['users', 'profile'])->put('user:1', $user, 3600);
Cache::tags(['users'])->flush();  // Clear all user cache
```

**Use cache locks for atomic operations:**

```php
$lock = Cache::lock('process-data', 10);

if ($lock->get()) {
    // Process data atomically
    $lock->release();
}
```

## Monitoring Redis

### CLI Commands

```bash
# Monitor Redis commands in real-time
redis-cli monitor

# Check Redis info
redis-cli info

# Check connected clients
redis-cli client list

# Check memory usage
redis-cli info memory

# Check all keys
redis-cli keys *
```

### Laravel Telescope (Development)

Install Laravel Telescope to monitor cache operations:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

## Troubleshooting

### Connection Refused

```bash
# Check if Redis is running
redis-cli ping

# Check Redis logs
tail -f /opt/homebrew/var/log/redis.log  # macOS
tail -f /var/log/redis/redis-server.log  # Ubuntu
```

### PHP Extension Not Loaded

```bash
# Check PHP configuration
php --ini

# Verify extension is loaded
php -m | grep redis

# If not loaded, check php.ini for:
extension=redis.so
```

### Permission Denied

```bash
# macOS
sudo chown -R $(whoami) /opt/homebrew/var/db/redis

# Ubuntu
sudo chown redis:redis /var/lib/redis
```

## Fallback to File Cache

If Redis becomes unavailable, you can quickly switch back to file cache:

**Update `.env`:**

```env
CACHE_STORE=file
```

**Clear config:**

```bash
php artisan config:clear
```

The `CacheRateLimitService` will continue to work with file cache, providing graceful degradation.

## Comparison: File Cache vs Redis

| Feature | File Cache | Redis |
|---------|------------|-------|
| **Performance** | Slow (disk I/O) | Fast (in-memory) |
| **Concurrency** | Poor (file locks) | Excellent (atomic ops) |
| **Setup** | None required | Requires Redis server |
| **Memory Usage** | Low (uses disk) | Higher (uses RAM) |
| **Distributed** | No | Yes |
| **Rate Limiting** | Basic | Advanced |
| **Best For** | Development | Production |

## Recommendations

- **Development**: Use file cache (simpler setup)
- **Staging/Production**: Use Redis (better performance)
- **Rate Limiting**: Always use Redis for accurate rate limiting
- **Caching**: Redis recommended for high-traffic applications

---

**Need Help?** Check the [Laravel Redis documentation](https://laravel.com/docs/11.x/redis) for more details.
