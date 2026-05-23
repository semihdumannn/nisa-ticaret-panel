# 🎯 Nisa Ticaret - E-Commerce Platform

> **Production-grade, modular e-commerce backend with Laravel 13 + PostgreSQL + Filament 5**

## 📋 Project Overview

**Mission:** Build a professional multi-role e-commerce system for beverage distribution company with:
- **Mobile App:** Flutter (Customer, Field Agent, Delivery, Admin)
- **Web Admin:** Filament 5 (Full management panel)
- **API:** Laravel 11 REST API (Sanctum auth)
- **Database:** PostgreSQL (Neon Cloud)
- **Infrastructure:** Docker-based, production-ready

---

## 🏗️ Architecture Principles

### Core Principles
1. ✅ **Domain-Driven Modular Design** - Not a monolith
2. ✅ **API-First** - Mobile app is primary consumer
3. ✅ **Async Job Processing** - No heavy operations in requests
4. ✅ **Docker-Native** - Single machine or multi-worker ready
5. ✅ **Production-Grade** - Code quality, testing, monitoring
6. ✅ **Idempotent Jobs** - Safe to retry
7. ✅ **Abstracted Storage** - S3-compatible (MinIO/AWS)
8. ✅ **State Machine** - All transitions logged
9. ✅ **Test-Driven** - Unit, Feature, Integration tests

### Architecture Pattern
```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT LAYER                          │
├─────────────────────────────────────────────────────────┤
│  Flutter Mobile App       │    Filament Admin Panel     │
│  (iOS + Android)          │    (Web - Laravel Blade)    │
└───────────┬───────────────┴──────────┬──────────────────┘
            │                          │
            └──────────┬───────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│                    API GATEWAY                           │
├─────────────────────────────────────────────────────────┤
│              Laravel 11 REST API                         │
│           (Sanctum + Firebase Auth)                      │
└───────────┬─────────────────────────────────────────────┘
            │
    ┌───────┴────────┬────────────────┬──────────────┐
    ↓                ↓                ↓              ↓
┌─────────┐    ┌──────────┐    ┌──────────┐   ┌──────────┐
│PostgreSQL│    │  Redis   │    │ Firebase │   │  MinIO   │
│ (Neon)   │    │  Queue   │    │ Auth+FCM │   │ Storage  │
└──────────┘    └──────────┘    └──────────┘   └──────────┘
```

---

## 🎭 Agent System

### Primary Agents

#### 🏛️ **Architect Agent** (@agent-architect)
- System design decisions
- Module structure definition
- Database schema design
- Infrastructure planning

#### 👨‍💻 **Backend Agent** (@agent-backend)
- Laravel code implementation
- API endpoints
- Service layer
- Repository pattern

#### 🎨 **Frontend Agent** (@agent-frontend)
- Filament resources
- Admin panel customization
- Blade components
- UI/UX implementation

#### 🧪 **QA Agent** (@agent-qa)
- Test writing (Unit, Feature, Integration)
- Code review
- Performance testing
- Security audit

#### 📦 **DevOps Agent** (@agent-devops)
- Docker setup
- CI/CD pipeline
- Environment configuration
- Deployment automation

---

## 📊 Technical Stack

### Backend
```yaml
Framework: Laravel 11
PHP: 8.3
Database: PostgreSQL 16 (Neon Cloud)
Cache: Redis 7
Queue: Redis (Laravel Queue)
Storage: MinIO (S3-compatible)
Auth: Laravel Sanctum + Firebase Auth
Admin: Filament 5
Search: Laravel Scout (optional)
```

### Infrastructure
```yaml
Container: Docker + Docker Compose
Web Server: Nginx
Process Manager: Supervisor
Queue Worker: Laravel Queue Worker
Cache: Redis
Database: PostgreSQL (Neon - managed)
Storage: MinIO (local) or S3 (production)
```

### Testing
```yaml
Unit Tests: PHPUnit
Feature Tests: Laravel HTTP Tests
Integration Tests: Pest PHP
API Testing: Postman/Insomnia
CI/CD: GitHub Actions
```

---

## 🗂️ Modular Structure

