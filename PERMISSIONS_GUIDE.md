# Hướng dẫn sử dụng Permission System

## 1. Chạy Migration và Seeder

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
```

## 2. Sử dụng trong Routes

### Middleware `permission`

```php
// Kiểm tra một permission
Route::middleware(['auth', 'permission:wallet.view'])->group(function () {
    Route::get('wallets/{user}', [WalletController::class, 'show']);
});

// Kiểm tra nhiều permissions (user chỉ cần có 1 trong các permissions)
Route::middleware(['auth', 'permission:wallet.view,wallet.adjust'])->group(function () {
    Route::get('wallets/{user}', [WalletController::class, 'show']);
});
```

### Ví dụ trong routes/web.php

```php
// Wallet Management với permissions
Route::middleware(['auth', 'permission:wallet.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('wallets/{user}', [WalletController::class, 'show'])->name('wallets.show');
    Route::post('wallets/{user}/adjust', [WalletController::class, 'adjust'])
        ->middleware('permission:wallet.adjust')
        ->name('wallets.adjust');
});

// Credit Management với permissions
Route::middleware(['auth', 'permission:credit.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('credits', [CreditController::class, 'index'])->name('credits.index');
    Route::get('credits/{user}/edit', [CreditController::class, 'edit'])
        ->middleware('permission:credit.edit')
        ->name('credits.edit');
});
```

## 3. Sử dụng trong Blade Views

### Directive `@canPermission`

```blade
{{-- Hiển thị nút nếu user có permission --}}
@canPermission('wallet.view')
    <a href="{{ route('admin.wallets.show', $user) }}">Xem ví</a>
@endcanPermission

{{-- Hiển thị form nếu user có permission --}}
@canPermission('wallet.adjust')
    <form method="POST" action="{{ route('admin.wallets.adjust', $user) }}">
        @csrf
        <input type="number" name="amount" step="0.01">
        <button type="submit">Điều chỉnh</button>
    </form>
@endcanPermission
```

### Directive `@canAnyPermission`

```blade
{{-- Hiển thị nếu user có bất kỳ permission nào trong danh sách --}}
@canAnyPermission(['wallet.view', 'wallet.adjust'])
    <div class="wallet-section">
        {{-- Nội dung --}}
    </div>
@endcanAnyPermission
```

### Ví dụ trong sidebar.blade.php

```blade
@canPermission('wallet.view')
    <a href="{{ route('admin.wallets.show', auth()->user()) }}" class="menu-item">
        💰 Wallet
    </a>
@endcanPermission

@canPermission('credit.view')
    <a href="{{ route('admin.credits.index') }}" class="menu-item">
        💳 Credit Management
    </a>
@endcanPermission

@canPermission('top-up.view')
    <a href="{{ route('admin.top-up-requests.index') }}" class="menu-item">
        💵 Top-up Requests
    </a>
@endcanPermission
```

## 4. Sử dụng trong Controllers

```php
use Illuminate\Support\Facades\Auth;

public function show(User $user)
{
    // Kiểm tra permission trong controller
    if (!Auth::user()->hasPermission('wallet.view')) {
        abort(403, 'Bạn không có quyền xem ví.');
    }
    
    // Hoặc sử dụng middleware, không cần check trong controller
    $wallet = $user->wallet;
    return view('admin.wallets.show', compact('wallet', 'user'));
}

public function adjust(Request $request, User $user)
{
    // Kiểm tra permission
    if (!Auth::user()->hasPermission('wallet.adjust')) {
        abort(403, 'Bạn không có quyền điều chỉnh ví.');
    }
    
    // Logic điều chỉnh
}
```

## 5. Danh sách Permissions

### Wallet Management
- `wallet.view` - Xem thông tin ví
- `wallet.adjust` - Điều chỉnh số dư ví

### Credit Management
- `credit.view` - Xem thông tin công nợ
- `credit.edit` - Chỉnh sửa công nợ
- `credit.pay` - Thanh toán công nợ từ ví

### Top-up Request Management
- `top-up.view` - Xem yêu cầu nạp tiền
- `top-up.approve` - Duyệt yêu cầu nạp tiền
- `top-up.reject` - Từ chối yêu cầu nạp tiền
- `top-up.create` - Tạo yêu cầu nạp tiền

### Product Management
- `products.view` - Xem sản phẩm
- `products.create` - Tạo sản phẩm
- `products.edit` - Chỉnh sửa sản phẩm
- `products.delete` - Xóa sản phẩm
- `products.variants` - Quản lý variants
- `products.prices` - Set giá sản phẩm
- `products.printing-prices` - Set giá in
- `products.user-prices` - Set giá riêng cho user
- `products.workshop-prices` - Set giá cho workshop

### Workshop Management
- `workshops.view` - Xem workshops
- `workshops.create` - Tạo workshop
- `workshops.edit` - Chỉnh sửa workshop
- `workshops.delete` - Xóa workshop

### Pricing Tier Management
- `pricing-tiers.view` - Xem pricing tiers
- `pricing-tiers.create` - Tạo pricing tier
- `pricing-tiers.edit` - Chỉnh sửa pricing tier
- `pricing-tiers.delete` - Xóa pricing tier
- `pricing-tiers.users` - Quản lý tier của user

### Market Management
- `markets.view` - Xem markets
- `markets.create` - Tạo market
- `markets.edit` - Chỉnh sửa market
- `markets.delete` - Xóa market

### User Management
- `users.view` - Xem users
- `users.create` - Tạo user
- `users.edit` - Chỉnh sửa user
- `users.delete` - Xóa user

## 6. Gán Permissions cho Roles

### Trong Seeder

```php
// Gán tất cả permissions cho super-admin
$superAdminRole = Role::where('slug', 'super-admin')->first();
$allPermissions = Permission::all();
$superAdminRole->permissions()->sync($allPermissions->pluck('id'));

// Gán một số permissions cho it-admin
$itAdminRole = Role::where('slug', 'it-admin')->first();
$basicPermissions = Permission::whereIn('slug', [
    'wallet.view',
    'credit.view',
    'top-up.view',
])->get();
$itAdminRole->permissions()->sync($basicPermissions->pluck('id'));
```

### Trong Code

```php
use App\Models\Role;
use App\Models\Permission;

// Gán permission cho role
$role = Role::find(1);
$permission = Permission::where('slug', 'wallet.view')->first();
$role->permissions()->attach($permission->id);

// Gán nhiều permissions
$permissions = Permission::whereIn('slug', ['wallet.view', 'wallet.adjust'])->get();
$role->permissions()->sync($permissions->pluck('id'));
```

## 7. Lưu ý

- **Super-admin** tự động có tất cả permissions (không cần check)
- Nếu user không có role, `hasPermission()` sẽ trả về `false`
- Middleware `permission` sẽ tự động redirect về login nếu chưa đăng nhập
- Nếu không có permission, sẽ trả về 403 Forbidden





















































