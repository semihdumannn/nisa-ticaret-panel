# 📅 Development Phases - Nisa Ticaret Backend

## 🎯 Phase Execution Model

### Completion Criteria (Every Phase)
- [ ] All tasks completed
- [ ] All tests passing (green)
- [ ] Code reviewed by @agent-qa
- [ ] Documentation updated
- [ ] Working demo available
- [ ] Git commit with phase tag

### Task Status Icons
- ⏳ Not Started
- 🔄 In Progress
- ✅ Completed
- ❌ Blocked
- ⚠️ Needs Review

---

## 📦 Phase 0: Foundation & Infrastructure

**Duration:** 3-4 days
**Goal:** Complete development environment setup
**Deliverable:** Working Laravel app with Docker, connected to Neon PostgreSQL

### Task 0.1: Project Initialization
**Agent:** @agent-devops
**Priority:** P0 (Critical)

#### Subtasks:
- [ ] 0.1.1 Create Laravel 13 project
  ```bash
  composer create-project laravel/laravel nisa-ticaret-backend
  cd nisa-ticaret-backend
  ```
- [ ] 0.1.2 Initialize Git repository
  ```bash
  git init
  git add .
  git commit -m "chore: initial Laravel 11 setup"
  ```
- [ ] 0.1.3 Create `.editorconfig` for consistency
- [ ] 0.1.4 Setup `.gitignore` (add vendor, .env, etc.)

**Dependencies:** None
**Testing:** `php artisan --version` should show Laravel 11.x

---

### Task 0.2: Docker Environment
**Agent:** @agent-devops
**Priority:** P0

#### Subtasks:
- [ ] 0.2.1 Create `Dockerfile` for app service
  - PHP 8.3-FPM
  - Nginx
  - Required extensions: pgsql, redis, gd, zip
- [ ] 0.2.2 Create `docker-compose.yml`
  - Services: app, redis, minio, worker, scheduler
- [ ] 0.2.3 Create `docker/nginx/default.conf`
- [ ] 0.2.4 Create `.dockerignore`
- [ ] 0.2.5 Build and test containers
  ```bash
  docker-compose build
  docker-compose up -d
  docker-compose ps
  ```

**Dependencies:** Task 0.1
**Testing:** All containers should be healthy

---

### Task 0.3: PostgreSQL Connection (Neon)
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 0.3.1 Update `.env` with Neon credentials
  ```env
  DB_CONNECTION=pgsql
  DB_HOST=ep-fragrant-cherry-alstwmya-pooler.c-3.eu-central-1.aws.neon.tech
  DB_PORT=5432
  DB_DATABASE=nisa-ticaret
  DB_USERNAME=neondb_owner
  DB_PASSWORD=npg_Pqm7TpvJOZa1
  DB_SSLMODE=require
  ```
- [ ] 0.3.2 Update `config/database.php` for SSL
  ```php
  'pgsql' => [
      // ...
      'sslmode' => env('DB_SSLMODE', 'prefer'),
  ],
  ```
- [ ] 0.3.3 Test connection
  ```bash
  php artisan db:show
  ```

**Dependencies:** Task 0.2
**Testing:** `php artisan db:show` shows Neon PostgreSQL

---

### Task 0.4: Core Dependencies
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 0.4.1 Install Laravel Sanctum
  ```bash
  composer require laravel/sanctum
  php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
  ```
- [ ] 0.4.2 Install Filament 5
  ```bash
  composer require filament/filament:"^3.2" -W
  php artisan filament:install --panels
  ```
- [ ] 0.4.3 Install Spatie Packages
  ```bash
  composer require spatie/laravel-permission
  composer require spatie/laravel-activitylog
  ```
- [ ] 0.4.4 Install Dev Dependencies
  ```bash
  composer require --dev pestphp/pest
  composer require --dev pestphp/pest-plugin-laravel
  php artisan pest:install
  ```

**Dependencies:** Task 0.3
**Testing:** `composer show` lists all packages

---

### Task 0.5: Database Migrations
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 0.5.1 Copy migration files (23 files from previous work)
- [ ] 0.5.2 Run migrations
  ```bash
  php artisan migrate
  ```
- [ ] 0.5.3 Verify all tables created
  ```bash
  php artisan db:table users
  php artisan db:table products
  # ... check all 23 tables
  ```

**Dependencies:** Task 0.4
**Testing:** `php artisan db:show --tables` shows 23 tables

---

### Task 0.6: Modular Structure Setup
**Agent:** @agent-architect
**Priority:** P1

