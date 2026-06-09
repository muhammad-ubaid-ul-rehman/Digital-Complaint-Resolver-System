# 🛡️ DCRS — Digital Complaint Resolver System

A web-based complaint management platform built for educational institutions. Students can submit complaints, admins assign them to resolvers, and resolvers update progress — all with live notifications.

---

## 📸 Screenshots

> Login → Student Dashboard → Submit Complaint → Admin Assigns → Resolver Updates → Resolved ✅

---

## 🚀 Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Backend | PHP 8.x |
| Database | MySQL 5.7+ |
| Local Server | Laragon (recommended) / XAMPP / WAMP |
| Architecture | 3-Tier MVC-style (Models, Controllers, Views) |

---

## 👥 User Roles

| Role | Capabilities |
|------|-------------|
| **Student** | Register, submit complaints, track status, view timeline, receive notifications |
| **Admin** | View all complaints, assign/reassign resolvers, set priority, manage users, view reports |
| **Resolver** | View assigned complaints, update progress, add remarks, mark resolved |

---

## 📁 Project Structure

```
dcrs/
│
├── index.php                        # Entry point — redirects by role
├── login.php                        # Login page
├── register.php                     # Student self-registration
├── unauthorized.php                 # 403 page
│
├── database/
│   └── dcrs_schema.sql              # Full MySQL schema + seed data
│
├── backend/
│   ├── config/
│   │   ├── database.php             # DB credentials
│   │   ├── db_connect.php           # PDO singleton connection
│   │   ├── session.php              # Session management + role guards
│   │   └── bootstrap.php           # Auto-loader (include this everywhere)
│   ├── models/
│   │   ├── UserModel.php            # User CRUD, resolver stats
│   │   ├── ComplaintModel.php       # Complaint lifecycle, timeline
│   │   └── NotificationModel.php   # Notifications + event triggers
│   ├── controllers/
│   │   ├── AuthController.php       # Login, register, logout
│   │   ├── ComplaintController.php  # Submit, delete (student)
│   │   ├── AdminController.php      # Assign, reassign, add resolver
│   │   ├── ResolverController.php   # Update progress, resolve
│   │   └── NotificationController.php # AJAX notification API
│   └── helpers/
│       └── helpers.php              # Utility functions (badges, sanitize, etc.)
│
├── frontend/
│   ├── css/style.css                # Full custom stylesheet
│   ├── js/app.js                    # Live notifications, modals, toasts
│   └── partials/layout.php          # Shared sidebar + header layout
│
└── pages/
    ├── complaint_detail.php         # Shared complaint detail (all roles)
    ├── student/
    │   ├── dashboard.php
    │   ├── submit_complaint.php
    │   ├── my_complaints.php
    │   └── notifications.php
    ├── admin/
    │   ├── dashboard.php
    │   ├── all_complaints.php
    │   ├── assign.php               # Assign + Reassign tab
    │   ├── reports.php
    │   └── users.php                # User management + Add Resolver
    └── resolver/
        ├── dashboard.php
        ├── assigned.php
        └── update.php
```

---

## ⚙️ Requirements

- **PHP** 8.0 or higher
- **MySQL** 5.7 or higher
- **Laragon** (recommended) — auto-creates virtual host
- OR **XAMPP / WAMP** — manual setup required

---

## 🛠️ Installation — Laragon (Recommended)

### Step 1 — Clone or Download

```bash
# Option A: Clone
git clone https://github.com/YOUR_USERNAME/dcrs.git

# Option B: Download ZIP and extract
```

### Step 2 — Place in Laragon www folder

```
C:\laragon\www\dcrs\
```

Make sure the folder is named exactly `dcrs`.

### Step 3 — Start Laragon

Open Laragon and click **Start All**.  
Laragon will automatically create a virtual host at:

```
http://dcrs.test
```

Or use:

```
http://localhost/dcrs
```

### Step 4 — Create the Database

1. Open your browser and go to:
   ```
   http://localhost/phpmyadmin
   ```
2. Click **New** in the left sidebar
3. Create a database named:
   ```
   dcrs_db
   ```
4. Select `dcrs_db` → click **Import** tab
5. Click **Choose File** → select `database/dcrs_schema.sql`
6. Click **Go**

