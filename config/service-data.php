<?php
/**
 * GrihGO Digital & Tech — Reusable Service Catalog
 * Version: 2.0 (Part 2)
 * 6 Core IT & Technology Capabilities
 */

function getServiceCatalog(): array {
    return [
        'website-development' => [
            'slug'       => 'website-development',
            'name'       => 'Website Development',
            'tagline'    => 'Enterprise Websites & Web Applications',
            'short_desc' => 'We engineer high-performance corporate portals, bespoke e-commerce platforms, and scalable web applications built with clean code and high Core Web Vitals.',
            'icon'       => '🌐',
            'file'       => 'services/website-development.php',
            'features'   => [
                'Core Web Vitals & Google PageSpeed Optimized',
                'Cross-Device Responsive Precision (Mobile to 4K)',
                'SEO-Friendly Semantic HTML5 Architecture',
                'Custom Backend APIs & Secure CMS Solutions'
            ]
        ],
        'application-development' => [
            'slug'       => 'application-development',
            'name'       => 'Application Development',
            'tagline'    => 'Native & Cross-Platform Mobile Apps',
            'short_desc' => 'High-performance iOS, Android, and cross-platform enterprise mobile applications architected for maximum stability, user retention, and speed.',
            'icon'       => '📱',
            'file'       => 'services/application-development.php',
            'features'   => [
                'Native iOS (Swift) & Android (Kotlin) Precision',
                'High-Velocity Flutter & React Native Engineering',
                'Offline-First Synchronized Database Caching',
                'Enterprise Push Notifications & Biometric Security'
            ]
        ],
        'software-development' => [
            'slug'       => 'software-development',
            'name'       => 'Custom Software Development',
            'tagline'    => 'Tailored Enterprise Systems & ERPs',
            'short_desc' => 'Tailored business software, microservices, and database systems built around unique operational workflows to eliminate business bottlenecks.',
            'icon'       => '💻',
            'file'       => 'services/software-development.php',
            'features'   => [
                'Custom Architecture Built to Your Business Workflows',
                'High-Concurrency Relational & NoSQL Database Design',
                'Third-Party API & Legacy System Integrations',
                'Multi-Tenant Cloud Deployments with Enterprise RBAC'
            ]
        ],
        'ai-solutions' => [
            'slug'       => 'ai-solutions',
            'name'       => 'AI Solutions & Chatbots',
            'tagline'    => 'Intelligent Automation & Smart Agents',
            'short_desc' => 'Conversational AI assistants, intelligent WhatsApp bot workflows, automated document processing, and predictive analytics models.',
            'icon'       => '🤖',
            'file'       => 'services/ai-solutions.php',
            'features'   => [
                '24/7 Conversational AI & Customer Support Bots',
                'Predictive Analytics & Enterprise Forecasting Models',
                'Automated Document OCR & Invoice Data Extraction',
                'Secure Enterprise LLM Integrations'
            ]
        ],
        'digital-marketing' => [
            'slug'       => 'digital-marketing',
            'name'       => 'Digital Marketing & Growth',
            'tagline'    => 'Performance Marketing & Multi-Channel ROI',
            'short_desc' => 'Data-backed customer acquisition campaigns across Google Ads, Meta Ads, and LinkedIn targeting verified high-intent business buyers.',
            'icon'       => '📈',
            'file'       => 'services/digital-marketing.php',
            'features'   => [
                'High-Intent Google Search & Performance Max Ads',
                'Meta & LinkedIn Precision B2B Audience Targeting',
                'End-to-End Conversion Funnel Optimization',
                'Real-Time Transparent ROI & CAC Analytics Reporting'
            ]
        ],
        'seo' => [
            'slug'       => 'seo',
            'name'       => 'Search Engine Optimization (SEO)',
            'tagline'    => 'Technical SEO & High-Intent Keyword Dominance',
            'short_desc' => 'Technical search audits, high-intent commercial keyword rankings, authoritative backlink building, and sustainable organic traffic growth.',
            'icon'       => '🔍',
            'file'       => 'services/seo.php',
            'features'   => [
                'Comprehensive Technical Audits & Core Vitals Remediation',
                'High-Commercial-Intent B2B Keyword Strategy',
                'High-Authority White-Hat Digital PR & Backlinks',
                'Multi-Location Local SEO & Google Business Optimization'
            ]
        ]
    ];
}

/**
 * Find service by slug or name.
 */
function findService(string $identifier): ?array {
    $catalog = getServiceCatalog();
    $slug = strtolower(trim($identifier));
    
    if (isset($catalog[$slug])) {
        return $catalog[$slug];
    }
    
    foreach ($catalog as $serv) {
        if (strcasecmp($serv['name'], $identifier) === 0 || str_contains(strtolower($serv['name']), $slug)) {
            return $serv;
        }
    }
    
    return null;
}
