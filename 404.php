<?php
http_response_code(404);
$page_title = '404 - Page Not Found | GrihGO Digital & Tech';
$meta_desc = 'The requested page could not be found on GrihGO Digital & Tech.';
$base_url = './';
include 'includes/header.php';
?>

<section class="section" style="min-height: 65vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 100px 20px;">
    <div class="container" style="max-width: 650px;">
        <div style="font-size: 5rem; font-weight: 800; line-height: 1; margin-bottom: 16px; background: linear-gradient(135deg, #0284c7, #2563eb); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            404
        </div>
        <h1 style="font-size: 2rem; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
            Page Not Found
        </h1>
        <p style="font-size: 1.1rem; color: #64748b; line-height: 1.6; margin-bottom: 32px;">
            The page you are looking for might have been moved, renamed, or is temporarily unavailable. Please explore our services or return to the homepage.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="index.php" class="btn btn-primary btn-lg">
                <span>Return to Homepage</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <a href="services.php" class="btn btn-secondary btn-lg">
                <span>Explore Services</span>
            </a>
            <a href="contact.php" class="btn btn-ghost btn-lg">
                <span>Contact Support</span>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
