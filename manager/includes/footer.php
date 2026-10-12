<?php
/**
 * GrihGO Digital & Tech — Dedicated Manager Portal Footer
 * Version: 4.5 (Antigravity 2.0 Integration)
 */
?>
            </main>
            <footer style="padding: 18px 28px; border-top: 1px solid var(--admin-border); background: #ffffff; font-size: 12px; color: var(--admin-text-muted); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div>
                    &copy; <?= date('Y') ?> <strong>GrihGO Digital &amp; Tech</strong> &bull; Manager Workspace.
                </div>
                <div>
                    Driver: <code><?= htmlspecialchars(Database::getActiveDriver()) ?></code> &bull; Role: <strong>Manager</strong>
                </div>
            </footer>
        </div>
    </div>

    <!-- Modals & Toasts Container -->
    <div id="adminToastContainer" class="admin-toast-container"></div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
