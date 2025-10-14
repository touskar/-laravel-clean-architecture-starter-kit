# Laravel Clean Architecture Starter Kit

A production-ready Laravel starter kit implementing **Clean Architecture** principles with JWT authentication and OTP verification.

## 🎯 What is Clean Architecture?

Clean Architecture is a software design philosophy that separates concerns into distinct layers, making your code:
- **Testable**: Business logic is independent of frameworks
- **Maintainable**: Changes in one layer don't affect others
- **Flexible**: Easy to swap implementations (databases, frameworks, UI)
- **Scalable**: Clear structure that grows with your application

## 📐 Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                      HTTP Layer                             │
│  Controllers, Middleware, Routes                            │
│  (Receives requests, returns responses)                     │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│                   Application Layer                         │
│  Use Cases, DTOs, Presenters                                │
│  (Orchestrates business logic)                              │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│                     Domain Layer                            │
│  Entities, Repository Interfaces, Service Interfaces        │
│  (Core business rules - framework independent)              │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│                 Infrastructure Layer                        │
│  Repository Implementations, Service Implementations        │
│  Eloquent Models, External APIs                             │
│  (Technical details and framework-specific code)            │
└─────────────────────────────────────────────────────────────┘
```

## 🏗️ Layer Breakdown

### 1. Domain Layer (`app/Domain/`)
**The heart of your application** - Contains business logic and rules.

```
app/Domain/
├── Entities/              # Pure business objects (User, Session, OtpVerification)
├── Repositories/          # Repository interfaces (what operations are available)
└── Services/              # Domain service interfaces (business operations)
```

**Key Principles:**
- ✅ No framework dependencies
- ✅ No database or external service dependencies
- ✅ Pure PHP objects with business rules
- ✅ Defines interfaces, not implementations

**Example - User Entity:**
```php
class User {
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $email,
        // ... other properties
    ) {}

    public function isActive(): bool {
        return $this->status === 'ACTIVE';
    }
}
```

### 2. Application Layer (`app/Application/`)
**Orchestrates** business operations by coordinating Domain and Infrastructure layers.

```
app/Application/
├── UseCases/              # Business use cases (SendOtp, VerifyOtp, etc.)
├── DTOs/                  # Data Transfer Objects
│   ├── Requests/          # Input validation and parsing
│   ├── Responses/         # Structured response objects
│   └── Shared/            # Reusable DTOs
└── Presenters/            # Format use case results for controllers
```

**Key Principles:**
- ✅ One use case per business operation
- ✅ Validates input via Request DTOs
- ✅ Calls domain services and repositories
- ✅ Returns results via Presenters
- ✅ No HTTP knowledge (doesn't know about requests/responses)

**Example - Use Case Flow:**
```php
class SendOtpUseCase {
    public function execute(SendOtpRequest $request): void {
        // 1. Validate input (done by DTO)
        // 2. Check business rules
        // 3. Generate OTP via domain service
        // 4. Save via repository
        // 5. Send via notification service
        // 6. Format response via presenter
    }
}
```

### 3. Infrastructure Layer (`app/Infrastructure/`)
**Technical implementation** - Handles databases, external APIs, and framework-specific code.

```
app/Infrastructure/
├── Models/                # Eloquent models (database representation)
├── Repositories/          # Repository implementations using Eloquent
├── Services/              # Service implementations (JWT, OTP, Auth)
└── Mappers/               # Convert between Domain Entities and Eloquent Models
```

**Key Principles:**
- ✅ Implements domain interfaces
- ✅ Uses Laravel/Eloquent specific features
- ✅ Handles database transactions
- ✅ Manages external service integrations

**Example - Repository Pattern:**
```php
// Domain Layer (Interface)
interface IUserRepository {
    public function findByPhone(string $phone): ?User;
    public function save(User $user): User;
}

