# 📁 IMS Final - Project Structure Overview

**Date**: April 12, 2026  
**Status**: ✅ Organized and Production-Ready

---

## 🎯 Project Organization

The IMS Final project follows a clean MVC architecture with organized folders for different concerns.

---

## 📊 Complete File Structure

```
C:\xampp\htdocs\IMS_FINAL\
│
├── 📁 app/                              [APPLICATION CORE - MVC]
│   ├── Config/                          [Configuration - 10 files]
│   │   ├── app.php                      • App settings
│   │   ├── database.php                 • Database config
│   │   ├── session.php                  • Session settings
│   │   ├── validation.php               • Validation rules
│   │   └── [+ 6 more config files]
│   │
│   ├── Controllers/                     [BUSINESS LOGIC - 16 Controllers]
│   │   ├── AuthController.php           • Login/Auth handling
│   │   ├── DashboardController.php      • Dashboard routing ⭐ ROLE-BASED
│   │   ├── BaseController.php           • Parent controller (reusable)
│   │   ├── UserController.php           • User management
│   │   ├── ProgramController.php        • Program management
│   │   ├── ReportController.php         • Reporting
│   │   ├── StudentFeeController.php     • Fee management
│   │   ├── StudentProfileController.php • Student profiles
│   │   ├── TeacherAssignmentController.php
│   │   ├── TimetableController.php
│   │   ├── AttendanceController.php
│   │   ├── SemesterController.php
│   │   ├── SubjectController.php
│   │   ├── AdminController.php
│   │   ├── HomeController.php
│   │   └── UploadController.php
│   │
│   ├── Core/                            [FRAMEWORK UTILITIES - 14 files]
│   │   ├── Application.php              • Main app class
│   │   ├── Router.php                   • URL routing
│   │   ├── Database.php                 • PDO wrapper (reusable)
│   │   ├── Session.php                  • Session management (reusable)
│   │   ├── Request.php                  • HTTP requests
│   │   ├── Response.php                 • HTTP responses
│   │   ├── Logger.php                   • Logging utility
│   │   ├── Cache.php                    • Caching system
│   │   ├── Autoloader.php               • PSR-4 autoloading
│   │   ├── FileUploadHandler.php        • File uploads
│   │   ├── StorageManager.php           • Storage management
│   │   ├── DocumentManager.php          • Document handling
│   │   ├── AvatarManager.php            • Avatar management
│   │   └── DownloadHandler.php          • Download handling
│   │
│   ├── Middleware/                      [REQUEST INTERCEPTORS - 5 files]
│   │   ├── AuthMiddleware.php           • Authentication protection (reusable)
│   │   ├── RoleMiddleware.php           • Role-based access (reusable)
│   │   ├── GuestMiddleware.php          • Guest redirection (reusable)
│   │   ├── CsrfMiddleware.php           • CSRF protection
│   │   └── MiddlewareInterface.php      • Interface definition
│   │
│   ├── Models/                          [DATABASE - 15 Models]
│   │   ├── BaseModel.php                • Parent model (reusable)
│   │   ├── UserModel.php                • User queries
│   │   ├── ProgramModel.php             • Program queries
│   │   ├── SemesterModel.php            • Semester queries
│   │   ├── SubjectModel.php             • Subject queries
│   │   ├── StudentProfileModel.php      • Student profile queries
│   │   ├── StudentFeeModel.php          • Fee queries
│   │   ├── TeacherAssignmentModel.php   • Assignment queries
│   │   ├── TimetableModel.php           • Timetable queries
│   │   ├── AttendanceModel.php          • Attendance queries
│   │   ├── PasswordResetRequestModel.php• Password reset queries
│   │   ├── JwtBlacklistModel.php        • JWT blacklist queries
│   │   ├── AuditLogModel.php            • Audit log queries
│   │   ├── SystemConfigModel.php        • System config queries
│   │   └── [+ 1 more model files]
│   │
│   ├── Helpers/                         [UTILITY FUNCTIONS]
│   │   └── [Helper utilities]
│   │
│   ├── Views/                           [TEMPLATES - By Feature]
│   │   ├── layouts/
│   │   │   ├── app.php                  • Main layout (reusable)
│   │   │   └── auth.php                 • Auth layout (reusable)
│   │   │
│   │   ├── auth/                        [LOGIN SYSTEM]
│   │   │   ├── login.php                • ⭐ PASSWORD TOGGLE INCLUDED
│   │   │   ├── forgot_password.php
│   │   │   └── reset_password.php
│   │   │
│   │   ├── dashboard/                   [ROLE-BASED DASHBOARDS]
│   │   │   ├── index.php                • Generic fallback
│   │   │   ├── principal.php            • ⭐ PRINCIPAL dashboard
│   │   │   ├── vp.php                   • ⭐ VP dashboard
│   │   │   ├── manager.php              • ⭐ MANAGER dashboard
│   │   │   ├── accountant.php           • ⭐ ACCOUNTANT dashboard
│   │   │   ├── teacher.php              • ⭐ TEACHER dashboard
│   │   │   └── student.php              • ⭐ STUDENT dashboard
│   │   │
│   │   ├── users/                       [USER MANAGEMENT]
│   │   │   ├── index.php                • User list
│   │   │   ├── create.php               • Create user form
│   │   │   ├── edit.php                 • Edit user form
│   │   │   ├── form.php                 • Reusable form partial
│   │   │   └── show.php                 • User details
│   │   │
│   │   ├── home/                        [PUBLIC PAGES]
│   │   │   └── landing.php              • Landing page
│   │   │
│   │   ├── errors/                      [ERROR VIEWS]
│   │   │   ├── 403.php                  • Forbidden error
│   │   │   ├── 404.php                  • Not found error
│   │   │   └── 500.php                  • Server error
│   │   │
│   │   ├── reports/                     [REPORTS]
│   │   │   ├── index.php                • Reports list
│   │   │   └── academic.php             • Academic reports
│   │   │
│   │   └── [Other feature folders]
│   │
│   └── Seeders/                         [DATABASE SEEDERS]
│       └── [Seeder files for test data]
│
├── 📁 bootstrap/                        [APPLICATION STARTUP - 4 files]
│   ├── app.php                          • Bootstrap application
│   ├── config.php                       • Load configuration
│   ├── errors.php                       • Error handling setup
│   └── helpers.php                      • Load helpers
│
├── 📁 routes/                           [ROUTING]
│   └── web.php                          • All route definitions
│                                         (Single file - clean)
│
├── 📁 database/                         [DATABASE MANAGEMENT]
│   ├── migrations/                      [Database schema]
│   │   ├── 2026_04_11_000000_create_migrations_table.sql
│   │   ├── 2026_04_11_000001_create_ims_core_schema.sql
│   │   └── README.md
│   │
│   ├── seeds/                           [Database seed data]
│   │   └── sql/                         [SQL seed files]
│   │       └── README.md
│   │
│   └── schema/                          [Schema files]
│
├── 📁 public/                           [WEB ROOT - Publicly accessible]
│   ├── index.php                        • Entry point
│   ├── .htaccess                        • URL rewriting rules
│   │
│   ├── assets/                          [STATIC ASSETS]
│   │   ├── css/                         [Stylesheets - 6 files]
│   │   │   ├── main.css                 • Global styles (reusable)
│   │   │   ├── layout.css               • Layout-specific
│   │   │   ├── login.css                • Login page styles
│   │   │   ├── dashboard.css            • Dashboard styles
│   │   │   ├── components.css           • Component styles (reusable)
│   │   │   └── sidebar.css              • Sidebar styles
│   │   │
│   │   ├── js/                          [JavaScript - 9 files]
│   │   │   ├── app.js                   • Main app logic
│   │   │   ├── form.js                  • Form utilities (reusable)
│   │   │   ├── ui.js                    • UI interactions (reusable)
│   │   │   ├── modal.js                 • Modal dialogs (reusable)
│   │   │   ├── table.js                 • Table utilities (reusable)
│   │   │   ├── api.js                   • API calls (reusable)
│   │   │   ├── notifications.js         • Notifications (reusable)
│   │   │   ├── uploads.js               • Upload handling (reusable)
│   │   │   └── utils.js                 • General utilities (reusable)
│   │   │
│   │   └── images/                      [Images]
│   │
│   └── uploads/                         [User uploaded files]
│       └── [Uploaded content]
│
├── 📁 storage/                          [APPLICATION STORAGE]
│   ├── logs/                            [Application logs]
│   ├── cache/                           [Cached files]
│   ├── sessions/                        [Session files]
│   └── temp/                            [Temporary files]
│
├── 📁 tests/                            [UNIT/FEATURE TESTS]
│   └── [Test files]
│
├── 📁 scripts/                          [DEVELOPMENT SCRIPTS]
│   └── test-verification/               [Test verification scripts]
│       ├── README.md                    • Info about test scripts
│       └── [verification scripts]
│
├── 📁 docs/                             [📚 DOCUMENTATION - NEW]
│   ├── README.md                        • Documentation index
│   ├── DASHBOARDS_IMPLEMENTATION_SUMMARY.md   • Dashboard summary
│   ├── ROLE_BASED_DASHBOARDS.md         • Technical dashboard docs
│   ├── WORKING_CREDENTIALS.md           • Test credentials guide
│   ├── PASSWORD_TOGGLE_FEATURE.md       • Password toggle docs
│   ├── AUTHENTICATION_FIX_REPORT.md     • Auth system details
│   ├── LOGIN_COMPLETE_GUIDE.md          • Login guide
│   ├── DASHBOARD_TESTING_GUIDE.md       • Dashboard testing
│   ├── PASSWORD_TOGGLE_TEST_GUIDE.md    • Toggle testing
│   ├── TESTING_GUIDE.md                 • General testing
│   ├── TEST_AND_VERIFICATION_REPORT.md  • Test results
│   ├── PROJECT_REPORT.md                • Project overview
│   ├── HOW_TO_RUN.md                    • Setup instructions
│   ├── COMPLETION_CHECKLIST.md          • Project checklist
│   ├── LANDING_PAGE_GUIDE.md            • Landing page docs
│   ├── LOGIN_PAGE_SIMPLIFICATION_SUMMARY.md
│   ├── UI_TEXT_UPDATES_SUMMARY.md
│   ├── VALIDATION_REPORT.md
│   ├── PHPMYADMIN_IMPORT_GUIDE.md
│   ├── ROUTING_FIX.md
│   └── TESTING.md
│
├── 📄 .env.example                      [Environment variables example]
├── 📄 phpunit.xml                       [PHPUnit configuration]
├── 📄 composer.json                     [Composer dependencies (if used)]
└── 📄 .gitignore                        [Git ignore rules]

```

