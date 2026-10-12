<?php
/**
 * GrihGO Digital & Tech — Client Portal Header Component
 * Version: 3.0 (Part 3 - Client Portal)
 */

if (!defined('GR_PORTAL_ACTIVE')) {
    define('GR_PORTAL_ACTIVE', true);
}

// Calculate client initials for avatar
$clientName = $clientName ?? $client['contact_person'] ?? $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Client';
$nameParts = explode(' ', trim($clientName));
$initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($portalPageTitle ?? 'Client Portal', ENT_QUOTES, 'UTF-8') ?> | GrihGO Digital & Tech</title>
    <meta name="csrf-token" content="<?= htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo/logo.png">
    
    <!-- Base Typography & Core Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Client Portal Dedicated SaaS Stylesheet -->
    <link rel="stylesheet" href="../assets/css/client.css">
    
    <script>
        window.portalCsrfToken = "<?= htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>";
    </script>
</head>
<body class="portal-body">
    <div class="portal-layout">
        <!-- Backdrop for mobile drawer -->
        <div class="portal-backdrop" id="portalBackdrop"></div>

        <?php include __DIR__ . '/sidebar.php'; ?>

        <div class="portal-main">
            <!-- Top Dashboard Header -->
            <header class="portal-header">
                <div class="portal-header-left">
                    <button type="button" class="portal-mobile-toggle" id="portalMobileToggle" aria-label="Toggle navigation menu">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <h1 class="portal-header-title"><?= htmlspecialchars($portalHeaderTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?></h1>
                </div>

                <div class="portal-header-actions">
                    <!-- Notifications Button with Badge -->
                    <a href="notifications.php" class="portal-header-btn" title="View Notifications" aria-label="Notifications">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <?php if (!empty($unreadNotificationsCount) && $unreadNotificationsCount > 0): ?>
                            <span class="portal-header-notif-count"><?= $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Profile Chip -->
                    <a href="profile.php" class="portal-client-chip" title="Account Profile">
                        <div class="portal-user-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                            <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <span style="font-size: 0.85rem; font-weight: 600; padding-right: 6px; display: none; @media(min-width: 600px){display: inline;}">
                            <?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </a>

                    <!-- Logout Button -->
                    <a href="logout.php" class="portal-header-btn" title="Sign Out" onclick="return confirm('Are you sure you want to log out of your client portal?');">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span style="display: none; @media(min-width: 768px){display: inline;}">Logout</span>
                    </a>
                </div>
            </header>

            <!-- Page Content Body -->
            <main class="portal-content">
