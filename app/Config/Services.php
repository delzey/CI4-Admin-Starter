<?php

namespace Config;

use App\Models\MenuModel;
use App\Models\MenuCategoryModel;
use App\Services\MenuService;
use App\Services\SettingsService;
use App\Services\ApplicationService;
use CodeIgniter\Config\BaseService;
use App\Services\AccessGuard as AccessGuardService;
/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{

    
    /**
     * Summary of menuService
     * @param mixed $menuModel
     * @param mixed $categoryModel
     * @param bool $getShared
     * @return MenuService|object
     * 
     * Usage:
     * -- $menuService = service('menuService');
     */
    public static function menuService(
        ?MenuModel $menuModel = null,
        ?MenuCategoryModel $categoryModel = null,
        bool $getShared = true
    ): MenuService {
        if ($getShared) {
            return static::getSharedInstance('menuService', $menuModel, $categoryModel);
        }

        return new MenuService(
            $menuModel     ?? new MenuModel(),
            $categoryModel ?? new MenuCategoryModel()
        );
    }

    public static function settingsService(?Settings $settings = null, bool $getShared = true): SettingsService
    {
        if ($getShared) {
            return static::getSharedInstance('settingsService', $settings);
        }
        return new SettingsService($settings);
    }

    public static function applicationService(
        ?SettingsService $settingsService = null,
        ?MenuService $menuService = null,
        bool $getShared = true
    ): ApplicationService {
        if ($getShared) {
            return static::getSharedInstance('applicationService', $settingsService, $menuService);
        }
        return new ApplicationService($settingsService, $menuService);
    }

    public static function messageService(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('messageService');
        }

        return new \App\Services\MessageService();
    }

    public static function accessGuard(bool $getShared = true): AccessGuardService
    {
        if ($getShared) {
            return static::getSharedInstance('accessGuard');
        }

        return new AccessGuardService(
            cache(),
            service('logger'),
            config('AccessGuard')
        );
    }
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
}
