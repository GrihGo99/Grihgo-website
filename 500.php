<?php
http_response_code(500);
$page_title = '500 - Server Error | GrihGO Digital & Tech';
$meta_desc = 'An internal system exception occurred. Our engineers are investigating.';
$base_url = './';
include 'includes/header.php';
?>

<section class="section" style="min-height: 65vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 100px 20px;">
    <div class="container" style="max-width: 650px;">
        <div style="font-size: 5rem; font-weight: 800; line-height: 1; margin-bottom: 16px; background: linear-gradient(135deg, #f59e0b, #d97706); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            500
        </div>
        <h1 style="font-size: 2rem; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
            Internal System Exception
        </h1>
        <p style="font-size: 1.1rem; color: #64748b; line-height: 1.6; margin-bottom: 32px;">
            We experienced an unexpected server processing error. Our engineering team has logged the event. Please try again shortly or contact support.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="index.php" class="btn btn-primary btn-lg">
                <span>Return to Homepage</span>
            </a>
            <a href="contact.php" class="btn btn-secondary btn-lg">
                <span>Contact Tech Support</span>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