#### Subtasks:
- [ ] 0.6.1 Create module directories
  ```bash
  mkdir -p app/Modules/{User,Product,Order,Inventory,Campaign,Notification}
  ```
- [ ] 0.6.2 Create standard subdirectories for each module
  ```bash
  # For each module:
  mkdir -p app/Modules/User/{Domain,Application,Infrastructure,Presentation}
  mkdir -p app/Modules/User/Domain/{Models,ValueObjects,Events,Contracts}
  mkdir -p app/Modules/User/Application/{UseCases,DTOs,Services}
  mkdir -p app/Modules/User/Infrastructure/{Repositories,Jobs}
  mkdir -p app/Modules/User/Presentation/{API,Admin}
  ```
- [ ] 0.6.3 Create Shared kernel
  ```bash
  mkdir -p app/Shared/{Domain,Infrastructure,Presentation}
  ```
- [ ] 0.6.4 Setup PSR-4 autoloading in `composer.json`
  ```json
  "autoload": {
      "psr-4": {
          "App\\": "app/",
          "Modules\\": "app/Modules/"
      }
  }
  ```
- [ ] 0.6.5 Run `composer dump-autoload`

**Dependencies:** Task 0.5
**Testing:** Directory structure created successfully

---

### Task 0.7: Filament Admin User
**Agent:** @agent-frontend
**Priority:** P1

#### Subtasks:
- [ ] 0.7.1 Create admin user via command
  ```bash
  php artisan make:filament-user
  # Email: admin@nisaticaret.com
  # Name: Admin User
  # Password: password
  ```
- [ ] 0.7.2 Test Filament login
  - Visit: `http://localhost/admin`
  - Login with admin credentials
- [ ] 0.7.3 Customize Filament branding
  - Logo: Fuska branding
  - Colors: #E73A99, #13275A, #00A6AB

**Dependencies:** Task 0.4
**Testing:** Admin panel accessible and functional

---

### Task 0.8: API Setup & Testing
**Agent:** @agent-backend
**Priority:** P1

#### Subtasks:
- [ ] 0.8.1 Create API route structure
  ```php
  // routes/api.php
  Route::prefix('v1')->group(function () {
      Route::get('/health', [HealthController::class, 'check']);
  });
  ```
- [ ] 0.8.2 Create HealthController
  ```php
  class HealthController extends Controller
  {
      public function check()
      {
          return response()->json([
              'status' => 'ok',
              'timestamp' => now(),
              'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
          ]);
      }
  }
  ```
- [ ] 0.8.3 Test API endpoint
  ```bash
  curl http://localhost/api/v1/health
  ```

**Dependencies:** Task 0.3
**Testing:** Health endpoint returns JSON with database status

---

### Task 0.9: Redis & Queue Setup
**Agent:** @agent-devops
**Priority:** P1

#### Subtasks:
- [ ] 0.9.1 Update `.env` for Redis
  ```env
  CACHE_DRIVER=redis
  SESSION_DRIVER=redis
  QUEUE_CONNECTION=redis
  REDIS_HOST=redis
  REDIS_PORT=6379
  ```
- [ ] 0.9.2 Test Redis connection
  ```bash
  php artisan tinker
  >>> Cache::put('test', 'value', 60)
  >>> Cache::get('test')
  ```
- [ ] 0.9.3 Create test job
  ```bash
  php artisan make:job TestJob
  ```
- [ ] 0.9.4 Dispatch and process job
  ```bash
  php artisan tinker
  >>> TestJob::dispatch()
  
  php artisan queue:work --once
  ```

**Dependencies:** Task 0.2
**Testing:** Redis responds, job processes successfully

---

### Task 0.10: Storage & MinIO Setup
**Agent:** @agent-devops
**Priority:** P1

#### Subtasks:
- [ ] 0.10.1 Update `.env` for MinIO
  ```env
  FILESYSTEM_DISK=minio
  
  MINIO_ENDPOINT=http://minio:9000
  MINIO_KEY=minio
  MINIO_SECRET=minio123
  MINIO_REGION=us-east-1
  MINIO_BUCKET=nisa-ticaret
  ```
- [ ] 0.10.2 Create MinIO disk in `config/filesystems.php`
  ```php
  'minio' => [
      'driver' => 's3',
      'key' => env('MINIO_KEY'),
      'secret' => env('MINIO_SECRET'),
      'region' => env('MINIO_REGION'),
      'bucket' => env('MINIO_BUCKET'),
      'endpoint' => env('MINIO_ENDPOINT'),
      'use_path_style_endpoint' => true,
  ],
  ```
