<?php
//Dropdown Config
$countryDropdown    = $this->mcommon->Dropdown('countries', array('country_id as Key', 'country_name as Value'), array('status' => '1'));  
$stateDropdown      = $this->mcommon->Dropdown('states', array('state_id as Key', 'name as Value'), array('status' => '1'));   

//Variable Initialization
$state_id       = "";
$city_name      = "";
$city_id        = "";
$status         = "";
 
if(!empty($_POST))
{
    $state_id    = $this->input->post('state_id');
    $city_name   = $this->input->post('city_name');
    $city_id     = $this->input->post('city_id');
    $status      = $this->input->post('status');
}
?>
<div ng-if="form.city_id">
    <h6 class="element-header"><?php echo $this->lang->line('label_city_edit');?></h6>
</div>
<form action="<?php echo base_url($ActionUrl);?>" method="post" name="myform">
    <input type="hidden" name="city_id" ng-model="form.city_id" id="city_id" value="{{form.city_id}}">
    <h5 class="form-header"></h5>
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
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for=""><?php echo $this->lang->line('label_state_name');?></label><span class="mandatory">*</span>
                <?php
                    $extraAttr="id='state_id' class='form-control widthSelect' ng-model='form.state_id' required select2";
                    echo form_dropdown('state_id', $stateDropdown, $state_id, $extraAttr);
                ?> 
                <span class="help-block" ng-show="showMsgs && myform.state_id.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
                <span class="help-block"><?php echo form_error('state_id')?></span>
            </div>
        </div> 
    </div>
    <div class="row"> 
        <div class="col-md-12">
            <div class="form-group">
                <label><?php echo $this->lang->line('label_city_name');?></label><span class="mandatory">*</span>
                <input type="text" name="city_name" id="city_name" value="<?php echo $city_name;?>" class="form-control txtNumeric" ng-model="form.name" allow-characters required/>
                <span class="help-block" ng-show="showMsgs && myform.city_name.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
                <span class="help-block"><?php echo form_error('city_name')?></span>
            </div>  
        </div>
    </div>
    <div class="form-group">
        <label> 
            <input type="checkbox" id="checkall" name="status" value="1" ng-checked="form.status == 1"/>  <?php echo $this->lang->line('label_active');?>
        </label> 
    </div><hr>
    <div class="form-buttons-w text-right">
        <a href="<?php echo base_url('master/city/add'); ?>" class="btn btn-danger"><?php echo $this->lang->line('label_cancel');?></a>

        <button class="btn btn-success" type="submit" ng-click="submited('myform')">
            <div ng-if="!form.city_id">
                <?php echo $this->lang->line('label_submit');?>      
            </div>
            <div ng-if="form.city_id">
                <?php echo $this->lang->line('label_update');?>      
            </div>
        </button>
    </div>
</form>