### Module-Based Organization
```
app/
├── Modules/
│   ├── User/              # User & Auth module
│   │   ├── Domain/        # Business logic
│   │   ├── Application/   # Use cases
│   │   ├── Infrastructure/# Persistence
│   │   └── Presentation/  # API & Filament
│   │
│   ├── Product/           # Product catalog module
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   └── Presentation/
│   │
│   ├── Order/             # Order management module
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   └── Presentation/
│   │
│   ├── Inventory/         # Stock management module
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   └── Presentation/
│   │
│   ├── Campaign/          # Campaigns & Coupons module
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   └── Presentation/
│   │
│   └── Notification/      # FCM & Notifications module
│       ├── Domain/
│       ├── Application/
│       ├── Infrastructure/
│       └── Presentation/
│
├── Shared/                # Shared kernel
│   ├── Domain/
│   ├── Infrastructure/
│   └── Presentation/
│
└── Support/               # Helpers & Utilities
```

---

## 🎯 Development Phases

### Phase 0: Foundation (Week 1)
**Goal:** Infrastructure & Core Setup
- [x] Docker environment
- [x] Laravel 11 installation
- [x] PostgreSQL connection (Neon)
- [x] Redis setup
- [x] Filament 5 installation
- [x] Sanctum auth setup
- [x] Git repository structure

**Deliverable:** Working Laravel app with Docker

### Phase 1: User Module (Week 2)
**Goal:** Authentication & User Management
- [ ] User domain model
- [ ] Firebase Auth integration
- [ ] Sanctum token management
- [ ] User profile management
- [ ] Address management
- [ ] Role & Permission system
- [ ] Filament User Resource
- [ ] API endpoints (auth, profile)

**Deliverable:** Working auth system + admin user management

### Phase 2: Product Module (Week 3)
**Goal:** Product Catalog Management
- [ ] Brand domain model
- [ ] Category domain model (nested)
- [ ] Product domain model
- [ ] Product variants
- [ ] Product images (MinIO integration)
- [ ] Multi-category support
- [ ] Filament Resources (Brand, Category, Product)
- [ ] API endpoints (products, categories, brands)

**Deliverable:** Full product catalog with admin CRUD

### Phase 3: Inventory Module (Week 4)
**Goal:** Stock Management System
- [ ] Warehouse domain model
- [ ] Inventory tracking
- [ ] Stock movements (in/out/transfer)
- [ ] Stock reservation system
- [ ] Low stock alerts
- [ ] Filament Inventory Resource
- [ ] API endpoints (stock check)

**Deliverable:** Working inventory system

### Phase 4: Order Module (Week 5)
**Goal:** Order Management System
- [ ] Order domain model
- [ ] Order state machine
- [ ] Cart service (stateless)
- [ ] Order creation pipeline
- [ ] Order status tracking
- [ ] Payment integration (stub)
- [ ] Filament Order Resource
- [ ] API endpoints (cart, orders)

**Deliverable:** Complete order flow

### Phase 5: Campaign Module (Week 6)
**Goal:** Marketing & Discounts
- [ ] Campaign domain model
- [ ] Coupon system
- [ ] Discount engine
- [ ] Campaign-product association
- [ ] Usage tracking
- [ ] Filament Campaign Resource
- [ ] API endpoints (campaigns, coupons)

**Deliverable:** Working promotion system

### Phase 6: Notification Module (Week 7)
**Goal:** Push Notifications & Alerts
- [ ] FCM token management
- [ ] Notification domain model
- [ ] Push notification service
- [ ] Email notifications (optional)
- [ ] SMS notifications (optional)
- [ ] Filament Notification Center
- [ ] API endpoints (fcm token, notifications)

**Deliverable:** Working notification system

### Phase 7: Analytics & Reporting (Week 8)
**Goal:** Business Intelligence
- [ ] Daily stats aggregation
- [ ] Sales reports
- [ ] Customer analytics
- [ ] Product performance
- [ ] Filament Dashboard widgets
- [ ] API endpoints (analytics)

**Deliverable:** Admin dashboard with insights

### Phase 8: Testing & Optimization (Week 9)
**Goal:** Production Readiness
- [ ] Unit tests (80% coverage)
- [ ] Feature tests
- [ ] Integration tests
- [ ] Performance optimization
- [ ] Security audit
- [ ] API documentation (Swagger)
- [ ] Deployment scripts

**Deliverable:** Production-ready system

---

## 🔧 Database Connection

