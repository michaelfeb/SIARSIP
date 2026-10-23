# Authentication and authorization

## Authentication lifecycle

SIARSIP uses Laravel's session authentication. Guests can register, sign in, request a password reset, and reset a password. Authenticated users can update their profile and password, confirm their password, and sign out.

| Action | Route | Implementation detail |
| --- | --- | --- |
| Register | `GET`/`POST /register` | Creates a user with role ID `1` (Mahasiswa). Required fields are name, NIM, email, programme of study, phone number, and password. |
| Sign in | `GET`/`POST /login` | Uses **NIM**, not email, plus password. Login is rate-limited to five attempts using NIM and IP address. |
| Password reset | `/forgot-password`, `/reset-password/{token}` | Uses Laravel's password broker and the configured mailer. |
| Sign out | `POST /logout` | Ends the session. |
| Account settings | `/settings/profile`, `/settings/password` | Available to authenticated users. |

After a successful login, the session is regenerated. A student (role `1`) is sent to the Sidang Nol list; all other roles are sent to the dashboard.

Email-verification routes and controllers are present, but the current `User` model does not implement Laravel's `MustVerifyEmail` contract and no retained route uses `verified` middleware. Treat verification as inactive unless that contract and a route guard are intentionally enabled.

## Roles

`RoleSeeder` defines the following persisted numeric IDs. They are used directly by route middleware and the sidebar, so the IDs must remain stable in every environment.

| ID | Role | Current responsibility |
| ---: | --- | --- |
| 1 | Mahasiswa | Submit and track own Sidang Nol request. |
| 2 | Resepsionis | Manage letter templates. |
| 3 | Dekan | Dashboard and future workflow role. |
| 4 | WD | Dashboard and future workflow role. |
| 5 | TU | Dashboard and future workflow role. |
| 6 | Bagian Akademik | Process Sidang Nol, manage carousel and landing links. |
| 7 | Bagian Layanan | Manage letter templates. |
| 8 | Super Admin | Full retained-module administration. |
| 9 | Admin TTPS | Dashboard access. |

The roles are seeded before users. Changing the insertion order or replacing rows without preserving IDs can silently change access because the application currently checks `role_id` values rather than named permissions.

PRD 003 will normalize persisted role names to uppercase and add `WD2`, `WD3`, and `PRODI` through a dedicated production migration. This retirement does not change role rows.

## Authorization mechanism

`app/Http/Middleware/RoleAccess.php` receives allowed IDs from a route declaration, compares them with the signed-in user's `role_id`, and returns HTTP 403 when they do not match. Laravel's `auth` middleware must also be present for a protected route.

Example:

```php
Route::middleware(['auth', 'role:6,8'])->group(function () {
    // Bagian Akademik and Super Admin only
});
```

The user is available in all Inertia responses as `auth.user`. The client sidebar uses the same numeric IDs only to decide which menu items to show; it is not an authorization boundary. Server-side route middleware remains required.

## Retained-module access matrix

| Area | Mahasiswa (1) | Template roles (2–8) | Akademik (6) | Super Admin (8) |
| --- | --- | --- | --- | --- |
| Sidang Nol list/create/edit/send | Own submissions | — | Process and decide | Process, decide, and delete accepted records |
| Sidang Nol upload preview/download | Own workflow routes | — | Private document route | Private document route |
| Template Surat browse/download | Browse published templates | Manage templates | Manage templates | Manage templates |
| Carousel management | — | — | Manage | Manage |
| Landing-link management | — | — | Manage | Manage |

The public landing page, published template download endpoint, and carousel image endpoint have their own public routes. Public delivery does not grant access to private Sidang Nol files.

## Adding or changing authorization

1. Preserve the existing role IDs until a deliberate permission migration is complete.
2. Add server-side middleware to every new state-changing or private-file route.
3. Update `AppSidebarMenu.ts` only after the route is protected; sidebar visibility is a convenience, not security.
4. Update the role table and affected module document in this folder.
