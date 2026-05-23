<?php

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Address;
use App\Modules\User\Domain\ValueObjects\UserRole;
use App\Modules\User\Application\DTOs\CreateAddressDTO;
use App\Modules\User\Application\DTOs\UpdateProfileDTO;
use App\Modules\User\Application\UseCases\ManageAddressUseCase;
use App\Modules\User\Application\UseCases\UpdateProfileUseCase;
use App\Modules\User\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── ValueObject Tests ─────────────────────────────────────────────────────────

test('UserRole enum has correct values', function () {
    expect(UserRole::CUSTOMER->value)->toBe('customer');
    expect(UserRole::ADMIN->value)->toBe('admin');
    expect(UserRole::FIELD_AGENT->value)->toBe('field_agent');
    expect(UserRole::DELIVERY->value)->toBe('delivery');
});

test('UserRole provides human-readable labels', function () {
    expect(UserRole::CUSTOMER->label())->toBe('Customer');
    expect(UserRole::ADMIN->label())->toBe('Admin');
    expect(UserRole::FIELD_AGENT->label())->toBe('Field Agent');
});

test('UserRole::values() returns all role strings', function () {
    $values = UserRole::values();
    expect($values)->toContain('customer', 'admin', 'field_agent', 'delivery');
    expect($values)->toHaveCount(4);
});

// ── User Model Tests ──────────────────────────────────────────────────────────

test('User model has correct fillable attributes', function () {
    $user = new User();
    expect($user->getFillable())->toContain('firebase_uid', 'phone', 'name', 'email', 'role');
});

test('User scopeActive filters correctly', function () {
    User::factory()->create(['is_active' => true, 'phone' => '+905551111111']);
    User::factory()->create(['is_active' => false, 'phone' => '+905552222222']);

    expect(User::active()->count())->toBe(1);
});

test('User scopeByRole filters correctly', function () {
    User::factory()->create(['role' => 'admin', 'phone' => '+905553333333']);
    User::factory()->create(['role' => 'customer', 'phone' => '+905554444444']);

    expect(User::byRole('admin')->count())->toBe(1);
    expect(User::byRole('customer')->count())->toBe(1);
});

test('User isAdmin returns correct boolean', function () {
    $admin    = new User(['role' => 'admin']);
    $customer = new User(['role' => 'customer']);

    expect($admin->isAdmin())->toBeTrue();
    expect($customer->isAdmin())->toBeFalse();
});

test('User has profile relationship', function () {
    $user = User::factory()->create(['phone' => '+905555555555']);
    $user->profile()->create([]);

    expect($user->profile)->toBeInstanceOf(UserProfile::class);
});

test('User has addresses relationship', function () {
    $user = User::factory()->create(['phone' => '+905556666666']);
    $user->addresses()->create(['full_address' => 'Test Street 1']);

    expect($user->addresses)->toHaveCount(1);
});

// ── Repository Tests ───────────────────────────────────────────────────────────

test('EloquentUserRepository can find user by firebase uid', function () {
    $user = User::factory()->create(['firebase_uid' => 'test-uid-123', 'phone' => '+905557777777']);
    $repo = app(EloquentUserRepository::class);

    expect($repo->findByFirebaseUid('test-uid-123'))->not->toBeNull();
    expect($repo->findByFirebaseUid('nonexistent'))->toBeNull();
});

test('EloquentUserRepository can create user', function () {
    $repo = app(EloquentUserRepository::class);
    $user = $repo->create([
        'name'  => 'Test User',
        'phone' => '+905558888888',
        'role'  => 'customer',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('Test User');
});

// ── UseCase Tests ─────────────────────────────────────────────────────────────

test('UpdateProfileUseCase updates user name', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'phone' => '+905559999999']);
    $user->profile()->create([]);

    $useCase = app(UpdateProfileUseCase::class);
    $updated = $useCase->execute($user, new UpdateProfileDTO(name: 'New Name'));

    expect($updated->name)->toBe('New Name');
});

test('UpdateProfileUseCase updates profile company', function () {
    $user = User::factory()->create(['phone' => '+905550001111']);
    $user->profile()->create([]);

    $useCase = app(UpdateProfileUseCase::class);
    $useCase->execute($user, new UpdateProfileDTO(companyName: 'ACME Corp'));

    expect($user->fresh()->profile->company_name)->toBe('ACME Corp');
});

test('ManageAddressUseCase creates address for user', function () {
    $user    = User::factory()->create(['phone' => '+905550002222']);
    $useCase = app(ManageAddressUseCase::class);

    $address = $useCase->create($user, new CreateAddressDTO(
        fullAddress: 'Atatürk Caddesi No:1',
        city:        'Istanbul',
        isDefault:   true,
    ));

    expect($address)->toBeInstanceOf(Address::class);
    expect($address->full_address)->toBe('Atatürk Caddesi No:1');
    expect($address->is_default)->toBeTrue();
});

test('ManageAddressUseCase setDefault removes previous default', function () {
    $user    = User::factory()->create(['phone' => '+905550003333']);
    $useCase = app(ManageAddressUseCase::class);

    $first  = $useCase->create($user, new CreateAddressDTO(fullAddress: 'First St', isDefault: true));
    $second = $useCase->create($user, new CreateAddressDTO(fullAddress: 'Second St', isDefault: false));

    $useCase->setDefault($user, $second);

    expect($first->fresh()->is_default)->toBeFalse();
    expect($second->fresh()->is_default)->toBeTrue();
});
