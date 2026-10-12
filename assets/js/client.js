/**
 * GrihGO Digital & Tech — Client Portal JavaScript Engine
 * Version: 3.0 (Part 3 - Client Portal)
 * Technology: Vanilla JavaScript ES6+, Fetch API, AJAX Helpers & DOM Controls
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Sidebar Drawer Controls
    const mobileToggle = document.getElementById('portalMobileToggle');
    const sidebar = document.getElementById('portalSidebar');
    const backdrop = document.getElementById('portalBackdrop');

    function openSidebar() {
        if (sidebar && backdrop) {
            sidebar.classList.add('open');
            backdrop.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSidebar() {
        if (sidebar && backdrop) {
            sidebar.classList.remove('open');
            backdrop.classList.remove('open');
            document.body.style.overflow = '';
        }
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    // Close sidebar on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });

    // 2. Global Portal Toast Alerts
    window.showPortalToast = function(message, type = 'success', duration = 4000) {
        let toastContainer = document.getElementById('portalToastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'portalToastContainer';
            toastContainer.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            `;
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        const isSuccess = type === 'success';
        const bg = isSuccess ? '#10b981' : (type === 'error' ? '#ef4444' : '#3b82f6');
        
        toast.style.cssText = `
            background: #ffffff;
            color: #0f172a;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 0 0 1px #e2e8f0;
            border-left: 4px solid ${bg};
            font-size: 0.88rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 280px;
            max-width: 420px;
            pointer-events: auto;
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        `;

        toast.innerHTML = `
            <span style="color: ${bg}; font-size: 1.1rem;">${isSuccess ? '✓' : (type === 'error' ? '✕' : 'ℹ')}</span>
            <span style="flex: 1;">${message}</span>
        `;

        toastContainer.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        });

        // Auto remove
        setTimeout(() => {
            toast.style.transform = 'translateY(10px)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    // 3. Mark Notification As Read Helper
    document.querySelectorAll('.btn-mark-notif-read').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const notifId = this.getAttribute('data-id');
            const notifItem = document.getElementById('notif-item-' + notifId);
            
            fetch('../api/client-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    notification_id: notifId,
                    csrf_token: window.portalCsrfToken || ''
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (notifItem) {
                        notifItem.classList.remove('notification-unread');
                        notifItem.style.opacity = '0.7';
                    }
                    this.remove();
                    window.showPortalToast('Notification marked as read.', 'success');
                }
            })
            .catch(() => {
                window.showPortalToast('Could not update notification state.', 'error');
            });
        });
    });

    // 4. Mark All Notifications As Read Helper
    const markAllBtn = document.getElementById('btnMarkAllNotifications');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('../api/client-notification-read-all.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    csrf_token: window.portalCsrfToken || ''
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-unread').forEach(el => {
                        el.classList.remove('notification-unread');
                        el.style.opacity = '0.7';
                    });
                    document.querySelectorAll('.btn-mark-notif-read').forEach(b => b.remove());
                    this.remove();
                    window.showPortalToast('All notifications marked as read.', 'success');
                }
            })
            .catch(() => {
                window.showPortalToast('Could not mark all notifications as read.', 'error');
            });
        });
    }
});
