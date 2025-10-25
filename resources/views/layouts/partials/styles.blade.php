<style>
    :root {
        --primary-color: #76B82A;
        --secondary-color: #246638;
        --accent-color: #F7A600;
        --blue-primary: #729BD1;
        --blue-secondary: #596692;
        --text-dark: #4D4D4D;
        --bg-light: #f8fafc;
        --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        background-color: var(--bg-light);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--text-dark);
    }
    
    /* Header */
    .navbar-custom {
        background-color: white;
        box-shadow: var(--card-shadow);
        padding: 1rem 0;
    }
    
    .navbar-brand {
        font-weight: 600;
        color: var(--text-dark) !important;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .logo-icon {
        width: 32px;
        height: 32px;
        background-color: var(--primary-color);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        color: white;
        flex-shrink: 0;
    }
    
    .brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    
    .navbar-toggler {
        border: none;
        padding: 0.25rem 0.5rem;
        background-color: transparent;
    }
    
    .navbar-toggler:focus {
        box-shadow: none;
    }
    
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23374151' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    
    .navbar-nav .nav-link {
        color: var(--text-dark) !important;
        font-weight: 500;
        margin: 0 0.75rem;
        display: flex;
        align-items: center;
    }
    
    .nav-link i {
        margin-right: 6px;
        font-size: 14px;
    }
    
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 4rem 0 3rem 0;
        text-align: center;
    }
    
    .hero-title {
        font-size: 3.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1rem;
        line-height: 1.1;
    }
    
    .hero-subtitle {
        color: var(--primary-color);
        font-weight: 700;
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
    }
    
    .hero-description {
        font-size: 1.1rem;
        color: #64748b;
        max-width: 1000px;
        margin: 0 auto 2.5rem auto;
        line-height: 1.6;
    }
    
    .btn-explore {
        background-color: var(--primary-color);
        color: white;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        border: none;
        font-size: 1rem;
    }
    
    .btn-explore:hover {
        background-color: #0e7490;
        color: white;
        text-decoration: none;
    }
    
    .btn-explore i {
        margin-left: 8px;
    }
    
    /* Stats Cards */
    .stats-row {
        margin-top: -3rem;
        position: relative;
        z-index: 10;
    }
    
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 2.5rem 2rem;
        text-align: center;
        box-shadow: var(--card-shadow);
        border: 1px solid #e2e8f0;
        height: 100%;
    }
    
    .stats-value {
        font-size: 2.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stats-value.primary { color: var(--primary-color); }
    .stats-value.success { color: #10b981; }
    .stats-value.info { color: #3b82f6; }
    
    .stats-label {
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    
    .stats-card small {
        display: block;
        font-size: 0.8rem;
        color: #9ca3af;
        margin-top: 0.25rem;
    }
    
    /* Key Indicators Section */
    .indicators-section {
        padding: 4rem 0;
    }
    
    .section-title {
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--text-dark);
        text-align: center;
        margin-bottom: 0.5rem;
    }
    
    .section-subtitle {
        color: #64748b;
        text-align: center;
        margin-bottom: 3rem;
        font-size: 1.05rem;
    }
    
    .indicator-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid #e2e8f0;
        height: 100%;
        text-align: center;
    }
    
    .indicator-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem auto;
        font-size: 1.5rem;
        color: white;
    }
    
    .icon-primary { background-color: var(--primary-color); }
    .icon-success { background-color: #10b981; }
    .icon-warning { background-color: #f59e0b; }
    .icon-info { background-color: #3b82f6; }
    
    .indicator-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .indicator-label {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .indicator-change {
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .change-positive { color: #10b981; }
    .change-negative { color: #ef4444; }
    
    /* Charts Section */
    .charts-section {
        padding: 4rem 0;
        background: white;
    }
    
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid #e2e8f0;
        height: 450px;
    }
    
    .chart-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
        overflow: hidden;
    }
    
    .chart-wrapper canvas {
        max-height: 100% !important;
        width: 100% !important;
    }
    
    .chart-title {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
    }
    
    .chart-subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 2rem;
    }
    
    /* Sector Cards */
    .sectors-section {
        padding: 4rem 0;
    }
    
    .sector-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .sector-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: flex-start;
    }
    
    .sector-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.4rem;
        color: white;
        flex-shrink: 0;
    }
    
    .sector-content {
        flex: 1;
    }
    
    .sector-name {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
        font-size: 1rem;
    }
    
    .sector-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
    }
    
    .sector-description {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0.5rem;
    }
    
    .sector-amount {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }
    
    .sector-growth {
        font-size: 0.8rem;
        font-weight: 500;
        margin-top: 0.25rem;
    }
    
    /* Evolution Section */
    .evolution-section {
        background: #f8fafc;
        padding: 4rem 0;
    }
    
    .evolution-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid #e2e8f0;
    }
    
    .selector-container {
        margin-bottom: 2rem;
    }
    
    .sector-selector {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-weight: 500;
        color: var(--text-dark);
        min-width: 250px;
    }
    
    /* Footer */
    .footer-section {
        background: white;
        padding: 3rem 0 2rem 0;
        border-top: 1px solid #e2e8f0;
    }
    
    .footer-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }
    
    .footer-description {
        color: #64748b;
        margin-bottom: 1rem;
    }
    
    .footer-copyright {
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
        font-size: 0.9rem;
        color: #64748b;
        text-align: center;
    }
    
    .btn-outline-light {
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        font-weight: 600;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .btn-outline-light:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(118, 184, 42, 0.3);
    }
    
    @media (max-width: 767px) {
        .footer-section .text-md-end {
            text-align: center !important;
            margin-top: 1.5rem;
        }
    }
    
    /* Responsive */
    @media (max-width: 991px) {
        .navbar-brand {
            font-size: 1rem;
        }
        
        .brand-text small {
            font-size: 0.75rem;
        }
        
        .navbar-collapse {
            margin-top: 1rem;
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
        }
        
        .navbar-nav .nav-link {
            padding: 0.5rem 0;
            text-align: center;
        }
    }
    
    @media (max-width: 768px) {
        .hero-title, .hero-subtitle {
            font-size: 2.5rem;
        }
        
        .sector-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-row {
            margin-top: -1rem;
        }
        
        .navbar-brand {
            font-size: 0.9rem;
        }
        
        .brand-text small {
            font-size: 0.7rem;
        }
        
        .logo-icon {
            width: 28px;
            height: 28px;
            margin-right: 8px;
        }
        
        .footer-section .text-md-end {
            text-align: center !important;
            margin-top: 1.5rem;
        }
    }
</style>