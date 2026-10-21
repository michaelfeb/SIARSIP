# Backend development base

## Status and purpose

This is the **target development convention for the revamp**. It is not a claim that all existing controllers already follow it.

The goal is a small, maintainable Laravel structure for the retained modules:

- Berkas Sidang Nol
- Template Surat
- Carousel
- Link Landing Page
- Authentication and account settings

It intentionally avoids a Repository layer, large domain framework, and many one-method classes. The application is small enough for one service per module.

## Request flow

```text
Route
  → Controller
    → Form Request: authorize, validate, normalize HTTP input
      → Module Service: query data or perform workflow
        → Eloquent models, storage, PDF/export integrations
  → Inertia response, redirect, download, or JSON response
```

The controller is the HTTP entry point. The service is the module's home for application queries and actions.

## Target directory structure

```text
app/
  Http/
    Controllers/
      BerkasSidangNolController.php
      TemplateSuratController.php
      CarouselController.php
      LinkLandingPageController.php
    Requests/
      SidangNol/
        IndexSidangNolRequest.php
        StoreSidangNolRequest.php
        DecisionSidangNolRequest.php
      TemplateSurat/
        IndexTemplateSuratRequest.php
        StoreTemplateSuratRequest.php
  Services/
    SidangNolService.php
    TemplateSuratService.php
    CarouselService.php
    LinkLandingPageService.php
  Models/
  Policies/
```

Create request subdirectories only when a module has more than one request. Keep one service file per module unless there is clear evidence that it has become unmanageably large.

## Layer responsibilities

| Layer | Responsible for | Must not own |
| --- | --- | --- |
| Route | URI, HTTP verb, middleware, route model binding | Business logic or database queries |
| Controller | Receive request, call the relevant service, choose response type | Validation rules, query construction, workflow logic |
| Form Request | Input authorization, validation, normalized/validated input | Database workflows, storage actions, response rendering |
| Module Service | Module queries, state changes, transactions, storage/PDF/export orchestration | HTTP redirects, Inertia rendering, reading raw request input |
| Model | Relations, casts, fillable fields, small reusable query scopes | Full workflows or HTTP behavior |
| Policy / middleware | Authorization | Data transformations or workflow side effects |

## Form Requests

A Form Request is Laravel's request object with validation and authorization attached. Laravel runs it before the controller method.

Use one when an endpoint accepts filters, form data, files, or decision input. A plain `Request` remains fine for a genuinely input-free endpoint.

```php
namespace App\Http\Requests\SidangNol;

use Illuminate\Foundation\Http\FormRequest;

class IndexSidangNolRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware enforces the allowed roles.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ];
    }

    public function filters(): array
    {
        $data = $this->validated();

        return [
            'search' => $data['search'] ?? null,
            'per_page' => $data['per_page'] ?? 10,
        ];
    }
}
```

Use `$request->validated()` for form data. Never pass `$request->all()` directly to a model or service. A request helper such as `filters()` is appropriate when it returns a small, named input structure for a service.

## Controller pattern

Controllers should make the endpoint readable without containing its implementation.

```php
use App\Http\Requests\SidangNol\IndexSidangNolRequest;
use App\Services\SidangNolService;
use Inertia\Inertia;

class BerkasSidangNolController extends Controller
{
    public function index(
        IndexSidangNolRequest $request,
        SidangNolService $sidangNolService,
    ) {
        return Inertia::render('BerkasSidangNol/Index', [
            'berkas' => $sidangNolService->datatable(
                user: $request->user(),
                filters: $request->filters(),
            ),
        ]);
    }
}
```

Laravel resolves the service automatically through dependency injection. The controller may call `$this->authorize(...)` or rely on explicit route middleware, depending on the authorization requirement.

## Module service pattern

Every retained module has one service file. Its methods use business-oriented names and receive explicit values rather than an entire HTTP request.

```php
namespace App\Services;

use App\Models\BerkasSidangNol;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SidangNolService
{
    public function datatable(User $user, array $filters): LengthAwarePaginator
    {
        return BerkasSidangNol::query()
            ->with('user')
            ->when(
                $user->role_id === 1,
                fn ($query) => $query->where('user_id', $user->id),
                fn ($query) => $query->where('status', '>=', 1),
            )
            ->when($filters['search'], function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('nama', 'like', "%{$search}%")
                                ->orWhere('nim', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'])
            ->withQueryString();
    }
}
```

Suggested method names for `SidangNolService`:

| Need | Method name |
| --- | --- |
| Paginated index data | `datatable(User $user, array $filters)` |
| One record for view/edit | `findForDisplay(int $id)` |
| Create or update draft | `save(array $data, User $actor, ?BerkasSidangNol $berkas = null)` |
| Send a draft | `submit(BerkasSidangNol $berkas, User $actor)` |
| Accept or reject | `decide(BerkasSidangNol $berkas, array $data, User $actor)` |
| Reset rejected record | `reset(BerkasSidangNol $berkas, User $actor)` |
| Delete record and private files | `delete(BerkasSidangNol $berkas, User $actor)` |
| Produce export | `export(array $filters)` |

`datatable()` is acceptable because it clearly represents the paginated table used by this application. Prefer similarly explicit names; avoid vague methods such as `process()`, `handle()`, or `data()`.

## Queries, scopes, and transactions

- A module service owns the complete query assembled for an endpoint.
- A model may expose a small, reusable scope such as `->submitted()` or `->forPeriod($start, $end)`. Services compose those scopes; controllers do not.
- A service must wrap multi-write workflows in `DB::transaction()`. Sidang Nol decisions are an example: update status, create a note, generate/store a response letter, and persist its path as one coherent operation.
- Keep private file handling in the service that owns the workflow and use the configured `secure` disk for student documents.
- Return models, paginator objects, arrays, or export/download objects from services—not redirects or `Inertia::render()` responses.

## Authorization rules

1. Route middleware provides broad route access, such as `auth` and allowed roles.
2. Policies or controller authorization protect a specific record where ownership matters.
3. Services receive the acting `User` for state-changing actions and enforce important workflow invariants as a second line of protection.
4. Frontend menu visibility never replaces server-side authorization.

## What not to add yet

- No Repository layer merely to wrap Eloquent calls.
- No separate service file for every individual action.
- No generic `BaseService` with shared CRUD methods.
- No service method that accepts `Request $request`; pass validated arrays, models, and the acting user instead.

If one module service becomes difficult to navigate after real growth, split only the proven independent part. For example, PDF generation may later become a dedicated `SidangNolLetterGenerator`; it should not be created in advance.

## Refactor checklist

For each retained module, make one endpoint work end-to-end before moving to the next.

1. Add or update the Form Request and its validation tests.
2. Move its controller query or workflow into the module service without changing the frontend response contract.
3. Keep middleware and authorization behavior unchanged unless the task explicitly improves it.
4. Test the service behavior, then the HTTP endpoint's validation and authorization.
5. Remove the old controller logic only after the replacement passes verification.
6. Update the relevant module document in `docs/modules/` when behavior or data contracts change.