---

## 🎯 Key Organization Principles

### 1. **MVC Separation**
```
Controllers/ → Business logic
Models/      → Database queries
Views/       → Templates
```

### 2. **Reusable Components**
- `BaseController` - Parent for all controllers
- `BaseModel` - Parent for all models
- `app.php` layout - Used by all views
- Common CSS/JS files - Shared across pages

### 3. **Feature-Organized Views**
- Each view folder groups related templates
- Examples: `auth/`, `users/`, `dashboard/`, `reports/`
- Easy to find and maintain

### 4. **Clean Configuration**
- All config files in `app/Config/`
- Single configuration source
- Easy to modify settings

### 5. **Centralized Utilities**
- `app/Core/` - Framework utilities
- `app/Middleware/` - Request processing
- `public/assets/` - Static files

---

## 📦 What Can Be Reused

| Component | Location | Purpose | Reusability |
|-----------|----------|---------|-------------|
| **BaseController** | `app/Controllers/` | Parent controller | Inherited by all 16 controllers |
| **BaseModel** | `app/Models/` | Parent model | Inherited by all 15 models |
| **app.php** | `app/Views/layouts/` | Main layout | Used by all authenticated pages |
| **auth.php** | `app/Views/layouts/` | Auth layout | Used by login/reset pages |
| **main.css** | `public/assets/css/` | Global styles | Used by all pages |
| **app.js** | `public/assets/js/` | App logic | Core functionality |
| **Database.php** | `app/Core/` | DB connection | Used by all models |
| **Session.php** | `app/Core/` | Session mgmt | Used for user sessions |
| **AuthMiddleware** | `app/Middleware/` | Auth checks | Protects routes |
| **RoleMiddleware** | `app/Middleware/` | Role checks | Enforces access control |

