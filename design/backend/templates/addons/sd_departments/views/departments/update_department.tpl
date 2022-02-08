{if $department_data}
    {assign var="id" value=$department_data.department_id}
{else}
    {assign var="id" value=0}
{/if}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="banners_form" enctype="multipart/form-data">
<input type="hidden" class="cm-no-hide-input" name="fake" value="1" />
<input type="hidden" class="cm-no-hide-input" name="department_id" value="{$id}" />




    <div id="content_general">
        <div class="control-group">
            <label for="elm_banner_name" class="control-label cm-required">{__("name")}</label>
            <div class="controls">
            <input type="text" name="department_data[department]" id="elm_banner_name" value="{$department_data.department}" size="25" class="input-large" /></div>
            
        </div>
    </div>


        <div class="control-group" id="banner_graphic">
            <label class="control-label">{__("image")}</label>
            <div class="controls">
                {include 
                    file="common/attach_images.tpl"
                    image_name="department"
                    image_object_type="department"
                    image_pair=$department_data.main_pair
                    image_object_id=$id
                    no_detailed=true
                    hide_titles=true
                }
            </div>
        </div>

        <div class="control-group" id="banner_text">
            <label class="control-label" for="elm_banner_description">{__("description")}:</label>
            <div class="controls">
                <textarea id="elm_banner_description" name="department_data[description]" cols="35" rows="8" class="cm-wysiwyg input-large">{$department_data.description}</textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_banner_timestamp_{$id}">{__("creation_date")}</label>
            {if $department_data}
            <div class="cm-hide-inputs">
            {else}
            <div class="controls">
            {/if}
            {include file="common/calendar.tpl" date_id="elm_banner_timestamp_`$id`" date_name="department_data[timestamp]" date_val=$department_data.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
            </div>
        </div>
            <div class="control-group">
                <label class="control-label">{__("leader")}</label>
                <div class="controls">
                    {include file="pickers/users/picker.tpl" 
                    but_text=__("add_leader") 
                    data_id="return_users" 
                    but_meta="btn" 
                    input_name="department_data[lead_user_id]" 
                    item_ids=$department_data.lead_user_id 
                    placement="right"
                    display="radio"
                    view_mode="single_button"
                    user_info=$u_info
                    }
                </div>
            </div>
                <div class="control-group">
                <label class="control-label">{__("workers")}</label>
                <div class="controls">
                    {include file="pickers/users/picker.tpl" 
                    but_text=__("add_worker") 
                    data_id="return_users" 
                    but_meta="btn" 
                    input_name="department_data[workers_ids]" 
                    item_ids=$department_data.worker_ids 
                    placement="left"
                    view_mode="mixed"
                    user_info=$u_info
                    }
                </div>
            </div>
            {include file="common/select_status.tpl" input_name="department_data[status]" id="elm_banner_status" obj_id=$id obj=$department_data hidden=false}


    
    <!--content_general--></div>




{capture name="buttons"}
    {if !$id}
        {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="banners_form" but_name="dispatch[departments.update_department]"}
    {else}
        {capture name="tools_list"}
        <li>{btn type="list" text=__("delete") class="cm-confirm" href="departments.delete_department?department_id=`$id`" method="POST"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[departments.update_department]" but_role="submit-link" but_target_form="banners_form" hide_first_button=$hide_first_button hide_second_button=$hide_second_button save=$id}
    {/if}
{/capture}

</form>

{/capture}

{include file="common/mainbox.tpl"
    title=($id) ? $department_data.department : __("add_department")
    content=$smarty.capture.mainbox
    buttons=$smarty.capture.buttons
    select_languages=true}

{** banner section **}
