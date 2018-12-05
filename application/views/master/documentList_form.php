<?php
//Variable Initialization
$document_id    = "";
$document_name  = "";
$is_mandatory   = "";
$document_for   = ""; 

if(!empty($_POST))
{
    $document_id    = $this->input->post('document_id');
    $document_name  = $this->input->post('document_name');
    $is_mandatory   = $this->input->post('is_mandatory');
    $document_for   = $this->input->post('document_for');
    $document_type  = $this->input->post('document_type');
}
?>
<div ng-if="form.document_id">
    <h6 class="element-header"><?php echo $this->lang->line('label_document_edit');?></h6>
</div>
<form action="<?php echo base_url($ActionUrl);?>" method="post" name="myform">        
<input type="hidden" name="document_id" id="document_id" ng-model="form.document_id" value="{{form.document_id}}">
    <div class="form-group">
        <label><?php echo $this->lang->line('label_document_name');?></label><span class="mandatory">*</span>
        <input type="text" name="document_name" class="form-control" id="document_name" value="<?php echo $document_name;?>" class="form-control" ng-model="form.document_name" allow-characters required/>
        <span class="help-block" ng-show="showMsgs && myform.document_name.$error.required"><?php echo $this->lang->line('common_validation_msg');?></span>
        <span class="help-block"><?php echo form_error('document_name')?></span>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label> 
                    <input type="checkbox" id="checkall" name="is_mandatory" value="1" ng-checked="form.is_mandatory == 1"/>  <?php echo $this->lang->line('label_document_is_mandatory');?>
                </label> 
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label><?php echo $this->lang->line('label_document_for');?></label><span class="mandatory">*</span><br>
                <div ng-repeat="row in docFor">
                    <label>
                        <input type="checkbox" name="document_for[]" value="{{row.role_id}}" ng-checked="form.document_for.indexOf(row.role_id) > -1" ng-model="selectCheck.document_for[row.role_id]" ng-required="!someSelected(selectCheck.document_for)"/>{{row.role_name}}
                    </label>
                </div>
                <span class="help-block" ng-show="showMsgs && !someSelected(selectCheck.document_for)"><?php echo $this->lang->line('common_validation_msg');?></span>
                <span class="help-block"><?php echo form_error('document_for[]')?></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label><?php echo $this->lang->line('label_document_type');?></label><span class="mandatory">*</span><br>
                <div ng-repeat="row in docType">                    
                    <label>
                        <input type="checkbox" name="document_type[]" ng-model="selectCheck.document_type[row.doc_type_id]" value="{{row.doc_type_id}}" ng-checked="form.document_type.indexOf(row.doc_type_id) > -1" ng-required="!someSelected(selectCheck.document_type)" />{{row.document_type}}
                    </label>
                </div>
                <span class="help-block" ng-show="showMsgs && !someSelected(selectCheck.document_type)"><?php echo $this->lang->line('common_validation_msg');?></span>
                <span class="help-block"><?php echo form_error('document_type[]')?></span>
            </div>
        </div>
    </div><hr>
    <div class="form-buttons-w text-right">
        <a href="<?php echo base_url('master/documentList/add'); ?>" class="btn btn-danger"><?php echo $this->lang->line('label_cancel');?></a>
        <button class="btn btn-success" type="submit" ng-click="submited('myform')">
            <div ng-if="!form.document_id">
                <?php echo $this->lang->line('label_submit');?>      
            </div>
            <div ng-if="form.document_id">
                <?php echo $this->lang->line('label_update');?>      
            </div>
        </button>
    </div>
</form>