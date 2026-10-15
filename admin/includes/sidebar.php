<aside class="admin-sidebar" id="adminSidebar">
    <div class="brand">
        <img src="<?= e($siteLogo) ?>" alt="Logo">
        <div>
            <div class="brand-title"><?= e($siteName) ?></div>
            <small class="text-white-50" style="font-size: 0.72rem;">Management Control</small>
        </div>
    </div>

    <ul class="nav-list">
        <li class="sidebar-header">Main Overview</li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/index.php">
                <span><i class="fa-solid fa-gauge-high"></i> Dashboard</span>
            </a>
        </li>

        <li class="sidebar-header">Catalog & Products</li>
        <li class="nav-item">
            <a class="nav-link <?= in_array($currentPage, ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/products.php">
                <span><i class="fa-solid fa-boxes-stacked"></i> Products</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= in_array($currentPage, ['categories.php', 'category-add.php', 'category-edit.php']) ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/categories.php">
                <span><i class="fa-solid fa-layer-group"></i> Categories</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'subcategories.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/subcategories.php">
                <span><i class="fa-solid fa-tags"></i> Subcategories</span>
            </a>
        </li>

        <li class="sidebar-header">Inquiries & Content</li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'leads.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/leads.php">
                <span><i class="fa-solid fa-envelope-open-text"></i> Leads & Inquiries</span>
                <?php if ($unreadLeadsCount > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?= $unreadLeadsCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'banners.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/banners.php">
                <span><i class="fa-solid fa-images"></i> Home Banners</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'reviews.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/reviews.php">
                <span><i class="fa-solid fa-star-half-stroke"></i> Reviews</span>
                <?php if ($pendingReviewsCount > 0): ?>
                    <span class="badge bg-warning text-dark rounded-pill"><?= $pendingReviewsCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'testimonials.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/testimonials.php">
                <span><i class="fa-solid fa-quote-left"></i> Testimonials</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/about.php">
                <span><i class="fa-solid fa-circle-info"></i> About Page</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'faqs.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/faqs.php">
                <span><i class="fa-solid fa-circle-question"></i> FAQs</span>
            </a>
        </li>

        <li class="sidebar-header">Administration</li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'settings.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/settings.php">
                <span><i class="fa-solid fa-sliders"></i> Logo & Settings</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'users.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/users.php">
                <span><i class="fa-solid fa-users-gear"></i> Admin Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'profile.php') ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/profile.php">
                <span><i class="fa-solid fa-user-circle"></i> My Profile</span>
            </a>
        </li>
        <li class="nav-item mt-3 pt-3 border-top border-secondary">
            <a class="nav-link text-danger" href="<?= ADMIN_URL ?>/logout.php" onclick="return confirm('Do you want to log out of the admin panel?');">
                <span><i class="fa-solid fa-right-from-bracket"></i> Secure Logout</span>
            </a>
        </li>
    </ul>
</aside>
