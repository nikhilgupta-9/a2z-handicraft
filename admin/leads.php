<?php
$pageTitle = 'Leads & Inquiries';
require_once __DIR__ . '/includes/header.php';

global $pdo;

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $leadsExport = $pdo->query("SELECT id, name, email, phone, subject, message, status, is_read, ip_address, created_at FROM leads ORDER BY id DESC")->fetchAll();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=a2z_leads_' . date('Y-m-d_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Customer Name', 'Email', 'Phone', 'Subject', 'Message', 'Status', 'Is Read', 'IP Address', 'Date Received']);
    foreach ($leadsExport as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
        $stmt->execute([$id]);

        log_activity('LEAD_DELETE', "Deleted lead ID: $id");
        set_flash('success', 'Lead record deleted.');
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/leads.php');
    exit;
}

// Handle Status Change / Mark Read
if (isset($_GET['action']) && $_GET['action'] === 'set_status' && isset($_GET['id']) && isset($_GET['status'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $status = clean_input($_GET['status']);
        if (in_array($status, ['new', 'contacted', 'resolved', 'spam'])) {
            $stmt = $pdo->prepare("UPDATE leads SET status = ?, is_read = 1 WHERE id = ?");
            $stmt->execute([$status, $id]);
            set_flash('success', "Lead marked as $status.");
        }
    }
    header('Location: ' . ADMIN_URL . '/leads.php');
    exit;
}

// Filter
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM leads WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();
?>

<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <form action="" method="GET" class="d-flex flex-wrap gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" style="max-width: 250px;" placeholder="Search leads by name, email, phone..." value="<?= e($search) ?>">
                    <select name="status" class="form-select form-select-sm" style="max-width: 160px;">
                        <option value="">All Statuses</option>
                        <option value="new" <?= ($statusFilter === 'new') ? 'selected' : '' ?>>New</option>
                        <option value="contacted" <?= ($statusFilter === 'contacted') ? 'selected' : '' ?>>Contacted</option>
                        <option value="resolved" <?= ($statusFilter === 'resolved') ? 'selected' : '' ?>>Resolved</option>
                        <option value="spam" <?= ($statusFilter === 'spam') ? 'selected' : '' ?>>Spam</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary-a2z"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <?php if (!empty($search) || !empty($statusFilter)): ?>
                        <a href="<?= ADMIN_URL ?>/leads.php" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-5 text-md-end">
                <a href="<?= ADMIN_URL ?>/leads.php?export=csv" class="btn btn-outline-success btn-sm">
                    <i class="fa-solid fa-file-csv me-1"></i> Export to CSV
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <span><i class="fa-solid fa-envelope-open-text text-primary me-2"></i>Inquiries & Contact Leads (<?= count($leads) ?>)</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Contact Details</th>
                    <th>Subject & Message</th>
                    <th>Status</th>
                    <th>Received At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leads)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No leads found matching criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leads as $l): ?>
                        <tr class="<?= ($l['is_read'] == 0) ? 'table-warning bg-opacity-10 fw-semibold' : '' ?>">
                            <td>
                                <div><?= e($l['name']) ?></div>
                                <?php if ($l['is_read'] == 0): ?>
                                    <span class="badge bg-danger" style="font-size: 0.68rem;">UNREAD</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><a href="mailto:<?= e($l['email']) ?>" class="text-decoration-none"><i class="fa-solid fa-envelope text-muted me-1"></i><?= e($l['email']) ?></a></div>
                                <div><a href="tel:<?= e($l['phone']) ?>" class="text-decoration-none"><i class="fa-solid fa-phone text-muted me-1"></i><?= e($l['phone']) ?></a></div>
                                <div><a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/[^0-9]/', '', $l['phone']) ?>&text=Hi%20<?= urlencode($l['name']) ?>%20thank%20you%20for%20contacting%20A2Z%20Handicraft" target="_blank" class="badge bg-success text-decoration-none mt-1"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp Reply</a></div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($l['subject'] ?? 'Inquiry') ?></div>
                                <div class="text-muted small text-truncate" style="max-width: 300px;"><?= e($l['message']) ?></div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown">
                                        <?php if ($l['status'] === 'new'): ?>
                                            <span class="text-danger fw-bold">New</span>
                                        <?php elseif ($l['status'] === 'contacted'): ?>
                                            <span class="text-primary fw-bold">Contacted</span>
                                        <?php elseif ($l['status'] === 'resolved'): ?>
                                            <span class="text-success fw-bold">Resolved</span>
                                        <?php else: ?>
                                            <span class="text-secondary">Spam</span>
                                        <?php endif; ?>
                                    </button>
                                    <ul class="dropdown-menu shadow">
                                        <li><a class="dropdown-item" href="<?= ADMIN_URL ?>/leads.php?action=set_status&status=new&id=<?= $l['id'] ?>&token=<?= csrf_token() ?>">Mark New</a></li>
                                        <li><a class="dropdown-item" href="<?= ADMIN_URL ?>/leads.php?action=set_status&status=contacted&id=<?= $l['id'] ?>&token=<?= csrf_token() ?>">Mark Contacted</a></li>
                                        <li><a class="dropdown-item" href="<?= ADMIN_URL ?>/leads.php?action=set_status&status=resolved&id=<?= $l['id'] ?>&token=<?= csrf_token() ?>">Mark Resolved</a></li>
                                        <li><a class="dropdown-item" href="<?= ADMIN_URL ?>/leads.php?action=set_status&status=spam&id=<?= $l['id'] ?>&token=<?= csrf_token() ?>">Mark Spam</a></li>
                                    </ul>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted"><?= date('M d, Y H:i', strtotime($l['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#leadModal<?= $l['id'] ?>" title="View Full Message">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </button>
                                    <a href="<?= ADMIN_URL ?>/leads.php?action=delete&id=<?= $l['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-light" onclick="return confirmDelete('Delete this inquiry?');" title="Delete">
                                        <i class="fa-solid fa-trash text-danger"></i>
                                    </a>
                                </div>

                                <!-- Lead View Modal -->
                                <div class="modal fade" id="leadModal<?= $l['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold"><i class="fa-solid fa-envelope me-2 text-primary"></i>Inquiry Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Customer Name:</strong> <?= e($l['name']) ?></p>
                                                <p><strong>Email Address:</strong> <a href="mailto:<?= e($l['email']) ?>"><?= e($l['email']) ?></a></p>
                                                <p><strong>Phone:</strong> <a href="tel:<?= e($l['phone']) ?>"><?= e($l['phone']) ?></a></p>
                                                <p><strong>Subject:</strong> <?= e($l['subject'] ?? 'Website Inquiry') ?></p>
                                                <hr>
                                                <p><strong>Message:</strong></p>
                                                <div class="p-3 bg-light rounded border text-secondary">
                                                    <?= nl2br(e($l['message'])) ?>
                                                </div>
                                                <div class="mt-3 small text-muted">
                                                    <div>IP Address: <?= e($l['ip_address'] ?? 'N/A') ?></div>
                                                    <div>Date: <?= e($l['created_at']) ?></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/[^0-9]/', '', $l['phone']) ?>" target="_blank" class="btn btn-success btn-sm">
                                                    <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                                                </a>
                                                <a href="mailto:<?= e($l['email']) ?>?subject=Regarding%20your%20inquiry%20with%20A2Z%20Handicraft" class="btn btn-primary btn-sm">
                                                    <i class="fa-solid fa-reply me-1"></i> Email Reply
                                                </a>
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