---

## 🚀 Adding New Features

When adding new features, follow this pattern:

1. **Create Controller** at `app/Controllers/FeatureController.php`
   - Extend `BaseController`
   - Add business logic methods

2. **Create Model** at `app/Models/FeatureModel.php`
   - Extend `BaseModel`
   - Add database queries

3. **Create Views** at `app/Views/feature/`
   - Use `app.php` layout
   - Reuse styles from `main.css`
   - Reuse scripts from `app.js`

4. **Add Routes** to `routes/web.php`
   - Define URL patterns
   - Apply middleware as needed

5. **Add Middleware** to `app/Middleware/` if needed
   - For custom access control

---

## 📊 Project Statistics

| Component | Count | Status |
|-----------|-------|--------|
| **Controllers** | 16 | Active |
| **Models** | 15 | Active |
| **Views** | 30+ | Active |
| **CSS Files** | 6 | Reusable |
| **JS Files** | 9 | Reusable |
| **Configuration Files** | 10 | Clean |
| **Middleware** | 5 | Reusable |
| **Routes** | 40+ | Clean |
| **Documentation Files** | 20+ | Complete |

---

## ✨ Recent Implementations (April 12, 2026)

### ⭐ New Features
1. **Password Toggle** - `app/Views/auth/login.php`
   - Show/hide password functionality
   - Keyboard accessible
   
