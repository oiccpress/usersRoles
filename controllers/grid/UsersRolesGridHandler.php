<?php

namespace APP\plugins\generic\usersRoles\controllers\grid;

use APP\notification\NotificationManager;
use APP\plugins\generic\usersRoles\controllers\grid\form\UserRoleForm;
use APP\plugins\generic\usersRoles\UsersRolesPlugin;
use Illuminate\Support\Facades\DB;
use PKP\controllers\grid\admin\plugins\AdminPluginGridHandler;
use PKP\controllers\grid\CategoryGridHandler;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\GridHandler;
use PKP\controllers\grid\plugins\PluginGridRow;
use PKP\core\JSONMessage;
use PKP\core\PKPApplication;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\notification\PKPNotification;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;
use PKP\security\Role;

class UsersRolesGridHandler extends GridHandler {

    /** @var UsersRolesPlugin The static pages plugin */
    public $plugin;

    /**
     * Constructor
     */
    public function __construct(UsersRolesPlugin $plugin)
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            ['index', 'fetchGrid', 'fetchRow', 'addRole', 'editRole', 'updateRole', 'delete']
        );
        $this->plugin = $plugin;
    }

    public function authorize($request, &$args, $roleAssignments)
    {
        // $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        $rolePolicy = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);

        foreach ($roleAssignments as $role => $operations) {
            $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
        }
        $this->addPolicy($rolePolicy);

        return parent::authorize($request, $args, $roleAssignments);
    }

    public function getRowInstance()
    {
        return new UsersRolesGridRow();
    }


    /**
     * @copydoc GridHandler::initialize()
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);
        $context = $request->getContext();

        // Set the grid details.
        $this->setTitle('plugins.generic.usersRoles.usersRoles');
        $this->setEmptyRowText('plugins.generic.usersRoles.empty');

        // Get the pages and add the data to the grid
        /** @var BlockPagesDAO */
        $this->setGridDataElements(
            DB::select('
                SELECT * FROM `user_user_groups` uug
                INNER JOIN `user_groups` ug ON uug.user_group_id = ug.user_group_id
                INNER JOIN `users` u ON uug.user_id = u.user_id;
            ')
        );

        // Add grid-level actions
        $router = $request->getRouter();
        $this->addAction(
            new LinkAction(
                'addRole',
                new AjaxModal(
                    $router->url($request, null, null, 'addRole'),
                    __('plugins.generic.blockPages.addBlockPage'),
                    'modal_add_item'
                ),
                __('plugins.generic.usersRoles.addRole'),
                'add_item'
            )
        );

        // Columns
        $cellProvider = new UsersRolesGridCellProvider($this->plugin);
        $this->addColumn(new GridColumn(
            'username',
            'plugins.generic.usersRoles.username',
            null,
            'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
            $cellProvider
        ));
        $this->addColumn(new GridColumn(
            'context',
            'plugins.generic.usersRoles.context',
            null,
            'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
            $cellProvider
        ));
        $this->addColumn(new GridColumn(
            'role',
            'plugins.generic.usersRoles.role',
            null,
            'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
            $cellProvider
        ));
    }

    /**
     * An action to add a new custom static page
     *
     * @param array $args Arguments to the request
     * @param PKPRequest $request Request object
     */
    public function addRole($args, $request)
    {
        // Calling editStaticPage with an empty ID will add
        // a new static page.
        return $this->editRole($args, $request);
    }

    /**
     * An action to edit a static page
     *
     * @param array $args Arguments to the request
     * @param PKPRequest $request Request object
     *
     * @return JSONMessage Serialized JSON object
     */
    public function editRole($args, $request)
    {
        $staticPageId = $request->getUserVar('roleId');
        $context = $request->getContext();
        $this->setupTemplate($request);

        // Create and present the edit form
        $staticPageForm = new UserRoleForm($this->plugin, $context?->getId() ?? null, $staticPageId);
        $staticPageForm->initData();
        return new JSONMessage(true, $staticPageForm->fetch($request));
    }

    /**
     * Update a custom block
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage Serialized JSON object
     */
    public function updateRole($args, $request)
    {
        $staticPageId = $request->getUserVar('roleId');
        $context = $request->getContext();
        $this->setupTemplate($request);

        // Create and populate the form
        $staticPageForm = new UserRoleForm($this->plugin, $context?->getId() ?? null, $staticPageId);
        $staticPageForm->readInputData();

        // Check the results
        if ($staticPageForm->validate()) {
            // Save the results
            $staticPageForm->execute();
            $json = new JSONMessage(true, '');
            $json->setEvent('dataChanged', null);
            return $json;
        }
        // Present any errors
        return new JSONMessage(true, $staticPageForm->fetch($request));
    }

}