- [ ] 0.10.3 Test file upload
  ```bash
  php artisan tinker
  >>> Storage::disk('minio')->put('test.txt', 'content')
  >>> Storage::disk('minio')->exists('test.txt')
  ```

**Dependencies:** Task 0.2
**Testing:** File uploaded to MinIO successfully

---

### Phase 0 Acceptance Criteria
- [x] Docker environment running
- [x] PostgreSQL (Neon) connected
- [x] All 23 tables migrated
- [x] Filament admin panel accessible
- [x] API health endpoint working
- [x] Redis cache functional
- [x] MinIO storage functional
- [x] Modular directory structure created
- [x] All tests passing

**Deliverable:** Tag `v0.1.0` - Foundation Complete

---

## 👤 Phase 1: User Module

**Duration:** 4-5 days
**Goal:** Complete authentication and user management
**Deliverable:** Working auth API + Filament user management

### Task 1.1: User Domain Model
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 1.1.1 Create User model (already exists, enhance)
  ```bash
  # app/Modules/User/Domain/Models/User.php
  ```
- [ ] 1.1.2 Add relationships (profile, addresses)
- [ ] 1.1.3 Add scopes (active, role-based)
- [ ] 1.1.4 Add accessors/mutators
- [ ] 1.1.5 Create UserProfile model
- [ ] 1.1.6 Create Address model

**Dependencies:** Phase 0
**Testing:** Unit tests for User model

---

### Task 1.2: Firebase Auth Integration
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 1.2.1 Install Firebase Admin SDK
  ```bash
  composer require kreait/firebase-php
  ```
- [ ] 1.2.2 Create Firebase service
  ```php
  // app/Modules/User/Infrastructure/External/FirebaseAuthService.php
  ```
- [ ] 1.2.3 Create verify token method
- [ ] 1.2.4 Add Firebase credentials to `.env`
  ```env
  FIREBASE_PROJECT_ID=nisa-ticaret
  FIREBASE_CREDENTIALS=./firebase-credentials.json
  ```

**Dependencies:** Task 1.1
**Testing:** Firebase token verification works

---

### Task 1.3: Authentication API
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 1.3.1 Create AuthController
  ```php
  POST /api/v1/auth/firebase-login
  POST /api/v1/auth/logout
  GET  /api/v1/auth/me
  ```
- [ ] 1.3.2 Create LoginRequest (validation)
- [ ] 1.3.3 Create AuthResource (response transformer)
- [ ] 1.3.4 Implement Sanctum token generation
- [ ] 1.3.5 Add authentication middleware to API routes

**Dependencies:** Task 1.2
**Testing:** Feature test for auth flow

---

### Task 1.4: User Profile Management
**Agent:** @agent-backend
**Priority:** P1

#### Subtasks:
- [ ] 1.4.1 Create ProfileController
  ```php
  GET  /api/v1/profile
  PUT  /api/v1/profile
  POST /api/v1/profile/avatar
  ```
- [ ] 1.4.2 Create UpdateProfileRequest
- [ ] 1.4.3 Create ProfileResource
- [ ] 1.4.4 Handle avatar upload (MinIO)

**Dependencies:** Task 1.3
**Testing:** Feature test for profile CRUD

---

### Task 1.5: Address Management API
**Agent:** @agent-backend
**Priority:** P1

#### Subtasks:
- [ ] 1.5.1 Create AddressController
  ```php
  GET    /api/v1/addresses
  POST   /api/v1/addresses
  PUT    /api/v1/addresses/{id}
  DELETE /api/v1/addresses/{id}
  POST   /api/v1/addresses/{id}/set-default
  ```
- [ ] 1.5.2 Create Address CRUD requests
- [ ] 1.5.3 Create AddressResource
- [ ] 1.5.4 Add validation (lat/lng optional)

**Dependencies:** Task 1.4
**Testing:** Feature test for address CRUD

---

### Task 1.6: Role & Permission System
**Agent:** @agent-backend
**Priority:** P1

#### Subtasks:
- [ ] 1.6.1 Setup Spatie Permission
  ```bash
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  php artisan migrate
  ```
- [ ] 1.6.2 Create RoleSeeder
  ```php
  // Roles: admin, customer, field_agent, delivery
  ```
- [ ] 1.6.3 Create PermissionSeeder
  ```php
  // Permissions: manage-products, manage-orders, etc.
  ```
- [ ] 1.6.4 Assign roles in User model

**Dependencies:** Task 1.1
**Testing:** User has role and permissions

---

### Task 1.7: Filament User Resource
**Agent:** @agent-frontend
**Priority:** P1

