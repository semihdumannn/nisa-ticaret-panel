# 🏛️ System Architecture - Nisa Ticaret Backend

## 🎯 Architecture Overview

### Design Philosophy
1. **Domain-Driven Design (DDD)** - Business logic first
2. **Modular Monolith** - Not microservices, but prepared for it
3. **Clean Architecture** - Dependency rule (inward only)
4. **CQRS Light** - Separate read/write models where needed
5. **Event-Driven** - Domain events for decoupling

---

## 📐 Module Structure

### Standard Module Layout
```
app/Modules/{ModuleName}/
├── Domain/
│   ├── Models/              # Eloquent models (Entities)
│   ├── ValueObjects/        # Immutable value objects
│   ├── Aggregates/          # Aggregate roots
│   ├── Events/              # Domain events
│   ├── Exceptions/          # Domain-specific exceptions
│   └── Contracts/           # Interfaces (Repository, Service)
│
├── Application/
│   ├── UseCases/            # Business use cases
│   │   ├── Commands/        # Write operations
│   │   └── Queries/         # Read operations
│   ├── DTOs/                # Data Transfer Objects
│   ├── Services/            # Application services
│   └── EventHandlers/       # Event listeners
│
├── Infrastructure/
│   ├── Repositories/        # Database implementation
│   ├── External/            # External service integrations
│   ├── Jobs/                # Queue jobs
│   └── Persistence/         # Migrations, Seeders
│
└── Presentation/
    ├── API/
    │   ├── Controllers/     # API endpoints
    │   ├── Requests/        # Form requests (validation)
    │   ├── Resources/       # API resources (transformers)
    │   └── Routes/          # API routes
    │
    └── Admin/
        ├── Resources/       # Filament resources
        ├── Widgets/         # Filament widgets
        └── Pages/           # Custom Filament pages
```

---

## 🔄 Data Flow

### Write Operation Flow
```
API Request
    ↓
Controller (validates)
    ↓
Use Case / Command Handler
    ↓
Domain Service (business logic)
    ↓
Repository (persist)
    ↓
Domain Event (dispatch)
    ↓
Event Listeners (side effects)
    ↓
API Response
```

### Read Operation Flow (Optimized)
```
API Request
    ↓
Controller
    ↓
Query Handler (bypasses domain)
    ↓
Repository (direct read)
    ↓
API Resource (transform)
    ↓
API Response (cached if possible)
```

---

## 📦 Core Modules

### 1. User Module
**Responsibility:** User management, authentication, authorization

**Entities:**
- User (Aggregate Root)
- UserProfile (Entity)
- Address (Entity)

**Use Cases:**
- RegisterUser
- AuthenticateUser
- UpdateProfile
- ManageAddresses

**Events:**
- UserRegistered
- UserAuthenticated
- ProfileUpdated

**External Services:**
- Firebase Auth (Phone OTP)
- Laravel Sanctum (API tokens)

---

### 2. Product Module
**Responsibility:** Product catalog management

**Entities:**
- Brand (Aggregate Root)
- Category (Aggregate Root, nested)
- Product (Aggregate Root)
- ProductImage (Entity)
- ProductVariant (Entity)

**Use Cases:**
- CreateProduct
- UpdateProduct
- UploadProductImage
- AssignCategories
- CreateVariant

**Events:**
- ProductCreated
- ProductUpdated
- ProductImageUploaded
- VariantCreated

**External Services:**
- MinIO/S3 (Image storage)

---

### 3. Inventory Module
**Responsibility:** Stock tracking and management

**Entities:**
- Warehouse (Aggregate Root)
- Inventory (Entity)
- StockMovement (Entity)

**Use Cases:**
- AdjustStock
- ReserveStock
- ReleaseReservation
- TransferStock
- CheckStockAvailability

**Events:**
- StockAdjusted
- StockReserved
- StockReleased
- LowStockAlert

---

### 4. Order Module
**Responsibility:** Order lifecycle management

