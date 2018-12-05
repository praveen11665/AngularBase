<?php
//Dropdown Config
$countryDropdown = $this->mcommon->Dropdown('countries', array('country_id as Key', 'country_name as Value'), array('status' => '1'));  

//Variable Initialization
$state_id       = "";
$state_name     = "";
$status         = "";
$country_id     = "";
 
if(!empty($tabledata))
{
    foreach ($tabledata as $row) 
    {
        $state_id       = $row->state_id;        
        $state_name     = $row->name;
        $status         = $row->status;
        $country_id     = $row->country_id;
    }
}
else
{
    $state_id       = $this->input->post('state_id');
    $state_name     = $this->input->post('state_name');
    $status         = $this->input->post('status');
    $country_id     = $this->input->post('country_id');
}
?>
<div ng-if="form.state_id">
    <h6 class="element-header"><?php echo $this->lang->line('label_state_edit');?></h6>
</div>
<form action="<?php echo base_url($ActionUrl);?>" method="post" name="myform">   
<input type="hidden" name="state_id" ng-model="form.state_id" value="{{form.state_id}}">     
    <div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for=""><?php echo $this->lang->line('label_country_name');?></label><span class="mandatory">*</span>
            <?php            
                $extraAttr="id='country_id' class='form-control widthSelect' ng-model='form.country_id' required select2";
                echo form_dropdown('country_id', $countryDropdown, $country_id, $extraAttr);
            ?> 
            <span class="help-block" ng-show="showMsgs && myform.country_id.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
            <span class="help-block"><?php echo form_error('country_id')?></span>
        </div>
    </div> 
    </div>
    <div class="form-group">
        <label><?php echo $this->lang->line('label_state_name');?></label>
        <span class="mandatory">*</span>
        <input type="text" name="state_name" id="state_name" value="<?php echo $state_name;?>" class="form-control txtNumeric" ng-model="form.name" allow-characters required ng-init="state_name = '<?php echo $state_name;?>'"/>
        <span class="help-block" ng-show="showMsgs && myform.state_name.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
        <span class="help-block"><?php echo form_error('state_name')?></span>
    </div>
    <div class="form-group">
        <label> 
            <input type="checkbox" id="checkall" name="status" value="1" ng-checked="form.status == 1"/>  <?php echo $this->lang->line('label_active');?>
        </label> 
    </div>
    <hr>    
    <div class="form-buttons-w text-right">
        <a href="<?php echo base_url('master/state/add'); ?>" class="btn btn-danger"><?php echo $this->lang->line('label_cancel');?></a>
        <button class="btn btn-success" type="submit" ng-click="submited('myform')">
            <div ng-if="!form.state_id">
                <?php echo $this->lang->line('label_submit');?>      
            </div>
            <div ng-if="form.state_id">
                <?php echo $this->lang->line('label_update');?>      
            </div>
        </button>
    </div>
</form>