# Iseki Podium - Central Production Planning & Dashboard System

## Overview

**Iseki Podium** serves as the central orchestration and monitoring hub for the entire Iseki production ecosystem. It is responsible for managing production plans, defining assembly sequence rules, and consolidating real-time data from various production stations (such as Oiler, Parcom, and Area-specific scanners).

As the "brain" of the operation, Podium ensures that every unit on the production line follows the correct route and that status updates are synchronized across all connected systems.

## Key Features

### 1. Central Production Planning
*   **Plan Management**: Create, update, and manage daily production plans with sequence-level detail.
*   **Bulk Import**: Import production plans and rule sets directly from Excel.
*   **Sequencing Rules**: Define "Rules" that specify exactly which stations (e.g., Mainline, Oiler, Daiichi) a specific model must pass through and in what order.

### 2. Operational Monitoring (Lineoff & Areas)
*   **Real-time Scan Tracking**: Consolidates scanning data from multiple areas:
    *   **Mainline**: Primary assembly tracking.
    *   **Daiichi**: Specialized station monitoring.
    *   **Area Reports**: Dedicated reporting for specific production zones.
*   **Lineoff Status**: Track units as they complete the final stage of production.

### 3. Advanced Reporting & Audit
*   **Process Gap Analysis**: Identify "Missing" processes where a unit has skipped a mandatory station based on defined rules.
*   **Consolidated Dashboards**: High-level views powered by **Yajra DataTables** for efficient navigation through thousands of production records.
*   **Multi-format Export**: Export filtered reports and missing-process audits to Excel for external analysis.

### 4. System Administration
*   **Station Integration**: Provides the central database and API logic consumed by specialized stations (Oiler, Parcom, etc.).
*   **User Management**: Secure role-based access for system administrators and area operators.

## Technology Stack

### Backend
*   **Framework**: [Laravel 12.x](https://laravel.com)
*   **Language**: PHP ^8.2
*   **Database**: MySQL / MariaDB (Central Hub) & SQLite (Local testing)
*   **Enterprise Integration**: 
    *   `phpoffice/phpspreadsheet` (Excel processing)
    *   `yajra/laravel-datatables-oracle` (Advanced data grids)

### Frontend
*   **Build Tool**: [Vite](https://vitejs.dev)
*   **Styling**: [Tailwind CSS v4.0](https://tailwindcss.com)
*   **HTTP Client**: Axios

## Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone <repository-url>
    cd iseki_podium
    ```

2.  **Dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Environment**
    *   Copy `.env.example` to `.env`.
    *   Configure the central database connection (this database will be shared/referenced by other Iseki station apps).

4.  **Database & Key**
    ```bash
    php artisan key:generate
    php artisan migrate
    ```

5.  **Build**
    ```bash
    npm run build
    ```

6.  **Serve**
    ```bash
    php artisan serve
    ```

## Ecosystem Role

Iseki Podium is the central server. Other stations in the ecosystem (like `iseki_oiler`) must be configured in their respective `.env` files to point to this project's database to ensure data integrity and real-time synchronization.

## License

This project is proprietary.
