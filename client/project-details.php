<?php
/**
 * GrihGO Digital & Tech — Project Details & Milestone Deep-Dive
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$projectId = (int)($_GET['id'] ?? 0);

if ($projectId <= 0) {
    header('Location: projects.php');
    exit;
}

$pdo = Database::getConnection();

// Strict Ownership Query: client_id MUST match logged-in client
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :pid AND client_id = :cid LIMIT 1");
$stmt->execute([':pid' => $projectId, ':cid' => $clientId]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    // Unauthorized or not found
    $portalPageTitle = 'Project Not Found';
    $portalHeaderTitle = 'Access Notice';
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="portal-card">
        <div class="portal-empty-state">
            <div class="portal-empty-icon" style="color: #ef4444; background: #fef2f2;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            </div>
            <div class="portal-empty-title">Project Not Found or Access Denied</div>
            <p class="portal-empty-desc">The requested project ID does not exist or does not belong to your authenticated organization.</p>
            <a href="projects.php" class="btn-portal-primary">&larr; Return to My Projects</a>
        </div>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$portalPageTitle = $project['name'];
$portalHeaderTitle = 'Project Details';
$current_portal_page = 'project-details';

// 1. Fetch Project Tasks
$stmtTasks = $pdo->prepare("SELECT * FROM project_tasks WHERE project_id = :pid ORDER BY CASE WHEN status = 'Completed' THEN 2 ELSE 1 END, due_date ASC");
$stmtTasks->execute([':pid' => $projectId]);
$tasks = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);

// 2. Fetch Project Documents (Scoped to client_id)
$stmtDocs = $pdo->prepare("SELECT * FROM documents WHERE project_id = :pid AND client_id = :cid ORDER BY created_at DESC");
$stmtDocs->execute([':pid' => $projectId, ':cid' => $clientId]);
$documents = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch Project Invoices (Scoped to client_id)
$stmtInvoices = $pdo->prepare("SELECT * FROM invoices WHERE project_id = :pid AND client_id = :cid ORDER BY invoice_date DESC");
$stmtInvoices->execute([':pid' => $projectId, ':cid' => $clientId]);
$invoices = $stmtInvoices->fetchAll(PDO::FETCH_ASSOC);

// 4. Fetch Project Messages
$stmtMsgs = $pdo->prepare("SELECT * FROM messages WHERE project_id = :pid AND client_id = :cid ORDER BY created_at ASC");
$stmtMsgs->execute([':pid' => $projectId, ':cid' => $clientId]);
$messages = $stmtMsgs->fetchAll(PDO::FETCH_ASSOC);

// Timeline Stage Calculation based on progress percentage
$progress = (int)$project['progress'];
$stage1 = $progress >= 15 ? 'completed' : 'active';
$stage2 = $progress >= 35 ? 'completed' : ($progress >= 15 ? 'active' : '');
$stage3 = $progress >= 70 ? 'completed' : ($progress >= 35 ? 'active' : '');
$stage4 = $progress >= 90 ? 'completed' : ($progress >= 70 ? 'active' : '');
$stage5 = $progress >= 100 ? 'completed' : ($progress >= 90 ? 'active' : '');

include __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="projects.php" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--portal-primary); text-decoration: none; font-weight: 600;">
        &larr; Back to All Projects
    </a>
</div>

<!-- Project Overview Card -->
<div class="portal-card">
    <div class="portal-card-header" style="flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--portal-primary); text-transform: uppercase;">
                <?= htmlspecialchars($project['service'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h2 style="font-size: 1.4rem; font-weight: 700; margin: 4px 0 0 0; color: var(--portal-text-main);">
                <?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') ?>
            </h2>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <span class="badge-status <?= $project['status'] === 'Completed' ? 'badge-completed' : 'badge-in-progress' ?>" style="font-size: 0.85rem; padding: 6px 14px;">
                <?= htmlspecialchars($project['status'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    </div>

    <div class="portal-card-body">
        <p style="font-size: 0.95rem; color: #334155; line-height: 1.6; margin-top: 0;">
            <?= nl2br(htmlspecialchars($project['description'] ?? 'No extended description recorded.', ENT_QUOTES, 'UTF-8')) ?>
        </p>

        <!-- Visual Milestone Timeline (Section 11) -->
        <div style="margin: 32px 0 24px 0;">
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--portal-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                Project Delivery Lifecycle
            </div>
            <div class="project-timeline">
                <div class="timeline-step <?= $stage1 ?>">
                    <div class="timeline-step-circle">01</div>
                    <div class="timeline-step-title">Requirement</div>
                </div>
                <div class="timeline-step <?= $stage2 ?>">
                    <div class="timeline-step-circle">02</div>
                    <div class="timeline-step-title">UI/UX Design</div>
                </div>
                <div class="timeline-step <?= $stage3 ?>">
                    <div class="timeline-step-circle">03</div>
                    <div class="timeline-step-title">Development</div>
                </div>
                <div class="timeline-step <?= $stage4 ?>">
                    <div class="timeline-step-circle">04</div>
                    <div class="timeline-step-title">Testing &amp; QA</div>
                </div>
                <div class="timeline-step <?= $stage5 ?>">
                    <div class="timeline-step-circle">05</div>
                    <div class="timeline-step-title">Deployment</div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div style="background: #f8fafc; border: 1px solid var(--portal-border); border-radius: 10px; padding: 18px 24px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 0.88rem; font-weight: 600; color: var(--portal-text-main);">Current Milestone Progress</span>
                <span style="font-size: 1.1rem; font-weight: 800; color: var(--portal-primary);"><?= $progress ?>%</span>
            </div>
            <div class="portal-progress-bar-bg" style="height: 10px;">
                <div class="portal-progress-bar-fill" style="width: <?= $progress ?>%;"></div>
            </div>
        </div>

        <!-- Meta Details Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 0.85rem;">
            <div style="padding: 12px; background: #f8fafc; border-radius: 8px;">
                <span style="color: var(--portal-text-muted); display: block; font-size: 0.78rem;">Start Date</span>
                <strong style="color: var(--portal-text-main); font-size: 0.95rem;"><?= htmlspecialchars($project['start_date'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div style="padding: 12px; background: #f8fafc; border-radius: 8px;">
                <span style="color: var(--portal-text-muted); display: block; font-size: 0.78rem;">Expected Completion</span>
                <strong style="color: var(--portal-text-main); font-size: 0.95rem;"><?= htmlspecialchars($project['expected_completion'] ?? 'Continuous', ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div style="padding: 12px; background: #f8fafc; border-radius: 8px;">
                <span style="color: var(--portal-text-muted); display: block; font-size: 0.78rem;">Assigned Engineering Team</span>
                <strong style="color: var(--portal-text-main); font-size: 0.95rem;"><?= htmlspecialchars($project['assigned_team'] ?? 'GrihGO Core Tech', ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div style="padding: 12px; background: #f8fafc; border-radius: 8px;">
                <span style="color: var(--portal-text-muted); display: block; font-size: 0.78rem;">Last Updated</span>
                <strong style="color: var(--portal-text-main); font-size: 0.95rem;"><?= date('d M Y', strtotime($project['updated_at'])) ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- Project Tasks (Section 12) -->
<div class="portal-card">
    <div class="portal-card-header">
        <h3 class="portal-card-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Project Tasks &amp; Milestones (<?= count($tasks) ?>)</span>
        </h3>
    </div>
    <div class="portal-card-body" style="padding: 0;">
        <?php if (empty($tasks)): ?>
            <div class="portal-empty-state">
                <div class="portal-empty-title">No Tasks Assigned Yet</div>
                <p class="portal-empty-desc">Sprint tasks are currently being scheduled by the engineering lead.</p>
            </div>
        <?php else: ?>
            <div class="portal-table-container">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Task Name &amp; Description</th>
                            <th>Assigned Team</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--portal-text-main);"><?= htmlspecialchars($task['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if (!empty($task['description'])): ?>
                                        <div style="font-size: 0.78rem; color: var(--portal-text-muted); margin-top: 2px;">
                                            <?= htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.82rem; color: #475569;">
                                    <?= htmlspecialchars($task['assigned_team'] ?? 'GrihGO Tech', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <span class="badge-status badge-<?= strtolower($task['priority']) ?>">
                                        <?= htmlspecialchars($task['priority'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.82rem; color: #475569;">
                                    <?= htmlspecialchars($task['due_date'] ?? 'Flexible', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <span class="badge-status <?= $task['status'] === 'Completed' ? 'badge-completed' : ($task['status'] === 'Blocked' ? 'badge-urgent' : 'badge-in-progress') ?>">
                                        <?= htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Project Documents & Invoices Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px; margin-bottom: 24px;">
    <!-- Project Documents -->
    <div class="portal-card" style="margin-bottom: 0;">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                <span>Project Documents (<?= count($documents) ?>)</span>
            </h3>
        </div>
        <div class="portal-card-body" style="padding: 16px 20px;">
            <?php if (empty($documents)): ?>
                <div class="portal-empty-state" style="padding: 24px;">
                    <div class="portal-empty-title">No Documents Yet</div>
                    <p class="portal-empty-desc">Project contracts, architecture specs, and deliverables will appear here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1px solid var(--portal-border); border-radius: 8px; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--portal-primary-light); color: var(--portal-primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem;">
                                PDF
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.88rem; color: var(--portal-text-main);">
                                    <?= htmlspecialchars($doc['name'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--portal-text-muted);">
                                    <?= htmlspecialchars($doc['type'], ENT_QUOTES, 'UTF-8') ?> &bull; <?= htmlspecialchars($doc['file_size'] ?? '1.2 MB', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        </div>
                        <a href="documents.php?download=<?= (int)$doc['id'] ?>" class="btn-portal-outline btn-portal-sm" title="Download Document">
                            Download
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Project Invoices -->
    <div class="portal-card" style="margin-bottom: 0;">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                <span>Project Invoices (<?= count($invoices) ?>)</span>
            </h3>
        </div>
        <div class="portal-card-body" style="padding: 16px 20px;">
            <?php if (empty($invoices)): ?>
                <div class="portal-empty-state" style="padding: 24px;">
                    <div class="portal-empty-title">No Invoices Connected</div>
                    <p class="portal-empty-desc">Billing schedules for this project will be reflected here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($invoices as $inv): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1px solid var(--portal-border); border-radius: 8px; margin-bottom: 10px;">
                        <div>
                            <strong style="color: var(--portal-primary); font-size: 0.9rem;">
                                <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                            </strong>
                            <div style="font-size: 0.78rem; color: var(--portal-text-muted); margin-top: 2px;">
                                Date: <?= htmlspecialchars($inv['invoice_date'], ENT_QUOTES, 'UTF-8') ?> &bull; Total: <strong>₹<?= number_format((float)$inv['total_amount'], 2) ?></strong>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="badge-status badge-<?= strtolower(str_replace(' ', '-', $inv['status'])) ?>">
                                <?= htmlspecialchars($inv['status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <a href="invoices.php?id=<?= (int)$inv['id'] ?>" class="btn-portal-outline btn-portal-sm">
                                View
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Project Communication / Direct Messages (Section 11 & 21) -->
<div class="portal-card">
    <div class="portal-card-header">
        <h3 class="portal-card-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span>Project Communication Stream</span>
        </h3>
    </div>
    <div class="portal-card-body">
        <div class="ticket-chat-container" id="projectMessagesStream" style="margin-bottom: 20px;">
            <?php if (empty($messages)): ?>
                <div style="text-align: center; padding: 24px; color: var(--portal-text-muted); font-size: 0.88rem;">
                    No communications logged yet for this project. Send a message to coordinate with the GrihGO engineering leads.
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isClient = ($msg['sender_type'] === 'client'); ?>
                    <div class="chat-bubble <?= $isClient ? 'client' : 'staff' ?>">
                        <div class="chat-bubble-meta">
                            <strong><?= htmlspecialchars($msg['sender_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span>&bull; <?= date('d M, h:i A', strtotime($msg['created_at'])) ?></span>
                        </div>
                        <div><?= nl2br(htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Send Message Form -->
        <form id="projectMsgForm" method="POST" action="../api/client-message-send.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="project_id" value="<?= $projectId ?>">
            <div style="display: flex; gap: 10px;">
                <input type="text" name="message" id="msgInput" class="portal-form-input" placeholder="Type an update or question regarding this project..." required style="flex: 1;">
                <button type="submit" id="btnSendMsg" class="btn-portal-primary" style="white-space: nowrap;">
                    <span>Send Message</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const msgForm = document.getElementById('projectMsgForm');
    const msgInput = document.getElementById('msgInput');
    const stream = document.getElementById('projectMessagesStream');
    const sendBtn = document.getElementById('btnSendMsg');

    if (msgForm && sendBtn) {
        msgForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = msgInput.value.trim();
            if (!text) return;

            sendBtn.disabled = true;
            const formData = new FormData(msgForm);

            fetch(msgForm.action, {
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
                    // Append bubble
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble client';
                    bubble.innerHTML = `
                        <div class="chat-bubble-meta">
                            <strong><?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') ?></strong>
                            <span>&bull; Just now</span>
                        </div>
                        <div>${text.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>
                    `;
                    stream.appendChild(bubble);
                    msgInput.value = '';
                    stream.scrollTop = stream.scrollHeight;
                    window.showPortalToast('Message dispatched.', 'success');
                } else {
                    window.showPortalToast(data.message || 'Could not send message.', 'error');
                }
                sendBtn.disabled = false;
            })
            .catch(() => {
                window.showPortalToast('Network error occurred.', 'error');
                sendBtn.disabled = false;
            });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
