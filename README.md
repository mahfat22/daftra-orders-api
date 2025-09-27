# 🛒 Daftra Orders & Payments API

A comprehensive Laravel 12 RESTful API for order management and payment processing with JWT authentication, extensible payment gateway architecture, and robust business rule validation.

## 🚀 Quick Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & npm (for asset compilation)
- MySQL/SQLite database
- Docker & Docker Compose (for containerized setup)

### Option 1: Docker Setup (Recommended)

1. **Clone the repository**
   ```bash
   git clone https://github.com/mahfat22/daftra-orders-api.git
   cd daftra-orders-api
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Configure environment variables**
   ```bash
   # Database Configuration (for Docker)
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=daftra_orders_api
   DB_USERNAME=daftra_orders_api_user
   DB_PASSWORD=daftra_orders_api_password

   # JWT Configuration
   JWT_SECRET=your_jwt_secret_key_here

   # Payment Gateway Configuration
   PAYMENT_DEFAULT_GATEWAY=stripe
   PAYMENT_GATEWAYS_ENABLED=stripe,paypal,credit_card
   ```

4. **Start Docker services**
   ```bash
   cd docker
   docker-compose up -d
   ```

5. **Install dependencies and setup application**
   ```bash
   docker exec -it daftra-app composer install
   docker exec -it daftra-app php artisan key:generate
   docker exec -it daftra-app php artisan jwt:secret
   docker exec -it daftra-app php artisan migrate --seed
   ```

### Option 2: Local Setup

1. **Clone and setup**
   ```bash
   git clone https://github.com/mahfat22/daftra-orders-api.git
   cd daftra-orders-api
   cp .env.example .env
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   # For MySQL
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=daftra_orders_api
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate keys and setup database**
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   php artisan migrate --seed
   ```

5. **Start development server**
   ```bash
   php artisan serve
   ```

## 🛠️ Services & Tools

The project includes comprehensive Docker setup with the following services:

### Core Services
- **Laravel App** - Main application container with PHP 8.2-FPM
- **Nginx** - Web server for handling HTTP requests
- **MySQL 8.0** - Primary database
- **Supervisor** - Process manager for queue workers

### Development Tools
- **PhpMyAdmin** - Database administration interface
- **Queue Workers** - Background job processing via Supervisor

## 📋 Services Overview Table

| Service | Container | Port | Description | Credentials |
|---------|-----------|------|-------------|-------------|
| **Web App** | `daftra-app` | - | Laravel application (PHP-FPM) | - |
| **Web Server** | `daftra-nginx` | `9000` | Nginx reverse proxy | - |
| **Database** | `daftra-mysql` | `3308` | MySQL 8.0 database | `root:root_password` |
| **PhpMyAdmin** | `daftra-phpmyadmin` | `8081` | Database management | `daftra_orders_api_user:daftra_orders_api_password` |
| **Queue Worker** | `daftra-app` | - | Background job processing | Managed by Supervisor |

## 🧪 Testing

The project includes comprehensive test coverage with PHPUnit:

### Running All Tests
```bash
# Local
./vendor/bin/phpunit

# Docker
docker exec -it daftra-app ./vendor/bin/phpunit
```

### Running Specific Test Suites
```bash
# Feature Tests (API endpoints, integration)
./vendor/bin/phpunit --testsuite=Feature

# Unit Tests (individual classes, business logic)
./vendor/bin/phpunit --testsuite=Unit

