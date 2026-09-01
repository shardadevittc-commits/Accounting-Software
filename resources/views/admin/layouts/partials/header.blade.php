<!-- Top Horizontal Navigation Bar Header Partial (Spacious & Clean Layout) -->
<header class="top-navbar">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-logo">
        <div class="brand-icon-gems">
            <div class="brand-gem bg-gem-1"></div>
            <div class="brand-gem bg-gem-2"></div>
            <div class="brand-gem bg-gem-3"></div>
            <div class="brand-gem bg-gem-4"></div>
        </div>
        <span>Accounting<span class="fs-6 fw-normal text-muted ms-1">Accounts ERP</span></span>
    </a>

    <!-- Top Horizontal Navigation Menu (Spacious 6 Core Categories with Submenu Indicators) -->
    <nav class="topbar-center-nav">
        <ul class="nav-links-container">
            <!-- 1. HOME -->
            <li class="nav-link-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>HOME</span>
                    <i class="fa-solid fa-chevron-down nav-chevron-icon"></i>
                </a>
                <div class="topbar-dropdown">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-white fw-bold' : '' }}"><i class="fa-solid fa-gauge-high"></i> Executive Overview</a>
                    <a href="#"><i class="fa-solid fa-chart-area"></i> Revenue Analytics</a>
                </div>
            </li>

            <!-- 2. TRANSACTIONS (Sales, Purchases, Expenses, Banking) -->
            <li class="nav-link-item {{ request()->is('transactions*') || request()->routeIs('sales.*') || request()->routeIs('purchase.*') ? 'active' : '' }}">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span>TRANSACTIONS</span>
                    <i class="fa-solid fa-chevron-down nav-chevron-icon"></i>
                </a>
                <div class="topbar-dropdown">
                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['sales', 'accountant']) || auth()->user()->hasPermission('sales.view'))
                        <a href="{{ route('sales.dispatch-invoicing') }}" class="{{ request()->routeIs('sales.dispatch-invoicing') ? 'text-white fw-bold' : '' }}"><i class="fa-solid fa-truck-ramp-box text-info"></i> Dispatch Invoicing</a>
                        <a href="{{ route('sales.orders') }}" class="{{ request()->routeIs('sales.orders') ? 'text-white fw-bold' : '' }}"><i class="fa-solid fa-file-invoice-dollar text-success"></i> Sales Orders List</a>
                    @endif
                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['purchase', 'accountant']) || auth()->user()->hasPermission('purchase.view'))
                        <a href="{{ route('purchase.orders') }}" class="{{ request()->routeIs('purchase.*') ? 'text-white fw-bold' : '' }}"><i class="fa-solid fa-receipt text-warning"></i> Purchase Bills & Orders</a>
                    @endif
                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant') || auth()->user()->hasPermission('expenses.view'))
                        <a href="#"><i class="fa-solid fa-wallet text-danger"></i> Expense Claims</a>
                    @endif
                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['sales', 'accountant']) || auth()->user()->hasPermission('payments.view'))
                        <a href="#"><i class="fa-solid fa-hand-holding-dollar text-primary"></i> Customer Payments</a>
                    @endif
                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant'))
                        <a href="#"><i class="fa-solid fa-building-columns text-info"></i> Bank Transactions</a>
                    @endif
                </div>
            </li>

            <!-- 3. ACCOUNTING (Ledger, Journal, Cash/Bank Book) -->
            @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant'))
            <li class="nav-link-item {{ request()->is('accounting*') ? 'active' : '' }}">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-book-journal-whills"></i>
                    <span>ACCOUNTING</span>
                    <i class="fa-solid fa-chevron-down nav-chevron-icon"></i>
                </a>
                <div class="topbar-dropdown">
                    <a href="#"><i class="fa-solid fa-sitemap"></i> Chart of Accounts</a>
                    <a href="#"><i class="fa-solid fa-pen-to-square"></i> Journal Entries</a>
                    <a href="#"><i class="fa-solid fa-book"></i> General Ledger</a>
                    <a href="#"><i class="fa-solid fa-wallet"></i> Cash Book</a>
                    <a href="#"><i class="fa-solid fa-building-columns"></i> Bank Book</a>
                    <a href="#"><i class="fa-solid fa-scale-balanced"></i> Trial Balance</a>
                </div>
            </li>
            @endif

            <!-- 4. INVENTORY (Products, Warehouses, Stock) -->
            <li class="nav-link-item {{ request()->is('inventory*') ? 'active' : '' }}">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>INVENTORY</span>
                    <i class="fa-solid fa-chevron-down nav-chevron-icon"></i>
                </a>
                <div class="topbar-dropdown">
                    <a href="#"><i class="fa-solid fa-box"></i> Products & Items</a>
                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant') || auth()->user()->hasRole('purchase'))
                        <a href="#"><i class="fa-solid fa-warehouse"></i> Warehouses</a>
                        <a href="#"><i class="fa-solid fa-cubes"></i> Stock Summary</a>
                        <a href="#"><i class="fa-solid fa-sliders"></i> Stock Adjustment</a>
                        <a href="#"><i class="fa-solid fa-right-left"></i> Stock Transfer</a>
                    @endif
                </div>
            </li>

            <!-- 5. TAX & REPORTS (GST, P&L, Balance Sheet) -->
            <li class="nav-link-item {{ request()->is('reports*') ? 'active' : '' }}">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-percent"></i>
                    <span>TAX & REPORTS</span>
                    <i class="fa-solid fa-chevron-down nav-chevron-icon"></i>
                </a>
                <div class="topbar-dropdown">
                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant'))
                        <a href="#"><i class="fa-solid fa-file-pdf text-purple"></i> GST Summary & Return (GSTR-3B)</a>
                        <a href="#"><i class="fa-solid fa-file-contract text-success"></i> Profit & Loss Statement</a>
                        <a href="#"><i class="fa-solid fa-scale-balanced text-primary"></i> Balance Sheet</a>
                        <a href="#"><i class="fa-solid fa-arrows-split-up-and-left text-info"></i> Cash Flow Statement</a>
                    @endif
                    <a href="#"><i class="fa-solid fa-chart-line text-warning"></i> Sales & Purchase Reports</a>
                </div>
            </li>

            <!-- 6. SETTINGS -->
            @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('roles.view'))
            <li class="nav-link-item {{ (request()->routeIs('roles.*') || request()->routeIs('users.*') || request()->is('settings*')) ? 'active' : '' }}">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-gear"></i>
                    <span>SETTINGS</span>
                    <i class="fa-solid fa-chevron-down nav-chevron-icon"></i>
                </a>
                <div class="topbar-dropdown">
                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasPermission('roles.view'))
                        <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'text-white fw-bold' : '' }}"><i class="fa-solid fa-user-shield text-warning"></i> Roles & Permissions</a>
                    @endif
                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasPermission('users.view'))
                        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'text-white fw-bold' : '' }}"><i class="fa-solid fa-users-gear text-info"></i> User Accounts & Permissions</a>
                    @endif
                    <a href="#"><i class="fa-solid fa-building-gear"></i> Company Settings</a>
                    <a href="#"><i class="fa-solid fa-calendar-days"></i> Financial Year Config</a>
                </div>
            </li>
            @endif
        </ul>
    </nav>

    <!-- Top Right Tools (Notification, User Profile & Direct Logout) -->
    <div class="topbar-right-tools">
        <!-- Theme Palette Switcher Button -->
        <button type="button" class="tool-icon-btn" data-bs-toggle="modal" data-bs-target="#themeSelectorModal" title="Change ERP Theme Color">
            <i class="fa-solid fa-palette text-warning"></i>
        </button>

        <!-- Notification Icon -->
        <a href="#" class="tool-icon-btn" title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <span class="notification-badge"></span>
        </a>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <div class="user-profile-badge" data-bs-toggle="dropdown" aria-expanded="false">
                @if(isset($user->avatar_url) || (auth()->check() && auth()->user()->avatar_url))
                    <img src="{{ auth()->check() ? auth()->user()->avatar_url : ($user->avatar_url ?? asset(config('profile.default_avatar'))) }}" class="user-initials-avatar" style="object-fit: cover;" alt="Avatar">
                @else
                    <div class="user-initials-avatar">
                        {{ strtoupper(substr($user->name ?? 'SU', 0, 2)) }}
                    </div>
                @endif
                <i class="fa-solid fa-chevron-down text-white-50 fs-8 ms-1"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg user-profile-dropdown-menu">
                <li class="px-3 py-2 border-bottom border-secondary border-opacity-25 mb-1">
                    <div class="fw-bold text-white text-capitalize fs-6 mb-0">{{ $user->name ?? (auth()->check() ? auth()->user()->name : 'Admin') }}</div>
                    <div class="text-white-50 fs-8 text-break mb-1">{{ $user->email ?? (auth()->check() ? auth()->user()->email : 'admin@gmail.com') }}</div>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 fs-8">{{ auth()->check() && auth()->user()->roles->first() ? auth()->user()->roles->first()->name : 'Admin' }}</span>
                </li>
                <li><a class="dropdown-item py-2 rounded-2 text-white-50" href="{{ route('profile.index') }}"><i class="fa-solid fa-user me-2 text-primary"></i> My Profile</a></li>
                @if(!auth()->check() || auth()->user()->isAdmin())
                <li><a class="dropdown-item py-2 rounded-2 text-white-50" href="{{ route('roles.index') }}"><i class="fa-solid fa-shield-halved me-2 text-warning"></i> Roles & Permissions</a></li>
                @endif
                <li><hr class="dropdown-divider border-secondary border-opacity-25 my-1"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 rounded-2 text-danger bg-transparent border-0 w-100 text-start fw-bold">
                            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <!-- Direct Logout Power Button -->
        <form action="{{ route('logout') }}" method="POST" class="d-inline ms-1">
            @csrf
            <button type="submit" class="tool-icon-btn btn-direct-logout" title="Logout Now">
                <i class="fa-solid fa-power-off"></i>
            </button>
        </form>
    </div>
</header>
