# Carousel

## Purpose

Carousel manages the announcement images shown on the public landing page. Only active carousel records are sent to the landing page, ordered by publish date descending.

## Access and routes

| Capability | Route(s) | Guard |
| --- | --- | --- |
| Manage carousel list/create/edit | routes under `/carousel` | `auth`, roles `6,8` |
| Save, toggle status, delete | action routes under `/carousel` | `auth`, roles `6,8` |
| Serve an image | `GET /carousel-image/{filename}` | Public |

The administration index is searchable and paginated. Use the server routes for status changes; hiding an item in the browser alone does not alter what the landing page receives.

## Fields and validation

| Field | Rule / behavior |
| --- | --- |
| Name (`nama`) | Required. |
| Image (`gambar`) | Required when creating; optional when editing. JPEG, JPG, or PNG, maximum 5 MB. |
| Publish date (`tanggal_publish`) | Required date. |
| Status | Boolean, defaults active. |

Images are stored on the `public` disk in the `carousel/` directory. Replacing an image deletes the previous stored image. The current delete action removes the database row but does not explicitly remove its image file, so orphan-file cleanup should be a separately planned maintenance improvement.

## Landing-page behavior

The landing controller retrieves only `status = true` records and the Vue landing page renders their image route. Keep images suitable for public display and validate visual dimensions in the content workflow; the server currently validates format and file size, not a required aspect ratio.

## Implementation touchpoints

- Controller: `app/Http/Controllers/CarouselController.php`
- Model: `app/Models/Carousel.php`
- Pages: `resources/js/pages/Carousel/`
- Public landing page: `resources/js/pages/LandingPage/Index.vue`
- Routes: `routes/web.php`