// Infrastructure Layer (Implementation)
class UserRepositoryImpl implements IUserRepository {
    public function findByPhone(string $phone): ?User {
        $model = UserModel::where('phone', $phone)->first();
        return $model ? UserMapper::toDomain($model) : null;
    }
}
```

### 4. HTTP Layer (`app/Http/`)
**Entry point** - Handles HTTP requests and responses.

```
app/Http/
├── Controllers/           # Thin controllers (delegate to use cases)
└── Middleware/            # Request processing (JWT validation, etc.)
```

**Key Principles:**
- ✅ Controllers are thin (5-20 lines)
- ✅ Validate HTTP-level concerns only
- ✅ Delegate to use cases
- ✅ Return JSON responses

**Example - Controller:**
```php
public function sendOtp(Request $request): JsonResponse {
    $dto = SendOtpRequest::fromArray($request->all());
    $presenter = app(SendOtpPresenter::class);
    $useCase = app(SendOtpUseCase::class, ['presenter' => $presenter]);

    $useCase->execute($dto);

    return response_json($presenter->getData(), 200);
}
```

## 🔄 Request Flow Example

Let's trace a complete request through all layers:

### Example: User Sends OTP

```
1. HTTP Request
   POST /api/v1/common/auth/send-otp
   Body: { "phone": "+1234567890", "userType": "USER" }

   ↓

2. Controller (HTTP Layer)
   AuthController::sendOtp()
   - Receives HTTP request
   - Extracts data
   - Creates SendOtpRequest DTO
   - Calls SendOtpUseCase

   ↓

3. Use Case (Application Layer)
   SendOtpUseCase::execute()
   - Validates phone number format
   - Checks if recent OTP exists (business rule)
   - Generates OTP via IOtpService
   - Saves via IOtpVerificationRepository
   - Sends SMS (simulated)
   - Formats response via Presenter

   ↓

4. Domain Services (Domain Layer)
   IOtpService::generateOtp()
   - Generates 6-digit OTP
   - Sets expiration (5 minutes)
   - Returns OtpVerification entity

   ↓

5. Repository (Infrastructure Layer)
   OtpVerificationRepositoryImpl::save()
   - Converts Entity to Eloquent Model
   - Saves to database
   - Returns updated Entity

   ↓

6. Presenter (Application Layer)
   SendOtpPresenter::present()
   - Formats success response
   - Includes message and OTP (in dev mode)

   ↓

7. Controller Returns Response
   JSON: { "code": "OTP_SENT", "success": true, "message": "...", "data": {...} }
```

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- PostgreSQL 14+
- Composer
- Laravel 11

### Installation

1. **Clone the repository:**
```bash
git clone https://github.com/touskar/laravel-clean-architecture-starter-kit.git
cd laravel-clean-architecture-starter-kit
```

2. **Install dependencies:**
```bash
composer install
```

3. **Configure environment:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database in `.env`:**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# JWT Configuration
JWT_SECRET=your-secret-key
JWT_TTL=3600

# OTP Configuration
OTP_EXPIRY_MINUTES=5
```

5. **Create database and run migrations:**
```bash
# Create PostgreSQL database
createdb your_database

# Run Laravel migrations
php artisan migrate
```

6. **Start the development server:**
```bash
php artisan serve
```

## 🔍 Reusable Filter System

This starter kit includes a **powerful reusable filter system** for list endpoints with:

- ✅ **Pagination** with `hasMore` for infinite scroll
- ✅ **Full-text search** across multiple fields
- ✅ **Advanced filtering** with operators (`=`, `in`, `gt`, `gte`, `lt`, `lte`, `like`)
- ✅ **Customizable sorting**
- ✅ **Clean Architecture compliant** (filterable fields defined in Domain entities)

### Quick Example

```http
GET /api/v1/users?search=john&filters[status:in]=ACTIVE,PENDING&sortBy=createdAt&page=1
```

### Response Format

```json
{
  "success": true,
  "data": {
    "users": [...],
    "pagination": {
      "currentPage": 1,
      "perPage": 20,
      "total": 156,
      "totalPages": 8,
      "hasMore": true,
      "hasPrevious": false
    }
  }
}
```

### User Entity Searchable/Filterable Fields

The `User` entity comes pre-configured with:

**Searchable fields** (full-text search):
- `name`, `firstName`, `lastName`, `email`, `username`, `phoneNumber`

**Filterable fields** (exact/operator matching):
- `status`: ACTIVE, INACTIVE, BANNED, PENDING
- `userType`: USER, PLATFORM_ADMIN, CONTENT_CREATOR, ADVERTISER

**Sortable fields**:
- `createdAt`, `updatedAt`, `name`, `firstName`, `lastName`, `email`, `username`

### Filter Operators

