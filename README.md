# CayDesk - Ticket Support System 

A robust, role-based support ticket management system built with Laravel. This project is designed to handle interactions between Customers, Agents, Supervisors, and Administrators efficiently without letting the codebase turn into a spaghetti monster.

## 📑 Table of Contents

- [Project Overview](#project-overview)
- [Feature Summary](#feature-summary)
- [Tech Stack](#tech-stack)
- [Installation & Environment Setup](#installation-env-setup)
- [Running Tests](#running-tests)
- [Seeded User Credentials](#seeded-user-credentials)
- [Screenshots](#screenshots)
- [Architecture Notes](#architecture-notes)
- [Database Relationship Explanation](#database-relationship-explanation)
- [API Examples](#api-examples)
- [Known Limitations](#known-limitations)
- [Developer Confession](#developer-confession)

<a id="project-overview"></a>
---

## 🚀 Project Overview
CayDesk provides a centralized platform for tracking, managing, and resolving customer issues. It features strict role-based access control (RBAC), SLA tracking, internal notes, file attachments, RESTful APIs, and comprehensive activity logging. The admin and agent interfaces are powered by Filament, ensuring a clean and responsive user experience.

<a id="feature-summary"></a>
---

## ✨ Feature Summary

### Customer
- Register and login
- Create support tickets
- View and track own tickets
- Add public comments
- Upload attachments
- View recent activity history
- Reopen resolved tickets (based on workflow)

### Agent
- View assigned tickets
- Update ticket status
- Add comments and internal notes
- Upload attachments
- View recent activity history
- Resolve tickets

### Supervisor
- Monitor team tickets
- Assign and reassign tickets
- Export reports
- View overdue and escalated tickets
- View recent activity history

### Administrator
- Full ticket management
- User management
- Category, Priority, Label management
- SLA Rule management
- Activity log management
- Dashboard analytics

### System Features
- Role-based access control (RBAC)
- Ticket status workflow
- SLA due-date calculation
- Polymorphic file attachments
- REST API using Sanctum
- Activity logging
- Queue-based notifications
- Dashboard reporting
- Pest test suite

<a id="tech-stack"></a>
---

## 🛠️ Tech Stack
* **Framework:** Laravel (v13)
* **Admin Panel & UI:** FilamentPHP (v5) + Tailwind CSS
* **Authentication & API:** Laravel Breeze & Laravel Sanctum
* **Roles & Permissions:** Spatie Laravel Permission
* **Activity Logging:** Spatie Activitylog
* **Testing:** Pest PHP
* **Database:** MySQL

<a id="installation-env-setup"></a>
---

## ⚙️ Installation & Environment Setup

Follow these steps to get the project running on your local machine.

1. **Clone the repository and install dependencies:**
   ```bash
   git clone https://github.com/xnoname2003/caydesk.git
   cd caydesk
   composer install
   npm install && npm run build
   ```

2. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Note: Configure your database settings in the `.env` file. For testing email notifications, set `MAIL_MAILER=log` or use Mailtrap.*

3. **Database Migration and Seeding:**
   ```bash
   php artisan migrate --seed
   ```

4. **Storage Link Setup:**
   *(Required for handling ticket and comment attachments)*
   ```bash
   php artisan storage:link
   ```

5. **Queue Setup:**
   *(Handled by our resident Queue Goblin 👺 to process emails and notifications)*
   ```bash
   php artisan queue:work
   ```

6. **Run the Application:**
   ```bash
   php artisan serve
   ```
   Access the app at `http://127.0.0.1:8000/`.

<a id="running-tests"></a>
---

## 🧪 Running Tests

To ensure everything is working correctly and the business logic is intact, run the test suite:

```bash
php artisan test
```
*(Tests passed? Good. Now go touch grass. 🌱)*

<a id="seeded-user-credentials"></a>
---

## 🔑 Seeded User Credentials

The database seeder automatically creates the following users for testing purposes. All passwords are set to `password`.

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@admin.com` | `password` |
| **Supervisor** | `supervisor@admin.com` | `password` |
| **Agent** | `agent@admin.com` | `password` |
| **Customer** | `customer@demo.com` | `password` |

<a id="screenshots"></a>
---

## 📸 Screenshots

### Role: Administrator

| Administrator Dashboard | Administrator Ticket |
| :---: | :---: |
| <img width="1469" height="776" alt="Image" src="https://github.com/user-attachments/assets/4f4ee87b-5cac-4be0-9df2-c172a8097fa0" /> | <img width="1469" height="770" alt="Image" src="https://github.com/user-attachments/assets/5bb3f792-4d41-4117-9f76-ffedbb5b0245" /> |

| Ticket Detail | Communication |
| :---: | :---: |
| <img width="1469" height="773" alt="Image" src="https://github.com/user-attachments/assets/447f8256-bf0c-43ed-81db-524923089192" /> | <img width="1469" height="772" alt="Image" src="https://github.com/user-attachments/assets/e6f56266-aa30-4e87-a4e2-f4db5db2deb7" /> |

### Role: Supervisor

| Supervisor Dashboard | Supervisor Ticket |
| :---: | :---: |
| <img width="1468" height="776" alt="Image" src="https://github.com/user-attachments/assets/4e34df41-2341-42b5-b3ec-a51dc20674fe" /> | <img width="1469" height="778" alt="Image" src="https://github.com/user-attachments/assets/72977815-a227-4505-bbb6-26f614c714ad" /> |

| Ticket Detail | Communication |
| :---: | :---: |
| <img width="1469" height="773" alt="Image" src="https://github.com/user-attachments/assets/a2d0ae8c-9cd5-4759-94c7-34a07db39a88" /> | <img width="1469" height="773" alt="Image" src="https://github.com/user-attachments/assets/c73cee0c-717f-47c3-95e2-a69aec5e9be8" /> |

### Role: Agent

| Agent Dashboard | Agent Ticket |
| :---: | :---: |
| <img width="1469" height="776" alt="Image" src="https://github.com/user-attachments/assets/9b7497e8-4ffe-4c51-ba17-fca384c24a47" /> | <img width="1469" height="777" alt="Image" src="https://github.com/user-attachments/assets/45f575d9-b7f9-47c0-8380-ca471f5cebe6" /> |

| Ticket Detail | Communication |
| :---: | :---: |
| <img width="1469" height="778" alt="Image" src="https://github.com/user-attachments/assets/ea2fac7f-109d-4819-861c-2c220ff7efbc" /> | <img width="1469" height="777" alt="Image" src="https://github.com/user-attachments/assets/6bf16cd2-9251-4bf5-8285-11fe1d83b1a6" /> |

### Role: Customer

| Customer Dashboard | Customer Ticket |
| :---: | :---: |
| <img width="1469" height="775" alt="Image" src="https://github.com/user-attachments/assets/35d7c867-a4a6-4faf-98ac-3480999084b7" /> | <img width="1469" height="776" alt="Image" src="https://github.com/user-attachments/assets/6ed53210-5cd9-45b6-af74-61694d34ffcc" /> |

| Ticket Detail | Communication |
| :---: | :---: |
| <img width="1469" height="775" alt="Image" src="https://github.com/user-attachments/assets/38ea8830-790e-4cd4-a508-4c08ca7d8c18" /> | <img width="1469" height="773" alt="Image" src="https://github.com/user-attachments/assets/7e4a268c-cd85-4a3e-92ba-25a81302d655" /> |

<a id="architecture-notes"></a>
---

## 🏗️ Architecture Notes

To keep the controllers clean and maintainable, the application uses a structured approach:
* **Controllers & Filament Resources:** Handle the HTTP layer, routing, and UI presentation.
* **Services (`App\Services`):** Business logic is abstracted here. For example, `TicketStatusService` centrally handles all allowed status transition rules so we don't have random `if/else` soups in the controllers.
* **Observers (`App\Observers`):** Event-driven logic (like sending notifications when a ticket is created or a comment is posted) is handled by `TicketObserver` to adhere to the Single Responsibility Principle.
* **Policies:** Strict backend authorization using Laravel Policies to prevent unauthorized IDOR attacks and data leaks.

<a id="database-relationship-explanation"></a>
---

## 🗄️ Database Relationship Explanation

The database is highly relational to support the RBAC and tracking requirements:
* **Users & Teams:** `Team hasMany Users`. A Supervisor monitors a specific team, and Agents belong to a single team.
* **Tickets:** `Ticket belongsTo User (Creator)` and `Ticket belongsTo User (Assigned Agent)`.
* **Master Data:** Tickets are categorized by `Category`, have a specific `Priority`, and can be tagged with multiple `Labels` (Many-to-Many).
* **Polymorphic Attachments:** `Ticket morphMany Attachment` and `Comment morphMany Attachment`. Files are linked dynamically to the relevant model.
* **Activity Logs:** Spatie Activitylog tracks actions globally, storing polymorphic relationships (`subject_type`, `subject_id`) to display precise role-based logs.

<a id="api-examples"></a>
---

## 🔌 API Examples

The system provides a REST API via Laravel Sanctum for customers to interact with their tickets remotely.

**1. Authentication (Get Token)**
```http
POST /api/login
Content-Type: application/json

{
  "email": "customer@demo.com",
  "password": "password"
}
```

**2. Create a Ticket**
```http
POST /api/tickets/create
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "title": "Cannot login after password reset",
  "description": "I tried resetting my password but the link is expired.",
  "category_id": {category_id},
  "priority_id": {priority_id},
  "labels[0]": {labels_id[0]},
  "labels[1]": {labels_id[1]},
  "labels[2]": {labels_id[2]},
}
```

**3. List My Tickets**
```http
GET /api/tickets
Authorization: Bearer {your_token}
```

<a id="known-limitations"></a>
---

## ⚠️ Known Limitations
* **SLA Calculation:** Currently, the SLA due-date calculation operates linearly and does not account for business hours, weekends, or public holidays.
* **Real-time Updates:** Ticket comments require a page refresh to appear. Real-time broadcasting via WebSockets (Laravel Reverb) is planned but not yet implemented.
* **Exporting:** Reports are exported synchronously. For massive datasets, this logic should be refactored into a queued background job.

---
<a id="developer-confession"></a>

## 🤫 Developer Confession

* **What part was hardest?**
  Writing the Activity Log scope filters. Finding the perfect balance between displaying all relevant actions for Agents and Supervisors without accidentally exposing system-wide master data changes (which only Admins should see) took a lot of polymorphic querying.
* **What shortcut did you take?**
  I leaned heavily on Filament v5's internal component wrappers for the frontend. Building out the dashboards and data tables from scratch using pure Tailwind + Blade would have taken significantly longer.
* **What would you improve with more time?**
  I would implement Laravel Reverb for real-time ticket comment updates and notifications. Waiting for a page reload feels a bit vintage for a modern helpdesk.
* **Which part of the code is most cursed but still works?**
  The manual nested `orWhereHasMorph` checks injected directly into the `RecentActivityWidget` query builder. I considered using Global Scopes, but it felt too risky for the Admin panel side. It looks like a linguistic maze of closures, but it serves as an ironclad defense against data leakage.
