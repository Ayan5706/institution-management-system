# Avatars

User profile pictures and avatar management.

## Upload Requirements

- **Format**: JPG, PNG, GIF, WebP
- **Size Limit**: 2MB
- **Recommended**: Square image (1:1 aspect ratio)
- **Minimum Size**: 100x100px
- **Recommended Size**: 256x256px or larger

## Features

- Automatic fallback to initials avatar
- Color-coded by user initials
- Responsive display at multiple sizes
- SVG placeholder generation
- Proper caching headers

## Usage

### Controller Example

```php
<?php namespace App\Controllers;

use App\Core\AvatarManager;

class ProfileController extends Controller
{
    public function updateAvatar()
    {
        $avatarManager = new AvatarManager();

        // Validate file
        $errors = AvatarManager::validateAvatarFile($_FILES['avatar']);
        if (!empty($errors)) {
            return $this->respond(['errors' => $errors], 400);
        }

        // Get current user
        $user = auth()->user();

        // Delete old avatar if exists
        if ($user->avatar_path) {
            $avatarManager->delete($user->avatar_path);
        }

        // Upload new avatar
        $avatarInfo = $avatarManager->upload($_FILES['avatar'], $user->id);

        if (!$avatarInfo) {
            return $this->respond(['errors' => $avatarManager->getErrors()], 400);
        }

        // Update user record
        $user->update([
            'avatar_path' => $avatarInfo['path'],
            'avatar_url' => $avatarInfo['url']
        ]);

        return $this->respond([
            'success' => true,
            'message' => 'Avatar updated successfully',
            'avatar_url' => $avatarInfo['url']
        ]);
    }
}
```

### Display Avatar

```php
<?php
// In blade template or PHP view
$user = ['name' => 'John Doe', 'avatar_url' => '/uploads/avatars/...jpg'];

// Option 1: Auto-select (real image or fallback)
echo AvatarManager::getAvatarHtml($user, 64);

// Option 2: Just get URL
$url = AvatarManager::getAvatarUrl($user);
echo '<img src="' . $url . '" class="avatar">';

// Option 3: Force initials
$url = AvatarManager::getInitialsAvatar($user);
echo '<img src="' . $url . '" class="avatar">';
```

### HTML Form

```html
<form method="POST" action="/profile/avatar" enctype="multipart/form-data">
    <div class="form-group">
        <label for="avatar">Profile Picture</label>
        <input 
            type="file" 
            id="avatar" 
            name="avatar" 
            accept="image/jpeg,image/png,image/gif,image/webp"
            data-validate="required"
        >
        <small>JPG, PNG, GIF, WebP - Max 2MB - Recommended: Square image</small>
    </div>
    <button type="submit">Upload</button>
</form>
```

---

# Documents

General document repository for uploads and downloads.

## Supported Formats

- **Word**: DOC, DOCX
- **Excel**: XLS, XLSX
- **PDF**: PDF
- **PowerPoint**: PPT, PPTX
- **Text**: TXT

## Upload Requirements

- **Size Limit**: 20MB
- **Allowed MIME Types**: Validated via finfo

## Features

- MIME type validation
- File icon display based on type
- Formatted file size display
- Download tracking support
- Batch operations
- Preview support for PDFs
- Organized storage

## Usage

### Controller Example

```php
<?php namespace App\Controllers;

use App\Core\DocumentManager;

class DocumentController extends Controller
{
    public function store()
    {
        $documentManager = new DocumentManager();

        // Validate file
        $errors = DocumentManager::validateDocumentFile($_FILES['document']);
        if (!empty($errors)) {
            return $this->respond(['errors' => $errors], 400);
        }

        $user = auth()->user();

        // Upload document
        $docInfo = $documentManager->upload(
            $_FILES['document'],
            $user->id,
            [
                'category' => $_POST['category'] ?? null,
                'description' => $_POST['description'] ?? null
            ]
        );

        if (!$docInfo) {
            return $this->respond(['errors' => $documentManager->getErrors()], 400);
        }

        // Save to database
        $document = Document::create([
            'user_id' => $user->id,
            'path' => $docInfo['path'],
            'url' => $docInfo['url'],
            'original_name' => $docInfo['original_name'],
            'size' => $docInfo['size'],
            'mime_type' => $docInfo['mime_type'],
            'extension' => $docInfo['extension'],
            'metadata' => json_encode($docInfo['metadata']),
            'uploaded_at' => $docInfo['uploaded_at']
        ]);

        return $this->respond([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'document' => $document
        ]);
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $documentManager = new DocumentManager();

        // Delete file
        $documentManager->delete($document->path);

        // Delete record
        $document->delete();

        return $this->respond([
            'success' => true,
            'message' => 'Document deleted successfully'
        ]);
    }
}
```

