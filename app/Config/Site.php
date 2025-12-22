<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Site extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Items Per Page
     * --------------------------------------------------------------------------
     *
     * The number of items that displayed by default in content lists.
     */
    public $perPage                     = 15;
    public $rma_prefix                  = 'RMA'; 
    public $rma_seq_padding             = 5;
    public $idleTimeoutMinutes          = 6;

    /**
     * --------------------------------------------------------------------------
     * Cache TTLs
     * --------------------------------------------------------------------------
     *
     * Adjustable cache lifetimes (in seconds) for model queries.
     */
    public $modelCacheTTL               = 300;
    public $invoiceDetailsCacheTTL      = 300;
    public $quoteDetailsCacheTTL        = 300;

    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * The name that should be displayed for the site.
     */
    public $siteName = 'BSS-Aviation';
    public $siteDescription = 'BitShout Solutions Software';
    public $siteKeyWords = 'software,users,user management,codeigniter';
    public $accountingUserIds = ['1,7,8'];

    /**
     * --------------------------------------------------------------------------
     * Site Online?
     * --------------------------------------------------------------------------
     *
     * When false, only superadmins and user groups with permission will be
     * able to view the site. All others will see the "System Offline" page.
     */
    public $siteOnline = true;
    public $activate_frontend = '1';
    public $default_theme_front = '';

    /**
     * --------------------------------------------------------------------------
     * Site Offline View
     * --------------------------------------------------------------------------
     *
     * The view file that is displayed when the site is offline.
     */
    public $siteOfflineView = 'Views\siteOffLine';

    /**
     * --------------------------------------------------------------------------
     * Default Site Settings
     * --------------------------------------------------------------------------
     *
     * The name that should be displayed for the site.
     */
    public $default_language = '';
    public $default_role = '';
    public $default_date_format = '';
    public $default_hour_format = '';
    public $default_currency = '';
    public $default_currency_position = '';
    public $default_currency_separation = '';
    public $default_country = '';
    public $default_timezone = '';
    
    public $seo_description = '';
    public $seo_keywords = '';

    public $terms_conditions = '';
    public $terms_conditions_text = '';

}
