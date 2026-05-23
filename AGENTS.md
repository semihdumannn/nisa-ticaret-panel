# 🤖 Agent System - Nisa Ticaret Backend

## 🎭 Agent Roles & Responsibilities

### Agent Interaction Model
```
┌──────────────┐
│   User       │
│  (You)       │
└──────┬───────┘
       │ "Start Phase 1"
       ↓
┌──────────────────────────────────┐
│   Claude Code                     │
│   (Orchestrator)                  │
└──────┬───────────────────────────┘
       │
       ├──→ @agent-architect
       ├──→ @agent-backend
       ├──→ @agent-frontend
       ├──→ @agent-qa
       └──→ @agent-devops
```

---

## 🏛️ @agent-architect

**Primary Responsibility:** System design and architecture decisions

### Capabilities
- Design database schemas
- Define module boundaries
- Create architectural diagrams
- Review design patterns
- Document technical decisions

### Typical Tasks
- Design domain models
- Define module interfaces
- Plan API structure
- Review code architecture
- Create ADR (Architecture Decision Records)

### Task Examples
```
@agent-architect design-module User
@agent-architect review-schema products_table
@agent-architect create-adr "Why PostgreSQL over MySQL"
```

### Deliverables
- Domain model diagrams
- Database ERDs
- Module dependency graphs
- Architecture Decision Records
- Technical specifications

---

## 👨‍💻 @agent-backend

**Primary Responsibility:** Laravel backend implementation

### Capabilities
- Write Laravel code (Models, Controllers, Services)
- Implement business logic
- Create API endpoints
- Write database migrations
- Handle queue jobs

### Typical Tasks
- Create Eloquent models
- Implement repositories
- Build API controllers
- Write service classes
- Create background jobs
- Database migrations & seeders

### Task Examples
```
@agent-backend create-model Product
@agent-backend implement-api ProductController
@agent-backend create-job ProcessProductImage
@agent-backend write-migration create_products_table
```

### Code Standards
```php
// Follow Laravel conventions
namespace App\Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'sku',
        'name',
        'price',
        // ...
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getPriceFormattedAttribute(): string
    {
        return number_format($this->price, 2) . ' TL';
    }
}
```

### Deliverables
- Laravel models
- API controllers
- Service classes
- Repository implementations
- Queue jobs
- Database migrations

---

## 🎨 @agent-frontend

**Primary Responsibility:** Filament admin panel implementation

### Capabilities
- Create Filament resources
- Design admin forms
- Build dashboard widgets
- Customize admin UI
- Implement custom actions

### Typical Tasks
- Create Filament resources (CRUD)
- Design form layouts
- Build table columns & filters
- Create dashboard widgets
- Implement bulk actions
- Customize Filament pages

### Task Examples
```
@agent-frontend create-resource Product
@agent-frontend design-form ProductResource
@agent-frontend create-widget SalesOverview
@agent-frontend customize-dashboard AdminPanel
```

### Code Standards
```php
// Filament Resource Example
namespace App\Modules\Product\Presentation\Admin\Resources;

use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(200),
                    
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true),
                    
                Forms\Components\Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('TL')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('price')
                    ->money('TRY')
                    ->sortable(),
                    
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
```

### Deliverables
- Filament resources
- Dashboard widgets
- Custom form components
- Admin panel pages
- Bulk actions

---

## 🧪 @agent-qa

**Primary Responsibility:** Testing, quality assurance, code review

### Capabilities
- Write unit tests (PHPUnit/Pest)
- Write feature tests
- Write integration tests
- Perform code reviews
- Check code coverage
- Security audits

### Typical Tasks
- Write test cases
- Review pull requests
- Check test coverage
- Perform security audits
- Validate API responses
- Test edge cases

### Task Examples
```
@agent-qa write-tests ProductTest
@agent-qa review-code ProductController
@agent-qa check-coverage Product module
@agent-qa security-audit API endpoints
```

### Code Standards
```php
// Pest PHP Test Example
use App\Modules\Product\Domain\Models\Product;

test('authenticated user can create product', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/products', [
            'name' => 'Coca Cola 1L',
            'sku' => 'CC-1L',
            'price' => 25.00,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'sku', 'price']
        ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Coca Cola 1L',
        'sku' => 'CC-1L',
    ]);
});

test('guest cannot create product', function () {
    $response = $this->postJson('/api/v1/products', [
        'name' => 'Product',
    ]);

    $response->assertStatus(401);
});
```

### Quality Gates
- [ ] All tests passing (green)
- [ ] Code coverage >80%
- [ ] No security vulnerabilities
- [ ] No code smells
- [ ] PSR-12 coding standards
- [ ] Documentation up-to-date

### Deliverables
- Unit tests
- Feature tests
- Integration tests
- Code review reports
- Security audit reports
- Test coverage reports

---

## 📦 @agent-devops

