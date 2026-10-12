<?php
/**
 * GrihGO Digital & Tech — Website & Brand Settings Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

// Super Admin / Admin role check
requireAdminRole(['super_admin', 'admin']);

$pageTitle = 'Website & Brand Settings';
$pageSection = 'settings';

$error = '';
$success = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $allowedKeys = [
            'company_name', 'tagline', 'phone', 'email', 'whatsapp', 'website',
            'office_address', 'social_instagram', 'social_facebook', 'social_linkedin', 'social_youtube', 'social_x',
            'footer_copyright', 'default_seo_title', 'default_seo_desc'
        ];

        try {
            $pdo->beginTransaction();
            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    $val = sanitizeInput($_POST[$key]);
                    
                    // Upsert setting key
                    $check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
                    $check->execute([$key]);
                    if ($check->fetchColumn() > 0) {
                        $upd = $pdo->prepare("UPDATE settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
                        $upd->execute([$val, $key]);
                    } else {
                        $ins = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                        $ins->execute([$key, $val]);
                    }
                }
            }

            logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Settings Updated', 'settings', null, "Updated website corporate settings");

            $pdo->commit();
            $success = 'Website settings and metadata saved successfully!';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Failed to save settings.';
        }
    }
}

// Fetch all current settings into key-value map
$allSettingsRaw = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Website & Corporate Settings</h1>
        <p class="page-subtitle">Configure contact channels, headquarters address, social presence, and global SEO metadata.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="admin-card" style="max-width: 800px;">
    <form method="POST" action="settings.php" class="admin-card-body">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_settings">

        <h3 style="font-size: 16px; font-weight: 700; margin-top: 0; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">
            Corporate Brand Identity
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Company Official Legal Name *</label>
                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['company_name'] ?? 'GrihGO Digital & Tech') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Brand Tagline</label>
                <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['tagline'] ?? 'Technology • Innovation • Growth') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Official Mobile *</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['phone'] ?? '8092932060') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">WhatsApp Helpline *</label>
                <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['whatsapp'] ?? '8092932060') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">General Contact Email *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['email'] ?? 'contact@grihgo.com') ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Primary Website Domain URL</label>
            <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['website'] ?? 'https://grihgodigital.com') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Registered Corporate Headquarters Address</label>
            <textarea name="office_address" class="form-control" rows="2"><?= htmlspecialchars($allSettingsRaw['office_address'] ?? 'AIIMS Patna, Phulwari Sharif, Patna, Bihar, India') ?></textarea>
        </div>

        <h3 style="font-size: 16px; font-weight: 700; margin-top: 24px; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">
            Social Channels & Public Profiles
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Instagram Profile URL</label>
                <input type="url" name="social_instagram" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['social_instagram'] ?? 'https://instagram.com/grihgodigital') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Facebook Page URL</label>
                <input type="url" name="social_facebook" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['social_facebook'] ?? 'https://facebook.com/grihgodigital') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">LinkedIn Corporate URL</label>
                <input type="url" name="social_linkedin" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['social_linkedin'] ?? 'https://linkedin.com/company/grihgodigital') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">YouTube Channel URL</label>
                <input type="url" name="social_youtube" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['social_youtube'] ?? 'https://youtube.com/@grihgodigital') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">X (Twitter) Profile URL</label>
                <input type="url" name="social_x" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['social_x'] ?? 'https://x.com/grihgodigital') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Footer Copyright Statement</label>
            <input type="text" name="footer_copyright" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['footer_copyright'] ?? '© 2026 GrihGO Digital & Tech. All Rights Reserved.') ?>">
        </div>

        <h3 style="font-size: 16px; font-weight: 700; margin-top: 24px; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">
            Default SEO Meta Tags
        </h3>

        <div class="form-group">
            <label class="form-label">Default SEO Title Tag</label>
            <input type="text" name="default_seo_title" class="form-control" value="<?= htmlspecialchars($allSettingsRaw['default_seo_title'] ?? 'GrihGO Digital & Tech | Website, Software, AI & Digital Marketing Solutions') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Default Meta Description</label>
            <textarea name="default_seo_desc" class="form-control" rows="2"><?= htmlspecialchars($allSettingsRaw['default_seo_desc'] ?? 'GrihGO Digital & Tech provides website development, software solutions, application development, AI automation, SEO, social media marketing and performance marketing services for businesses in Patna, Bihar and across India.') ?></textarea>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary btn-lg">Save Website Settings</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
