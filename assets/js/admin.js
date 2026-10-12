/**
 * GrihGO Digital & Tech — Admin Console Interactive Script
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 * Technology: Pure Vanilla JavaScript (ES6+), Fetch API, No Frameworks
 */

document.addEventListener('DOMContentLoaded', () => {
    initSidebarToggle();
    initConfirmActions();
    initInvoiceCalculators();
});

/**
 * Mobile/Tablet Sidebar Drawer Navigation
 */
function initSidebarToggle() {
    const toggleBtn = document.getElementById('adminMobileToggle');
    const sidebar = document.getElementById('adminSidebar');
    if (!toggleBtn || !sidebar) return;

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.toggle('open');
    });

    document.addEventListener('click', (e) => {
        if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
            sidebar.classList.remove('open');
        }
    });
}

/**
 * Destructive Action Confirmation
 */
function initConfirmActions() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const message = el.getAttribute('data-confirm') || 'Are you sure you want to proceed with this action?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Modal Dialog Helpers
 */
window.openAdminModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closeAdminModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

/**
 * Interactive Toast Notification
 */
window.showAdminToast = function(message, type = 'success') {
    let container = document.getElementById('adminToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'adminToastContainer';
        container.className = 'admin-toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `admin-toast admin-toast-${type}`;
    
    let icon = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`;
    if (type === 'danger' || type === 'error') {
        icon = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`;
    }

    toast.innerHTML = `
        <div style="color: ${type === 'danger' ? 'var(--admin-danger)' : 'var(--admin-accent)'}">${icon}</div>
        <div style="flex: 1; font-weight: 500;">${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};

/**
 * Real-time Invoice Financial Breakdown Calculator
 */
function initInvoiceCalculators() {
    const invoiceTable = document.getElementById('invoiceItemsTable');
    if (!invoiceTable) return;

    function recalculate() {
        let subtotal = 0;
        const rows = invoiceTable.querySelectorAll('tbody tr.item-row');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const rate = parseFloat(row.querySelector('.item-rate')?.value) || 0;
            const rowTotal = Math.max(0, qty * rate);
            const totalInput = row.querySelector('.item-total');
            if (totalInput) totalInput.value = rowTotal.toFixed(2);
            subtotal += rowTotal;
        });

        const subtotalEl = document.getElementById('invSubtotal');
        if (subtotalEl) subtotalEl.value = subtotal.toFixed(2);

        const taxRate = parseFloat(document.getElementById('invTaxPercent')?.value) || 18.0;
        const taxAmount = (subtotal * taxRate) / 100;
        const taxEl = document.getElementById('invTaxAmount');
        if (taxEl) taxEl.value = taxAmount.toFixed(2);

        const discount = parseFloat(document.getElementById('invDiscount')?.value) || 0;
        const grandTotal = Math.max(0, subtotal + taxAmount - discount);
        
        const grandTotalEl = document.getElementById('invGrandTotal');
        if (grandTotalEl) grandTotalEl.value = grandTotal.toFixed(2);

        const grandDisplay = document.getElementById('invGrandDisplay');
        if (grandDisplay) grandDisplay.innerText = '₹' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    }

    invoiceTable.addEventListener('input', recalculate);
    document.getElementById('invTaxPercent')?.addEventListener('input', recalculate);
    document.getElementById('invDiscount')?.addEventListener('input', recalculate);

    // Add item row dynamically
    const addRowBtn = document.getElementById('addInvoiceRowBtn');
    if (addRowBtn) {
        addRowBtn.addEventListener('click', () => {
            const tbody = invoiceTable.querySelector('tbody');
            const newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.innerHTML = `
                <td><input type="text" name="item_desc[]" class="form-control" placeholder="Item description" required></td>
                <td><input type="number" name="item_qty[]" class="form-control item-qty" value="1" min="1" step="1" required style="width: 80px;"></td>
                <td><input type="number" name="item_rate[]" class="form-control item-rate" value="0" min="0" step="0.01" required style="width: 130px;"></td>
                <td><input type="text" name="item_total[]" class="form-control item-total" value="0.00" readonly style="width: 130px; background: #f8fafc;"></td>
                <td style="text-align: center;"><button type="button" class="btn btn-icon btn-remove-row" title="Remove Item"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button></td>
            `;
            tbody.appendChild(newRow);
            recalculate();
        });
    }

    // Remove item row
    invoiceTable.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-remove-row');
        if (btn) {
            const rows = invoiceTable.querySelectorAll('tbody tr.item-row');
            if (rows.length > 1) {
                btn.closest('tr').remove();
                recalculate();
            } else {
                alert('At least one line item is required.');
            }
        }
    });

    // Initial calculation
    recalculate();
}
