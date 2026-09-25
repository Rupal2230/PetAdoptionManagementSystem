# PetAdoption — Role-Based Access Control Setup

## What's included

```
PetAdoption/
├── admin/
│   ├── dashboard.php       Admin view of all adoption requests + Accept/Reject
│   ├── pets.php            Admin: add/list/delete pets
│   └── update_request.php  Handles the Accept/Reject POST actions
├── includes/
│   ├── auth.php            Session bootstrap + require_login()/require_admin()/require_customer()
│   └── nav.php             Shared navigation bar
├── css/style.css
├── images/                 (put pet photos here, or use full URLs)
├── create_admin.php        One-time script to seed the first admin account
├── dbconnect.php           PDO connection (edit your DB credentials here)
├── home.php                Customer: browse available pets
├── login.php                Login + role-based redirect
├── logout.php
├── my_requests.php         Customer: view status of their own requests
├── pet_details.php         Pet detail page + "Submit Adoption Request" form
├── register.php            Signup — always creates role = 'customer'
└── schema.sql               users / pets / adoption_requests tables
```

## 1. Create the database

```bash
mysql -u root -p < schema.sql
```

This creates the `petadoption` database with three tables:
- **users** — has a `role ENUM('admin','customer') DEFAULT 'customer'` column.
- **pets** — pets owned/managed by admins (`added_by` -> users.id).
- **adoption_requests** — links `customer_id` + `pet_id`, with `status ENUM('pending','approved','rejected')`.

## 2. Configure the DB connection

Edit `dbconnect.php` and set `$DB_USER` / `$DB_PASS` (and host/db name if different).

## 3. Create your first admin account

Preferred (command line — not reachable by web visitors):

```bash
php create_admin.php "Shelter Admin" "admin@petadoption.test" "SomeStrongPassword123"
```

If you must do it over the web instead, first change `SETUP_KEY` inside
`create_admin.php` to a real secret, then visit:

```
https://yoursite.com/create_admin.php?key=YOUR_SECRET&name=Shelter+Admin&email=admin@petadoption.test&password=SomeStrongPassword123
```

It refuses to run again once an admin already exists. **Delete the file
(or blank out SETUP_KEY) once you're done.**

## 4. How the roles work

- **register.php** always inserts new users with `role = 'customer'` —
  there is no way for a public visitor to sign themselves up as admin.
- **login.php** stores `$_SESSION['user_id']` and `$_SESSION['role']`,
  and redirects admins to `admin/dashboard.php` and customers to
  `home.php`.
- **includes/auth.php** provides:
  - `require_login()` — any logged-in user.
  - `require_admin()` — only `role = 'admin'`; everyone else is redirected
    to `home.php`.
  - `require_customer()` — only `role = 'customer'`.
  Every admin page (`admin/dashboard.php`, `admin/pets.php`,
  `admin/update_request.php`) calls `require_admin()` at the top, so
  direct URL access by a customer or guest is blocked and redirected.

## 5. Adoption flow

1. Admin adds pets via **admin/pets.php**.
2. Customer browses pets on **home.php**, opens **pet_details.php**,
   and submits a request (one pending request per pet per customer).
3. Admin sees all requests on **admin/dashboard.php** and clicks
   **Accept** or **Reject**.
   - Accepting sets the request to `approved`, marks the pet `adopted`,
     and auto-rejects any other pending requests for that same pet.
   - Rejecting just sets the request to `rejected`.
4. Customer checks **my_requests.php** any time to see the current
   status (`pending` / `approved` / `rejected`).

## Notes / things you may want to extend

- Passwords are hashed with `password_hash()` / verified with
  `password_verify()` — never stored in plain text.
- State-changing admin actions (`update_request.php`, `pets.php`
  delete) are POST-only and protected with a CSRF token stored in
  the session.
- `require_login()` redirects unauthenticated visitors to
  `login.php?redirect=...` so they land back where they started
  after logging in.
- Image uploads aren't implemented — `image_path` is just a text
  field (a relative path under `images/` or a full URL). Wiring up
  real file uploads is a natural next step.
