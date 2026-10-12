<?php
/**
 * GrihGO Digital & Tech — Create Support Ticket
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'Create Support Ticket';
$portalHeaderTitle = 'Open New Support Ticket';
$current_portal_page = 'support-create';

include __DIR__ . '/includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="support.php" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--portal-primary); text-decoration: none; font-weight: 600;">
            &larr; Back to Ticket Desk
        </a>
    </div>

    <div class="portal-card">
        <div class="portal-card-header">
            <h2 class="portal-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span>New Engineering &amp; Operations Support Ticket</span>
            </h2>
        </div>

        <div class="portal-card-body">
            <form id="createTicketForm" method="POST" action="../api/client-ticket-create.php" enctype="multipart/form-data">
                <?= getCsrfField() ?>

                <div class="portal-form-group">
                    <label for="ticketSubject" class="portal-form-label">
                        Ticket Subject / Summary <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" class="portal-form-input" id="ticketSubject" name="subject" 
                           placeholder="e.g. Invoicing discrepancy regarding INV-2026-004 or WhatsApp webhook timeout" required maxlength="200">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="portal-form-group">
                        <label for="ticketCategory" class="portal-form-label">
                            Issue Category <span style="color: #ef4444;">*</span>
                        </label>
                        <select class="portal-form-select" id="ticketCategory" name="category" required>
                            <option value="Technical Issue">Technical Issue</option>
                            <option value="Billing">Billing &amp; Invoices</option>
                            <option value="Project">Project Milestone &amp; Scope</option>
                            <option value="Website">Website Maintenance</option>
                            <option value="Software">Custom Software</option>
                            <option value="CRM">WhatsApp / Enterprise CRM</option>
                            <option value="General" selected>General Inquiry</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="portal-form-group">
                        <label for="ticketPriority" class="portal-form-label">
                            Priority Level <span style="color: #ef4444;">*</span>
                        </label>
                        <select class="portal-form-select" id="ticketPriority" name="priority" required>
                            <option value="Low">Low (General guidance, 24-48h SLA)</option>
                            <option value="Medium" selected>Medium (Standard operational question, 12-24h SLA)</option>
                            <option value="High">High (Impacting deliverable or timeline, 4-8h SLA)</option>
                            <option value="Urgent">Urgent (Production down, critical blocker, &lt;2h SLA)</option>
                        </select>
                    </div>
                </div>

                <div class="portal-form-group">
                    <label for="ticketMessage" class="portal-form-label">
                        Detailed Description <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea class="portal-form-textarea" id="ticketMessage" name="message" rows="6" 
                              placeholder="Please describe what happened, expected behavior, error logs, or relevant milestone details..." required></textarea>
                </div>

                <div class="portal-form-group">
                    <label for="ticketAttachment" class="portal-form-label">
                        File Attachment <span style="font-size: 0.75rem; color: var(--portal-text-muted); font-weight: normal;">(Optional - Screenshots, PDFs, ZIP archives up to 8MB)</span>
                    </label>
                    <input type="file" class="portal-form-input" id="ticketAttachment" name="attachment" 
                           accept=".pdf,.png,.jpg,.jpeg,.zip,.doc,.docx,.txt" style="padding: 7px;">
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px;">
                        Permitted formats: PDF, PNG, JPG, JPEG, ZIP, DOCX, TXT. Executable files are strictly forbidden.
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--portal-border);">
                    <a href="support.php" class="btn-portal-outline">Cancel</a>
                    <button type="submit" id="btnSubmitTicket" class="btn-portal-primary">
                        <span>Create Support Ticket</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createTicketForm');
    const submitBtn = document.getElementById('btnSubmitTicket');

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Opening Ticket...</span>';

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
                    window.showPortalToast(data.message || 'Ticket created successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = data.data.redirect_url || 'support.php';
                    }, 600);
                } else {
                    window.showPortalToast(data.message || 'Failed to open ticket. Please check input.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Create Support Ticket</span>';
                }
            })
            .catch(() => {
                window.showPortalToast('Network error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Create Support Ticket</span>';
            });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
