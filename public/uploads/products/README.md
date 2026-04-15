# Product Uploads

Store product-related files (images, documents, specifications).

## Subdirectories

- **images/** - Product images (JPG, PNG, GIF, WebP)
- **documents/** - Product documents (PDF, DOC, DOCX, XLS, XLSX)
- **thumbnails/** - Auto-generated product image thumbnails

## Access

Files in this directory are served to users with proper cache headers.
Images are cached for 1 year.
Documents are cached for 7 days.
