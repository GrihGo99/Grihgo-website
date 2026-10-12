<?php
http_response_code(403);
$page_title = '403 - Access Forbidden | GrihGO Digital & Tech';
$meta_desc = 'Access to this resource is strictly restricted.';
$base_url = './';
include 'includes/header.php';
?>

<section class="section" style="min-height: 65vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 100px 20px;">
    <div class="container" style="max-width: 650px;">
        <div style="font-size: 5rem; font-weight: 800; line-height: 1; margin-bottom: 16px; background: linear-gradient(135deg, #ef4444, #dc2626); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            403
        </div>
        <h1 style="font-size: 2rem; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
            Access Forbidden
        </h1>
        <p style="font-size: 1.1rem; color: #64748b; line-height: 1.6; margin-bottom: 32px;">
            You do not have administrative or authorization clearance to view this resource. If you believe this is an error, please log into your designated portal.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="login.php" class="btn btn-primary btn-lg">
                <span>Client Login</span>
            </a>
            <a href="manager/login.php" class="btn btn-secondary btn-lg">
                <span>Manager Portal</span>
            </a>
            <a href="index.php" class="btn btn-ghost btn-lg">
                <span>Return to Homepage</span>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
