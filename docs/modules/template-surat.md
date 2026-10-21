# Template Surat

## Purpose

Template Surat publishes downloadable letter templates for students and displays the published set on the landing page. It continues to depend on the `jenis_surat` reference table, even though Jenis Surat is not a retained user-facing module.

## Access and routes

| Capability | Route(s) | Guard |
| --- | --- | --- |
| Browse templates | `GET /template-surat`, `/template-surat-mahasiswa` | `auth` |
| View template detail | `GET /template-surat/{id}` | `auth` |
| Create, edit, save, delete | routes under `/template-surat` | `auth`, roles `2,3,4,5,6,7,8` |
| Download document | `GET /template-surat/download/{id}` | Public |

The management list is searchable by template name and letter type and is paginated. The student-facing list filters to published templates (`status = 1`). The controller can return JSON for requests that explicitly expect it.

## Template fields and validation

| Field | Rule / behavior |
| --- | --- |
| Name (`nama`) | Required. |
| Description (`deskripsi`) | Optional. |
| Letter type (`jenis_surat_id`) | Required and must reference an existing `jenis_surat` row. Creation presents active types. |
| Status | Boolean; `1` makes the template available in published views and on the landing page. |
| Publish date (`tanggal_publish`) | Required date. |
| Document (`dokumen`) | Required on create; `.doc` or `.docx`, maximum 2 MB. Existing documents can remain on edit. |

Documents are stored on the Laravel `public` disk below `template_surat/`. Updates that include a new document remove the previous stored document; deletion also removes the associated stored file. Downloads are returned with a filename based on the template name.

## Landing-page behavior

`LandingPageController` selects active templates and passes them to `LandingPage/Index.vue`. Publishing status—not merely file presence—is the switch that exposes a template in this public-facing presentation.

Because the download route itself is public, publishing and filename rules should be reviewed before a template is made active. Do not use this module for confidential documents.

## Implementation touchpoints

- Controller: `app/Http/Controllers/TemplateSuratController.php`
- Model: `app/Models/TemplateSurat.php`
- Reference model: `app/Models/JenisSurat.php`
- Pages: `resources/js/pages/TemplateSurat/`
- Routes: `routes/web.php`
