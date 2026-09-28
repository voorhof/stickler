---
sessionId: session-260928-135912-1vm2
---

# Requirements

### Overview & Goals
Normalize translation directory and file names within the `lang/` folder by migrating from regional locale tags (`en_US`, `nl_BE`) to primary two-letter language codes (`en`, `nl`).

### Scope
#### In Scope
- Rename root JSON translation files (`en_US.json` -> `en.json`, `nl_BE.json` -> `nl.json`).
- Rename root translation folders (`lang/en_US` -> `lang/en`, `lang/nl_BE` -> `lang/nl`).
- Rename all published vendor translation folders (`lang/vendor/*/en_US` -> `lang/vendor/*/en`, `lang/vendor/*/nl_BE` -> `lang/vendor/*/nl`).

#### Out of Scope
- Modifying configurations outside `lang/` (e.g., `config/app.php`).
- Changing string keys or translation contents inside the PHP or JSON files.

### Functional Requirements
- Any file or directory currently named `en_US` inside `lang/` or any of its subdirectories must be renamed to `en`.
- Any file or directory currently named `nl_BE` inside `lang/` or any of its subdirectories must be renamed to `nl`.
- All sub-files and nested directory structures inside the renamed directories must be preserved intact.

# Technical Design

### Current Implementation
The `lang/` directory contains:
- Root locale JSON files: `lang/en_US.json` and `lang/nl_BE.json`.
- Root locale folders: `lang/en_US/` and `lang/nl_BE/`.
- 15 published vendor package translation folders under `lang/vendor/*/` containing `en_US/` and `nl_BE/` subdirectories:
  - `backup`
  - `cookie-consent`
  - `filament`
  - `filament-actions`
  - `filament-forms`
  - `filament-infolists`
  - `filament-notifications`
  - `filament-panels`
  - `filament-query-builder`
  - `filament-schemas`
  - `filament-spatie-backup`
  - `filament-spatie-laravel-settings-plugin`
  - `filament-tables`
  - `filament-widgets`
  - `health`

### Proposed Changes
Perform directory and file rename operations strictly within `lang/`:

1. **Top-Level Files**:
   - `lang/en_US.json` -> `lang/en.json`
   - `lang/nl_BE.json` -> `lang/nl.json`

2. **Top-Level Folders**:
   - `lang/en_US/` -> `lang/en/`
   - `lang/nl_BE/` -> `lang/nl/`

3. **Vendor Folders**:
   - For each package in `lang/vendor/<package>/`:
     - Rename `en_US` to `en`
     - Rename `nl_BE` to `nl`

### File Structure Summary
```
lang/
├── en.json (renamed from en_US.json)
├── nl.json (renamed from nl_BE.json)
├── en/ (renamed from en_US/)
│   ├── auth.php
│   ├── backups.php
│   ├── mail/
│   ├── pagination.php
│   ├── passwords.php
│   └── validation.php
├── nl/ (renamed from nl_BE/)
│   ├── auth.php
│   ├── backups.php
│   ├── mail/
│   ├── pagination.php
│   ├── passwords.php
│   └── validation.php
└── vendor/
    └── <package>/
        ├── en/ (renamed from en_US/)
        └── nl/ (renamed from nl_BE/)
```

# Delivery Steps

### ✓ Step 1: Rename root language files and directories
Rename the top-level locale files and directory trees under `lang/`:

- Rename `lang/en_US.json` to `lang/en.json`.
- Rename `lang/nl_BE.json` to `lang/nl.json`.
- Rename the root locale directory `lang/en_US/` to `lang/en/`.
- Rename the root locale directory `lang/nl_BE/` to `lang/nl/`.

### ✓ Step 2: Rename vendor language directories
Rename all vendor package locale folders located under `lang/vendor/*`:

- Rename all `en_US` directories to `en` under:
  - `lang/vendor/backup/`
  - `lang/vendor/cookie-consent/`
  - `lang/vendor/filament/`
  - `lang/vendor/filament-actions/`
  - `lang/vendor/filament-forms/`
  - `lang/vendor/filament-infolists/`
  - `lang/vendor/filament-notifications/`
  - `lang/vendor/filament-panels/`
  - `lang/vendor/filament-query-builder/`
  - `lang/vendor/filament-schemas/`
  - `lang/vendor/filament-spatie-backup/`
  - `lang/vendor/filament-spatie-laravel-settings-plugin/`
  - `lang/vendor/filament-tables/`
  - `lang/vendor/filament-widgets/`
  - `lang/vendor/health/`
- Rename all `nl_BE` directories to `nl` under each of the same vendor package subdirectories in `lang/vendor/`.