**Entities:**
- Order (Aggregate Root)
- OrderItem (Entity)
- OrderStatusHistory (Entity)

**State Machine:**
```
pending → confirmed → preparing → on_the_way → delivered
            ↓                          ↓
        cancelled                  cancelled
```

**Use Cases:**
- CreateOrder
- ConfirmOrder
- UpdateOrderStatus
- CancelOrder
- AssignDeliveryAgent

**Events:**
- OrderCreated
- OrderConfirmed
- OrderStatusChanged
- OrderDelivered
- OrderCancelled

**External Services:**
- Payment Gateway (stub for now)
- FCM (notifications)

---

### 5. Campaign Module
**Responsibility:** Promotions and discounts

**Entities:**
- Campaign (Aggregate Root)
- Coupon (Aggregate Root)
- CouponUsage (Entity)

**Use Cases:**
- CreateCampaign
- ApplyDiscount
- ValidateCoupon
- UseCoupon

**Events:**
- CampaignCreated
- CouponUsed
- DiscountApplied

---

### 6. Notification Module
**Responsibility:** Push notifications and alerts

**Entities:**
- Notification (Aggregate Root)
- FcmToken (Entity)

**Use Cases:**
- SendPushNotification
- RegisterFcmToken
- MarkAsRead

**Events:**
- NotificationSent
- FcmTokenRegistered

**External Services:**
- Firebase Cloud Messaging

---

## 🔗 Shared Kernel

### Common Components (Available to All Modules)

```
app/Shared/
├── Domain/
│   ├── ValueObjects/
│   │   ├── Email.php
│   │   ├── PhoneNumber.php
│   │   ├── Money.php
│   │   └── Address.php
│   │
│   └── Contracts/
│       ├── Repository.php
│       ├── EventDispatcher.php
│       └── CacheRepository.php
│
├── Infrastructure/
│   ├── Cache/
│   │   └── RedisCacheRepository.php
│   ├── Events/
│   │   └── LaravelEventDispatcher.php
│   └── Storage/
│       └── S3StorageAdapter.php
│
└── Presentation/
    ├── Middleware/
    │   ├── EnsureJsonResponse.php
    │   └── LogApiRequest.php
    └── Traits/
        └── ApiResponse.php
```

---

## 🗄️ Database Design Principles

### 1. Table Naming Convention
- Plural, snake_case: `users`, `products`, `order_items`
- Pivot tables: `model1_model2` (alphabetical): `campaign_products`, `product_category`

### 2. Column Naming Convention
- snake_case: `first_name`, `created_at`
- Foreign keys: `{model}_id`: `user_id`, `product_id`
- Boolean: `is_*`, `has_*`: `is_active`, `has_stock`

### 3. Index Strategy
- Primary key: `id` (BIGSERIAL)
- Foreign keys: Always indexed
- Frequent WHERE clauses: Indexed
- Composite indexes: For multi-column queries

### 4. Soft Deletes
- Applied to: `users`, `products`, `brands`, `categories`, `orders`
- Not applied to: `inventory`, `stock_movements`, `notifications`

---

## 🔐 Security Architecture

### Authentication Flow
```
1. Mobile App → Firebase Auth (Phone OTP)
2. Firebase → Custom Token
3. Mobile App → Laravel API (/auth/firebase-login)
4. Laravel → Verify Firebase Token
5. Laravel → Create/Get User
6. Laravel → Generate Sanctum Token
7. Mobile App → Store Sanctum Token
8. All API Calls → Use Sanctum Token (Bearer)
```

### Authorization
- **Spatie Laravel Permission** package
- Roles: `admin`, `customer`, `field_agent`, `delivery`
- Permissions: Granular (e.g., `create-product`, `view-orders`)
- Guard: `sanctum` for API, `web` for Filament

### API Security
- Rate limiting: 60 requests/minute per user
- CORS: Configured for mobile app domain
- Input validation: Form Requests
- SQL Injection: Eloquent ORM (parameterized)
- XSS: Blade auto-escaping

