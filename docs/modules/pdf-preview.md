# Letter PDF preview contract

Every active Letter module must provide a draft PDF preview from its create and edit form. A preview lets the applicant verify the rendered document before it is saved, but it is never an official letter.

## Required behavior

- Place **Pratinjau PDF** between the back/cancel action and the save action.
- Send current form values and files to an authenticated module preview endpoint. Do not persist a `Letter`, attachment, PDF, workflow route, or log.
- Reuse the exact draft validation rules used by create/update. A `422` response must populate the same field errors as Save.
- Return `application/pdf` inline and display its Blob URL in shared `PdfPreviewDialog.vue`. Revoke the Blob URL when the dialog closes.
- Render `PRATINJAU - BELUM BERLAKU`. Final-only values (number, signer, signature, QR code, archive date) use clear placeholders.

## Backend pattern

Declare `POST /preview` before `/{letter}`. Include the preview route in the Form Request draft-rule branch. The service constructs an **unsaved** `Letter` from server-owned applicant data and validated input, then calls `PdfGeneratorService`. Pass `isPreview = true` to the Blade template. Only finalisation may assign official number/signatory or store a PDF.

## Frontend pattern

Use `axios`, `FormData`, and `responseType: 'blob'`; an Inertia visit cannot render a binary response. On success, create an object URL and open `PdfPreviewDialog`. On a `422` Blob response, parse JSON `errors` and set the first error per field through `useForm().setError()`.

## Verification checklist

- Valid preview returns PDF bytes and does not change Letter or attachment count.
- Invalid preview returns the same errors as Save.
- Preview contains its non-official notice and no final QR/signature.
- Closing revokes the object URL.
- Final PDF still stores its official number, signer, and QR code.
