<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - Sumber Rezeki')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('sumber-rezeki-mark.svg') }}">
    
    <!-- Google Fonts Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR (Clean White Theme like screenshot) */
        aside.sidebar {
            width: 220px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar-brand {
            height: 64px;
            padding: 0 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 36px;
        }

        .brand-icon img {
            display: block;
            width: 100%;
            height: 100%;
        }

        .brand-name {
            font-size: 16px;
            font-weight: 700;
            color: #15803d;
            letter-spacing: -0.3px;
        }

        .sidebar-menu {
            padding: 12px 0;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 18px;
            color: #475569;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
        }

        .menu-item i {
            font-size: 15px;
            width: 18px;
            text-align: center;
            color: #64748b;
        }

        .menu-item:hover {
            color: #0f172a;
            background-color: #f8fafc;
        }

        /* Active Green Item */
        .menu-item.active {
            color: #15803d;
            background-color: #f0fdf4;
            font-weight: 600;
            border-left: 4px solid #16a34a;
        }

        .menu-item.active i {
            color: #16a34a;
        }

        /* MAIN WRAPPER */
        .main-wrapper {
            margin-left: 220px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* TOPBAR / HEADER */
        header.topbar {
            height: 64px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-profile-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            color: #334155;
            padding: 6px 10px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .user-profile-btn:hover {
            background: #f1f5f9;
        }

        .user-avatar-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        .dropdown-menu-content {
            display: none;
            position: absolute;
            right: 0;
            top: 110%;
            background: #ffffff;
            min-width: 170px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 0;
            z-index: 1000;
        }

        .dropdown-menu-content.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            color: #475569;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            width: 100%;
            border: none;
            background: none;
            text-align: left;
        }

        .dropdown-item:hover {
            background-color: #fee2e2;
            color: #dc2626;
        }

        /* CONTENT AREA */
        main.content-area {
            padding: 24px 28px;
            flex: 1;
        }

        .page-heading {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
        }

        /* 4 KPI CARDS IN TOP ROW + 1 TOTAL PIUTANG */
        .kpi-row-top {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }

        .kpi-row-bottom {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .kpi-card {
            border-radius: 8px;
            padding: 16px 20px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .kpi-card .kpi-label {
            font-size: 13px;
            font-weight: 500;
            opacity: 0.95;
            margin-bottom: 6px;
        }

        .kpi-card .kpi-value {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .kpi-card .kpi-desc {
            font-size: 11.5px;
            opacity: 0.85;
            font-weight: 400;
        }

        .kpi-card .kpi-icon {
            font-size: 32px;
            opacity: 0.28;
            margin-top: 4px;
        }

        /* Exact card color matches */
        .kpi-blue {
            background-color: #1d72f2;
        }

        .kpi-green {
            background-color: #168a4a;
        }

        .kpi-yellow {
            background-color: #f59e0b;
        }

        .kpi-cyan {
            background-color: #06b6d4;
        }

        .kpi-red {
            background-color: #ef4444;
            max-width: 280px;
        }

        /* LOWER SECTION: CHART & RINGKASAN STOK */
        .charts-section {
            display: grid;
            grid-template-columns: 2.1fr 1fr;
            gap: 20px;
        }

        .panel-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px 22px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }

        .panel-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
        }

        /* Stock Summary List */
        .stock-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .stock-item:last-child {
            border-bottom: none;
        }

        .stock-name-val .name {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .stock-name-val .val {
            font-size: 12px;
            color: #64748b;
        }

        .stock-badge {
            background-color: #1d72f2;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 14px;
            letter-spacing: 0.2px;
        }

        .stock-total-footer {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stock-total-footer .total-label {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
        }

        .stock-total-footer .total-val {
            font-size: 14px;
            font-weight: 800;
            color: #168a4a;
        }

        /* Responsive */
        @media (max-width: 1100px) {
            .kpi-row-top {
                grid-template-columns: repeat(2, 1fr);
            }
            .charts-section {
                grid-template-columns: 1fr;
            }
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
                box-shadow: 12px 0 30px rgba(15, 23, 42, 0.12);
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
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #fff;
                color: #334155;
                font-size: 16px;
                cursor: pointer;
            }

            .sidebar-backdrop.is-visible {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 99;
                background: rgba(15, 23, 42, 0.45);
            }

            .main-wrapper {
                width: 100%;
                min-width: 0;
                margin-left: 0;
            }

            header.topbar {
                justify-content: space-between;
                padding: 0 20px;
            }

            .mobile-menu-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #fff;
                color: #334155;
                font-size: 17px;
                cursor: pointer;
            }

            main.content-area {
                min-width: 0;
                padding: 20px;
            }
        }

        @media (max-width: 600px) {
            header.topbar {
                height: 58px;
                padding: 0 12px;
            }

            main.content-area {
                padding: 16px 12px;
            }

            .page-heading {
                font-size: 18px;
                margin-bottom: 14px;
            }

            .kpi-row-top,
            .kpi-row-bottom {
                grid-template-columns: 1fr;
                gap: 10px;
                margin-bottom: 12px;
            }

            .kpi-card {
                padding: 14px 16px;
            }

            .kpi-card .kpi-value {
                font-size: 19px;
                overflow-wrap: anywhere;
            }

            .kpi-red {
                max-width: none;
            }

            .charts-section {
                gap: 12px;
            }

            .panel-card {
                min-width: 0;
                padding: 14px;
            }

            .page-header-row,
            .cash-inflow-heading,
            .receivable-heading-row,
            .opname-heading-row,
            .container-fluid > .d-flex.justify-content-between {
                flex-wrap: wrap;
                align-items: stretch;
                gap: 10px;
            }

            .page-header-row .btn-add-purchase,
            .cash-inflow-heading .cash-inflow-btn,
            .receivable-heading-row .receivable-btn,
            .opname-heading-row .opname-btn,
            .container-fluid > .d-flex.justify-content-between > .btn {
                width: 100%;
            }

            .form-grid-2,
            .form-grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .modal-overlay,
            .modal,
            .cash-inflow-modal,
            .receivable-modal,
            .opname-modal {
                align-items: flex-start;
                padding: 12px;
            }

            .modal-dialog,
            .cash-inflow-dialog,
            .receivable-modal-dialog,
            .opname-modal-dialog {
                width: 100%;
                max-height: calc(100dvh - 24px);
                overflow-y: auto;
            }

            .modal-body {
                padding: 16px 14px;
            }

            .modal-footer,
            .cash-inflow-modal-footer,
            .receivable-modal-footer,
            .opname-modal-footer {
                flex-wrap: wrap;
            }

            .table-container,
            .table-responsive,
            .cash-inflow-table-wrap,
            .cash-outflow-table-wrap,
            .receivable-list-wrap,
            .receivable-history-wrap,
            .opname-table-wrap,
            .stock-table-wrap,
            .report-table-wrap {
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <img src="{{ asset('sumber-rezeki-mark.svg') }}" alt="">
            </div>
            <div class="brand-name">Sumber Rezeki</div>
            <button type="button" class="sidebar-close-toggle" id="sidebarCloseToggle"
                aria-label="Tutup menu navigasi">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="sidebar-menu">
            <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.purchases.index') }}" class="menu-item {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}">
                <i class="fa-solid fa-scale-balanced"></i>
                <span>Pembelian (Petani)</span>
            </a>
            <a href="{{ route('admin.shipments.index') }}" class="menu-item {{ request()->routeIs('admin.shipments.*') ? 'active' : '' }}">
                <i class="fa-solid fa-truck"></i>
                <span>Pengiriman (PKS)</span>
            </a>
            <a href="{{ route('admin.stocks.index') }}" class="menu-item {{ request()->routeIs('admin.stocks.*') ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Informasi Stok</span>
            </a>
            <a href="{{ route('admin.stock-opnames.index') }}" class="menu-item {{ request()->routeIs('admin.stock-opnames.*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>Stok Opname</span>
            </a>
            <a href="{{ route('admin.receivables.index') }}" class="menu-item {{ request()->routeIs('admin.receivables.*') ? 'active' : '' }}">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <span>Piutang Petani</span>
            </a>
            <a href="{{ route('admin.cash-inflows.index') }}" class="menu-item {{ request()->routeIs('admin.cash-inflows.*') ? 'active' : '' }}">
                <i class="fa-solid fa-wallet"></i>
                <span>Kas Masuk</span>
            </a>
            <a href="{{ route('admin.cash-outflows.index') }}" class="menu-item {{ request()->routeIs('admin.cash-outflows.*') ? 'active' : '' }}">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>Kas Keluar</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-lines"></i>
                <span>Laporan</span>
            </a>
        </nav>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <!-- TOPBAR -->
        <header class="topbar">
            <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle"
                aria-label="Buka menu navigasi" aria-controls="adminSidebar" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="user-dropdown">
                <button type="button" class="user-profile-btn" id="userDropdownBtn">
                    <div class="user-avatar-circle">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <span>{{ auth()->user()->name ?? 'Admin Keuangan' }}</span>
                    <i class="fa-solid fa-caret-down" style="font-size: 11px; color: #64748b;"></i>
                </button>

                <div class="dropdown-menu-content" id="dropdownMenu">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span>Keluar (Logout)</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <main class="content-area">
            @yield('content')
        </main>
    </div>

    <script>
        // Dropdown logout toggle
        const userDropdownBtn = document.getElementById('userDropdownBtn');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const sidebarCloseToggle = document.getElementById('sidebarCloseToggle');

        function setMobileMenuOpen(isOpen) {
            adminSidebar.classList.toggle('is-open', isOpen);
            sidebarBackdrop.classList.toggle('is-visible', isOpen);
            mobileMenuToggle.setAttribute('aria-expanded', String(isOpen));
            mobileMenuToggle.setAttribute('aria-label', isOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi');
            document.body.style.overflow = isOpen ? 'hidden' : '';
        }

        mobileMenuToggle.addEventListener('click', function() {
            setMobileMenuOpen(!adminSidebar.classList.contains('is-open'));
        });
        sidebarCloseToggle.addEventListener('click', () => setMobileMenuOpen(false));
        sidebarBackdrop.addEventListener('click', () => setMobileMenuOpen(false));
        adminSidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => setMobileMenuOpen(false));
        });
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') setMobileMenuOpen(false);
        });

        userDropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        window.addEventListener('click', function() {
            if (dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
            }
        });

        window.parseFormattedNumber = function(value) {
            let normalized = String(value ?? '').trim().replace(/[^\d,.-]/g, '');
            const commaIndex = normalized.indexOf(',');

            if (commaIndex !== -1) {
                normalized = normalized.replace(/\./g, '');
                const parts = normalized.split(',');
                normalized = parts.shift() + (parts.length ? '.' + parts.join('') : '');
            } else if ((normalized.match(/\./g) || []).length === 1) {
                const decimalLength = normalized.length - normalized.lastIndexOf('.') - 1;
                if (decimalLength === 0 || decimalLength === 3) {
                    normalized = normalized.replace(/\./g, '');
                }
            } else {
                normalized = normalized.replace(/\./g, '');
            }

            const parsed = Number(normalized);
            return Number.isFinite(parsed) ? parsed : 0;
        };

        window.formatNumberInput = function(input) {
            const original = input.value;
            const caret = input.selectionStart ?? original.length;
            const digitsBeforeCaret = (original.slice(0, caret).match(/\d/g) || []).length;
            let decimalMarker = original.indexOf(',');

            if (decimalMarker === -1) {
                const dotCount = (original.match(/\./g) || []).length;
                const lastDot = original.lastIndexOf('.');
                const trailingDigits = original.length - lastDot - 1;
                const maxDecimals = input.dataset.numberDecimals === undefined
                    ? null
                    : Number(input.dataset.numberDecimals);
                if (dotCount === 1 && trailingDigits !== 3
                    && (maxDecimals === null || trailingDigits <= maxDecimals)) {
                    decimalMarker = lastDot;
                }
            }
            const decimalDigitsBeforeCaret = decimalMarker !== -1 && caret > decimalMarker
                ? (original.slice(decimalMarker + 1, caret).match(/\d/g) || []).length
                : null;

            if (input.dataset.numberInteger !== undefined) {
                const digits = original.replace(/\D/g, '');
                input.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                let nextCaret = 0;
                let digitsSeen = 0;
                while (nextCaret < input.value.length && digitsSeen < digitsBeforeCaret) {
                    if (/\d/.test(input.value[nextCaret])) digitsSeen++;
                    nextCaret++;
                }
                input.setSelectionRange(nextCaret, nextCaret);
                return;
            }

            let value = original.replace(/[^\d,.-]/g, '');
            const negative = value.startsWith('-');
            value = value.replace(/-/g, '');

            const commaIndex = value.indexOf(',');
            let integerPart;
            let decimalPart = '';
            let hasDecimal = commaIndex !== -1;

            if (hasDecimal) {
                integerPart = value.slice(0, commaIndex).replace(/[.,]/g, '');
                decimalPart = value.slice(commaIndex + 1).replace(/[.,]/g, '');
            } else {
                const dotCount = (value.match(/\./g) || []).length;
                const lastDot = value.lastIndexOf('.');
                const trailingDigits = value.length - lastDot - 1;
                const maxDecimals = input.dataset.numberDecimals === undefined
                    ? null
                    : Number(input.dataset.numberDecimals);
                const dotIsDecimal = dotCount === 1 && trailingDigits !== 3
                    && (maxDecimals === null || trailingDigits <= maxDecimals);

                if (dotIsDecimal) {
                    integerPart = value.slice(0, lastDot).replace(/[.,]/g, '');
                    decimalPart = value.slice(lastDot + 1).replace(/[.,]/g, '');
                    hasDecimal = true;
                } else {
                    integerPart = value.replace(/[.,]/g, '');
                }
            }

            if (input.dataset.numberDecimals !== undefined) {
                decimalPart = decimalPart.slice(0, Number(input.dataset.numberDecimals));
            }

            const groupedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            const formatted = (negative ? '-' : '') + groupedInteger
                + (hasDecimal ? ',' + decimalPart : '');
            input.value = formatted;

            let nextCaret = 0;
            let digitsSeen = 0;
            while (nextCaret < formatted.length && digitsSeen < digitsBeforeCaret) {
                if (/\d/.test(formatted[nextCaret])) digitsSeen++;
                nextCaret++;
            }
            if (decimalDigitsBeforeCaret !== null && hasDecimal) {
                nextCaret = formatted.indexOf(',') + 1 + decimalDigitsBeforeCaret;
            }
            if (digitsBeforeCaret === 0) nextCaret = negative ? 1 : 0;
            input.setSelectionRange(nextCaret, nextCaret);
        };

        window.validateFormattedNumberInput = function(input) {
            const value = input.value.trim();
            if (!value) {
                input.setCustomValidity('');
                return;
            }

            const parsed = input.dataset.numberInteger !== undefined
                ? Number(value.replace(/\./g, ''))
                : window.parseFormattedNumber(value);
            let message = '';
            if (input.dataset.numberMin !== undefined && parsed < Number(input.dataset.numberMin)) {
                message = 'Nilai terlalu kecil.';
            } else if (input.dataset.numberMax !== undefined && parsed > Number(input.dataset.numberMax)) {
                message = 'Nilai terlalu besar.';
            } else if (input.dataset.numberDecimals !== undefined) {
                const decimalPart = value.includes(',')
                    ? value.split(',')[1]
                    : ((value.match(/\./g) || []).length === 1 && value.split('.').pop().length !== 3
                        ? value.split('.').pop()
                        : '');
                if (decimalPart.length > Number(input.dataset.numberDecimals)) {
                    message = 'Maksimal ' + input.dataset.numberDecimals + ' angka di belakang koma.';
                }
            }

            input.setCustomValidity(message);
        };

        document.querySelectorAll('[data-format-number]').forEach(input => {
            window.formatNumberInput(input);
            window.validateFormattedNumberInput(input);
            input.addEventListener('input', () => {
                window.formatNumberInput(input);
                window.validateFormattedNumberInput(input);
            });
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                form.querySelectorAll('[data-format-number]').forEach(input => {
                    const parsed = input.dataset.numberInteger !== undefined
                        ? Number(input.value.replace(/\./g, ''))
                        : window.parseFormattedNumber(input.value);
                    input.value = Number.isFinite(parsed) ? String(parsed) : '';
                });
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
