# Uploads Documentation

Complete file upload system for IMS with validation, storage, and thumbnail generation.

## Directory Structure

```
uploads/
├── products/
│   ├── images/       - Product images
│   ├── documents/    - Product PDFs, specs
│   └── thumbnails/   - Auto-generated thumbnails
├── avatars/          - User profile pictures
├── documents/        - General documents
└── temp/             - Temporary upload files
```

## Upload Handler Class

**File:** `app/Core/FileUploadHandler.php`

### Supported File Types

**Images:**
- JPEG (.jpg, .jpeg) - 5MB max
- PNG (.png) - 5MB max
- GIF (.gif) - 5MB max
- WebP (.webp) - 5MB max

**Documents:**
- PDF (.pdf) - 20MB max
- Word (.doc, .docx) - 20MB max
- Excel (.xls, .xlsx) - 20MB max

**Avatars:**
- JPEG, PNG, GIF, WebP - 2MB max

### Upload Methods

```php
$uploader = new FileUploadHandler();

// Upload product image
$uploader->uploadProductImage($_FILES['image'], $productId);

// Upload product document
$uploader->uploadProductDocument($_FILES['document'], $productId);

// Upload user avatar
$uploader->uploadAvatar($_FILES['avatar'], $userId);

// Upload general document
$uploader->uploadDocument($_FILES['file']);
```

### Response Handling

```php
if ($uploader->uploadProductImage($_FILES['image'], 123)) {
    $fileInfo = $uploader->getFileInfo();
    // $fileInfo contains:
    // - filename: stored filename
    // - original_name: original upload name
    // - path: relative path from uploads dir
    // - url: web accessible URL
    // - size: file size in bytes
    // - mime_type: detected MIME type
    // - extension: file extension
    // - upload_time: upload timestamp
    
    // Save to database
    // $file->path = $fileInfo['path'];
    // $file->url = $fileInfo['url'];
} else {
    $errors = $uploader->getErrors();
    // Handle errors
    foreach ($errors as $error) {
        echo $error;
    }
}
```

### Delete Files

```php
$uploader = new FileUploadHandler();
if ($uploader->deleteFile('products/images/filename.jpg')) {
    // File deleted successfully
    // Associated thumbnail also deleted
} else {
    $error = $uploader->getLastError();
}
```

## Security Features

### Protection Mechanisms

1. **File Type Validation**
   - Extension whitelist checking
   - MIME type verification
   - Magic bytes detection

2. **Size Limits**
   - Images: 5MB max
   - Documents: 20MB max
   - Avatars: 2MB max

3. **Filename Sanitization**
   - Remove special characters
   - Prevent directory traversal
   - Add timestamp for uniqueness
   - Preserve entity ID for reference

4. **Script Execution Prevention**
   - `.htaccess` disables script execution
   - Only specific file types allowed
   - Upload folder not web-accessible directly

5. **Directory Protection**
   - `.htaccess` restricts access
   - Deny all by default
   - Allow only whitelisted extensions

### Filename Generation

Files are stored with pattern:
```
[entity_id]_[sanitized_name]_[timestamp].[extension]

Examples:
- 123_product_spec_1712973600.pdf
- 456_profile_photo_1712973620.jpg
- my_document_1712973640.pdf
```

## HTML Form Integration

```html
<form method="POST" enctype="multipart/form-data">
    <!-- Product Image Upload -->
    <div class="form-group">
        <label for="product_image">Product Image</label>
        <input 
            type="file" 
            id="product_image" 
            name="product_image" 
            accept="image/jpeg,image/png,image/gif,image/webp"
            data-validate="required"
            data-max-size="5242880"
        >
        <small>JPG, PNG, GIF, WebP - Max 5MB</small>
    </div>

    <!-- Product Document Upload -->
    <div class="form-group">
        <label for="product_doc">Product Document</label>
        <input 
            type="file" 
            id="product_doc" 
            name="product_doc" 
            accept=".pdf,.doc,.docx,.xls,.xlsx"
        >
        <small>PDF, Word, Excel - Max 20MB</small>
    </div>

    <!-- Avatar Upload -->
    <div class="form-group">
        <label for="avatar">Profile Picture</label>
        <input 
            type="file" 
            id="avatar" 
            name="avatar" 
            accept="image/*"
        >
        <small>JPG, PNG, GIF, WebP - Max 2MB</small>
    </div>

    <button type="submit" class="btn btn-primary">Upload</button>
</form>
```

## Controller Integration

