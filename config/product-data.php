<?php
/**
 * GrihGO Digital & Tech — Reusable Product Catalog
 * Version: 2.0 (Part 2)
 * 14 Enterprise SaaS and Business Systems
 */

function getProductCatalog(): array {
    return [
        'whatsapp-crm' => [
            'slug'        => 'whatsapp-crm',
            'name'        => 'GrihGO WhatsApp CRM',
            'category'    => 'crm',
            'category_name' => 'CRM & Communication',
            'short_desc'  => 'Centralized multi-agent WhatsApp team inbox, automated lead routing, quick reply templates, and campaign broadcasting on your official business number.',
            'icon'        => '💬',
            'icon_bg'     => 'var(--secondary-green-light)',
            'icon_color'  => 'var(--secondary-green)',
            'badge'       => 'Flagship SaaS',
            'badge_style' => 'background: rgba(16, 185, 129, 0.15); color: var(--secondary-green); font-weight: 700;',
            'file'        => 'products/whatsapp-crm.php'
        ],
        'billing-software' => [
            'slug'        => 'billing-software',
            'name'        => 'GST Billing Software',
            'category'    => 'finance',
            'category_name' => 'Finance & ERP',
            'short_desc'  => 'Fast barcode POS billing, GSTR-1/3B automated reports, professional customized invoice templates, and credit ledger management.',
            'icon'        => '🧾',
            'icon_bg'     => '#fef3c7',
            'icon_color'  => '#d97706',
            'badge'       => 'GST Ready',
            'badge_style' => '',
            'file'        => 'products/billing-software.php'
        ],
        'crm' => [
            'slug'        => 'crm',
            'name'        => 'Enterprise CRM',
            'category'    => 'crm',
            'category_name' => 'CRM & Communication',
            'short_desc'  => 'Sales pipeline visualization, lead scoring, deal velocity tracking, activity scheduling, and cross-channel customer history.',
            'icon'        => '📊',
            'icon_bg'     => '#eff6ff',
            'icon_color'  => '#2563eb',
            'badge'       => 'High Demand',
            'badge_style' => '',
            'file'        => 'products/crm.php'
        ],
        'inventory' => [
            'slug'        => 'inventory',
            'name'        => 'Inventory Management',
            'category'    => 'ops',
            'category_name' => 'Operations & Team',
            'short_desc'  => 'Real-time multi-warehouse stock monitoring, automated low-stock reorder alerts, batch/expiry tracking, and barcode scanning.',
            'icon'        => '📦',
            'icon_bg'     => '#f0fdf4',
            'icon_color'  => '#16a34a',
            'badge'       => 'Multi-Warehouse',
            'badge_style' => '',
            'file'        => 'products/inventory.php'
        ],
        'erp' => [
            'slug'        => 'erp',
            'name'        => 'Enterprise ERP Platform',
            'category'    => 'finance',
            'category_name' => 'Finance & ERP',
            'short_desc'  => 'Unified enterprise platform coordinating multi-branch procurement, manufacturing, accounts, inventory, and supply chain logistics.',
            'icon'        => '⚙️',
            'icon_bg'     => '#fdf2f8',
            'icon_color'  => '#db2777',
            'badge'       => 'Enterprise Grade',
            'badge_style' => '',
            'file'        => 'products/erp.php'
        ],
        'hr-payroll' => [
            'slug'        => 'hr-payroll',
            'name'        => 'HR Management & Payroll',
            'category'    => 'ops',
            'category_name' => 'Operations & Team',
            'short_desc'  => 'Automated salary calculation, biometric attendance integration, PF/ESI statutory compliance, and employee self-service portal.',
            'icon'        => '👥',
            'icon_bg'     => '#f5f3ff',
            'icon_color'  => '#7c3aed',
            'badge'       => 'Automated Compliance',
            'badge_style' => '',
            'file'        => 'products/hr-payroll.php'
        ],
        'school-management' => [
            'slug'        => 'school-management',
            'name'        => 'School Management System',
            'category'    => 'sector',
            'category_name' => 'Industry Specific',
            'short_desc'  => 'End-to-end student lifecycle management, fee collection gateway, exam report cards, teacher timetables, and parent mobile app.',
            'icon'        => '🎓',
            'icon_bg'     => '#fff7ed',
            'icon_color'  => '#ea580c',
            'badge'       => 'Education Tech',
            'badge_style' => '',
            'file'        => 'products/school-management.php'
        ],
        'hospital-management' => [
            'slug'        => 'hospital-management',
            'name'        => 'Hospital Management Suite',
            'category'    => 'sector',
            'category_name' => 'Industry Specific',
            'short_desc'  => 'Electronic Medical Records (EMR), OPD/IPD bed management, pharmacy inventory, lab diagnostic dispatch, and doctor scheduling.',
            'icon'        => '🏥',
            'icon_bg'     => '#fef2f2',
            'icon_color'  => '#dc2626',
            'badge'       => 'Healthcare Tech',
            'badge_style' => '',
            'file'        => 'products/hospital-management.php'
        ],
        'restaurant-management' => [
            'slug'        => 'restaurant-management',
            'name'        => 'Restaurant POS & Management',
            'category'    => 'sector',
            'category_name' => 'Industry Specific',
            'short_desc'  => 'Touch POS billing, Kitchen Order Ticket (KOT) automation, table reservation mapping, inventory recipe management, and swiggy/zomato integrations.',
            'icon'        => '🍽️',
            'icon_bg'     => '#ecfdf5',
            'icon_color'  => '#059669',
            'badge'       => 'F&B Solution',
            'badge_style' => '',
            'file'        => 'products/restaurant-management.php'
        ],
        'real-estate-crm' => [
            'slug'        => 'real-estate-crm',
            'name'        => 'Real Estate CRM',
            'category'    => 'crm',
            'category_name' => 'CRM & Communication',
            'short_desc'  => 'Property inventory allocation, site visit scheduling, channel partner payouts, automated WhatsApp follow-ups, and payment milestone tracking.',
            'icon'        => '🏢',
            'icon_bg'     => '#eef2ff',
            'icon_color'  => '#4f46e5',
            'badge'       => 'Realty Suite',
            'badge_style' => '',
            'file'        => 'products/real-estate-crm.php'
        ],
        'lead-management' => [
            'slug'        => 'lead-management',
            'name'        => 'Lead Management Platform',
            'category'    => 'crm',
            'category_name' => 'CRM & Communication',
            'short_desc'  => 'Unified capture from Meta Ads, Google Ads, portals, and websites with automated agent assignment and instantaneous notifications.',
            'icon'        => '🎯',
            'icon_bg'     => '#fffbeb',
            'icon_color'  => '#b45309',
            'badge'       => 'Growth Engine',
            'badge_style' => '',
            'file'        => 'products/lead-management.php'
        ],
        'project-management' => [
            'slug'        => 'project-management',
            'name'        => 'Project Management Suite',
            'category'    => 'ops',
            'category_name' => 'Operations & Team',
            'short_desc'  => 'Kanban sprint boards, Gantt timelines, resource capacity tracking, client milestone signoffs, and timesheet management.',
            'icon'        => '📋',
            'icon_bg'     => '#f0fdfa',
            'icon_color'  => '#0d9488',
            'badge'       => 'Productivity',
            'badge_style' => '',
            'file'        => 'products/project-management.php'
        ],
        'customer-support' => [
            'slug'        => 'customer-support',
            'name'        => 'Customer Support System',
            'category'    => 'crm',
            'category_name' => 'CRM & Communication',
            'short_desc'  => 'Omnichannel ticketing desk, SLA breach tracking, interactive self-service knowledge base, and automated CSAT surveys.',
            'icon'        => '🎧',
            'icon_bg'     => '#faf5ff',
            'icon_color'  => '#9333ea',
            'badge'       => 'Omnichannel Desk',
            'badge_style' => '',
            'file'        => 'products/customer-support.php'
        ],
        'appointment-management' => [
            'slug'        => 'appointment-management',
            'name'        => 'Appointment Management',
            'category'    => 'ops',
            'category_name' => 'Operations & Team',
            'short_desc'  => 'Automated online calendar booking, two-way Google Calendar sync, SMS/WhatsApp appointment reminders, and buffer management.',
            'icon'        => '📅',
            'icon_bg'     => '#fdf4ff',
            'icon_color'  => '#c026d3',
            'badge'       => 'Smart Scheduler',
            'badge_style' => '',
            'file'        => 'products/appointment-management.php'
        ]
    ];
}

/**
 * Find product by slug or name.
 */
function findProduct(string $identifier): ?array {
    $catalog = getProductCatalog();
    $slug = strtolower(trim($identifier));
    
    if (isset($catalog[$slug])) {
        return $catalog[$slug];
    }
    
    foreach ($catalog as $prod) {
        if (strcasecmp($prod['name'], $identifier) === 0 || str_contains(strtolower($prod['name']), $slug)) {
            return $prod;
        }
    }
    
    return null;
}
