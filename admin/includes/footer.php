<?php
/**
 * GrihGO Digital & Tech — Admin Layout Footer
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */
?>
            </main>
            <footer style="padding: 18px 28px; border-top: 1px solid var(--admin-border); background: #ffffff; font-size: 12px; color: var(--admin-text-muted); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div>
                    &copy; <?= date('Y') ?> <strong>GrihGO Digital & Tech</strong>. All rights reserved.
                </div>
                <div>
                    Connected Driver: <code><?= htmlspecialchars(Database::getActiveDriver()) ?></code> &bull; Secure Session Active
                </div>
            </footer>
        </div>
    </div>

    <!-- Modals & Toasts Container -->
    <div id="adminToastContainer" class="admin-toast-container"></div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
