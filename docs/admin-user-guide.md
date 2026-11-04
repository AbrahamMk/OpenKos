# OpenKOS Admin User Guide

> A practical guide for owners and staff who manage properties, leases, billing, and day-to-day operations in OpenKOS.

**Audience:** Owners, admins, and operational staff  
**Product:** OpenKOS — open-source property management for boarding houses (kos), apartments, and rentals  
**Last updated:** November 2026

---

## Table of contents

1. [What OpenKOS is](#1-what-openkos-is)
2. [Getting started](#2-getting-started)
3. [Roles and access](#3-roles-and-access)
4. [Navigation overview](#4-navigation-overview)
5. [Dashboard and billing queue](#5-dashboard-and-billing-queue)
6. [Set up your portfolio](#6-set-up-your-portfolio)
7. [Tenants and applications](#7-tenants-and-applications)
8. [Leases lifecycle](#8-leases-lifecycle)
9. [Invoices and payments](#9-invoices-and-payments)
10. [Rent reminders](#10-rent-reminders)
11. [Maintenance](#11-maintenance)
12. [Expenses](#12-expenses)
13. [Inspections](#13-inspections)
14. [Documents](#14-documents)
15. [Public listings and SEO](#15-public-listings-and-seo)
16. [Users and custom roles](#16-users-and-custom-roles)
17. [Settings](#17-settings)
18. [Import and export](#18-import-and-export)
19. [Tenant portal vs staff app](#19-tenant-portal-vs-staff-app)
20. [Common workflows cheat sheet](#20-common-workflows-cheat-sheet)
21. [Troubleshooting tips](#21-troubleshooting-tips)

---

## 1. What OpenKOS is

OpenKOS helps you run the full rental lifecycle from one place:

- Properties and units (rooms, apartments, whole buildings)
- Leases and tenants (including shared rooms)
- Invoices, payments, and rent reminders
- Maintenance tickets
- Expenses and inspections
- Public listings and a tenant portal
- Staff accounts with role-based permissions

You can run it for a single kos or a multi-property portfolio. Most screens open forms in a side panel (sheet) so you stay on the page you were working on.

---

## 2. Getting started

### First login

1. Open your OpenKOS URL (for example `https://your-domain.com` or your local install).
2. Sign in with the **owner** account created during installation (`php artisan app:install`).
3. You land on the **Dashboard**.

### Recommended first-time setup (owner)

Do these once before day-to-day operations:

1. **Settings → General** — site name, country, timezone, currencies, branding.
2. **Settings → Mail** — configure email and send a test message.
3. **Settings → Reminders** — enable rent reminders and edit message templates.
4. **Settings → Payment Gateway** (optional) — connect an online payment provider.
5. **Properties → Property Types / Amenities** — set catalogs you will reuse.
6. Create your first **property**, then units/rates, then a lease.

### Security basics

- Use a strong owner password.
- Open **Settings → Security** to change your password (and enable 2FA if available on your install).
- Invite staff with the least permissions they need (see [Roles and access](#3-roles-and-access)).

---

## 3. Roles and access

OpenKOS uses permissions. What you see in the sidebar depends on your role.

| Role type | Who | Access |
| --- | --- | --- |
| **Owner** | Single system owner created at install | Full access to everything, including settings, plugins, roles, and catalogs |
| **Custom roles** | Staff you invite | Only the permissions you assign (Admin, Staff, Finance, etc.) |

### Important distinction

The recommended **Admin** role template is for **operations**, not platform control.

- **Owner** can change organization settings, plugins, payment gateway, mail/WhatsApp, public SEO, property types, amenities, expense categories, and roles.
- **Admin (template)** can manage properties, units, tenants, leases, expenses, and users — but not platform settings.

### Recommended role templates

When creating a role, you can start from a template:

| Template | Best for |
| --- | --- |
| **Admin** | Day-to-day portfolio operations |
| **Staff** | Limited view/update of tenants and leases |
| **Finance Staff** | Financial reports and expense exports |
| **Front Desk** | Check-ins and tenant inquiries |
| **Property Manager** | Properties, units, tenants, leases (including move / move-out) |
| **Maintenance Staff** | Start from template, then add maintenance ticket permissions as needed |

### Permissions you will see most often

- View / create / update / delete for properties, units, tenants, leases, expenses
- Payments: create and verify
- Reminders: send
- Maintenance tickets: view, create, update, assign
- Inspections: view, create, update, complete; templates manage
- Users: invite, update, reset password
- Import / export for bulk CSV transfers

---

## 4. Navigation overview

The staff sidebar is grouped into sections:

### Overview

- **Dashboard** — attention items, quick actions, recent activity
- **Financial Dashboard** — revenue, expenses, NOI, property performance

### Daily operations

- **Billing** — invoice work queue (overdue, due today, pending review, and more)
- **Maintenance** — tickets and unit blocking
- **Expenses** — one-off and recurring costs (owner also sees Expense Categories)
- **Inspections** — checklists and templates

### Portfolio

- **Properties** — portfolio, units, pricing, listings
- **Leases** — global lease list and lifecycle actions
- **Tenants** — tenant records, invitations, documents
- **Public Portal → SEO & Sharing** (owner) — public site SEO

### Administration

- **Users** — invite and manage staff
- **Roles & Permissions** (owner) — custom roles

### Settings

- Profile and Security (everyone)
- Owner-only: General, Reminders, Mail, WhatsApp, Payment Gateway, Plugins, catalogs

> **Note:** Rental **Applications** are managed at `/portal/applications`. Operators with owner access or tenant view permission can review and convert applicants there. This screen is not always listed in the main admin sidebar.

---

## 5. Dashboard and billing queue

### Dashboard

Use the dashboard as your daily start screen:

- See overdue invoices and items that need attention
- Jump into properties, tenants, or tickets
- Quickly create a tenant, property, or maintenance ticket

### Billing (`/dashboard/rent`)

This is your rent collection board. Filter invoices by:

- All
- Overdue
- Due Today
- Upcoming
- Pending Review
- Partial
- Paid

Typical loop:

1. Open Billing.
2. Filter by urgency (for example Overdue or Pending Review).
3. Open an invoice.
4. Record a payment, verify a proof, or follow the lease to send a reminder.

### Financial Dashboard

View month-to-date or year-to-date performance:

- Revenue and expenses
- Collected trends
- Per-property performance

Requires the `financials.view` permission.

---

## 6. Set up your portfolio

### Step A — Create a property

1. Go to **Properties**.
2. Create a property and fill in location and details.
3. Choose a **rental mode**:

| Mode | Label in UI | When to use |
| --- | --- | --- |
| `unit` | Individual units | Kos / boarding house with separately rented rooms |
| `whole_property` | Whole property | Entire house or building rented as one |
| `hybrid` | Both | Sometimes rent rooms, sometimes the whole property |

### Step B — Configure the property workspace

Open a property to use its tabs:

| Tab | Purpose |
| --- | --- |
| **Overview** | Edit property details |
| **Units** | Inventory of rooms/units (unit and hybrid modes) |
| **Unit Types** | Shared types, default rates, listing status, gallery |
| **Pricing** | Whole-property rates (whole-property and hybrid modes) |
| **Leases** | Property-scoped leases |
| **Inspections** | Property-level inspections |
| **Listing** | Publish to the public site |
| **Documents** | Property files |

### Step C — Units and rates

1. Create **unit types** (for example Standard, Deluxe).
2. Set default rates on the unit type.
3. Create units and assign types.
4. Override rates on a specific unit when needed (unit rates win over unit-type defaults for the same billing interval and currency).

Unit statuses you will use:

- Available
- Occupied
- Maintenance
- Unavailable

### Step D — Occupancy rules to remember

- An active **whole-property lease** blocks unit leases on that property (and the reverse in hybrid mode).
- A shared unit can hold multiple tenants up to the unit **capacity**, under **one lease**, with one **primary tenant**.
- You cannot archive a property that still has active leases.
- Units blocked for maintenance cannot receive new leases, assignments, or moves until restored.

---

## 7. Tenants and applications

### Create a tenant manually

1. Go to **Tenants**.
2. Create the tenant profile (name, contact details, etc.).
3. Assign them to a unit or property lease (Assign Unit / lease flow).
4. Optionally **invite** them to the tenant portal.

### Invite to the portal

- **Invite** sends an invitation so the tenant can create/login to their account.
- **Resend invitation** if they did not receive it.
- **Disable portal access** revokes login; they can be re-invited later. Notifications may still apply depending on configuration.

### Applications (from public listings)

Prospects can apply from a public listing.

Operator flow:

1. Open **Applications** (`/portal/applications`).
2. Review status: new → reviewing / accepted / rejected / withdrawn.
3. Add private operator notes and optional applicant feedback.
4. When accepted, use **Convert to Tenant**.

> Registration alone does **not** make someone a tenant. Conversion (or manual tenant creation) does.

---

## 8. Leases lifecycle

Leases are the center of billing. Every lease belongs to a property. It may target:

- One **unit**, or
- The **whole property** (no unit)

### Create a lease

From **Leases**, a property workspace, or a unit workspace:

1. Choose the target (unit or whole property).
2. Set tenants (mark one as primary).
3. Set deposit, billing interval, and billing strategy:
   - **Advance** (default) — due within the billed period
   - **Arrears** — due after the period is consumed
4. Save. OpenKOS snapshots rates and generates invoices for a short horizon (about two months). More invoices are generated daily by the system.

### Day-to-day lease actions

| Action | What it does |
| --- | --- |
| **Edit** | Update lease terms |
| **Record payment** | Apply money to a specific invoice |
| **Verify / reject proof** | Confirm or reject uploaded payment proof |
| **Send reminder** | Manually remind the primary tenant |
| **Move** | Transfer the lease to another unit (history is kept) |
| **Move out** | End occupancy; settle deposit; cancel eligible future pending invoices |
| **Renew** | Extend an eligible lease after financial checks |
| **Print / download invoice** | PDF when invoice PDFs are enabled in General settings |

### Multi-tenant (shared) units

- One lease, multiple tenants, capacity-limited.
- Deposit lives on the lease (not per co-tenant).
- Moving out one co-tenant can leave the lease active; full move-out terminates it.
- Reminders and most communications go to the **primary tenant**.

### Lease workspace tabs

Overview · Invoices · Payments · Documents · Inspections

---

## 9. Invoices and payments

Billing is **invoice-centric**:

```
Lease → Invoice → Payment → Payment proof
```

### Invoice statuses

Statuses are driven by **confirmed** payments only:

- Pending
- Partial
- Paid
- (Plus workflow views like overdue / pending review in Billing)

Rules:

- Partial payments are allowed.
- Overpayment is rejected.
- Future pending invoices with no confirmed money can be cancelled on move-out.

### Record a manual payment

1. Open the invoice (from Billing or the lease).
2. Record amount, method, and optional proof.
3. If proof requires review, another staff member with verify permission confirms or rejects it.

### Online payments (gateway)

If a payment gateway is configured (owner: **Settings → Payment Gateway**):

- Tenants can pay from the portal or a signed pay link.
- The **webhook** is the source of truth for confirmation.
- Staff can use **Recheck** on a payment attempt if status looks stuck.
- Do **not** treat a browser redirect alone as confirmation.

---

## 10. Rent reminders

### Configure (owner)

**Settings → Reminders**:

- Enable / disable automated reminders
- Days before due date
- Overdue intervals
- Channels (for example log, WhatsApp, mail — depending on installed drivers)
- Message templates (preview before saving)

Automated reminders typically run on a daily schedule (morning). Manual **Send Reminder** is available on the lease when you have permission.

### Behavior to know

- Reminders use **payable invoice outstanding** amounts (partial-aware).
- Only the **primary tenant** is reminded.
- Duplicate reminders for the same lease/period/type are suppressed.
- Email reminders require the tenant to have a linked user with an email address.
- In development, WhatsApp may log messages instead of sending them until a real driver is configured.

---

## 11. Maintenance

Go to **Maintenance** to manage tickets.

### Typical ticket flow

1. Create a ticket (property/unit, priority, description).
2. Assign staff.
3. Update status through reported → in progress → resolved (or cancel).
4. Optionally **block the unit** while work is underway.

### If the unit is occupied

When blocking for maintenance you can:

- Move the tenant to another available unit, **or**
- Keep the tenant in place and still mark the unit under maintenance

A maintenance move updates the lease’s unit and history. It does **not** end the legal lease.

### Restoring a unit

When resolving or cancelling, you can restore the unit to available — unless other open tickets still block it.

Blocked units are hidden from “available unit” pickers for new leases, assignments, and moves.

---

## 12. Expenses

### One-off expenses

**Expenses**:

- Create expenses with property, category, amount, currency, date, vendor, notes
- Attach a receipt
- Filter by property, category, currency, status, and date
- Delete voids an expense (it is not hard-deleted for audit purposes)

### Recurring expenses

**Expenses → Recurring**:

- Create recurring templates
- Pause / resume as needed

### Categories (owner)

Manage the shared category catalog under Expense Categories (sidebar or settings).

Currencies are never auto-converted. Enter amounts in the correct currency for each expense.

---

## 13. Inspections

**Inspections** support move-in, move-out, and periodic checks.

### How to run an inspection

1. Create an inspection from the property, unit, or lease context (or the global list).
2. Optionally start from a **template**.
3. Fill checklist conditions, notes, and photos while the inspection is a draft.
4. **Complete** the inspection to lock it.

### Templates

Manage reusable checklists under **Inspections → Templates** (requires template manage permission).

---

## 14. Documents

Upload and manage files in context:

- Property → Documents
- Tenant → Documents
- Lease → Documents

Use these for contracts, ID scans, handover forms, and similar records. Tenant document upload/delete typically requires tenant update permission.

---

## 15. Public listings and SEO

### Publish a property

On the property **Listing** tab:

1. Complete listing details and media gallery.
2. Review readiness issues (blocking vs recommendations).
3. Sync facilities/amenities from the global catalog.
4. **Publish** when ready (you can unpublish later).

Unit types can also have their own listing and publication state.

### Public site

Visitors can browse listings and apply. Public URLs typically include the listing index and individual property/unit-type pages.

### SEO & sharing (owner)

**Public Portal → SEO & Sharing**:

- Site title and description for search/sharing
- Open Graph image and related public branding copy

---

## 16. Users and custom roles

### Invite staff

1. Go to **Users**.
2. Invite by email and assign a role plus property access.
3. The invitee opens `/invitations/{token}` to complete setup.

You can also:

- Edit role and property access
- Reset password
- Resend invitation
- Disable a user

> The Users screen manages **staff** accounts. Tenant portal accounts are managed from the Tenants flow (invite / disable access).

### Create a custom role (owner)

1. Go to **Roles & Permissions**.
2. Create or clone a role.
3. Start from a recommended template if useful.
4. Toggle permission groups carefully.
5. Assign the role to users.

---

## 17. Settings

### Everyone

| Page | Use it for |
| --- | --- |
| Profile | Name and personal details |
| Security | Password (and related security options) |
| About | Product / license information |

### Owner-only

| Page | Use it for |
| --- | --- |
| **General** | Site name, locale, country, timezone, currencies, lease/invoice ID prefixes, invoice PDF toggle, logo/favicon, appearance |
| **Reminders** | Automated rent reminder rules and templates |
| **Mail** | Mail driver configuration and test send |
| **WhatsApp** | WhatsApp driver (via plugin), health check, test |
| **Payment Gateway** | Active gateway and encrypted credentials |
| **Plugins** | Install, enable, disable, update, or remove plugins |
| **Property Types** | Global property type catalog |
| **Amenities** | Global amenity catalog |
| **Expense Categories** | Shared expense categories |

Installed plugins may add extra settings pages under Settings.

---

## 18. Import and export

OpenKOS supports CSV data transfer for bulk setup and reporting.

### Where to find it

- Global **Data Transfer** screen (`/data-transfer`)
- Import / Export actions on relevant list pages (properties, units, tenants, expenses, and related datasets)

### Supported datasets (typical)

- Properties
- Units
- Tenants (sensitive export needs a special permission)
- Unit rates
- Property types (export)
- Expenses

### Safe import process

1. Download the CSV **template**.
2. Fill rows carefully (required columns, dates as `Y-m-d`, valid currencies).
3. Upload for **preview / validation**.
4. Commit only when validation passes.

Important rules:

- Imports validate **all rows first**, then commit atomically.
- Imports are generally **create-only** (they do not update existing records by design).
- Media and receipt files are **not** included in CSV transfers.
- Expense identity is property + reference (case-insensitive); duplicates are rejected.
- Voided expenses can be exported for reporting but not imported.

---

## 19. Tenant portal vs staff app

| Topic | Staff app | Tenant portal |
| --- | --- | --- |
| Who | Owner and staff with permissions | Applicants and tenants (linked user) |
| Main jobs | Manage portfolio, billing, tickets, users | View lease, pay invoices, submit tickets, track applications |
| Billing | Manage all invoices and verify payments | Pay own invoices; view history |
| Maintenance | Full ticket workflow + unit blocking | Create and view own tickets |
| Leases | Full lifecycle | Read-only |
| Settings | Organization-wide (owner) | Personal profile |

A person can be both staff and tenant (dual persona). In the portal, staff may see a **Staff Dashboard** link back to the admin app.

Guests can also pay via a signed invoice link (`/pay/invoices/...`) without logging into the portal.

---

## 20. Common workflows cheat sheet

### Onboard a new kos / property

1. Create property → choose rental mode  
2. Add amenities / unit types / units  
3. Set rates  
4. (Optional) Publish listing  
5. Create leases as tenants arrive  

### Check in a walk-in tenant

1. Create tenant  
2. Assign unit / create lease (deposit + billing strategy)  
3. Invite to portal  
4. Run move-in inspection  
5. Confirm first invoice / payment  

### Collect overdue rent

1. Open **Billing → Overdue**  
2. Contact tenant / send reminder  
3. Record payment or verify proof  
4. Confirm invoice moves to Partial or Paid  

### Handle a broken AC in an occupied room

1. Create maintenance ticket on the unit  
2. Assign technician  
3. Block unit if needed; move tenant or keep in place  
4. Resolve ticket and restore unit when clear  

### End of tenancy

1. Run move-out inspection  
2. Move out the lease and settle deposit  
3. Confirm future pending invoices are cancelled when appropriate  
4. Disable portal access if desired  
5. Mark unit available (unless still in maintenance)  

### Accept an online applicant

1. Review application  
2. Accept  
3. Convert to tenant  
4. Create / confirm lease assignment  
5. Invite to portal  

---

## 21. Troubleshooting tips

| Symptom | What to check |
| --- | --- |
| Menu item missing | Your role may lack permission; ask the owner |
| Cannot create a unit lease | Active whole-property lease may be blocking; or unit is maintenance-blocked / at capacity |
| Invoice still Pending after payment | Payment may be awaiting **verification**, or gateway webhook has not confirmed yet — try Recheck |
| Reminder not received | Reminders enabled? Primary tenant set? Channel configured? Tenant linked user email for mail? |
| Tenant cannot log in | Invitation completed? Portal access disabled? |
| Cannot archive property | Active leases still exist |
| Import failed | Fix validation errors from preview; remember imports are create-only |
| Online payment unclear | Trust webhook/recheck status, not the browser return page alone |

---

## Appendix A — Glossary

| Term | Meaning |
| --- | --- |
| **Property** | A building or kos location you manage |
| **Unit** | A rentable room/apartment inside a property |
| **Lease** | The occupancy + billing agreement for a unit or whole property |
| **Primary tenant** | Main contact for reminders and communications on a multi-tenant lease |
| **Invoice** | Bill for a specific period; source of truth for amounts due |
| **Confirmed payment** | Payment that counts toward invoice status |
| **Portal** | Tenant/applicant self-service area |
| **Owner** | Full-access system role created at install |

## Appendix B — Exporting this guide

This file is plain Markdown (`docs/admin-user-guide.md`). You can:

- Copy it into Notion, Confluence, GitBook, or Outline
- Convert to PDF/DOCX with Pandoc, for example:  
  `pandoc docs/admin-user-guide.md -o OpenKOS-Admin-User-Guide.pdf`
- Publish it on your internal wiki and keep this repo copy as the source of truth

---

*OpenKOS is open-source software licensed under Apache 2.0. This guide describes the admin product experience for MVP / early releases and may evolve as features change.*