# With coverage report
./vendor/bin/phpunit --coverage-html coverage
```

### Test Configuration
- **Test Database**: `daftra_orders_api_test` (MySQL) or in-memory SQLite
- **Test Environment**: Isolated from development data
- **Fixtures**: Uses factories and seeders for test data
- **Coverage**: Targets `app/` directory for code coverage analysis

## 📮 Postman Collection

Import the comprehensive API collection for testing:

1. **Import Collection**
   ```
   File: postman/postman_collection.json
   ```

2. **Base URL Configuration**
   ```
   Local: http://localhost:8000/api/v1
   Docker: http://localhost:9000/api/v1
   ```

3. **Authentication Setup**
   - Collection includes JWT token management
   - Automatic token extraction and storage
   - Pre-configured test user credentials
   - Collection variables for dynamic testing

4. **Available Endpoints**
   - Authentication (register, login, refresh, logout)
   - Order Management (CRUD, status transitions)
   - Payment Processing (multiple gateways)
   - User Profile Management

## ✨ Key Features

### 🔐 Authentication & Authorization
- **JWT-based authentication** using `tymon/jwt-auth`
- **User registration and login** with email verification ready
- **Token refresh mechanism** for seamless user experience
- **Role-based access control** architecture ready

### 📦 Order Management System
- **Complete order lifecycle** (pending → confirmed → cancelled)
- **Order items management** with quantity and pricing
- **Order total calculation** with business rule validation
- **Order status transitions** with strict business rules
- **Order history and tracking** capabilities

### 💳 Payment Processing
- **Multi-gateway payment support** (Stripe, PayPal, Credit Card)
- **Strategy pattern implementation** for easy gateway extension
- **Multiple payment methods** per order
- **Payment status tracking** (pending, successful, failed)
- **Payment retry mechanisms** with configurable policies
- **PCI compliance ready** architecture

### 🏗️ Architecture Excellence
- **Clean Architecture** with separation of concerns
- **Repository Pattern** for data access abstraction
- **Service Layer** for business logic encapsulation
- **Strategy Pattern** for payment gateway extensibility
- **Factory Pattern** for payment gateway instantiation
- **Business Rules Service** for centralized validation

### 🔧 API Design
- **RESTful API design** following industry standards
- **API versioning** with `/api/v1/` structure
- **Consistent JSON responses** with standardized error handling
- **Request validation** with Laravel Form Requests
- **API resource transformations** for clean data output
- **Comprehensive error handling** with detailed messages

### 📊 Data Management
- **Eloquent ORM** with optimized queries
- **Database migrations** with version control
- **Model factories** for testing and seeding
- **Soft deletes** for data integrity
- **Foreign key constraints** with cascade options
- **Indexing strategy** for optimal performance

## ⚡ Performance Optimizations

### 🚀 Caching Strategy
- **Database query caching** for frequently accessed data
- **Payment gateway configuration caching** (3600 seconds default)
- **Route caching** for production performance
- **Configuration caching** for faster bootstrap

### 🔄 Queue System
- **Database-driven queues** for reliability
- **Background job processing** via Supervisor
- **Queue worker management** with auto-restart
- **Failed job handling** with retry mechanisms
- **Job timeout configuration** (60 seconds default)

### 📈 Database Optimization
- **Eager loading** to prevent N+1 queries
- **Database indexing** on frequently queried columns
- **Connection pooling** for better resource usage
- **Query optimization** with proper relationships

## 🔧 Development Commands

### Laravel Artisan Commands
```bash
# Application management
php artisan serve                          # Start development server
php artisan migrate                        # Run database migrations
php artisan migrate:rollback               # Rollback migrations
php artisan db:seed                        # Seed database
php artisan migrate --seed                 # Migrate and seed

# Cache management
php artisan config:cache                   # Cache configuration
php artisan route:cache                    # Cache routes
php artisan view:cache                     # Cache views
php artisan cache:clear                    # Clear application cache

# JWT management
php artisan jwt:secret                     # Generate JWT secret
```

### Docker Commands
```bash
# Container management
docker-compose up -d                       # Start all services
docker-compose down                        # Stop all services
docker-compose restart                     # Restart services
docker-compose logs -f app                 # View app logs

# Application commands in container
docker exec -it daftra-app php artisan migrate
docker exec -it daftra-app composer install
docker exec -it daftra-app ./vendor/bin/phpunit
```

### Code Quality Commands
```bash
# Code formatting
./vendor/bin/pint                          # Fix code style issues
./vendor/bin/pint --dirty                  # Fix only changed files

# Testing
./vendor/bin/phpunit                       # Run all tests
./vendor/bin/phpunit --coverage-html coverage  # Generate coverage report
```

## 📦 API Versioning

### Version Structure
```
Base URL: /api/v1/
```

### Endpoints Structure
```
Authentication:
├── POST /api/v1/auth/register
├── POST /api/v1/auth/login
├── POST /api/v1/auth/refresh
├── GET  /api/v1/auth/me
└── POST /api/v1/auth/logout

Orders:
├── GET    /api/v1/orders
├── POST   /api/v1/orders
├── GET    /api/v1/orders/{id}
├── PUT    /api/v1/orders/{id}
├── DELETE /api/v1/orders/{id}
├── POST   /api/v1/orders/{id}/confirm
└── POST   /api/v1/orders/{id}/cancel

