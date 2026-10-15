/**
 * A2Z Handicraft Admin Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Sidebar Toggle & Overlay
    const sidebar = document.getElementById('adminSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // 2. Auto-generate Slug from Name
    const nameInput = document.querySelector('input[data-slug-source]');
    const slugInput = document.querySelector('input[data-slug-target]');

    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function () {
            // Only update if slug is not locked or empty
            if (!slugInput.dataset.locked) {
                slugInput.value = nameInput.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                
                // Trigger change event to update Yoast preview
                slugInput.dispatchEvent(new Event('input'));
            }
        });

        slugInput.addEventListener('change', function () {
            if (slugInput.value.length > 0) {
                slugInput.dataset.locked = "true";
            }
        });
    }

    // 3. Image File Upload Preview
    document.querySelectorAll('input[type="file"][data-preview-target]').forEach(input => {
        input.addEventListener('change', function () {
            const previewId = this.getAttribute('data-preview-target');
            const previewImg = document.getElementById(previewId);
            if (previewImg && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // 4. Yoast SEO Real-time Snippet Preview & Counters
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescInput = document.getElementById('meta_description');
    const previewTitle = document.getElementById('yoastPreviewTitle');
    const previewDesc = document.getElementById('yoastPreviewDesc');
    const previewUrl = document.getElementById('yoastPreviewUrl');
    const titleCounter = document.getElementById('metaTitleCount');
    const descCounter = document.getElementById('metaDescCount');
    const titleBar = document.getElementById('metaTitleBar');
    const descBar = document.getElementById('metaDescBar');

    function updateYoastTitle() {
        if (!metaTitleInput) return;
        const val = metaTitleInput.value.trim() || (nameInput ? nameInput.value.trim() : 'Title Preview');
        const len = val.length;

        if (previewTitle) {
            previewTitle.textContent = val || 'Your Page Title Here - A2Z Handicraft';
        }
        if (titleCounter) {
            titleCounter.textContent = `${len} / 60 characters`;
        }
        if (titleBar) {
            const pct = Math.min((len / 60) * 100, 100);
            titleBar.style.width = pct + '%';
            if (len >= 40 && len <= 60) {
                titleBar.style.backgroundColor = '#10b981'; // Good green
            } else if (len > 60) {
                titleBar.style.backgroundColor = '#ef4444'; // Too long
            } else {
                titleBar.style.backgroundColor = '#f59e0b'; // Too short
            }
        }
    }

    function updateYoastDesc() {
        if (!metaDescInput) return;
        const val = metaDescInput.value.trim();
        const len = val.length;

        if (previewDesc) {
            previewDesc.textContent = val || 'Please provide a meta description by editing the snippet below. If you don’t, search engines will try to find a relevant part of your page to show in the search results.';
        }
        if (descCounter) {
            descCounter.textContent = `${len} / 160 characters`;
        }
        if (descBar) {
            const pct = Math.min((len / 160) * 100, 100);
            descBar.style.width = pct + '%';
            if (len >= 120 && len <= 160) {
                descBar.style.backgroundColor = '#10b981'; // Green
            } else if (len > 160) {
                descBar.style.backgroundColor = '#ef4444'; // Red
            } else {
                descBar.style.backgroundColor = '#f59e0b'; // Orange
            }
        }
    }

    if (metaTitleInput) {
        metaTitleInput.addEventListener('input', updateYoastTitle);
        updateYoastTitle();
    }
    if (metaDescInput) {
        metaDescInput.addEventListener('input', updateYoastDesc);
        updateYoastDesc();
    }
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            if (metaTitleInput && !metaTitleInput.value) {
                updateYoastTitle();
            }
        });
    }
});

// Confirmation dialog for delete actions
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.');
}
