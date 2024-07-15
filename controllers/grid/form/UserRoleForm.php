<?php

/**
 * @file controllers/grid/form/StaticPageForm.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StaticPageForm
 *
 * @ingroup controllers_grid_staticPages
 *
 * @brief Form for press managers to create and modify sidebar blocks
 */

namespace APP\plugins\generic\usersRoles\controllers\grid\form;

use APP\core\Application;
use APP\plugins\generic\usersRoles\controllers\grid\UsersRolesGridCellProvider;
use APP\template\TemplateManager;
use Illuminate\Support\Facades\DB;
use PKP\db\DAORegistry;
use PKP\plugins\Hook;

class UserRoleForm extends \PKP\form\Form
{
    /** @var int Context (press / journal) ID */
    public $contextId;

    /** @var string Static page name */
    public $staticPageId;

    /** @var UsersRolesPlugin Static pages plugin */
    public $plugin;

    /**
     * Constructor
     *
     * @param UsersRolesPlugin $staticPagesPlugin The static page plugin
     * @param int $contextId Context ID
     * @param int $staticPageId Static page ID (if any)
     */
    public function __construct($staticPagesPlugin, $contextId, $staticPageId = null)
    {
        parent::__construct($staticPagesPlugin->getTemplateResource('editRoleForm.tpl'));

        $this->contextId = $contextId;
        $this->staticPageId = $staticPageId;
        $this->plugin = $staticPagesPlugin;

        // Add form checks
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    /**
     * Initialize form data from current group group.
     */
    public function initData()
    {
        $templateMgr = TemplateManager::getManager();
        if ($this->staticPageId) {
            $data = DB::selectOne('SELECT * FROM user_user_groups uug WHERE user_user_group_id = ?', [ $this->staticPageId ]);
            $this->setData('user_id', $data->user_id);
            $this->setData('group_id', $data->user_group_id);
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData()
    {
        $this->readUserVars(['user_id', 'group_id']);
    }

    /**
     * @copydoc Form::fetch
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager();
        $context = $request->getContext();

        $users = [];
        foreach(DB::select('SELECT * FROM users') as $user) {
            $users[ $user->user_id ] = $user->username;
        }

        $contexts = [ 0 => 'Global', ];
        foreach(Application::getContextDAO()->getAll()->toArray() as $context) {
            $contexts[ $context->getId() ] = $context->getLocalizedName();
        }

        $groups = [];
        foreach(DB::select('SELECT * FROM user_groups') as $group) {
            $groups[ $group->user_group_id ] =
                $contexts[$group->context_id] . ' - ' . UsersRolesGridCellProvider::ROLES[ $group->role_id ] . ' - ' .
                $group->user_group_id;
        }

        $templateMgr->assign([
            'roleId' => $this->staticPageId,
            'contextPath' => ($context?->getPath() ?? ''),
            'users' => $users,
            'contexts' => $contexts,
            'groups' => $groups,
        ]);

        return parent::fetch($request, $template, $display);
    }

    /**
     * Save form values into the database
     */
    public function execute(...$functionParams)
    {
        parent::execute(...$functionParams);
        
        if ($this->staticPageId) {
            DB::update('UPDATE user_user_groups SET user_id = ?, user_group_id = ? WHERE user_user_group_id = ?', [
                $this->getData('user_id'),
                $this->getData('group_id'),
                $this->staticPageId,
            ]);
        } else {
            DB::insert('INSERT INTO user_user_groups ( user_id, user_group_id ) VALUES ( ?, ? )', [ $this->getData('user_id'), $this->getData('group_id'), ]);
        }
    }
}