Payments:
├── GET  /api/v1/payments
├── POST /api/v1/payments
├── GET  /api/v1/payments/{id}
├── GET  /api/v1/orders/{orderId}/payments
└── GET  /api/v1/payment-methods
```

### Versioning Strategy
- **URL-based versioning** for clear API evolution
- **Backward compatibility** maintained within major versions
- **Deprecation notices** for outdated endpoints
- **Migration guides** for version upgrades

### Response Format
All API responses follow a consistent structure:
```json
{
  "success": true|false,
  "message": "Human readable message",
  "data": {}, // Response data
  "errors": [], // Validation errors (if any)
  "meta": {} // Pagination, additional info
}
```

## 🔌 Extensibility - Payment Gateway Integration

The system uses the **Strategy Pattern** for payment gateway extensibility, making it easy to add new payment providers.

### Adding a New Payment Gateway

1. **Create Gateway Class**
   ```php
   // app/Services/Payments/Gateways/NewGateway.php
   <?php

   namespace App\Services\Payments\Gateways;

   use App\Contracts\PaymentGatewayInterface;
   use App\Models\Order;

   class NewGateway implements PaymentGatewayInterface
   {
       public function processPayment(Order $order, array $paymentData): array
       {
           // Implementation for new gateway
       }

       public function getMethod(): string
       {
           return 'new_gateway';
       }

       public function refund(string $paymentId, float $amount): array
       {
           // Refund implementation
       }

       public function getPaymentStatus(string $paymentId): string
       {
           // Status check implementation
       }
   }
   ```

2. **Update Payment Methods Enum**
   ```php
   // app/Enums/PaymentMethod.php
   enum PaymentMethod: string
   {
       case CREDIT_CARD = 'credit_card';
       case PAYPAL = 'paypal';
       case STRIPE = 'stripe';
       case NEW_GATEWAY = 'new_gateway'; // Add new method
   }
   ```

3. **Register in Gateway Factory**
   ```php
   // app/Services/Payments/PaymentGatewayFactory.php
   public function getGateway(PaymentMethod $method): PaymentGatewayInterface
   {
       return match ($method) {
           PaymentMethod::STRIPE => new StripeGateway($this->config),
           PaymentMethod::PAYPAL => new PaypalGateway($this->config),
           PaymentMethod::CREDIT_CARD => new CreditCardGateway($this->config),
           PaymentMethod::NEW_GATEWAY => new NewGateway($this->config),
       };
   }
   ```

4. **Update Configuration**
   ```php
   // config/payments.php
   'enabled' => ['stripe', 'paypal', 'credit_card', 'new_gateway'],
   
   'gateways' => [
       'new_gateway' => [
           'name' => 'New Payment Gateway',
           'description' => 'Description of new gateway',
           'enabled' => env('NEW_GATEWAY_ENABLED', false),
           'api_key' => env('NEW_GATEWAY_API_KEY'),
           'api_secret' => env('NEW_GATEWAY_SECRET'),
           'webhook_secret' => env('NEW_GATEWAY_WEBHOOK_SECRET'),
       ],
   ],
   ```

5. **Add Environment Variables**
   ```bash
   NEW_GATEWAY_ENABLED=true
   NEW_GATEWAY_API_KEY=your_api_key
   NEW_GATEWAY_SECRET=your_secret_key
   NEW_GATEWAY_WEBHOOK_SECRET=your_webhook_secret
   ```

### Gateway Interface Contract
All payment gateways must implement the `PaymentGatewayInterface`:

```php
interface PaymentGatewayInterface
{
    public function processPayment(Order $order, array $paymentData): array;
    public function getMethod(): string;
    public function refund(string $paymentId, float $amount): array;
    public function getPaymentStatus(string $paymentId): string;
}
```

### Testing New Gateways
```bash
# Create gateway-specific tests
php artisan make:test NewGatewayTest

# Run gateway tests
./vendor/bin/phpunit --filter=NewGateway
```

## 🤝 Contributing & Development Notes

### Code Standards
- **PSR-12** coding standard compliance
- **Laravel Pint** for automatic code formatting
- **PHPDoc** blocks for all public methods
- **Type hints** for all method parameters and return types

### Business Rules Architecture
- **Centralized validation** in `BusinessRulesService`
- **Exception-based error handling** with `InvalidArgumentException`
- **Consistent error responses** via global exception handler
- **Domain-driven validation** separate from HTTP concerns

### Database Design Principles
- **Foreign key constraints** with proper cascade rules
- **Soft deletes** for audit trails
- **Timestamps** on all tables for tracking
- **JSON columns** for flexible metadata storage
- **Proper indexing** for query performance

### Security Considerations
- **JWT token expiration** and refresh mechanisms
- **Rate limiting** ready for implementation
- **Input validation** at multiple layers
- **SQL injection prevention** via Eloquent ORM
- **CORS configuration** for API access control

### Performance Considerations
- **Eager loading** relationships to prevent N+1 queries
- **Database query optimization** with proper indexing
- **Caching strategies** for frequently accessed data
- **Queue system** for time-consuming operations
- **Pagination** for large data sets

### Limitations & Future Improvements
- **Payment webhooks** - Currently simulated, webhook handling ready for implementation
- **Real-time notifications** - WebSocket integration planned
- **Advanced reporting** - Analytics and reporting features planned
- **Multi-currency support** - Architecture ready for implementation
- **Advanced inventory management** - Stock tracking capabilities planned

### Environment Configuration
- **Flexible database support** (SQLite for development, MySQL for production)
- **Queue driver configuration** (database, Redis, SQS ready)
- **Cache driver options** (database, Redis, Memcached supported)
- **Mail driver configuration** (SMTP, Mailgun, SES ready)

---