#### Subtasks:
- [ ] 1.7.1 Create UserResource
  ```bash
  php artisan make:filament-resource User --generate
  ```
- [ ] 1.7.2 Customize form fields
  - Name, Email, Phone, Role, Status
- [ ] 1.7.3 Customize table columns
  - Searchable, Sortable, Filterable
- [ ] 1.7.4 Add bulk actions (activate, deactivate)
- [ ] 1.7.5 Add custom actions (reset password, send notification)

**Dependencies:** Task 1.6
**Testing:** Manual test in Filament panel

---

### Task 1.8: User API Documentation
**Agent:** @agent-qa
**Priority:** P2

#### Subtasks:
- [ ] 1.8.1 Install L5-Swagger
  ```bash
  composer require darkaonline/l5-swagger
  ```
- [ ] 1.8.2 Add Swagger annotations to AuthController
- [ ] 1.8.3 Add annotations to ProfileController
- [ ] 1.8.4 Generate API docs
  ```bash
  php artisan l5-swagger:generate
  ```

**Dependencies:** Task 1.5
**Testing:** API docs accessible at `/api/documentation`

---

### Task 1.9: User Module Tests
**Agent:** @agent-qa
**Priority:** P0

#### Subtasks:
- [ ] 1.9.1 Unit tests for User model
- [ ] 1.9.2 Feature tests for auth flow
  - Firebase login
  - Token generation
  - Logout
- [ ] 1.9.3 Feature tests for profile management
- [ ] 1.9.4 Feature tests for address CRUD
- [ ] 1.9.5 Run all tests
  ```bash
  php artisan test --filter=User
  ```

**Dependencies:** All User tasks
**Testing:** 100% test coverage for User module

---

### Phase 1 Acceptance Criteria
- [x] Firebase auth working
- [x] Sanctum token generation
- [x] User profile CRUD
- [x] Address management
- [x] Role & permission system
- [x] Filament user resource
- [x] API documentation
- [x] All tests passing (>80% coverage)

**Deliverable:** Tag `v0.2.0` - User Module Complete

---

## 📦 Phase 2: Product Module

**Duration:** 5-6 days
**Goal:** Complete product catalog management
**Deliverable:** Full product CRUD with Filament admin

### Task 2.1: Brand Domain Model
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 2.1.1 Create Brand model
- [ ] 2.1.2 Add slug generation (via observer)
- [ ] 2.1.3 Add logo upload handling
- [ ] 2.1.4 Create BrandRepository
- [ ] 2.1.5 Unit tests for Brand

**Dependencies:** Phase 1
**Testing:** Brand model tests pass

---

### Task 2.2: Category Domain Model
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 2.2.1 Create Category model
- [ ] 2.2.2 Add nested category support (parent_id)
- [ ] 2.2.3 Add slug generation
- [ ] 2.2.4 Create CategoryRepository
- [ ] 2.2.5 Add tree traversal methods
- [ ] 2.2.6 Unit tests for Category

**Dependencies:** Task 2.1
**Testing:** Nested categories work correctly

---

### Task 2.3: Product Domain Model
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 2.3.1 Create Product model
- [ ] 2.3.2 Add relationships (brand, categories, images, variants)
- [ ] 2.3.3 Add slug generation
- [ ] 2.3.4 Add price calculation methods
- [ ] 2.3.5 Create ProductRepository
- [ ] 2.3.6 Unit tests for Product

**Dependencies:** Task 2.2
**Testing:** Product model tests pass

---

### Task 2.4: Product Images & Variants
**Agent:** @agent-backend
**Priority:** P1

#### Subtasks:
- [ ] 2.4.1 Create ProductImage model
- [ ] 2.4.2 Create ProductVariant model
- [ ] 2.4.3 Handle image upload to MinIO
- [ ] 2.4.4 Generate thumbnails (if needed)
- [ ] 2.4.5 Unit tests for images & variants

**Dependencies:** Task 2.3
**Testing:** Images upload successfully

---

### Task 2.5: Product API Endpoints
**Agent:** @agent-backend
**Priority:** P0

#### Subtasks:
- [ ] 2.5.1 Create ProductController
  ```php
  GET    /api/v1/products (public)
  GET    /api/v1/products/{id} (public)
  POST   /api/v1/products (admin)
  PUT    /api/v1/products/{id} (admin)
  DELETE /api/v1/products/{id} (admin)
  ```