The schema will create all tables and insert demo data automatically.

### Step 5 — Configure Database (if needed)

Open `backend/config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dcrs_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // Laragon default is empty
```

> ⚠️ Laragon uses `root` with **no password** by default. No change needed.

### Step 6 — Open the App

```
http://dcrs.test
```
or
```
http://localhost/dcrs
```

---

## 🛠️ Installation — XAMPP / WAMP

### Step 1 — Place project

**XAMPP:**
```
C:\xampp\htdocs\dcrs\
```

**WAMP:**
```
C:\wamp64\www\dcrs\
```

### Step 2 — Update APP_URL

Open `backend/config/database.php` and update:

```php
define('APP_URL', 'http://localhost/dcrs');
```

### Step 3 — Import database

Same as Laragon Step 4 above, then visit:

```
http://localhost/dcrs
```

---

## 🔑 Demo Login Credentials

All accounts use the same password:

```
Password: password
```

| Role | Email |
|------|-------|
| 👨‍💼 Admin | admin@university.edu |
| 🎓 Student | ali@university.edu |
| 🎓 Student | sara@university.edu |
| 🔧 Resolver | fatima@university.edu |
| 🔧 Resolver | tariq@university.edu |

---

## ✨ Features

### Student
- 📝 Submit complaints with title, category, priority, description
- 📋 View all personal complaints with status and progress
- 🗑️ Delete pending (unassigned) complaints
- 📊 Visual progress bars per complaint
- 🔔 Live notifications (bell icon, auto-refreshes every 10 sec)
- 📜 Full activity timeline per complaint

### Admin
- 🏠 Dashboard with system-wide stats
- 📁 View & filter all complaints (by status, priority, category, search)
- 👤 Assign complaints to resolvers with priority setting
- 🔄 **Reassign** already-assigned complaints to a different resolver
- ➕ **Add new resolver accounts** directly from Users panel
- 📊 Reports — category breakdown, resolver performance, resolution rate
- 🔒 Activate / deactivate user accounts

### Resolver
- 🏠 Personal dashboard with assigned complaint stats
- ✅ View all assigned complaints with filter
- ✏️ Update progress (slider 0–100%), status, and remarks
- 📜 Full timeline view per complaint
- 🔔 Live notifications when new complaint assigned or reassigned

### System
- 🔔 **Live notifications** — polls every 10 seconds, shows toast popup on new notification
- 🔐 Role-based access control — each role only sees its own pages
- 🛡️ Password hashing with bcrypt
- 📱 Responsive design — works on mobile
- ⚡ Auto-dismiss alerts after 5 seconds

---

## 🗄️ Database Tables

| Table | Description |
|-------|-------------|
| `users` | All users — students, admins, resolvers |
| `complaints` | Complaint records with full lifecycle fields |
| `progress_updates` | Timeline entries for each complaint |
| `notifications` | Per-user notification inbox |
| `sessions` | Server-side session tracking |

---

## 🔒 Security Features

- Passwords hashed with `password_hash()` (bcrypt, cost 10)
- Sessions use `session_regenerate_id()` on login
- All user input sanitized with `htmlspecialchars()` + `strip_tags()`
- Role guards on every page — unauthorized access redirects to 403
- PDO prepared statements — no SQL injection possible

---

## 🐛 Common Issues

**Blank page / no output**
- Enable PHP error display in `backend/config/bootstrap.php`:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

**Database connection failed**
- Make sure MySQL is running in Laragon
- Confirm `DB_NAME = dcrs_db` exists in phpMyAdmin
- Check `DB_USER` and `DB_PASS` in `database.php`

**Session not working / keeps logging out**
- Make sure `session_name` is consistent — only `DCRS_SESSION` is used
- Check PHP session path is writable

**APP_URL mismatch (links broken)**
- Update `APP_URL` in `backend/config/database.php` to match your actual URL

---

## 📄 License

This project is built for academic/university use.  
Free to use, modify, and distribute for educational purposes.

---

## 🙏 Credits

Built as a university project — **Digital Complaint Resolver System (DCRS)**  
Stack: PHP · MySQL · Vanilla JS · Custom CSS · Laragon
