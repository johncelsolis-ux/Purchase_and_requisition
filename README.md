# AmbatuGrow ERP — PR & PO Approval Module (Laravel)

This is a Laravel Blade implementation of the 3 screens you shared, wired
together into one working flow with real routes and a SQLite database:

```
Create Requisition  →  Route for Approval  →  PR & PO Approval Queue
  (Screen 3)              (Screen 2)              (Screen 1)
```

## What's included

```
app/Http/Controllers/RequisitionController.php   Create + Route-for-approval steps
app/Http/Controllers/ApprovalController.php       Approval queue + approve/reject/delegate
app/Models/Requisition.php, RequisitionItem.php, ApprovalStep.php
database/migrations/...                           requisitions, requisition_items, approval_steps
database/seeders/RequisitionSeeder.php            Seeds the 3 sample PRs from your screenshots
resources/views/layouts/app.blade.php             Shared sidebar/topbar shell
resources/views/requisitions/create.blade.php     Screen 3
resources/views/requisitions/route.blade.php      Screen 2
resources/views/approvals/index.blade.php         Screen 1
routes/web.php                                    Connects all 3 screens
```

## How the screens are connected

| Route | Name | Purpose |
|---|---|---|
| `GET /requisitions/create` | `requisitions.create` | Screen 3 form |
| `POST /requisitions` | `requisitions.store` | Saves the requisition + items, redirects to Screen 2 |
| `GET /requisitions/{requisition}/route` | `requisitions.route.edit` | Screen 2 form |
| `POST /requisitions/{requisition}/route` | `requisitions.route.store` | Saves the approval steps, sets status to `pending_approval`, redirects to Screen 1 |
| `GET /approvals` | `approvals.index` | Screen 1 queue + detail panel (`?selected={id}`) |
| `POST /approvals/{requisition}/decide` | `approvals.decide` | Approve / Reject / Delegate — advances `current_step` or closes the requisition |

Sidebar links (`Create PO`, `Order Management`, `Approvals`) use these same
named routes, so navigating between the 3 screens from any page always works.

## Setup

This project ships only the app-specific code (controllers, models, views,
migrations, routes) — not Laravel's core framework files — so drop it into a
fresh Laravel install:

```bash
composer create-project laravel/laravel ambatugrow-app
cd ambatugrow-app

# copy the contents of this delivered folder in, overwriting:
#   app/Http/Controllers/, app/Models/, database/migrations/,
#   database/seeders/, resources/views/, routes/web.php

touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=RequisitionSeeder
php artisan serve
```

Then open `http://localhost:8000` — it redirects straight to the Approval
Queue (Screen 1), with a "+ New requisition" link that starts the flow from
Screen 3.

## Notes / next steps you may want

- Requestor/approver names are free-text inputs for now — swap in a real
  `users` table + `Auth` once you have login.
- Budget figures on Screen 1 (`$50,000` cap) are hard-coded in
  `ApprovalController@index` — move to a `budgets` table when ready.
- Styling uses the Tailwind CDN build for portability; swap to a compiled
  Tailwind (Vite) build for production.