2. **Role-Based Dashboards** - `app/Views/dashboard/`
   - 6 role-specific dashboards created
   - Automatic routing based on user role
   - Session-based access control

3. **Working Test Credentials** - Database `users` table
   - 6 roles with working bcrypt hashes
   - All verified and tested

### 📚 Documentation
- Moved to `/docs/` for organization
- README guides added
- Complete technical documentation

---

## 🔐 Security Structure

- **Authentication**: `app/Middleware/AuthMiddleware.php`
- **Role-Based Access**: `app/Middleware/RoleMiddleware.php`
- **CSRF Protection**: `app/Middleware/CsrfMiddleware.php`
- **Password Hashing**: Using bcrypt via PHP `password_hash()`
- **Session Management**: `app/Core/Session.php`

---

## 🎓 Beginner-Friendly

The project is organized to be:
- ✅ Easy to understand
- ✅ Easy to navigate
- ✅ Easy to add features
- ✅ Easy to debug
- ✅ Well-documented

---

## 📋 Best Practices Followed

- ✅ MVC architecture
- ✅ PSR-4 autoloading
- ✅ DRY (Don't Repeat Yourself)
- ✅ SOLID principles
- ✅ Security best practices
- ✅ Database preparation
- ✅ Organized routing
- ✅ Middleware system
- ✅ Configuration management
- ✅ Separation of concerns

---

**Status**: ✅ Clean, organized, production-ready

**Last Updated**: April 12, 2026  
**Project**: IMS Final (Institutional Management System)
