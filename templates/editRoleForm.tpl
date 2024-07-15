<script type="text/javascript">
	// Attach the file upload form handler.
	$(function() {ldelim}
		$('#userRoleForm').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler'
		);
	{rdelim});
</script>

{capture assign=actionUrl}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.usersRoles.controllers.grid.UsersRolesGridHandler" op="updateRole" roleId=$roleId escape=false}{/capture}
<form class="pkp_form" id="userRoleForm" method="post" action="{$actionUrl}">
    {csrf}
    {if $roleId}
        <input type="hidden" name="roleId" value="{$roleId|escape}" />
    {/if}
    {fbvFormArea id="blockPagesFormArea" class="border"}
        {fbvFormSection}
            {fbvElement type="select" from=$users translate=false label="plugins.generic.usersRoles.username" id="user_id" selected=$user_id maxlength="40" inline=true size=$fbvStyles.size.MEDIUM}
            {fbvElement type="select" from=$groups translate=false label="plugins.generic.usersRoles.context" id="group_id" selected=$group_id maxlength="40" inline=true size=$fbvStyles.size.MEDIUM}
        {/fbvFormSection}
    {/fbvFormArea}
    {fbvFormSection class="formButtons"}
        {assign var=buttonId value="submitFormButton"|concat:"-"|uniqid}
        {fbvElement type="submit" class="submitFormButton" id=$buttonId label="common.save"}
    {/fbvFormSection}
</form>