```php
<?php namespace App\Controllers;

use App\Core\FileUploadHandler;

class ProductController extends Controller
{
    public function store()
    {
        $uploader = new FileUploadHandler();
        
        // Validate product data
        $this->validate([
            'name' => 'required',
            'description' => 'required',
            'product_image' => 'required'
        ]);

        // Upload image
        if (!$uploader->uploadProductImage($_FILES['product_image'])) {
            return $this->respond([
                'success' => false,
                'errors' => $uploader->getErrors()
            ], 400);
        }

        $imageInfo = $uploader->getFileInfo();

        // Create product
        $product = Product::create([
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'image_path' => $imageInfo['path'],
            'image_url' => $imageInfo['url']
        ]);

        // Upload document if provided
        if (isset($_FILES['product_doc']) && $_FILES['product_doc']['size'] > 0) {
            if ($uploader->uploadProductDocument($_FILES['product_doc'], $product->id)) {
                $docInfo = $uploader->getFileInfo();
                ProductDocument::create([
                    'product_id' => $product->id,
                    'file_path' => $docInfo['path'],
                    'file_url' => $docInfo['url'],
                    'file_name' => $docInfo['original_name']
                ]);
            }
        }

        return $this->respond([
            'success' => true,
            'message' => 'Product created successfully',
            'product' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $uploader = new FileUploadHandler();

        // Delete image
        if ($product->image_path) {
            $uploader->deleteFile($product->image_path);
        }

        // Delete associated documents
        ProductDocument::where('product_id', $product->id)->each(function($doc) use ($uploader) {
            $uploader->deleteFile($doc->file_path);
        });

        $product->delete();

        return $this->respond([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
```

## Database Schema

```php
// Table: products
Schema::create('products', function(Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description');
    $table->string('image_path')->nullable();  // uploads/products/images/filename.jpg
    $table->string('image_url')->nullable();   // /uploads/products/images/filename.jpg
    $table->string('thumbnail_url')->nullable(); // /uploads/products/thumbnails/filename.jpg
    $table->decimal('price', 10, 2)->nullable();
    $table->timestamps();
});

// Table: product_documents
Schema::create('product_documents', function(Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('product_id');
    $table->string('file_path');  // uploads/products/documents/filename.pdf
    $table->string('file_url');   // /uploads/products/documents/filename.pdf
    $table->string('file_name');
    $table->string('file_type');  // application/pdf
    $table->integer('file_size');
    $table->timestamps();
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
});
```

## Frontend Integration

### JavaScript Upload Handler

```javascript
// Product image upload with preview
const imageInput = document.querySelector('input[type="file"][name="product_image"]');
const imagePreview = document.querySelector('.image-preview');

imageInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    
    // Validate file size
    if (file.size > 5 * 1024 * 1024) {
        alert('File size exceeds 5MB limit');
        return;
    }
    
    // Show preview
    const reader = new FileReader();
    reader.onload = (event) => {
        imagePreview.src = event.target.result;
        imagePreview.style.display = 'block';
    };
    reader.readAsDataURL(file);
});

// Form submission with file upload
const form = document.querySelector('form');
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': document.querySelector('[name="_token"]').value
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            // Redirect or refresh
        } else {
            alert('Errors: ' + data.errors.join(', '));
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Upload failed');
    }
});
```

### Drag and Drop Upload

```javascript
const dropZone = document.querySelector('[data-role="drop-zone"]');

if (dropZone) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('highlight');
    }

    function unhighlight(e) {
        dropZone.classList.remove('highlight');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        const input = document.querySelector('input[type="file"]');
        input.files = files;
        input.dispatchEvent(new Event('change'));
    }
}
```

## Performance Optimization

### Image Optimization
- Automatic thumbnail generation (200x200px)
- JPEG quality: 85%
- PNG compression: Level 9
- WebP support for modern browsers

### Caching
- Images cached 1 year (long-term)
- Documents cached 7 days (shorter for updates)
- Cache headers set via `.htaccess`

### Storage
- Entity ID in filename for quick lookup
- Timestamp prevents collisions
- Organized by type for easy maintenance

## Troubleshooting

### File Upload Fails
1. Check `upload_tmp_dir` permission (755)
2. Verify `upload_max_filesize` in php.ini
3. Ensure `post_max_size` >= file size
4. Check upload folder permissions (755)

### Thumbnails Not Generated
1. Verify GD extension loaded: `php -m | grep gd`
2. Check source image is valid
3. Ensure destination folder is writable

### Files Not Accessible
1. Check `.htaccess` rules not blocking
2. Verify file permissions (644)
3. Confirm MIME types configured

## Best Practices

1. **Always validate files** on both client and server
2. **Set reasonable size limits** per upload type
3. **Sanitize filenames** to prevent issues
4. **Use HTTPS** for upload pages (especially sensitive data)
5. **Implement quotas** per user if needed
6. **Log uploads** for audit trails
7. **Scan for malware** in production
8. **Archive old files** to separate storage
9. **Implement versioning** for important documents
10. **Monitor disk usage** regularly

---

**Last Updated**: April 12, 2026
**Version**: 1.0.0