| Operator | Example | Description |
|----------|---------|-------------|
| `=` (default) | `status=ACTIVE` | Exact match |
| `in` | `status:in=ACTIVE,PENDING` | Match any value |
| `gt` | `createdAt:gt=2024-01-01` | Greater than |
| `gte` | `createdAt:gte=2024-01-01` | Greater than or equal |
| `lt` | `createdAt:lt=2024-12-31` | Less than |
| `lte` | `createdAt:lte=2024-12-31` | Less than or equal |
| `like` | `name:like=john` | Pattern match |

📚 **Full Documentation**: See [`docs/REUSABLE_FILTER_SYSTEM.md`](docs/REUSABLE_FILTER_SYSTEM.md) and [`docs/FILTER_SYSTEM_API_EXAMPLES.md`](docs/FILTER_SYSTEM_API_EXAMPLES.md)

## 📡 API Endpoints

### Authentication Endpoints

#### 1. Send OTP
```http
POST /api/v1/common/auth/send-otp
Content-Type: application/json

{
  "phone": "+1234567890",
  "userType": "USER"
}
```

**Response:**
```json
{
  "code": "OTP_SENT",
  "success": true,
  "message": "Code OTP envoyé avec succès",
  "data": {
    "phone": "+1234567890",
    "expiresAt": "2024-01-15T10:25:00Z",
    "otpCode": "123456"  // Only in development
  }
}
```

#### 2. Verify OTP
```http
POST /api/v1/common/auth/verify-otp
Content-Type: application/json

{
  "phone": "+1234567890",
  "otpCode": "123456"
}
```

**Response:**
```json
{
  "code": "OTP_VERIFIED",
  "success": true,
  "message": "OTP vérifié avec succès",
  "data": {
    "verified": true
  }
}
```

#### 3. Complete Registration
```http
POST /api/v1/common/auth/complete-registration
Content-Type: application/json

{
  "phone": "+1234567890",
  "email": "user@example.com",
  "username": "johndoe",
  "firstName": "John",
  "lastName": "Doe",
  "password": "SecurePass123!",  // Optional (auto-generated if omitted)
  "userType": "USER"
}
```

**Response:**
```json
{
  "code": "REGISTRATION_COMPLETED",
  "success": true,
  "message": "Inscription complétée avec succès",
  "data": {
    "user": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expiresIn": 3600
  }
}
```

#### 4. Get Current User (Protected)
```http
GET /api/v1/common/auth/me
Authorization: Bearer <token>
```

**Response:**
```json
{
  "code": "SUCCESS",
  "success": true,
  "data": {
    "user": {
      "id": "01HK...",
      "username": "johndoe",
      "email": "user@example.com",
      "phone": "+1234567890",
      "userType": "USER",
      "status": "ACTIVE"
    }
  }
}
```

## 🧪 Testing

The Clean Architecture makes testing easy because business logic is isolated:

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

**Example Unit Test (Domain Layer):**
```php
public function test_user_entity_is_active() {
    $user = new User(
        id: '01HK...',
        status: 'ACTIVE',
        // ... other fields
    );

    $this->assertTrue($user->isActive());
}
```

**Example Integration Test (Use Case):**
```php
public function test_send_otp_use_case() {
    $request = new SendOtpRequest(
        phone: '+1234567890',
        userType: 'USER'
    );

    $useCase->execute($request);

    $this->assertDatabaseHas('otp_verifications', [
        'phone' => '+1234567890'
    ]);
}
```

## 🔐 Security Features

- ✅ **JWT Authentication**: Secure stateless authentication
- ✅ **OTP Verification**: Phone number verification via OTP
- ✅ **Password Hashing**: BCrypt with cost factor 12
- ✅ **Token Hashing**: SHA-256 for session token storage
- ✅ **Rate Limiting**: Built-in rate limiting on auth endpoints
- ✅ **Input Validation**: Comprehensive DTO validation
- ✅ **SQL Injection Protection**: Eloquent ORM with parameter binding
- ✅ **CORS Protection**: Configurable CORS middleware

## 📁 Project Structure

