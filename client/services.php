<?php
/**
 * GrihGO Digital & Tech — Client Services Catalog & Scope Ingestion
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/service-data.php';

$portalPageTitle = 'Services Catalog';
$portalHeaderTitle = 'Enterprise Services & Solutions';
$current_portal_page = 'services';

// Fetch services from memory or DB
$servicesList = getServicesData();

include __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--portal-text-main); margin: 0 0 4px 0;">Available Enterprise Services</h2>
        <p style="font-size: 0.85rem; color: var(--portal-text-muted); margin: 0;">Explore digital engineering capabilities and commission new project scopes with preferred client SLA</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px; margin-bottom: 32px;">
    <?php foreach ($servicesList as $slug => $srv): ?>
        <div class="portal-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div class="portal-card-body" style="padding: 24px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--portal-primary-light); color: var(--portal-primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 16px;">
                    <?= $srv['icon'] ?? '⚡' ?>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--portal-text-main); margin: 0 0 8px 0;">
                    <?= htmlspecialchars($srv['title'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <p style="font-size: 0.88rem; color: #475569; line-height: 1.5; margin: 0 0 16px 0;">
                    <?= htmlspecialchars($srv['short_desc'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <?php if (!empty($srv['deliverables'])): ?>
                    <div style="margin-bottom: 18px;">
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #94a3b8; letter-spacing: 0.6px; display: block; margin-bottom: 6px;">
                            Key Capabilities
                        </span>
                        <ul style="margin: 0; padding-left: 18px; font-size: 0.8rem; color: #475569; line-height: 1.6;">
                            <?php foreach (array_slice($srv['deliverables'], 0, 3) as $deliv): ?>
                                <li><?= htmlspecialchars($deliv, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <div style="padding: 16px 24px; background: #fafafa; border-top: 1px solid var(--portal-border); display: flex; align-items: center; justify-content: space-between;">
                <a href="../services/<?= urlencode($slug) ?>.php" target="_blank" style="font-size: 0.82rem; font-weight: 600; color: #475569; text-decoration: none;">
                    View Scope &rarr;
                </a>
                <button type="button" class="btn-portal-primary btn-portal-sm btn-request-service" 
                        data-service="<?= htmlspecialchars($srv['title'], ENT_QUOTES, 'UTF-8') ?>">
                    <span>Request Service</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Request Service Modal -->
<div id="serviceRequestModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; position: relative; animation: modalFadeIn 0.25s ease;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--portal-border); display: flex; align-items: center; justify-content: space-between; background: #fafafa;">
            <div>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--portal-text-main);">Commission Service Scope</h3>
                <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: var(--portal-text-muted);">Direct enterprise proposal request connecting to your client account</p>
            </div>
            <button type="button" id="closeModalBtn" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>

        <form id="serviceModalForm" method="POST" action="../api/service-enquiry.php" style="padding: 24px;">
            <?= getCsrfField() ?>

            <div class="portal-form-group">
                <label class="portal-form-label">Service Required <span style="color: #ef4444;">*</span></label>
                <input type="text" class="portal-form-input" id="modalServiceInput" name="service" readonly style="background: #f1f5f9; font-weight: 600;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="portal-form-group">
                    <label class="portal-form-label">Full Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" class="portal-form-input" name="full_name" value="<?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="portal-form-group">
                    <label class="portal-form-label">Business Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" class="portal-form-input" name="business_name" value="<?= htmlspecialchars($clientCompany, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="portal-form-group">
                    <label class="portal-form-label">Email Address <span style="color: #ef4444;">*</span></label>
                    <input type="email" class="portal-form-input" name="email" value="<?= htmlspecialchars($clientEmail, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="portal-form-group">
                    <label class="portal-form-label">Phone Number <span style="color: #ef4444;">*</span></label>
                    <input type="text" class="portal-form-input" name="phone" value="<?= htmlspecialchars($clientPhone, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="portal-form-group">
                <label class="portal-form-label">Project Scope &amp; Requirements <span style="color: #ef4444;">*</span></label>
                <textarea class="portal-form-textarea" name="message" rows="3" placeholder="Describe deliverables, desired timelines, or integrations needed..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button type="button" id="cancelModalBtn" class="btn-portal-outline">Cancel</button>
                <button type="submit" id="btnSubmitModalService" class="btn-portal-primary">
                    <span>Submit Service Enquiry</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('serviceRequestModal');
    const serviceInput = document.getElementById('modalServiceInput');
    const closeBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelModalBtn');
    const form = document.getElementById('serviceModalForm');
    const submitBtn = document.getElementById('btnSubmitModalService');

    document.querySelectorAll('.btn-request-service').forEach(btn => {
        btn.addEventListener('click', function() {
            const sName = this.getAttribute('data-service');
            serviceInput.value = sName;
            modal.style.display = 'flex';
        });
    });

    function hideModal() {
        modal.style.display = 'none';
    }

    if (closeBtn) closeBtn.addEventListener('click', hideModal);
    if (cancelBtn) cancelBtn.addEventListener('click', hideModal);
    window.addEventListener('click', function(e) {
        if (e.target === modal) hideModal();
    });

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Submitting Scope...</span>';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.showPortalToast(data.message || 'Service request submitted successfully! Your account manager will contact you.', 'success');
                    hideModal();
                    form.reset();
                } else {
                    window.showPortalToast(data.message || 'Could not submit request.', 'error');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Submit Service Enquiry</span>';
            })
            .catch(() => {
                window.showPortalToast('Failed to connect to server. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Submit Service Enquiry</span>';
            });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