```env
# Neon PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=ep-fragrant-cherry-alstwmya-pooler.c-3.eu-central-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=nisa-ticaret
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_Pqm7TpvJOZa1
DB_SSLMODE=require

# Connection String
DATABASE_URL=postgresql://neondb_owner:npg_Pqm7TpvJOZa1@ep-fragrant-cherry-alstwmya-pooler.c-3.eu-central-1.aws.neon.tech/nisa-ticaret?sslmode=require
```

---

## 🐳 Docker Setup

### Services
```yaml
services:
  - app (Laravel + PHP 8.3 + Nginx)
  - redis (Cache + Queue)
  - minio (S3-compatible storage)
  - worker (Queue worker)
  - scheduler (Laravel scheduler)
```

### Commands
```bash
# Start all services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Install dependencies
docker-compose exec app composer install

# Queue worker
docker-compose exec worker php artisan queue:work

# Run tests
docker-compose exec app php artisan test
```

---

## 📝 Task Breakdown

### Each Phase Contains:
1. **Domain Modeling** - Define entities, value objects, aggregates
2. **Application Layer** - Use cases, DTOs, commands
3. **Infrastructure** - Repository implementation, external services
4. **Presentation** - API controllers + Filament resources
5. **Testing** - Unit + Feature tests
6. **Documentation** - API docs, README updates

---

## 🚀 Quick Start (For Claude Code)

### Step 1: Clone & Setup
```bash
# Claude Code will handle this automatically
composer create-project laravel/laravel nisa-ticaret-backend
cd nisa-ticaret-backend
```

### Step 2: Environment Setup
```bash
# Copy .env.example to .env
# Update with Neon PostgreSQL credentials
```

### Step 3: Dependencies
```bash
composer require laravel/sanctum
composer require filament/filament:"^3.2" -W
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
```

### Step 4: Docker Up
```bash
docker-compose up -d
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

### Step 5: Access
```
Admin Panel: http://localhost/admin
API: http://localhost/api
```

---

## 🎓 Agent Workflow

### For Claude Code Agent:

1. **Read Phase Document** (`phases/phase-{N}.md`)
2. **Understand Tasks** (broken into subtasks)
3. **Generate Code** (following architecture patterns)
4. **Run Tests** (automated)
5. **Commit Changes** (with descriptive messages)
6. **Update Progress** (mark tasks as done)
7. **Move to Next Task**

### Example Agent Command:
```
@agent-backend execute phase-1 task-1.1 "Implement User Domain Model"
```

---

## 📚 Documentation Structure

```
.claude/
├── ARCHITECTURE.md       # Detailed architecture
├── PHASES.md            # All phases with tasks
├── AGENTS.md            # Agent definitions
├── CONVENTIONS.md       # Coding standards
├── DATABASE_SCHEMA.md   # Database design
└── phases/
    ├── phase-0.md       # Foundation
    ├── phase-1.md       # User Module
    ├── phase-2.md       # Product Module
    └── ...
```

---

## 🔐 Security Checklist

- [x] HTTPS only (production)
- [x] Sanctum token auth
- [x] CORS configuration
- [x] Rate limiting
- [x] SQL injection prevention (Eloquent)
- [x] XSS prevention (Blade escaping)
- [x] CSRF protection
- [x] Input validation
- [x] File upload validation
- [ ] Security audit (before production)

---

## 📊 Monitoring & Logging

```yaml
Logging: Laravel Log (daily rotation)
Error Tracking: Sentry (optional)
Performance: Laravel Telescope (dev only)
API Monitoring: Custom middleware
Queue Monitoring: Horizon (optional)
```

---

## 🎯 Success Criteria

### Phase Completion Criteria:
1. ✅ All tasks marked as done
2. ✅ All tests passing (green)
3. ✅ Code reviewed
4. ✅ Documentation updated
5. ✅ Working demo available
6. ✅ Deployed to staging (if applicable)

### Final Deliverable:
- Production-ready Laravel API
- Fully functional Filament admin panel
- 100+ unit/feature tests
- API documentation (Swagger/Postman)
- Docker deployment ready
- CI/CD pipeline configured

---

## 🚀 Let's Build!

**Next Step:** Claude Code will execute Phase 0 tasks automatically.

**Your Role:** Just say "Start Phase 0" and monitor progress.

**Agent Role:** Execute tasks, run tests, commit code, move forward.

---

**Version:** 1.0.0
**Last Updated:** 2024-12-20
**Project Lead:** Claude Code + Development Team
