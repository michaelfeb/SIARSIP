# Link Landing Page

## Purpose

Link Landing Page manages the quick-link cards displayed on the public landing page. Each card can have a title, explanatory text, external/internal URL, presentation icon, and colour.

## Access and routes

| Capability | Route(s) | Guard |
| --- | --- | --- |
| Manage links | routes under `/link-landing-page` | `auth`, roles `6,8` |
| Save, toggle status, delete | action routes under `/link-landing-page` | `auth`, roles `6,8` |
| Public display | landing page `/` | Public |

The administrative index is ordered by `no_urut`, can be searched, and supports pagination. The public landing page receives only active records, also sorted by `no_urut` ascending.

## Fields and behavior

| Field | Rule / behavior |
| --- | --- |
| Name (`nama`) | Required. |
| Description (`deskripsi`) | Required text, maximum 255 characters. |
| URL (`link`) | Required URL, maximum 255 characters. |
| Display order (`no_urut`) | Required integer when saving. The create page supplies the next highest number as a form default. |
| Status | Boolean; only active links appear publicly. |
| Icon (`icon`) | Required icon identifier, maximum 100 characters, consumed by the Vue landing page. |
| Colour (`color`) | Required presentation value, maximum 100 characters, consumed by the Vue landing page. |

The landing page opens these links in a new tab. Treat every published URL as public content and review it for correctness and destination safety before enabling it.

## Content-management guidance

1. Use explicit order numbers to make card position predictable.
2. Disable a link instead of deleting it when the destination may return.
3. Keep icon and colour values compatible with the icon/rendering conventions in `LandingPage/Index.vue`.
4. Test a link from the public landing page after saving; successful admin storage does not guarantee the remote destination is available.

## Implementation touchpoints

- Controller: `app/Http/Controllers/LinkLandingPageController.php`
- Model: `app/Models/LinkLandingPage.php`
- Pages: `resources/js/pages/LinkLandingPage/`
- Public landing page: `resources/js/pages/LandingPage/Index.vue`
- Routes: `routes/web.php`
