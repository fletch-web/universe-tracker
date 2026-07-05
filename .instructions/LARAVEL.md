# Laravel Framework & Best Practices Guide

This document serves as the developer instructions and best-practices guide for the Laravel codebase in the Universe Tracker repository. It is designed to be parsed and utilized by AI coding assistants and developers alike.

---

## 1. Modern Architecture (Laravel 13 & PHP 8.3+)

Laravel 13 simplifies the application skeleton by eliminating several configuration files and boilerplate folders. Crucial initialization logic is centralized.

### 1.1 Application Configuration: `bootstrap/app.php`
Instead of standard HTTP/Console Kernel classes and `app/Exceptions/Handler.php`, the entire routing, middleware, and exception handling are configured fluently in `bootstrap/app.php`:

```php
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
```

- **Routing:** Web routes are registered under `routes/web.php` and CLI commands in `routes/console.php`.
- **Middleware:** Middlewares can be appended, prepended, excluded, or grouped in the `$middleware` callback. Cookie encryption exclusions are defined here.
- **Exceptions:** Use the `$exceptions` callback to customize exception rendering, logging, or custom JSON responses.

---

## 2. Eloquent ORM Best Practices

### 2.1 Model Declaration with PHP Attributes (Laravel 11/12/13 style)
Laravel 11+ supports PHP attributes on the Model class definition for settings like `$fillable` and `$hidden`, keeping model attributes clean and readable.

```php
namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

> [!NOTE]
> Define casting in a `casts()` method rather than the legacy `$casts` property. This allows calling class methods (like `AsEnumCollection` or custom cast classes) cleanly.

### 2.2 Eager Loading & The N+1 Query Problem
Never run database queries inside loops or Blade/Vue templates. Eager load relations to prevent the N+1 problem.

*   **Bad:**
    ```php
    // Triggers 1 query for books, and N queries for each book's author
    $books = Book::all();
    foreach ($books as $book) {
        echo $book->author->name;
    }
    ```
*   **Good:**
    ```php
    // Triggers only 2 queries
    $books = Book::with('author')->get();
    foreach ($books as $book) {
        echo $book->author->name;
    }
```

If a relationship needs loading dynamically after the query is run, use **lazy eager loading**:
```php
$books->load('author');
```

To prevent accidental N+1 queries during development, prevent lazy loading in the application boot service provider:
```php
use Illuminate\Database\Eloquent\Model;

Model::preventLazyLoading(! app()->isProduction());
```

### 2.3 Query Scopes
Encapsulate query filters inside Eloquent Local Scopes prefixing the method name with `scope`.

```php
class Universe extends Model
{
    /**
     * Scope a query to only include active universes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by owner.
     */
    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}

// Usage:
$activeUniverses = Universe::active()->ownedBy($user)->get();
```

### 2.4 Database Transactions
Always wrap operations that execute multiple write statements inside a transaction to ensure database consistency.

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($user, $universeData) {
    $universe = Universe::create($universeData);
    $user->universes()->attach($universe->id);
    
    // If any exception is thrown, the transaction rolls back automatically
});
```

---

## 3. Controllers & Routing

### 3.1 Controller Single Responsibility
*   Keep controllers thin. Business logic should reside in dedicated Services, Actions, or Custom Model methods.
*   Prefer **Single Action Controllers** using the `__invoke` method when a controller only does one job.

```php
// Route registration
Route::post('/universes/{universe}/publish', PublishUniverseController::class);

// Controller implementation
namespace App\Http\Controllers;

use App\Models\Universe;
use Illuminate\Http\RedirectResponse;

class PublishUniverseController extends Controller
{
    public function __invoke(Universe $universe): RedirectResponse
    {
        $universe->publish();

        return back()->with('toast', 'Universe published successfully!');
    }
}
```

### 3.2 Route Model Binding
Use implicit model binding by matching route parameter names with controller action parameters:

```php
// Route:
Route::get('/universes/{universe}', [UniverseController::class, 'show']);

// Controller:
public function show(Universe $universe)
{
    return Inertia::render('universes/Show', [
        'universe' => $universe
    ]);
}
```

*   **Custom Keys:** To bind using a field other than `id`, specify it in the route definition:
    ```php
    Route::get('/universes/{universe:slug}', [UniverseController::class, 'show']);
    ```