---

## ⚡ Performance Optimization

### Caching Strategy

#### Cache Layers
1. **Application Cache** (Redis)
   - Categories: 24 hours
   - Brands: 24 hours
   - Products: 6 hours
   - Config: 1 hour

2. **Query Cache** (Laravel)
   - Product listings: 10 minutes
   - Category trees: 1 hour

3. **HTTP Cache** (Nginx)
   - Static assets: 1 year
   - API responses: 5 minutes (with tags)

#### Cache Invalidation
```php
// Event-driven cache invalidation
class ProductUpdatedListener
{
    public function handle(ProductUpdated $event)
    {
        Cache::tags(['products', "product:{$event->productId}"])->flush();
    }
}
```

### Database Optimization
- **Connection Pooling:** PgBouncer (Neon has built-in)
- **Query Optimization:** EXPLAIN ANALYZE before indexing
- **Eager Loading:** Always use `with()` to avoid N+1
- **Chunking:** For large datasets (>1000 rows)
- **Database Indexes:** See migration files

### Queue Optimization
- **Priority Queues:** `high`, `default`, `low`
- **Failed Jobs:** Retry 3 times with exponential backoff
- **Job Timeout:** 60 seconds default, 300 for heavy jobs
- **Worker Scaling:** Auto-scale based on queue depth

---

## 🔄 Event-Driven Architecture

### Domain Events
```php
namespace App\Modules\Order\Domain\Events;

class OrderCreated
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $customerId,
        public readonly float $total,
        public readonly Carbon $createdAt
    ) {}
}
```

### Event Listeners
```php
namespace App\Modules\Order\Application\EventHandlers;

class SendOrderConfirmationNotification
{
    public function handle(OrderCreated $event): void
    {
        // Send FCM notification
        // Send email (optional)
        // Send WhatsApp message (optional)
    }
}
```

### Event Registration
```php
// EventServiceProvider
protected $listen = [
    OrderCreated::class => [
        SendOrderConfirmationNotification::class,
        ReserveStock::class,
        UpdateDailyStats::class,
    ],
];
```

---

## 🚀 Job Queue Architecture

### Job Types

#### 1. Immediate Jobs (Synchronous)
- User login
- Product search
- Cart operations

#### 2. Deferred Jobs (Async)
- Image upload & processing
- Email sending
- FCM notifications
- Stock updates
- Daily stats calculation

#### 3. Scheduled Jobs (Cron)
- Daily stats aggregation (midnight)
- Low stock alerts (hourly)
- Abandoned cart reminders (daily)
- Order status sync (every 5 minutes)

### Job Example
```php
namespace App\Modules\Product\Infrastructure\Jobs;

class ProcessProductImage implements ShouldQueue
{
    use Queueable, Dispatchable;

    public function __construct(
        private int $productId,
        private string $imagePath
    ) {}

    public function handle(ImageService $imageService): void
    {
        // Resize, optimize, generate thumbnails
        $processed = $imageService->process($this->imagePath);
        
        // Save to MinIO
        $url = Storage::disk('minio')->put(
            "products/{$this->productId}",
            $processed
        );
        
        // Update database
        ProductImage::create([
            'product_id' => $this->productId,
            'image_url' => $url,
        ]);
    }
}
```

---

## 📊 API Design

### RESTful Conventions
```
GET     /api/products              # List products
GET     /api/products/{id}         # Get product
POST    /api/products              # Create product (admin)
PUT     /api/products/{id}         # Update product (admin)
DELETE  /api/products/{id}         # Delete product (admin)

GET     /api/products/{id}/variants    # Get variants
POST    /api/products/{id}/variants    # Create variant
```