### Display in HTML

```php
<?php
// Display single document
$document = [
    'filename' => 'report.pdf',
    'original_name' => 'Annual Report 2025.pdf',
    'size' => 1024000,
    'extension' => 'pdf',
    'url' => '/uploads/documents/report.pdf',
    'uploaded_at' => '2025-04-12 10:30:00'
];

echo DocumentManager::getDocumentHtml($document, showActions: true);
?>

<!-- Display in table -->
<table>
    <thead>
        <tr>
            <th></th>
            <th>Name</th>
            <th>Type</th>
            <th>Size</th>
            <th>Uploaded</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($documents as $doc): ?>
            <?php echo DocumentManager::getDocumentTableRow($doc); ?>
        <?php endforeach; ?>
    </tbody>
</table>
```

### HTML Form

```html
<form method="POST" action="/documents" enctype="multipart/form-data">
    <div class="form-group">
        <label for="document">Upload Document</label>
        <input 
            type="file" 
            id="document" 
            name="document" 
            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt"
            data-validate="required"
        >
        <small>PDF, Word, Excel, PowerPoint, Text - Max 20MB</small>
    </div>

    <div class="form-group">
        <label for="category">Category</label>
        <select id="category" name="category">
            <option value="">General</option>
            <option value="academic">Academic</option>
            <option value="administrative">Administrative</option>
            <option value="reports">Reports</option>
        </select>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Describe this document..."></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Upload</button>
</form>
```

## Utility Functions

### Get File Icon

```php
echo DocumentManager::getFileIcon('pdf'); // 📄
echo DocumentManager::getFileIcon('docx'); // 📝
echo DocumentManager::getFileIcon('xlsx'); // 📊
```

### Format File Size

```php
echo DocumentManager::formatFileSize(1024000); // 1000 KB
echo DocumentManager::formatFileSize(5242880); // 5 MB
```

### Get File Type Label

```php
echo DocumentManager::getFileTypeLabel('pdf'); // PDF Document
echo DocumentManager::getFileTypeLabel('xlsx'); // Excel Spreadsheet
```

### Check if Previewable

```php
if (DocumentManager::canPreview('pdf')) {
    // Show preview button
}
```

---

## Database Schema

### Users Avatar Column

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('avatar_path')->nullable()->after('email');
    $table->string('avatar_url')->nullable()->after('avatar_path');
});
```

### Documents Table

```php
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('path');              // uploads/documents/filename.pdf
    $table->string('url');               // /uploads/documents/filename.pdf
    $table->string('original_name');     // Annual Report 2025.pdf
    $table->string('extension');         // pdf
    $table->string('mime_type');         // application/pdf
    $table->unsignedBigInteger('size');  // File size in bytes
    $table->text('metadata')->nullable(); // JSON: category, description, etc
    $table->integer('downloads')->default(0);
    $table->timestamps();
    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
    
    $table->index('user_id');
    $table->index('created_at');
});
```

---

## CSS Styling

### Avatar

```css
/* Avatar display */
.avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--dark-border);
}

.avatar-sm {
    width: 40px;
    height: 40px;
}

.avatar-lg {
    width: 96px;
    height: 96px;
}

.avatar-xl {
    width: 128px;
    height: 128px;
}

.avatar-group {
    display: flex;
    gap: -8px;
}

.avatar-group .avatar {
    border: 2px solid var(--primary-dark);
}

.avatar-group .avatar:not(:first-child) {
    margin-left: -16px;
}
```

### Documents

```css
/* Document item */
.document-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid var(--dark-border);
    border-radius: var(--radius-md);
    background-color: var(--dark-card);
}

.document-icon {
    font-size: 32px;
    flex-shrink: 0;
}

.document-info {
    flex: 1;
    min-width: 0;
}

.document-name {
    display: block;
    font-weight: 500;
    color: var(--accent-blue);
    text-decoration: none;
    word-break: break-word;
    margin-bottom: 0.25rem;
}

.document-name:hover {
    text-decoration: underline;
}

.document-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--light-text-muted);
}

.document-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}
```

---

**Last Updated**: April 12, 2026
