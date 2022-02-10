<div id="department_features_{$department.department_id}">
<div class="ty-feature">

    {if $department_data.main_pair}
    <div class="ty-feature__image">
        {include file="common/image.tpl" images=$department_data.main_pair}
    </div>
    {/if}
    <div class="ty-feature__description ty-wysiwyg-content">
        <b>{__("description")}:</b> 
        <br>
        {if $department_data.description}
        {$department_data.description}
        {/if}
        <br>
        <b>{__("leader")}:</b> 
        <br>
        {$department_data.user_info.firstname} {$department_data.user_info.lastname}
        <br>
        <b>{__("workers")}:</b>                      
        {foreach from=$department_data.workers item="worker"}
            <br> 
            {$worker.firstname} {$worker.lastname}
        {/foreach}
    </div>
</div>
</div>

{capture name="mainbox_title"}{$collection_data.variant nofilter}{/capture}