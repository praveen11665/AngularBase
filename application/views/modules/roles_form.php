<?php
//Variable Initialize
$role_id          = "1";
$role_name        = "";
$category_id      = array();
$action_id        = array();

/*
    --Check form load for edit operation
    --If yes get the data from table and assign values to variables
*/

if(!empty($appRoleData))
{
    // Role table Data
    foreach($appRoleData as $row)
    {
        $role_id          = $row->role_id;
        $role_name        = $row->role_name;
    }

    //Role actions table data set as array
    foreach ($appRoleActionData as $row) 
    {
        $category_id[]    = $row->category_id;
        $action_id[]      = $row->action_id;
    }
}
else
{
    $role_id          = $this->input->post('role_id');
    $role_name        = $this->input->post('role_name');
    $category_id      = $this->input->post('category_id[]');
    $action_id        = $this->input->post('action_id[]');
}

$rolesActionsArr = array();  
foreach ($actionData as $row) 
{
    $rolesActionsArr[$row->category_id][$row->action_id] = $row->action_code;  
}
?>
<?php 
    if($role_id)
    {
        ?>     
        <!-- Start form -->
            <h6 class="element-header"><?php echo $this->lang->line('label_edit_role');?></h6>
            <form action="<?php echo base_url($ActionUrl);?>" method="post" name="myform" autocomplete="off">
                <input type="hidden" name="role_id" id="role_id" value="<?php echo $role_id;?>">
                <!--Role name with textbox -->
                <div class="row">
                  <div class="col-md-12">
                      <div class="form-group">
                          <label for=""><?php echo $this->lang->line('label_role_name');?></label>
                          <span class="mandatory">*</span>
                            <input type="text" name="role_name" id="role_name" value="<?php echo $role_name;?>" class="form-control" ng-model="role_name" required/>
                            <span class="help-block"><?php echo form_error('role_name')?></span>
                      </div>
                  </div>
                </div>
                <!--Category based actions checkbox view -->
                <?php
                foreach ($categoryData as $row) 
                {
                    ?>
                    <div class="form-group">
                        <h5> <?php echo ucwords($row->category_code);?>&nbsp;
                                <small>
                                <input type="checkbox" id="checkall<?php echo $row->category_id;?>" onclick="checkAllMenu('<?php echo $row->category_id;?>')" /> Check All</small>
                        </h5>
                        <hr/>                
                        <div class="form-check">
                            <?php
                            foreach ($rolesActionsArr[$row->category_id] as $actionID => $actionCode) 
                            {
                                $checkArr[$row->category_id][] = in_array($actionID, $action_id);
                                ?>
                                    <label class="col-md-12">
                                        <input type="checkbox" name="action_id[<?php echo $actionID;?>]" value="<?php echo $actionID;?>" class="checkbox<?php echo $row->category_id;?>" onclick="checkMenu('<?php echo $actionID; ?>');"  <?php echo (in_array($actionID, $action_id)) ? 'checked' : '';?>> <?php echo ucwords(str_replace('_', ' ', $actionCode));?>
                                    </label>
                                    <input type="hidden" name="category_id[<?php echo $actionID;?>]" value="<?php echo $row->category_id;?>"
                                    >
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <hr>                         
                <!--Buttons for sumbit and reset-->
                <div class="form-buttons-w text-right">
                    <a href="<?php echo base_url();?>modules/roles/add" class="btn btn-danger"><?php echo $this->lang->line('label_cancel');?></a>
                    <button class="btn btn-success" type="submit" name="submit" ng-click="submited('myform')"><?php echo $this->lang->line('label_submit');?></button>
                </div>                           
            </form>
        <?php
    }

    /* EDIT DATA COME ALL ACTIONS ARE CHECKED THE CHECKALL FIELD IS CHECKED */
    if(!empty($checkArr))
    {
        foreach (($checkArr) as $cat_id => $checkedArr) 
        {
            $newArr[$cat_id][] = in_array("", $checkedArr);
        }

        foreach ($newArr as $categ_id => $valueArr) 
        {
            foreach ($valueArr as $key => $value) 
            {
                if($value == '')
                {
                    ?>
                <script type="text/javascript">
                    var cat_id = '<?php echo $categ_id;?>';                     
                    $('#checkall'+cat_id).prop("checked", true);
                </script>
                    <?php
                }
            }
            
        }
    }
?>

<script type="text/javascript">
    function checkAllMenu(category_id) 
    {
       if($('#checkall'+category_id).is(':checked'))
       {
            $('.checkbox'+category_id).prop("checked", true);
       }
       else
       {
            $('.checkbox'+category_id).prop("checked", false);
       }
    }
</script>