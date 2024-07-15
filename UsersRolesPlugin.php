<?php

/**
 * Main class for users and roles plugin
 * 
 * @author Joe Simpson
 * 
 * @class UsersRolesPlugin
 *
 * @ingroup plugins_generic_usersRoles
 *
 * @brief Users and Roles
 */

namespace APP\plugins\generic\usersRoles;

use APP\plugins\generic\usersRoles\controllers\grid\UsersRolesGridHandler;
use PKP\core\Registry;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class UsersRolesPlugin extends GenericPlugin {

    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {
            Hook::add( 'Template::Settings::admin', [$this, 'callbackShowWebsiteSettingsTabs']) ;
            Hook::add( 'LoadComponentHandler', [$this, 'setupGridHandler'] );
        }

        return $success;
    }

    /**
     * Permit requests to the grid handler
     *
     * @param string $hookName The name of the hook being invoked
     */
    public function setupGridHandler($hookName, $params)
    {
        $component = & $params[0];
        $componentInstance = & $params[2];
        if ($component == 'plugins.generic.usersRoles.controllers.grid.UsersRolesGridHandler') {
            // Allow the static page grid handler to get the plugin object
            $componentInstance = new UsersRolesGridHandler($this);
            return true;
        }
        return false;
    }

    /**
     * Extend the website settings tabs to include pages
     *
     * @param string $hookName The name of the invoked hook
     * @param array $args Hook parameters
     *
     * @return bool Hook handling status
     */
    public function callbackShowWebsiteSettingsTabs($hookName, $args)
    {
        $templateMgr = $args[1];
        $output = & $args[2];
        $request = & Registry::get('request');
        $dispatcher = $request->getDispatcher();

        $output .= $templateMgr->fetch($this->getTemplateResource('usersRolesTab.tpl'));

        // Permit other plugins to continue interacting with this hook
        return false;
    }

    /**
     * Provide a name for this plugin
     *
     * The name will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     */
    public function getDisplayName()
    {
        return 'Users and Roles';
    }

    /**
     * Provide a description for this plugin
     *
     * The description will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     */
    public function getDescription()
    {
        return 'This plugin allows for visual representation of users and roles.';
    }

}