```
laravel-clean-architecture-start/
├── app/
│   ├── Domain/                      # Business logic layer
│   │   ├── Entities/                # Pure business objects
│   │   │   ├── User.php
│   │   │   ├── OtpVerification.php
│   │   │   └── Session.php
│   │   ├── Repositories/            # Repository interfaces
│   │   │   ├── IUserRepository.php
│   │   │   ├── IOtpVerificationRepository.php
│   │   │   └── ISessionRepository.php
│   │   └── Services/                # Domain service interfaces
│   │       ├── IAuthenticationService.php
│   │       ├── IJwtService.php
│   │       ├── IOtpService.php
│   │       └── IRandomStringService.php
│   │
│   ├── Application/                 # Application logic layer
│   │   ├── UseCases/                # Business use cases
│   │   │   └── Auth/
│   │   │       ├── SendOtpUseCase.php
│   │   │       ├── VerifyOtpUseCase.php
│   │   │       ├── CompleteRegistrationUseCase.php
│   │   │       └── GetCurrentUserUseCase.php
│   │   ├── DTOs/                    # Data Transfer Objects
│   │   │   ├── Requests/            # Input DTOs
│   │   │   ├── Responses/           # Output DTOs
│   │   │   └── Shared/              # Shared DTOs
│   │   └── Presenters/              # Format use case output
│   │       └── Auth/
│   │
│   ├── Infrastructure/              # Technical implementation layer
│   │   ├── Models/                  # Eloquent models
│   │   │   ├── User.php
│   │   │   ├── OtpVerification.php
│   │   │   └── Session.php
│   │   ├── Repositories/            # Repository implementations
│   │   │   ├── UserRepositoryImpl.php
│   │   │   ├── OtpVerificationRepositoryImpl.php
│   │   │   └── SessionRepositoryImpl.php
│   │   ├── Services/                # Service implementations
│   │   │   ├── AuthenticationServiceImpl.php
│   │   │   ├── JwtServiceImpl.php
│   │   │   ├── OtpServiceImpl.php
│   │   │   └── RandomStringServiceImpl.php
│   │   └── Mappers/                 # Entity ↔ Model conversion
│   │       ├── UserMapper.php
│   │       ├── OtpVerificationMapper.php
│   │       └── SessionMapper.php
│   │
│   ├── Http/                        # HTTP layer
│   │   ├── Controllers/
│   │   │   └── Api/V1/Common/
│   │   │       └── AuthController.php
│   │   └── Middleware/
│   │       └── JwtAuthMiddleware.php
│   │
│   ├── Providers/                   # Service providers
│   │   ├── AppServiceProvider.php
│   │   ├── RepositoryServiceProvider.php
│   │   └── ServicesServiceProvider.php
│   │
│   ├── Exceptions/                  # Global exception handling
│   │   └── Handler.php
│   │
│   └── Helpers/                     # Helper functions
│       └── response_helper.php
│
├── database/
│   ├── migrations/                  # Database migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   └── 0001_01_01_000001_create_otp_verifications_table.php
│   └── seeders/                     # Database seeders
│
├── routes/
│   └── api.php                      # API routes
│
├── config/                          # Configuration files
├── tests/                           # Test files
└── README.md                        # This file
```

## 🔧 Adding New Features

Follow the Clean Architecture flow when adding new features:

### Example 1: Adding a List Endpoint with Filters

**1. Define filterable fields in your Domain Entity:**
```php
// app/Domain/Entities/Product.php
use App\Domain\Traits\HasFilterableFields;

class Product {
    use HasFilterableFields;

    protected static array $searchableFields = ['name', 'description', 'sku'];

    protected static array $filterableFields = [
        'category' => ['type' => 'string'],
        'status' => [
            'type' => 'string',
            'allowed_values' => ['ACTIVE', 'INACTIVE']
        ],
        'price' => ['type' => 'float'],
    ];

    protected static array $sortableFields = ['createdAt', 'name', 'price'];
}
```

**2. Create List Request DTO:**
```php
// app/Application/DTOs/Requests/ListProductsRequest.php
class ListProductsRequest {
    public function __construct(
        public readonly ListQueryRequest $listQuery
    ) {}

    public static function fromArray(array $data): self {
        return new self(ListQueryRequest::fromArray($data));
    }

    public function validate(): array {
        return $this->listQuery->validate(
            allowedSortFields: Product::getSortableFields(),
            allowedFilterFields: Product::getFilterableFields()
        );
    }
}
```

**3. Use QueryFilterService in Repository:**
```php
// app/Infrastructure/Repositories/ProductRepositoryImpl.php
public function findAll(ListQueryRequest $listQuery): array {
    $query = ProductModel::query()->with(['category']);

    $fieldMapping = [
        'createdAt' => 'created_at',
        // ... map domain fields to database columns
    ];

    return $this->queryFilterService->applyFilters(
        $query,
        $listQuery,
        Product::class,
        $fieldMapping
    );
}
```