*   **Scoped Binding:** When using nested routes, automatically scope child model checks relative to the parent:
    ```php
    Route::get('/universes/{universe}/planets/{planet}', [PlanetController::class, 'show'])->scopeBindings();
    ```

---

## 4. Request Validation & Form Requests

Never validate requests manually or run ad-hoc validation arrays inside controller actions. Use **Form Requests** to encapsulate validation rules and authorization.

### 4.1 Creating Form Requests
Generate a Form Request class:
```bash
php artisan make:request StoreUniverseRequest
```

### 4.2 Form Request Example
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUniverseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user is allowed to perform this action
        return $this->user()->can('create', Universe::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('universes')],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['fantasy', 'sci-fi', 'steampunk'])],
        ];
    }
}
```

Inject this Request into your controller methods; Laravel will validate automatically before entering the controller:

```php
public function store(StoreUniverseRequest $request)
{
    // The request is already validated. Retreive safe data using:
    $validated = $request->validated();
    
    Universe::create($validated);
    
    return redirect()->route('universes.index');
}
```

---

## 5. Security Practices

1.  **Mass Assignment:** Avoid using `request()->all()` to update or create models. Rely solely on validated inputs: `Model::create($request->validated())`.
2.  **Authorization (Policies & Gates):** Keep permissions centralized in Policy files (`app/Policies`). Apply authorization checks on controllers or routes.
    ```php
    // In Controllers:
    $this->authorize('update', $universe); // Or using Gate facade
    Gate::authorize('update', $universe);
    ```
3.  **XSS & Blade Directives:** Blade automatically escapes strings rendered via `{{ $value }}`. For Inertia apps, Vue handles DOM escaping by default (e.g., standard data bindings `{{ value }}`). Be careful when using `v-html` or `{!! $value !!}`.
4.  **SQL Injection:** Use Eloquent query builders or bindings. Never pass un-sanitized user inputs directly to `DB::raw()`, `whereRaw()`, or raw query statements.

---

## 6. Inertia.js with Vue 3 (Vite Integration)

Since this app uses Inertia.js with Vue 3, response rendering should follow Inertia specifications.

### 6.1 Rendering Pages
```php
use Inertia\Inertia;
use Inertia\Response;

class UniverseController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('universes/Index', [
            'universes' => Universe::latest()->paginate(10),
        ]);
    }
}
```

### 6.2 Lazy Loading Prop Values
Improve performance on complex pages by utilizing Lazy Loading for expensive datasets:
```php
return Inertia::render('universes/Show', [
    'universe' => $universe,
    'events' => Inertia::lazy(fn () => $universe->loadEvents()),
]);
```

### 6.3 Flash Alerts
Laravel session flash messages can be sent via redirects and processed by the Inertia front-end:
```php
return redirect()->route('universes.index')
    ->with('toast', 'Universe created successfully!');
```
Make sure `HandleInertiaRequests.php` registers these fields within the `share` array:
```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'flash' => [
            'toast' => $request->session()->get('toast'),
        ],
    ]);
}
```

---

## 7. Testing with Pest PHP

This repository uses Pest PHP for testing. Pest provides an expressive, fluid interface to test-driven development.

### 7.1 Best Practices
*   Use `it()` for descriptive sentences.
*   Use the standard HTTP response assertions to test routes, redirects, and rendering.
*   Utilize database transaction assertions and `RefreshDatabase` trait.

### 7.2 Example Test Suite
```php
use App\Models\User;
use App\Models\Universe;
use function Pest\Laravel\{actingAs, get, post, assertDatabaseHas};

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('renders the universes index page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('universes.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('universes/Index'));
});

it('allows authenticated users to create a universe', function () {
    $user = User::factory()->create();
    $universeData = [
        'name' => 'Aloria',
        'type' => 'fantasy',
    ];

    actingAs($user)
        ->post(route('universes.store'), $universeData)
        ->assertRedirect(route('universes.index'));

    assertDatabaseHas('universes', [
        'name' => 'Aloria',
        'type' => 'fantasy',
    ]);
});
```

---

## 8. Development Commands & Lints

Make sure to enforce lint rules and type checks before committing changes.

1.  **Pint Linting:** Style formatting is enforced with Laravel Pint:
    ```bash
    composer run lint
    ```
2.  **Static Analysis:** Static typing verification is done using PHPStan (Larastan):
    ```bash
    composer run types:check
    ```
3.  **Run Tests:** Execute the test suite (lints, types, and pest tests):
    ```bash
    composer test
    ```
