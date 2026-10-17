<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Sumber Rezeki</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('sumber-rezeki-mark.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        :root {
            --primary: #15803d;
            --primary-dark: #166534;
            --primary-light: #dcfce7;
            --bg-body: #f8fafc;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        aside.sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: #16a34a;
            color: #ffffff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 40px;
        }

        .brand-icon img {
            display: block;
            width: 100%;
            height: 100%;
        }

        .brand-text h2 {
            font-size: 16px;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-nav {
            padding: 20px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
        }

        .nav-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #475569;
            padding: 8px 12px 4px 12px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .nav-item i {
            font-size: 16px;
            width: 20px;
        }

        .nav-item:hover, .nav-item.active {
            color: #ffffff;
            background: rgba(22, 163, 74, 0.2);
            border-left: 3px solid #22c55e;
        }

        .owner-action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 16px;
            border: 0;
            border-radius: 8px;
            background: #15803d;
            color: #ffffff;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .owner-action-button:hover:not(:disabled) {
            background: #166534;
        }

        .owner-action-button:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .cash-page {
            margin-top: 22px;
        }

        .cash-section {
            margin-bottom: 22px;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .cash-section-heading {
            margin-bottom: 14px;
        }

        .cash-section-heading h2,
        .cash-section-heading h3 {
            margin: 0 0 5px;
            color: var(--text-dark);
        }

        .cash-section-heading h2 { font-size: 17px; }
        .cash-section-heading h3 { font-size: 16px; }

        .cash-section-heading p {
            margin: 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .cash-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .cash-table {
            margin: 0;
        }

        .cash-table td:last-child,
        .cash-table th:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .cash-table .cash-total-row td {
            border-top: 2px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            font-weight: 800;
        }

        .cash-actions-grid {
            display: grid;
            grid-template-columns: minmax(260px, 0.8fr) minmax(320px, 1.2fr);
            gap: 16px;
            margin-bottom: 22px;
        }

        .cash-form-card {
            min-width: 0;
            margin: 0;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .cash-form-card h3 {
            margin: 0 0 5px;
            color: var(--text-dark);
            font-size: 16px;
        }

        .cash-form-hint {
            margin: 0 0 16px;
            color: var(--text-muted);
            font-size: 12.5px;
            line-height: 1.5;
        }

        .owner-form-field {
            margin-bottom: 12px;
        }

        .owner-form-field label {
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
        }

        .owner-form-control {
            display: block;
            width: 100%;
            min-height: 42px;
            padding: 9px 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #fff;
            color: #1e293b;
            font: inherit;
            font-size: 13px;
        }

        .owner-form-control:focus {
            outline: 3px solid rgba(22, 163, 74, 0.14);
            border-color: #16a34a;
        }

        .sales-page {
            display: grid;
            gap: 18px;
            margin-top: 22px;
        }

        .sales-section {
            min-width: 0;
            margin: 0;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .sales-section-heading {
            margin-bottom: 16px;
        }

        .sales-section-heading h2,
        .sales-section-heading h3 {
            margin: 0 0 5px;
            color: var(--text-dark);
        }

        .sales-section-heading h2 { font-size: 17px; }
        .sales-section-heading h3 { font-size: 16px; }

        .sales-section-heading p {
            margin: 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .sales-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .sales-summary-card {
            padding: 16px;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            background: linear-gradient(135deg, #f0fdf4, #eff6ff);
        }

        .sales-summary-label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .sales-summary-value {
            margin: 5px 0 2px;
            color: #166534;
            font-size: 21px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .sales-summary-detail {
            color: #475569;
            font-size: 12px;
        }

        .sales-subsection {
            margin-top: 18px;
        }

        .sales-subsection h3 {
            margin: 0 0 12px;
            color: var(--text-dark);
            font-size: 15px;
        }

        .sales-table-wrap {
            max-width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .sales-table {
            min-width: 680px;
            margin: 0;
        }

        .sales-warning {
            margin: 0 0 14px;
            padding: 11px 13px;
            border: 1px solid #fde68a;
            border-radius: 8px;
            background: #fffbeb;
            color: #92400e;
            font-size: 13px;
            line-height: 1.5;
        }

        .sales-period-form {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 12px;
            margin-bottom: 14px;
            padding: 14px;
            border-radius: 8px;
            background: #f8fafc;
        }

        .sales-period-form .owner-form-field {
            flex: 1 1 180px;
            margin: 0;
        }

        .sales-form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .sales-form-grid .owner-form-field {
            min-width: 0;
            margin: 0;
        }

        .sales-form-grid .sales-form-wide {
            grid-column: 1 / -1;
        }

        .sales-form-grid textarea.owner-form-control {
            min-height: 78px;
            resize: vertical;
        }

        .sales-form-actions {
            margin-top: 16px;
        }

        .sales-payment-form {
            max-width: none;
        }

        .sales-payment-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .sales-payment-grid .owner-form-field {
            min-width: 0;
            margin: 0;
        }

        .reports-page {
            display: grid;
            gap: 18px;
            margin-top: 22px;
        }

        .reports-section {
            min-width: 0;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .reports-section-heading {
            margin-bottom: 14px;
        }

        .reports-section-heading h2 {
            margin: 0 0 5px;
            color: var(--text-dark);
            font-size: 17px;
        }

        .reports-section-heading p {
            margin: 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .report-period-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(150px, 1fr)) auto;
            align-items: end;
            gap: 12px;
        }

        .report-period-form .owner-form-field {
            margin: 0;
        }

        .report-export-actions {
            grid-column: 1 / -1;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .report-export-actions .owner-action-button {
            gap: 8px;
            text-decoration: none;
        }

        .report-pdf-button {
            background: #b91c1c;
        }

        .report-pdf-button:hover:not(:disabled) {
            background: #991b1b;
        }

        .report-sheet-button {
            background: #15803d;
        }

        .reports-table-wrap {
            max-width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .reports-table {
            margin: 0;
        }

        .reports-table td:last-child {
            width: 35%;
            text-align: right;
            white-space: nowrap;
        }

        .reports-table tr.reports-total-row td {
            border-top: 2px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            font-weight: 800;
        }

        .reports-table tr.reports-profit-row td {
            border-top: 2px solid #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
        }

        .reports-detail-table {
            min-width: 900px;
            font-size: 12px;
        }

        .reports-detail-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            white-space: nowrap;
        }

        .reports-detail-table td {
            max-width: 260px;
        }

        .reports-detail-table td:nth-child(1),
        .reports-detail-table td:nth-child(2),
        .reports-detail-table td:nth-child(6) {
            white-space: nowrap;
        }

        .report-amount {
            color: #166534;
            font-weight: 700;
            text-align: right;
        }

        .reports-note {
            margin: 12px 0 0;
            padding: 11px 13px;
            border-left: 3px solid #94a3b8;
            border-radius: 4px;
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.6;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .user-profile-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #22c55e;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .user-info {
            flex: 1;
            overflow: hidden;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role-badge {
            display: inline-block;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-admin {
            background: #0284c7;
            color: #ffffff;
        }

        .badge-owner {
            background: #b45309;
            color: #ffffff;
        }

        /* Main Content Container */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header */
        header.topbar {
            height: 70px;
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header-title h1 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #fecaca;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: #dc2626;
            color: #ffffff;
        }

        /* Page Content */
        .content-area {
            padding: 32px;
            flex: 1;
        }

        .alert-bar {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* KPI Cards Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 22px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-info span {
            font-size: 12.5px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info h3 {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-dark);
            margin-top: 6px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .icon-green { background: #dcfce7; color: #16a34a; }
        .icon-blue { background: #e0f2fe; color: #0284c7; }
        .icon-amber { background: #fef3c7; color: #d97706; }
        .icon-purple { background: #f3e8ff; color: #9333ea; }

        /* General Card */
        .card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .card-header {
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* Table */
        .custom-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .custom-table th {
            text-align: left;
            background: #f8fafc;
            padding: 12px 14px;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid var(--border);
        }

        .custom-table td {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }

        .custom-table tr:hover td {
            background: #f8fafc;
        }

        .transfer-history {
            margin: 0;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
        }

        .transfer-history h3 {
            margin: 0 0 14px;
            color: var(--text-dark);
            font-size: 16px;
        }

        .mobile-menu-toggle,
        .sidebar-close-toggle,
        .sidebar-backdrop {
            display: none;
        }

        @media (max-width: 900px) {
            body {
                min-width: 320px;
            }

            aside.sidebar {
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                box-shadow: 12px 0 30px rgba(15, 23, 42, 0.2);
            }

            aside.sidebar.is-open {
                transform: translateX(0);
            }

            .sidebar-close-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                margin-left: auto;
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 8px;
                background: transparent;
                color: #f8fafc;
                font-size: 16px;
                cursor: pointer;
            }

            .sidebar-backdrop.is-visible {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 99;
                background: rgba(15, 23, 42, 0.55);
            }

            .main-wrapper {
                width: 100%;
                min-width: 0;
                margin-left: 0;
            }

            header.topbar {
                min-height: 64px;
                height: auto;
                gap: 12px;
                padding: 10px 18px;
            }

            .mobile-menu-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 40px;
                width: 40px;
                height: 40px;
                border: 1px solid var(--border);
                border-radius: 8px;
                background: #fff;
                color: #334155;
                font-size: 17px;
                cursor: pointer;
            }

            .header-title {
                flex: 1;
                min-width: 0;
            }

            .header-title h1 {
                font-size: 17px;
                overflow-wrap: anywhere;
            }

            .header-actions {
                gap: 8px;
            }

            .content-area {
                min-width: 0;
                padding: 22px;
            }
        }

        @media (max-width: 600px) {
            header.topbar {
                padding: 9px 12px;
            }

            .header-title h1 {
                font-size: 15px;
            }

            .header-actions {
                flex: 0 0 auto;
            }

            .btn-logout {
                width: 40px;
                height: 40px;
                justify-content: center;
                padding: 0;
            }

            .btn-logout span {
                display: none;
            }

            .content-area {
                padding: 16px 12px;
            }

            .stats-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
                margin-bottom: 18px;
            }

            .stat-card {
                min-width: 0;
                padding: 16px;
            }

            .stat-info {
                min-width: 0;
            }

            .stat-info h3 {
                font-size: 19px;
                overflow-wrap: anywhere;
            }

            .card {
                min-width: 0;
                padding: 16px;
                margin-bottom: 16px;
            }

            .card-header {
                align-items: flex-start;
                gap: 10px;
            }

            .custom-table {
                min-width: max-content;
            }

            .cash-section,
            .transfer-history {
                padding: 14px;
                margin-bottom: 14px;
            }

            .cash-actions-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            .cash-form-card {
                padding: 16px;
            }

            .cash-section-heading h2,
            .cash-section-heading h3 {
                font-size: 15px;
            }

            .sales-page {
                gap: 12px;
            }

            .sales-section {
                padding: 14px;
            }

            .sales-summary-grid,
            .sales-form-grid,
            .sales-payment-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .sales-summary-value {
                font-size: 19px;
            }

            .reports-page {
                gap: 12px;
            }

            .reports-section {
                padding: 14px;
            }

            .reports-section-heading h2 {
                font-size: 15px;
            }

            .report-period-form {
                grid-template-columns: minmax(0, 1fr);
            }

            .report-period-form > .owner-action-button,
            .report-export-actions .owner-action-button {
                width: 100%;
            }

            .report-export-actions {
                grid-column: auto;
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .reports-table td:last-child {
                width: auto;
            }

            .sales-form-grid .sales-form-wide {
                grid-column: auto;
            }

            .sales-period-form .owner-action-button,
            .sales-form-actions .owner-action-button,
            .sales-payment-form > .owner-action-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="ownerSidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <img src="{{ asset('sumber-rezeki-mark.svg') }}" alt="">
            </div>
            <div class="brand-text">
                <h2>Sumber Rezeki</h2>
            </div>
            <button type="button" class="sidebar-close-toggle" id="ownerSidebarCloseToggle"
                aria-label="Tutup menu navigasi">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            @yield('sidebar-menu')
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile-badge">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">
                        {{ auth()->user()->role === 'owner' ? 'Pemilik Usaha' : auth()->user()->name }}
                    </div>
                    <span class="user-role-badge {{ auth()->user()->role === 'owner' ? 'badge-owner' : 'badge-admin' }}">
                        {{ auth()->user()->role }}
                    </span>
                </div>
            </div>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="ownerSidebarBackdrop"></div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <header class="topbar">
            <button type="button" class="mobile-menu-toggle" id="ownerMobileMenuToggle"
                aria-label="Buka menu navigasi" aria-controls="ownerSidebar" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="header-title">
                <h1>@yield('page-title', 'Dashboard')</h1>
            </div>
            <div class="header-actions">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="content-area">
            @if(session('success'))
                <div class="alert-bar alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-bar alert-error">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function formatMoneyInteger(input) {
            const original = input.value;
            const caret = input.selectionStart ?? original.length;
            const digitsBeforeCaret = (original.slice(0, caret).match(/\d/g) || []).length;
            const digits = original.replace(/\D/g, '');
            const formatted = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            input.value = formatted;

            let nextCaret = 0;
            let digitsSeen = 0;
            while (nextCaret < formatted.length && digitsSeen < digitsBeforeCaret) {
                if (/\d/.test(formatted[nextCaret])) digitsSeen++;
                nextCaret++;
            }
            input.setSelectionRange(nextCaret, nextCaret);

            const amount = digits ? Number(digits) : 0;
            let message = '';
            if (input.dataset.moneyMin !== undefined && amount < Number(input.dataset.moneyMin)) {
                message = 'Nilai terlalu kecil.';
            } else if (input.dataset.moneyMax !== undefined && amount > Number(input.dataset.moneyMax)) {
                message = 'Nilai terlalu besar.';
            }
            input.setCustomValidity(message);
        }

        document.querySelectorAll('[data-money-integer]').forEach(input => {
            formatMoneyInteger(input);
            input.addEventListener('input', () => formatMoneyInteger(input));
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                form.querySelectorAll('[data-money-integer]').forEach(input => {
                    input.value = input.value.replace(/\./g, '');
                });
            });
        });

        const ownerSidebar = document.getElementById('ownerSidebar');
        const ownerSidebarBackdrop = document.getElementById('ownerSidebarBackdrop');
        const ownerMobileMenuToggle = document.getElementById('ownerMobileMenuToggle');
        const ownerSidebarCloseToggle = document.getElementById('ownerSidebarCloseToggle');

        function setOwnerMobileMenuOpen(isOpen) {
            ownerSidebar.classList.toggle('is-open', isOpen);
            ownerSidebarBackdrop.classList.toggle('is-visible', isOpen);
            ownerMobileMenuToggle.setAttribute('aria-expanded', String(isOpen));
            ownerMobileMenuToggle.setAttribute('aria-label', isOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi');
            document.body.style.overflow = isOpen ? 'hidden' : '';
        }

        ownerMobileMenuToggle.addEventListener('click', function() {
            setOwnerMobileMenuOpen(!ownerSidebar.classList.contains('is-open'));
        });
        ownerSidebarCloseToggle.addEventListener('click', () => setOwnerMobileMenuOpen(false));
        ownerSidebarBackdrop.addEventListener('click', () => setOwnerMobileMenuOpen(false));
        ownerSidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => setOwnerMobileMenuOpen(false));
        });
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') setOwnerMobileMenuOpen(false);
        });
    </script>
</body>
</html>
