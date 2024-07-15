<?php

namespace APP\plugins\generic\usersRoles\controllers\grid;

use APP\core\Application;
use PKP\controllers\grid\GridHandler;
use PKP\controllers\grid\plugins\PluginGridCellProvider;

class UsersRolesGridCellProvider extends PluginGridCellProvider {

    protected $plugin;

    public const ROLES = [
        16 => 'MANAGER',
        1 => 'SITE_ADMIN',
        17 => 'SUB_EDITOR',
        65536 => 'AUTHOR',
        4096 => 'REVIEWER',
        4097 => 'ASSISTANT',
        1048576 => 'READER',
        2097152 => 'SUBSCRIPTION_MANAGER',
    ];

    public function __construct($plugin)
    {
        parent::__construct();
        $this->plugin = $plugin;
    }

    public function getTemplateVarsFromRowColumn($row, $column)
    {
        $data = & $row->getData();
        $columnId = $column->getId();

        switch ($columnId) {
            case 'role':
                return [
                    'label' => static::ROLES[ $data->role_id ]
                ];
            case 'context':
                if($data->context_id == 0) {
                    return ['label' => 'Global'];
                } else {
                    $contextDOA = Application::getContextDAO();
                    $context = $contextDOA->getById($data->context_id);
                    return [
                        'label' => $context->getLocalizedName()
                    ];
                }
            case 'username':
                return ['label' => $data->username];
            default:
                break;
        }

        return parent::getTemplateVarsFromRowColumn($row, $column);
    }

    /**
     * @copydoc GridCellProvider::getCellActions()
     */
    public function getCellActions($request, $row, $column, $position = GridHandler::GRID_ACTION_POSITION_DEFAULT)
    {
        /*switch ($column->getId()) {
            
        }*/
        return parent::getCellActions($request, $row, $column, $position);
    }

}