<?php
$pageTitle = 'Logo & Site Settings';
require_once __DIR__ . '/includes/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        // Handle Text Settings
        $fields = [
            'site_name', 'site_tagline', 'phone', 'whatsapp', 'email', 'address',
            'topbar_info', 'topbar_whatsapp_text', 'facebook_url', 'instagram_url',
            'youtube_url', 'copyright_text', 'google_map_iframe',
            'footer_about_text', 'footer_newsletter_title', 'footer_newsletter_subtitle', 'workshop_timings',
            'meta_title', 'meta_description', 'meta_keywords'
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_setting($field, trim($_POST[$field]));
            }
        }

        // Handle Logo Upload
        if (!empty($_FILES['site_logo']['name'])) {
            $upload = upload_media($_FILES['site_logo'], 'logos', ['png', 'jpg', 'jpeg', 'webp', 'svg']);
            if ($upload['success']) {
                update_setting('site_logo', $upload['path']);
            } else {
                $error = 'Logo upload failed: ' . $upload['error'];
            }
        }

        // Handle Favicon Upload
        if (!empty($_FILES['site_favicon']['name'])) {
            $upload = upload_media($_FILES['site_favicon'], 'logos', ['png', 'ico', 'svg', 'webp']);
            if ($upload['success']) {
                update_setting('site_favicon', $upload['path']);
            } else {
                $error = 'Favicon upload failed: ' . $upload['error'];
            }
        }

        // Handle Footer Logo Upload
        if (!empty($_FILES['footer_logo']['name'])) {
            $upload = upload_media($_FILES['footer_logo'], 'logos', ['png', 'jpg', 'jpeg', 'webp', 'svg']);
            if ($upload['success']) {
                update_setting('footer_logo', $upload['path']);
            }
        }

        if (empty($error)) {
            log_activity('SETTINGS_UPDATE', 'Updated site branding and contact settings');
            set_flash('success', 'Site settings and branding successfully updated!');
            header('Location: ' . ADMIN_URL . '/settings.php');
            exit;
        }
    }
}

