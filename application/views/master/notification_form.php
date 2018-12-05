<?php
//Variable Initialization
$notify_id      = "";
$title          = "";
$content        = "";
$modules_id     = "";
 
if(!empty($_POST))
{
    $notify_id      = $this->input->post('notify_id');
    $title          = $this->input->post('title');
    $content        = $this->input->post('content');
    $modules_id     = $this->input->post('modules_id');
}
?>
<div ng-if="form.notify_id">
    <h6 class="element-header"><?php echo $this->lang->line('label_edit_notification');?></h6>
</div>
<form action="<?php echo base_url($ActionUrl);?>" method="post" name="myform">        
<input type="hidden" name="notify_id" id="notify_id" ng-model="form.notify_id" value="{{form.notify_id}}">
    <div class="form-group">
        <label><?php echo $this->lang->line('label_notification_title');?></label>
        <span class="mandatory">*</span>
        <input type="text" name="title" id="title" value="<?php echo $title;?>" class="form-control" ng-model="form.title" allow-characters required/>
        <span class="help-block" ng-show="showMsgs && myform.title.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
        <span class="help-block"><?php echo form_error('title')?></span>
    </div>
   
    <div class="form-group">
        <label><?php echo $this->lang->line('label_notification_content');?></label><span class="mandatory">*</span>
        <textarea name="content" id="content" class="form-control" ng-model="form.content" required allow-characters><?php echo $content;?></textarea>
        <span class="help-block" ng-show="showMsgs && myform.content.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
        <span class="help-block"><?php echo form_error('content')?></span>
    </div>

    <div ng-if="form.notify_id"> 
        <div class="form-group">
            <label for=""><?php echo $this->lang->line('label_notification_modules');?></label><span class="mandatory">*</span>
            <?php
                //Dropdown Config  
                $defmoduleDropdown = $this->mcommon->Dropdown('def_modules', array('modules_id as Key', 'module_name as Value'));

                $extraAttr="id='modules_id' class='form-control widthSelect' ng-model='form.modules_id' ng-disabled='form.notify_id' required select2";
                echo form_dropdown('modules_id', $defmoduleDropdown, $modules_id, $extraAttr);
            ?> 
            <span class="help-block" ng-show="showMsgs && myform.modules_id.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
            <span class="help-block"><?php echo form_error('modules_id')?></span>
        </div> <hr>
    </div>

    <div ng-if="!form.notify_id"> 
        <div class="form-group">
            <label for=""><?php echo $this->lang->line('label_notification_modules');?></label><span class="mandatory">*</span>
            <?php
                //Dropdown Config
                $defmoduleData  = $this->mcommon->Dropdown('def_modules', array('modules_id as Key', 'module_name as Value'));

                //Alreay Given Data not come in the list
                $defmoduleDropdown = array();
                foreach ($defmoduleData as $module_id => $value) 
                {
                    if($module_id <= 1)
                    {
                        $defmoduleDropdown[$module_id] = $value;
                    }    
                    else if($module_id > 1)
                    {
                        $isModuleExist   =  $this->mcommon->specific_record_counts('notification', array('modules_id' => $module_id));

                        if($isModuleExist == '0')
                        {
                            $defmoduleDropdown[$module_id] = $value;
                        }   
                    }   
                }  

                $extraAttr="id='modules_id' class='form-control widthSelect' ng-model='form.modules_id' ng-disabled='form.notify_id' required select2";
                echo form_dropdown('modules_id', $defmoduleDropdown, $modules_id, $extraAttr);
            ?> 
            <span class="help-block" ng-show="showMsgs && myform.modules_id.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
            <span class="help-block"><?php echo form_error('modules_id')?></span>
        </div> <hr>
    </div>

    <div class="form-buttons-w text-right">
        <a href="<?php echo base_url('master/notification/add'); ?>" class="btn btn-danger"><?php echo $this->lang->line('label_cancel');?></a>
        <button class="btn btn-success" type="submit" ng-click="submited('myform')">
            <div ng-if="!form.notify_id">
                <?php echo $this->lang->line('label_submit');?>      
            </div>
            <div ng-if="form.notify_id">
                <?php echo $this->lang->line('label_update');?>      
            </div>
        </button>
    </div>
</form>