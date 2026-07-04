# School Management System Developer Guide

This project uses a shared layout architecture so Student, Teacher, Accountant, and Admin pages do not repeat wrapper, sidebar, asset, and footer code.

## Folder Structure

```text
includes/
  assets.php                  Central CSS/JS asset loader
  config/
    config.php                Application, school, theme, and DB settings
    database.php              Shared PDO connection factory
  helpers/
    functions.php             Shared escaping, config, asset, money helpers
    auth.php                  Shared authentication/session hook
    permissions.php           Future role/permission helper
    validation.php            Shared validation helpers
  layouts/
    header.php                Shared dashboard HTML head, navbar, sidebar wrapper
    footer.php                Shared dashboard scripts and sidebar behavior
    navbar.php                Shared mobile navbar
    sidebar.php               Dynamic sidebar renderer
    breadcrumbs.php           Reusable breadcrumb renderer
    page-header.php           Reusable page heading block
  components/
    dashboard-card.php        Reusable KPI/dashboard card
    statistics-card.php       Alias for dashboard-card
    table.php                 Responsive table component
    search-form.php           Search/filter form shell
    pagination.php            Pagination control
    alert.php                 Alert component
    modal.php                 Bootstrap modal component
    profile-card.php          Compact profile summary component

student/includes/
  layout.php                  Student layout entry point
  sidebar.php                 Student navigation and profile summary
  navbar.php                  Student navbar extension placeholder
  header.php                  Adapter to shared layout
  footer.php                  Adapter to shared footer

teacher/includes/
  layout.php                  Teacher layout entry point
  sidebar.php                 Teacher navigation
  navbar.php                  Teacher navbar extension placeholder
  header.php                  Adapter to shared layout
  footer.php                  Adapter to shared footer

accountant/includes/
  layout.php                  Accountant layout entry point
  sidebar.php                 Accountant navigation
  navbar.php                  Accountant navbar extension placeholder
  header.php                  Adapter to shared layout
  footer.php                  Adapter to shared footer

admin/includes/
  layout.php                  Admin layout entry point
  sidebar.php                 Admin navigation placeholder
  navbar.php                  Admin navbar extension placeholder
  header.php                  Adapter to shared layout
  footer.php                  Adapter to shared footer
```

## Creating a New Page

Create the page inside the correct module folder and keep the existing pattern:

```php
<?php require_once('includes/header.php'); ?>

<div class="your-page-class">
    <!-- Page content -->
</div>

</div>
</div>

<?php require_once('includes/footer.php'); ?>
```

The role-level `includes/header.php` loads the module `includes/sidebar.php`, then delegates to the global shared dashboard layout.

## Adding Sidebar Items

Edit the module sidebar file:

- `student/includes/sidebar.php`
- `teacher/includes/sidebar.php`
- `accountant/includes/sidebar.php`
- `admin/includes/sidebar.php`

Add an item to `$smsNavItems`:

```php
[
    'label' => 'Library',
    'href' => 'library.php',
    'icon' => 'fa-solid fa-book',
    'pages' => ['library.php', 'library-details.php'],
]
```

The shared sidebar automatically highlights active links using the current page filename.

## Using Shared Components

Load a component from any PHP page with:

```php
<?php sms_render_component('dashboard-card', [
    'title' => 'Total Students',
    'value' => '650',
    'icon' => 'fa-users',
    'color' => 'success',
    'link' => 'students.php',
]); ?>
```

Available components:

- `dashboard-card`
- `statistics-card`
- `table`
- `search-form`
- `pagination`
- `alert`
- `modal`
- `profile-card`

## Page Headers and Breadcrumbs

Use the shared page header when building new modules:

```php
<?php
$title = 'Attendance';
$description = 'Mark and review class attendance.';
$kicker = 'Teacher Module';
$icon = 'fa-calendar-check';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'dashboard.php'],
    ['label' => 'Attendance'],
];
require '../includes/layouts/page-header.php';
?>
```

## Assets

Do not add Bootstrap, Font Awesome, sidebar CSS, or common scripts directly in new pages. They are loaded by `includes/assets.php` through the shared layout. Only add page-specific CSS/JS when the page genuinely needs it.

## Database and Auth

- Use `sms_db()` from `includes/config/database.php` when the schema is ready.
- Use `sms_require_auth($role)` from `includes/helpers/auth.php` for access checks.
- Keep SQL out of views where possible. Future controllers should prepare data before rendering pages.

## Best Practices

- Keep shared UI in `includes/components`.
- Keep role navigation in each module's `includes/sidebar.php`.
- Keep page-specific placeholder data at the top of the page until controllers replace it.
- Use `sms_e()` for output escaping.
- Use `sms_money()` for currency formatting.
- Avoid duplicating CSS/JS includes.
- Prefer small reusable components over repeating card, alert, modal, table, pagination, and profile markup.

## Current Compatibility Note

Existing pages still close the content/container wrappers before including the footer. The shared footer is intentionally compatible with that convention. When creating a future fully componentized view renderer, this can be tightened into a single layout render function.
