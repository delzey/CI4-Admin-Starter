<?php
 
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Assets extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Cache Busting Type
     * --------------------------------------------------------------------------
     *
     * Assets can work with 2 different methods to determine the cache busting
     * string, either 'file' or 'version'.
     *
     * 'version' uses the Cache Busting Version setting below on production servers,
     * but uses the current timestamp for testing/development environments. This
     * has lower server load, but does require the version number to be updated
     * after every change.
     *
     * 'file' uses the modified time on the file. This ensures it is always up to
     * date and no chance for a developer to forget to update it. However, this
     * does increase server load as we're reading the value of the file on every
     * non-cached page load. This should probably only be used on small-medium
     * sites.
     */
    //public $bustingType = 'version';
    public string $bustingType = 'file';

    /**
     * --------------------------------------------------------------------------
     * Cache Busting Versions
     * --------------------------------------------------------------------------
     *
     * The version of the css that gets shipped to production. When changes
     * happen, this should be updated to reflect the changes. We do it this
     * way instead of relying on server file modification time to reduce
     * server load.
     */
    public $versions = [
        'css' => '1.0.0',
        'js'  => '1.0.0',
        'png' => '1.0.0',
        'jpg' => '1.0.0',
        'jpeg'=> '1.0.0',
        'gif' => '1.0.0',
        'svg' => '1.0.0',
        'webp'=> '1.0.0',
        'woff'=> '1.0.0',
        'woff2'=> '1.0.0',
    ];

    /**
     * --------------------------------------------------------------------------
     * Asset Locations
     * --------------------------------------------------------------------------
     *
     * Specifies the locations that can have assets located in them.
     * This should make up the first segment of an asset URL.
     */

    public array $folders = [
        'admin' => APPPATH . '../assets/Admin',
        'app'   => APPPATH . '../assets/App',
        'auth'  => APPPATH . '../assets/Auth',
        'docs'  => APPPATH . '../assets/Docs',
    ];
}
