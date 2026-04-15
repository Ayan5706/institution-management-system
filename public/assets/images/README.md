# Images Asset Library

Complete image asset collection for the IMS (Institution Management System) application.

## Directory Structure

```
images/
├── logo.svg                 # IMS main brand logo
├── icons/                   # Navigation and action icons (24x24)
├── avatars/                 # User role-based avatars (64x64)
├── backgrounds/             # Pattern and texture backgrounds
└── illustrations/           # Empty states and status illustrations
```

## Available Assets

### Logo
- **logo.svg** (64x64) - Main IMS brand logo with gradient design

### Icons (24x24)
All icons are SVG-based with `currentColor` for easy theming.

| Icon | File | Usage |
|------|------|-------|
| Dashboard | `icons/dashboard.svg` | Main dashboard nav link |
| Users | `icons/users.svg` | Users management nav link |
| Reports | `icons/reports.svg` | Reports/analytics nav link |
| Settings | `icons/settings.svg` | Settings/configuration |
| Profile | `icons/profile.svg` | User profile, "My Account" |
| Logout | `icons/logout.svg` | Sign out button |
| Home | `icons/home.svg` | Home/back to dashboard |
| Calendar | `icons/calendar.svg` | Dates, schedules |
| Check | `icons/check.svg` | Success, marked complete |
| Alert | `icons/alert.svg` | Warning, alerts |
| Search | `icons/search.svg` | Search functionality |
| Edit | `icons/edit.svg` | Edit/update actions |
| Delete | `icons/delete.svg` | Delete/remove actions |

**Usage in HTML:**
```html
<img src="/assets/images/icons/dashboard.svg" alt="Dashboard" class="icon">
```

**Usage with CSS styling:**
```html
<svg class="icon icon-lg" viewBox="0 0 24 24">
  <use xlink:href="/assets/images/icons/dashboard.svg"></use>
</svg>
```

### Avatars (64x64)
Color-coded by user role for easy recognition.

| Avatar | File | Color | Role |
|--------|------|-------|------|
| Teacher | `avatars/teacher.svg` | Orange (#EF9A5D) | Teacher profile |
| Student | `avatars/student.svg` | Blue (#6BA3F5) | Student profile |
| Admin | `avatars/admin.svg` | Purple (#A78BFA) | System admin |
| Principal | `avatars/principal.svg` | Green (#10B981) | Principal/head |

**Usage in HTML:**
```html
<div class="user-card">
  <img src="/assets/images/avatars/teacher.svg" alt="John Doe" class="avatar">
  <h4>John Doe</h4>
  <p>Teacher</p>
</div>
```

### Backgrounds
SVG pattern backgrounds for cards, sections, and decorative elements.

| Pattern | File | Usage |
|---------|------|-------|
| Dots | `backgrounds/pattern-dots.svg` | Card backgrounds, subtle texture |
| Grid | `backgrounds/pattern-grid.svg` | Data table headers, grid layouts |
| Waves | `backgrounds/pattern-waves.svg` | Hero sections, page headers |

**Usage in CSS:**
```css
.card {
  background-image: url('/assets/images/backgrounds/pattern-dots.svg');
  background-position: top right;
  background-repeat: no-repeat;
  background-size: 200px 200px;
}
```

### Illustrations
SVG illustrations for empty states, status messages, and notifications.

| Illustration | File | Usage |
|--------------|------|-------|
| No Data | `illustrations/no-data.svg` | Empty list/table states |
| Empty State | `illustrations/empty-state.svg` | No files/items view |
| Success Check | `illustrations/success-check.svg` | Operation success |
| Error Alert | `illustrations/error-alert.svg` | Error/failure states |

**Usage in HTML:**
```html
<div class="empty-state">
  <img src="/assets/images/illustrations/no-data.svg" alt="No data available">
  <h3>No records found</h3>
  <p>Start by creating a new record.</p>
  <a href="#" class="btn btn-primary">Create New</a>
</div>
```

## CSS Integration

### Icon Sizing
Define icon size classes in your CSS:

```css
/* In components.css or utilities */
.icon {
  width: 24px;
  height: 24px;
  display: inline-block;
  color: currentColor;
}

.icon-sm {
  width: 16px;
  height: 16px;
}

.icon-lg {
  width: 32px;
  height: 32px;
}

.icon-xl {
  width: 48px;
  height: 48px;
}

.icon-primary {
  color: var(--accent-blue);
}

.icon-success {
  color: var(--accent-success);
}

.icon-danger {
  color: var(--accent-danger);
}

.icon-muted {
  color: var(--light-text-muted);
}
```

### Avatar Sizing
```css
.avatar {
  width: 64px;
  height: 64px;
  border-radius: var(--radius-full);
  object-fit: contain;
  background-color: rgba(74, 144, 226, 0.1);
  padding: 4px;
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
```

## Color Coding Reference

### Avatar Colors by Role
- **Teacher**: Orange #EF9A5D
- **Student**: Blue #6BA3F5
- **Admin**: Purple #A78BFA
- **Principal**: Green #10B981

Use these colors for:
- Role badges and labels
- User status indicators
- Filter/category tags
- Charts and statistics

## Best Practices

### 1. **Scalability**
All images are SVG format, ensuring crisp rendering at any size.

### 2. **Performance**
- Use SVG for icons and logos (smallest file size)
- Cache backgrounds and patterns at CDN level
- Lazy-load illustrations for below-the-fold content

### 3. **Accessibility**
Always include alt text:
```html
<img src="/assets/images/avatars/student.svg" alt="Student profile">
```

### 4. **Theming**
Use CSS variables in SVGs for dark/light theme support:
```css
:root {
  --icon-color: #e0e0e0; /* light theme */
  --icon-hover: #4A90E2;
}

@media (prefers-color-scheme: dark) {
  :root {
    --icon-color: #e0e0e0;
  }
}
```

### 5. **Responsive Icons**
Use CSS viewport-relative sizing:
```css
.icon {
  width: clamp(20px, 5vw, 32px);
  height: auto;
}
```

## Adding New Images

1. **Create SVG files** with consistent dimensions
2. **Use CSS variables** for colors (`currentColor` for icons)
3. **Add to appropriate folder** (icons, avatars, backgrounds, illustrations)
4. **Update this README** with new asset documentation
5. **Test scaling** at multiple viewport sizes and zoom levels

## File Naming Convention

```
[type]-[purpose]-[variant].svg

Examples:
- dashboard.svg
- icon-user.svg
- avatar-teacher.svg
- bg-pattern-dots.svg
- illustration-success.svg
```

## Integration Checklist

- [ ] Link `/assets/images/logo.svg` in site header/branding
- [ ] Add icon CSS classes to components.css
- [ ] Add avatar CSS classes to components.css
- [ ] Add background patterns to dashboard.css
- [ ] Test icon rendering across browsers
- [ ] Verify avatar colors match role system
- [ ] Confirm SVG animations work on mobile devices

---

**Last Updated**: April 12, 2026  
**Total Assets**: 33 SVG files (1 logo, 13 icons, 4 avatars, 3 backgrounds, 5 illustrations, 7 utility)