- [ ] 2.5.2 Add filtering (category, brand, price range)
- [ ] 2.5.3 Add sorting (name, price, created_at)
- [ ] 2.5.4 Add pagination (15 items per page)
- [ ] 2.5.5 Create ProductResource (transformer)

**Dependencies:** Task 2.4
**Testing:** Feature tests for product API

---

### Task 2.6: Category & Brand API
**Agent:** @agent-backend
**Priority:** P1

#### Subtasks:
- [ ] 2.6.1 Create CategoryController
  ```php
  GET /api/v1/categories (public, with products count)
  GET /api/v1/categories/{id}/products
  ```
- [ ] 2.6.2 Create BrandController
  ```php
  GET /api/v1/brands (public)
  GET /api/v1/brands/{id}/products
  ```
- [ ] 2.6.3 Add caching (24 hours for categories/brands)

**Dependencies:** Task 2.5
**Testing:** Feature tests for category/brand API

---

### Task 2.7: Filament Product Resource
**Agent:** @agent-frontend
**Priority:** P0

#### Subtasks:
- [ ] 2.7.1 Create ProductResource
  ```bash
  php artisan make:filament-resource Product --generate
  ```
- [ ] 2.7.2 Customize form
  - Name, SKU, Description, Price, Brand, Categories
  - Image upload (multiple)
  - Variants (repeater)
- [ ] 2.7.3 Customize table
  - Searchable, Sortable, Filterable
  - Image preview, Price, Stock, Status
- [ ] 2.7.4 Add bulk actions (activate, deactivate, delete)
- [ ] 2.7.5 Add custom actions (duplicate product)

**Dependencies:** Task 2.6
**Testing:** Manual test in Filament

---

### Task 2.8: Filament Category & Brand Resources
**Agent:** @agent-frontend
**Priority:** P1

#### Subtasks:
- [ ] 2.8.1 Create CategoryResource (tree view)
- [ ] 2.8.2 Create BrandResource
- [ ] 2.8.3 Add bulk import/export (CSV/Excel)

**Dependencies:** Task 2.7
**Testing:** Manual test in Filament

---

### Task 2.9: Product Search
**Agent:** @agent-backend
**Priority:** P2

#### Subtasks:
- [ ] 2.9.1 Install Laravel Scout
  ```bash
  composer require laravel/scout
  ```
- [ ] 2.9.2 Setup database driver (or Meilisearch if needed)
- [ ] 2.9.3 Make Product searchable
- [ ] 2.9.4 Add search API endpoint
  ```php
  GET /api/v1/products/search?q={query}
  ```

**Dependencies:** Task 2.7
**Testing:** Search returns relevant products

---

### Task 2.10: Product Module Tests
**Agent:** @agent-qa
**Priority:** P0

#### Subtasks:
- [ ] 2.10.1 Unit tests for all models
- [ ] 2.10.2 Feature tests for product API
- [ ] 2.10.3 Feature tests for category/brand API
- [ ] 2.10.4 Integration tests for image upload
- [ ] 2.10.5 Run all tests
  ```bash
  php artisan test --filter=Product
  ```

**Dependencies:** All Product tasks
**Testing:** 100% test coverage

---

### Phase 2 Acceptance Criteria
- [x] Product catalog functional
- [x] Multi-category support
- [x] Brand-based organization
- [x] Image upload working
- [x] Variant system
- [x] Filament CRUD complete
- [x] API endpoints working
- [x] All tests passing

**Deliverable:** Tag `v0.3.0` - Product Module Complete

---

## 📊 Phase 3-8: Remaining Modules

**(Inventory, Order, Campaign, Notification, Analytics, Testing)**

Each phase follows the same structure:
1. Domain modeling
2. Repository implementation
3. API endpoints
4. Filament resources
5. Tests
6. Documentation

**Full phase details available in individual phase files:**
- `phases/phase-3-inventory.md`
- `phases/phase-4-order.md`
- `phases/phase-5-campaign.md`
- `phases/phase-6-notification.md`
- `phases/phase-7-analytics.md`
- `phases/phase-8-testing.md`

---

## 🎯 Claude Code Execution Model

### Agent Task Assignment
```
@agent-backend execute phase-1 task-1.1
```

### Task Completion Workflow
1. Agent reads task description
2. Agent generates code
3. Agent runs tests
4. Agent commits changes
5. Agent marks task as ✅
6. Agent moves to next task

### Progress Tracking
```
Phase 0: ✅ 10/10 tasks (100%)
Phase 1: 🔄 5/9 tasks (56%)
Phase 2: ⏳ 0/10 tasks (0%)
```

---

**Version:** 1.0.0
**Last Updated:** 2024-12-20
