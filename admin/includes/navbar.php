<header class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h5 class="m-0 fw-bold d-none d-sm-block text-secondary"><?= isset($pageTitle) ? e($pageTitle) : 'Admin Dashboard' ?></h5>
    </div>

    <div class="d-flex align-items-center gap-3">
        <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span class="d-none d-md-inline">View Website</span>
        </a>

        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user-shield text-primary"></i>
                <span class="fw-semibold d-none d-md-inline"><?= e($currentUser['full_name'] ?? 'Admin') ?></span>
                <span class="badge bg-secondary d-none d-lg-inline"><?= e(strtoupper($currentUser['role'] ?? 'ADMIN')) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><h6 class="dropdown-header">Signed in as <strong><?= e($currentUser['username'] ?? 'admin') ?></strong></h6></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= ADMIN_URL ?>/profile.php"><i class="fa-regular fa-id-badge me-2 text-muted"></i> My Profile</a></li>
                <li><a class="dropdown-item" href="<?= ADMIN_URL ?>/settings.php"><i class="fa-solid fa-gear me-2 text-muted"></i> Site Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= ADMIN_URL ?>/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>
