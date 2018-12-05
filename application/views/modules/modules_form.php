<?php
//Variable Initialization
$category_id    = "";
$category_code  = "";
$category_desc  = "";
$module_icon    = "";
 
if(!empty($tabledata))
{
    foreach ($tabledata as $row) 
    {
        $category_id   = $row->category_id;        
        $category_code = $row->category_code;
        $category_desc = $row->category_desc;
    }
}
else
{
    $category_id   = $this->input->post('category_id');
    $category_code = $this->input->post('category_code');
    $category_desc = $this->input->post('category_desc');
    $module_icon   = $this->input->post('module_icon');
}
?>
<form action="<?php echo base_url($ActionUrl);?>" method="post">
    <input type="hidden" name="category_id" id="category_id" value="<?php echo $category_id;?>">
    <div class="form-group">
        <label><?php echo $this->lang->line('label_module_name');?></label>
        <span class="mandatory">*</span>
        <input type="text" name="category_code" id="category_code" value="<?php echo $category_code;?>" class="form-control" />
        <span class="help-block"><?php echo form_error('category_code')?></span>
    </div>   
    <div class="form-group">
        <label><?php echo $this->lang->line('label_module_description');?></label><span class="mandatory">*</span>
        <textarea name="category_desc" cols="40" rows="3" id="category_desc" class="form-control"><?php echo $category_desc;?></textarea> 
        <span class="help-block"><?php echo form_error('category_desc')?></span>
    </div>
    <hr>
    <div class="form-buttons-w text-right">
        <a href="<?php echo base_url('modules/modules/add'); ?>" class="btn btn-danger"><?php echo $this->lang->line('label_cancel');?></a>
        <button class="btn btn-success" type="submit"><?php echo $this->lang->line('label_submit');?></button>
    </div>
</form>