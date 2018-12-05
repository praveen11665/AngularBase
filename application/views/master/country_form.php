<div ng-if="form.country_id">
    <h6 class="element-header"><?php echo $this->lang->line('label_country_edit');?></h6>
</div>
<form action="<?php echo base_url($ActionUrl);?>" method="post" name="myform">
<input type="hidden" name="country_id" id="country_id" ng-model="form.country_id" value="{{form.country_id}}">
    <div class="form-group">
        <label><?php echo $this->lang->line('label_country_name');?></label>
        <span class="mandatory">*</span>
        <input type="text" name="country_name" id="country_name" value="<?php echo $country_name;?>" class="form-control" ng-model="form.country_name" allow-characters required ng-init="country_name = '<?php echo $country_name;?>'"/>
        <span class="help-block" ng-show="showMsgs && myform.country_name.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
        <span class="help-block"><?php echo form_error('country_name')?></span>
    </div>   
    <div class="form-group">
        <label><?php echo $this->lang->line('label_country_code');?></label><span class="mandatory">*</span>
        <input type="text" name="country_code" id="country_code" value="<?php echo $country_code;?>" class="form-control" maxlength="5" ng-model="form.country_code" allow-characters required ng-init="country_code = '<?php echo $country_code;?>'"/>
        <span class="help-block" ng-show="showMsgs && myform.country_code.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
        <span class="help-block"><?php echo form_error('country_code')?></span>
    </div>
    <div class="form-group">
        <label><?php echo $this->lang->line('label_isd_code');?></label><span class="mandatory">*</span>
        <input type="text" name="isd_code" id="isd_code" value="<?php echo $isd_code;?>" class="form-control" onkeypress="return isNumberKey(event)" maxlength="5" ng-model="form.isd_code" required ng-init="isd_code = '<?php echo $isd_code;?>'"/>
        <span class="help-block" ng-show="showMsgs && myform.isd_code.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
        <span class="help-block"><?php echo form_error('isd_code')?></span>
    </div>
    <div class="form-group">
        <label> 
            <input type="checkbox" id="checkall" name="status" value="1" ng-checked="form.status == 1"/><?php echo $this->lang->line('label_active');?>
        </label> 
    </div><hr>
    <div class="form-buttons-w text-right">
        <a href="<?php echo base_url('master/country/add'); ?>" class="btn btn-danger"><?php echo $this->lang->line('label_cancel');?></a>
        <button class="btn btn-success" type="submit" ng-click="submited('myform')">
            <div ng-if="!form.country_id">
                <?php echo $this->lang->line('label_submit');?>      
            </div>
            <div ng-if="form.country_id">
                <?php echo $this->lang->line('label_update');?>      
            </div>
        </button>
    </div>
</form>