**Primary Responsibility:** Infrastructure, deployment, CI/CD

### Capabilities
- Docker configuration
- CI/CD pipelines
- Environment setup
- Deployment automation
- Monitoring setup

### Typical Tasks
- Create Dockerfiles
- Setup docker-compose
- Configure CI/CD (GitHub Actions)
- Environment configuration
- Deployment scripts
- Monitoring setup

### Task Examples
```
@agent-devops setup-docker
@agent-devops create-ci-pipeline
@agent-devops configure-env production
@agent-devops setup-monitoring
```

### Docker Configuration
```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "80:80"
    volumes:
      - .:/var/www/html
    environment:
      - APP_ENV=local
      - DB_CONNECTION=pgsql
    depends_on:
      - redis
      - minio

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

  minio:
    image: minio/minio
    ports:
      - "9000:9000"
      - "9001:9001"
    environment:
      MINIO_ROOT_USER: minio
      MINIO_ROOT_PASSWORD: minio123
    command: server /data --console-address ":9001"

  worker:
    build:
      context: .
      dockerfile: Dockerfile
    command: php artisan queue:work --tries=3
    depends_on:
      - app
      - redis

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    command: php artisan schedule:work
    depends_on:
      - app
```

### CI/CD Pipeline
```yaml
# .github/workflows/ci.yml
name: CI

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          
      - name: Install Dependencies
        run: composer install
        
      - name: Run Tests
        run: php artisan test --coverage
        
      - name: Upload Coverage
        uses: codecov/codecov-action@v3
```

### Deliverables
- Dockerfile
- docker-compose.yml
- CI/CD pipelines
- Environment configs
- Deployment scripts
- Monitoring dashboards

---

## 🔄 Agent Workflow

### Task Execution Flow
```
1. Task Assignment
   └─→ Agent receives task specification

2. Context Loading
   └─→ Agent reads relevant documentation
   └─→ Agent reviews existing code
   └─→ Agent understands requirements

3. Code Generation
   └─→ Agent writes code following standards
   └─→ Agent adds comments & documentation
   └─→ Agent follows architectural patterns

4. Testing
   └─→ Agent runs tests (if @agent-backend/frontend)
   └─→ Agent fixes failing tests
   └─→ Agent ensures green build

5. Documentation
   └─→ Agent updates relevant docs
   └─→ Agent adds code comments
   └─→ Agent creates examples

6. Commit & Review
   └─→ Agent commits with descriptive message
   └─→ Agent marks task as complete
   └─→ @agent-qa reviews (if needed)

7. Next Task
   └─→ Agent picks next task
   └─→ Repeat from step 1
```

---

## 📝 Agent Communication Protocol

### Task Assignment Format
```
@agent-{role} {action} {target} [options]

Examples:
@agent-backend create-model Product
@agent-frontend create-resource User --with-permissions
@agent-qa test-module Order --coverage
@agent-devops deploy staging
```

### Progress Reporting
```
Task: 1.1.1 Create User Model
Status: 🔄 In Progress
Agent: @agent-backend
Progress: 75% (3/4 subtasks complete)
ETA: 5 minutes
```

### Completion Report
```
Task: 1.1.1 Create User Model
Status: ✅ Completed
Agent: @agent-backend
Files Created:
  - app/Modules/User/Domain/Models/User.php
  - tests/Unit/UserTest.php
Tests: 5/5 passing
Coverage: 95%
Commit: abc123f
```

---

## 🎯 Agent Collaboration

### Cross-Agent Dependencies
```
@agent-architect designs → @agent-backend implements
@agent-backend creates → @agent-qa tests
@agent-frontend builds → @agent-qa validates
@agent-backend/frontend code → @agent-devops deploys
```

### Example Collaboration Flow
```
Phase 1: User Module

1. @agent-architect
   └─→ Design User domain model
   └─→ Define API contract

2. @agent-backend
   └─→ Implement User model
   └─→ Create API endpoints
   └─→ Write migrations

3. @agent-frontend
   └─→ Create Filament UserResource
   └─→ Build admin forms

4. @agent-qa
   └─→ Write unit tests
   └─→ Write feature tests
   └─→ Review all code
   └─→ Check coverage

5. @agent-devops
   └─→ Update CI/CD
   └─→ Deploy to staging
   └─→ Monitor logs
```

---

## 🚀 Quick Command Reference

### Start Phase
```bash
# Claude Code orchestrates agents automatically
"Start Phase 1"
```

### Manual Agent Assignment
```bash
@agent-backend execute phase-1 task-1.1
@agent-frontend execute phase-1 task-1.7
@agent-qa test phase-1
@agent-devops deploy staging
```

### Status Check
```bash
"Show Phase 1 progress"
"What tasks are remaining?"
"Which agent is working on what?"
```

---

**Version:** 1.0.0
**Last Updated:** 2024-12-20