### Response Format
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Coca Cola 1L",
    "price": 25.00
  },
  "message": "Product retrieved successfully",
  "meta": {
    "timestamp": "2024-12-20T10:30:00Z",
    "version": "1.0.0"
  }
}
```

### Error Format
```json
{
  "success": false,
  "message": "Product not found",
  "errors": {
    "product_id": ["The selected product is invalid."]
  },
  "code": "PRODUCT_NOT_FOUND",
  "meta": {
    "timestamp": "2024-12-20T10:30:00Z"
  }
}
```

### Pagination
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

---

## 🧪 Testing Strategy

### Test Pyramid
```
         /\
        /  \  E2E (5%)
       /────\
      /      \  Integration (15%)
     /────────\
    /          \  Unit (80%)
   /────────────\
```

### Test Types

#### 1. Unit Tests
- Domain models
- Value objects
- Services (mocked dependencies)
- Helpers & utilities

#### 2. Feature Tests
- API endpoints (authenticated)
- Use cases (full flow)
- Event listeners
- Job execution

#### 3. Integration Tests
- Database operations
- External services (Firebase, S3)
- Queue processing
- Cache invalidation

### Test Example
```php
namespace Tests\Feature\Order;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_create_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2]
                ]
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['order_number', 'total']]);
        
        $this->assertDatabaseHas('orders', [
            'customer_id' => $user->id,
            'total' => 200
        ]);
    }
}
```

---

## 📈 Scalability Considerations

### Vertical Scaling (Single Server)
- 4 CPU cores: Good for 1000-5000 users
- 8GB RAM: Sufficient for moderate load
- Redis: In-memory caching
- PostgreSQL: Connection pooling

### Horizontal Scaling (Future)
- **Load Balancer:** Nginx/HAProxy
- **App Servers:** Multiple Laravel instances
- **Worker Pool:** Separate queue workers
- **Database:** Read replicas (Neon supports)
- **Cache:** Redis cluster
- **Storage:** S3 (already stateless)

### Monitoring & Alerts
- Laravel Telescope (development)
- Laravel Horizon (queue monitoring)
- Sentry (error tracking)
- Custom metrics (API response time, queue depth)

---

## 🔧 Infrastructure as Code

### Docker Compose Services
```yaml
services:
  app:       # Laravel + Nginx + PHP-FPM
  redis:     # Cache + Queue
  minio:     # Object storage (S3-compatible)
  worker:    # Queue worker
  scheduler: # Cron scheduler
```

### Environment Variables
```env
# App
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database (Neon PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=ep-fragrant-cherry-alstwmya-pooler.c-3.eu-central-1.aws.neon.tech

# Redis
REDIS_HOST=redis

# MinIO
MINIO_ENDPOINT=http://minio:9000
AWS_ACCESS_KEY_ID=minio
AWS_SECRET_ACCESS_KEY=minio123

# Firebase
FIREBASE_PROJECT_ID=nisa-ticaret
FIREBASE_CREDENTIALS=./firebase-credentials.json
```

---

## 🎯 Design Decisions & Rationale

### Why Modular Monolith?
✅ **Simpler than microservices** for initial launch
✅ **Prepared for extraction** if module needs scaling
✅ **Shared database** for ACID transactions
✅ **Easier testing** with single codebase

### Why PostgreSQL?
✅ **JSONB support** for flexible metadata
✅ **Full-text search** built-in
✅ **Robust indexing** (GIN, GIST)
✅ **Neon Cloud** auto-scaling & backups

### Why Filament 5?
✅ **Rapid admin panel** development
✅ **Built on Livewire** (reactive UI)
✅ **Tailwind CSS** (modern design)
✅ **Extensible** with custom pages & widgets

### Why Sanctum?
✅ **Simple token-based** auth for mobile
✅ **SPA support** if needed later
✅ **No OAuth complexity** (Firebase handles OTP)

---

## 📚 References
- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com)
- [Domain-Driven Design](https://domainlanguage.com/ddd/)
- [Clean Architecture](https://blog.cleancoder.com)

---

**Version:** 1.0.0
**Last Updated:** 2024-12-20
