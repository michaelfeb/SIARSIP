# Carousel

## Purpose

Carousel manages the announcement images shown on the public landing page. Only active carousel records are sent to the landing page, ordered by publish date descending.

## Access and routes

| Capability | Route(s) | Guard |
| --- | --- | --- |
| List/create/edit | `GET /carousel`, `GET /carousel/create`, `GET /carousel/{carousel}/edit` | `auth`, roles `6,8` |
| Create/update | `POST /carousel/save`, `PUT /carousel/save/{carousel}` | `auth`, roles `6,8` |
| Toggle/delete | `PUT /carousel/{carousel}/toggle`, `DELETE /carousel/{carousel}` | `auth`, roles `6,8` |
| Serve an image | `GET /carousel-image/{filename}` | Public |

The administration index uses URL-driven search and pagination through Inertia.
Its local `DataTable.vue` owns search, table cells, and emitted action intents;
`Index.vue` owns navigation, confirmations, and mutations. Use the server route
for status changes; hiding an item in the browser alone does not alter what the
landing page receives.

## Fields and validation

| Field | Rule / behavior |
| --- | --- |
| Name (`nama`) | Required. |
| Image (`gambar`) | Required when creating; optional when editing. JPEG, JPG, or PNG, maximum 5 MB. |
| Publish date (`tanggal_publish`) | Required date. |
| Status | Boolean, defaults active. |

Images are stored on the `public` disk in the `carousel/` directory.
`CarouselService` stores a replacement before persisting the record, removes a
new upload if persistence fails, and removes the previous image only after a
successful update. Deleting a Carousel record also removes its associated
stored image. Existing orphan files from before this lifecycle are not scanned
or removed automatically.

## Landing-page behavior

`CarouselService::publishedForLanding()` returns only `status = true` records,
ordered by publication date descending. The landing controller delegates to
this service and the Vue landing page renders the public image route. Keep
images suitable for public display and validate visual dimensions in the
content workflow; the server validates format and file size, not aspect ratio.

## Implementation touchpoints

- Controller: `app/Http/Controllers/CarouselController.php`
- Request: `app/Http/Requests/CarouselRequest.php`
- Service: `app/Services/CarouselService.php`
- Model: `app/Models/Carousel.php`
- Pages and module components: `resources/js/pages/Carousel/`
- Public landing page: `resources/js/pages/LandingPage/Index.vue`
- Routes: `routes/web.php`