**4. Create Controller Method:**
```php
public function list(Request $request): JsonResponse {
    $dto = ListProductsRequest::fromArray($request->all());
    $presenter = app(ListProductsPresenter::class);
    $useCase = app(ListProductsUseCase::class, ['presenter' => $presenter]);

    $useCase->execute($dto);

    return response_json($presenter->getData(), 200);
}
```

**Usage:**
```http
GET /api/products?search=laptop&filters[status:in]=ACTIVE,FEATURED&sortBy=price&page=1
```

### Example 2: Adding a "Reset Password" Feature

**1. Domain Layer** - Define business rules
```php
// app/Domain/Entities/PasswordReset.php
class PasswordReset {
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $token,
        public readonly DateTimeImmutable $expiresAt
    ) {}

    public function isExpired(): bool {
        return new DateTimeImmutable() > $this->expiresAt;
    }
}

// app/Domain/Repositories/IPasswordResetRepository.php
interface IPasswordResetRepository {
    public function findByToken(string $token): ?PasswordReset;
    public function save(PasswordReset $reset): PasswordReset;
}
```

**2. Application Layer** - Create use case
```php
// app/Application/UseCases/Auth/ResetPasswordUseCase.php
class ResetPasswordUseCase {
    public function __construct(
        private readonly IPasswordResetRepository $resetRepo,
        private readonly IUserRepository $userRepo,
        private readonly IAuthenticationService $authService,
        private readonly ResetPasswordPresenter $presenter
    ) {}

    public function execute(ResetPasswordRequest $request): void {
        // 1. Find reset token
        // 2. Validate not expired
        // 3. Hash new password
        // 4. Update user
        // 5. Delete reset token
        // 6. Present result
    }
}
```

**3. Infrastructure Layer** - Implement repository
```php
// app/Infrastructure/Repositories/PasswordResetRepositoryImpl.php
class PasswordResetRepositoryImpl implements IPasswordResetRepository {
    public function findByToken(string $token): ?PasswordReset {
        $model = PasswordResetModel::where('token', $token)->first();
        return $model ? PasswordResetMapper::toDomain($model) : null;
    }
}
```

**4. HTTP Layer** - Create controller method
```php
// app/Http/Controllers/Api/V1/Common/AuthController.php
public function resetPassword(Request $request): JsonResponse {
    $dto = ResetPasswordRequest::fromArray($request->all());
    $presenter = app(ResetPasswordPresenter::class);
    $useCase = app(ResetPasswordUseCase::class, ['presenter' => $presenter]);

    $useCase->execute($dto);

    return response_json($presenter->getData(), 200);
}
```

**5. Register bindings** - Update service provider
```php
// app/Providers/RepositoryServiceProvider.php
$this->app->bind(IPasswordResetRepository::class, PasswordResetRepositoryImpl::class);
```

## 📚 Best Practices

### ✅ DO:
- Keep controllers thin (delegate to use cases)
- Use dependency injection everywhere
- Define interfaces in Domain layer
- Implement interfaces in Infrastructure layer
- Use DTOs for data transfer
- Write tests for business logic
- Use repository pattern for data access
- Keep entities framework-independent

### ❌ DON'T:
- Put business logic in controllers
- Use Eloquent models in use cases
- Access database directly from controllers
- Mix HTTP concerns with business logic
- Couple domain layer to framework
- Skip input validation
- Return Eloquent models from repositories
- Use static methods (breaks testability)

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Follow Clean Architecture principles
4. Write tests for new features
5. Update documentation
6. Commit your changes (`git commit -m 'feat: Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

## 📖 Further Reading

- [Clean Architecture by Robert C. Martin](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Laravel Documentation](https://laravel.com/docs)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
- [Repository Pattern](https://martinfowler.com/eaaCatalog/repository.html)
- [JWT Authentication](https://jwt.io/introduction)

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 👨‍💻 Author

**Touskar**
- GitHub: [@touskar](https://github.com/touskar)

## 🙏 Acknowledgments

- Clean Architecture principles by Robert C. Martin
- Laravel framework by Taylor Otwell
- The PHP community

---

**Happy Coding! 🚀**

If you find this starter kit helpful, please give it a ⭐ on GitHub!