$settings = get_all_settings();
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Branding & Logos -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="fa-solid fa-image text-primary me-2"></i>Logo & Branding Assets</span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Website Header Logo</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="<?= e(resolve_image_url($settings['site_logo'] ?? 'images/logo.png')) ?>" id="logoPreview" class="border p-2 bg-light rounded" style="max-height: 60px; max-width: 180px;" alt="Logo">
                            <input type="file" name="site_logo" class="form-control" data-preview-target="logoPreview" accept="image/*">
                        </div>
                        <small class="text-muted">Recommended: Transparent PNG or SVG (height ~50-60px)</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Website Favicon</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="<?= e(resolve_image_url($settings['site_favicon'] ?? 'images/logo.png')) ?>" id="faviconPreview" class="border p-2 bg-light rounded" style="max-height: 40px; max-width: 40px;" alt="Favicon">
                            <input type="file" name="site_favicon" class="form-control" data-preview-target="faviconPreview" accept="image/*">
                        </div>
                        <small class="text-muted">Square icon (32x32px or 64x64px)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Footer Logo (Optional)</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="<?= e(resolve_image_url($settings['footer_logo'] ?? 'images/footer-logo.png')) ?>" id="footerLogoPreview" class="border p-2 bg-dark rounded" style="max-height: 50px; max-width: 180px;" alt="Footer Logo">
                            <input type="file" name="footer_logo" class="form-control" data-preview-target="footerLogoPreview" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Website Name / Brand Title</label>
                        <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'A2Z Handicraft') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Site Tagline</label>
                        <input type="text" name="site_tagline" class="form-control" value="<?= e($settings['site_tagline'] ?? 'Elevate Your Style With Handmade Fashion') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact & Location Info -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="fa-solid fa-address-book text-success me-2"></i>Contact & Communication Details</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Support Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($settings['phone'] ?? '+91-7983894672') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">WhatsApp Number (with country code)</label>
                            <input type="text" name="whatsapp" class="form-control" placeholder="917983894672" value="<?= e($settings['whatsapp'] ?? '917983894672') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= e($settings['email'] ?? 'a2zhandicraft@gmail.com') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Physical Workshop / Office Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= e($settings['address'] ?? 'Main Hapur Road, Arifpur Saravni, Distt. Hapur, UP - 245101') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Google Map Embed iframe URL</label>
                        <input type="text" name="google_map_iframe" class="form-control" value="<?= e($settings['google_map_iframe'] ?? '') ?>">
                        <small class="text-muted">Direct iframe src link for Contact page map</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Topbar & Footer Text -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="fa-solid fa-bullhorn text-warning me-2"></i>Topbar & Footer Content</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Topbar Info Announcement</label>
                        <input type="text" name="topbar_info" class="form-control" value="<?= e($settings['topbar_info'] ?? 'Exclusive Best Handmade Products - Your Perfect Choice!') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Topbar WhatsApp Button Text</label>
                        <input type="text" name="topbar_whatsapp_text" class="form-control" value="<?= e($settings['topbar_whatsapp_text'] ?? 'Bulk Order Now: +91-7983894672') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Footer About / Brand Description</label>
                        <textarea name="footer_about_text" class="form-control" rows="3" placeholder="Brief description of the brand shown in the footer column..."><?= e($settings['footer_about_text'] ?? 'A2Z Handicraft is a premier manufacturer and exporter of authentic Indian handcrafted decor, wedding props, torans, dream catchers, and festival gifts.') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Footer Newsletter Title</label>
                            <input type="text" name="footer_newsletter_title" class="form-control" value="<?= e($settings['footer_newsletter_title'] ?? 'Stay In Touch') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Newsletter Subtitle</label>
                            <input type="text" name="footer_newsletter_subtitle" class="form-control" value="<?= e($settings['footer_newsletter_subtitle'] ?? 'Subscribe for latest arrivals & bulk festival discounts.') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Workshop / Business Hours</label>
                        <input type="text" name="workshop_timings" class="form-control" value="<?= e($settings['workshop_timings'] ?? 'Mon - Sat: 9:00 AM - 8:00 PM (Sunday Closed)') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Footer Copyright Text</label>
                        <input type="text" name="copyright_text" class="form-control" value="<?= e($settings['copyright_text'] ?? 'Copyright © {year} A2Z HANDICRAFT. All Rights Reserved!') ?>">
                        <small class="text-muted">Use <code>{year}</code> for automatic current year</small>
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-share-nodes me-2"></i>Social Media Links</h6>

                    <div class="mb-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-brands fa-facebook text-primary"></i></span>
                            <input type="url" name="facebook_url" class="form-control" placeholder="https://facebook.com/..." value="<?= e($settings['facebook_url'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-brands fa-instagram text-danger"></i></span>
                            <input type="url" name="instagram_url" class="form-control" placeholder="https://instagram.com/..." value="<?= e($settings['instagram_url'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-brands fa-youtube text-danger"></i></span>
                            <input type="url" name="youtube_url" class="form-control" placeholder="https://youtube.com/..." value="<?= e($settings['youtube_url'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Yoast SEO Settings -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="fa-solid fa-chart-line text-info me-2"></i>Global Yoast SEO Settings</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Global Meta Title (Default)</label>
                        <input type="text" name="meta_title" id="meta_title" class="form-control" value="<?= e($settings['meta_title'] ?? '') ?>">
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" id="metaTitleCount">0 / 60 characters</small>
                        </div>
                        <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaTitleBar"></div></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Global Meta Description</label>
                        <textarea name="meta_description" id="meta_description" class="form-control" rows="3"><?= e($settings['meta_description'] ?? '') ?></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" id="metaDescCount">0 / 160 characters</small>
                        </div>
                        <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaDescBar"></div></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Default Meta Keywords (comma separated)</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?= e($settings['meta_keywords'] ?? '') ?>">
                    </div>

                    <!-- Yoast Snippet Preview -->
                    <div class="yoast-preview mt-3">
                        <div class="yoast-preview-url">
                            <i class="fa-solid fa-globe text-muted"></i>
                            <?= BASE_URL ?>
                        </div>
                        <div class="yoast-preview-title" id="yoastPreviewTitle"><?= e($settings['meta_title'] ?? 'A2Z Handicraft') ?></div>
                        <div class="yoast-preview-desc" id="yoastPreviewDesc"><?= e($settings['meta_description'] ?? 'Handcrafted decorative products manufacturer.') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mb-5">
        <button type="submit" class="btn btn-primary-a2z btn-lg px-4">
            <i class="fa-solid fa-floppy-disk me-2"></i> Save All Settings
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
