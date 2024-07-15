<tab id="usersRoles" label="{translate key="plugins.generic.usersRoles.usersRoles"}">
{capture assign=pubPrefPageGridUrl}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.usersRoles.controllers.grid.UsersRolesGridHandler" op="fetchGrid" escape=false}{/capture}
{load_url_in_div id="usesRolesGrid" url=$pubPrefPageGridUrl}
</tab>
