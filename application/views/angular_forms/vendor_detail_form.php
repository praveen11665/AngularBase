<?php
    //Collecting data from the controller
    foreach ($userData as $row) 
    {
        $user_id            = $row->user_id;
        $username           = $row->username;
        $role_name          = $row->role_name;
        $company_name       = $row->company_name;
        $address            = $row->address;
        $city_name          = $row->city_name;
        $state_name         = $row->state_name;
        $country_name       = $row->country_name;
        $cont_first_name    = $row->cont_first_name;
        $cont_email_id      = $row->cont_email_id;
        $cont_number        = $row->cont_number;
        $document_path      = explode(",", $row->document_path);
        $document_name      = explode(",", $row->document_name);
        $reason             = $row->reason;
    }
?>
<!--VENDOR,COMPANY AND CONTACT PERSON INFORMATIONS -->
<h6 class="element-header">
    <?php echo ucfirst($username);?>
    <label class="pull-right badge" style="background-color: #254afc; color: white;"><?php echo $role_name;?></label>
</h6>
<ul class="task-dates list-inline m-b-0">
    <?php if(!empty($company_name))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('table_head_company_name');?></h5>
            <p><?php echo $company_name; ?></p>
        </li>
        <?php
        }
    ?>

    <?php if(!empty($address))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('label_company_address');?></h5>
            <p><?php echo $address.",".$city_name.",".$state_name.",".$country_name; ?></p>
        </li>
        <?php
        }
    ?>

    <?php if(!empty($cont_first_name))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('table_cont_name');?></h5>
            <p><?php echo $cont_first_name; ?></p>
        </li>
        <?php
        }
    ?>        

    <?php if(!empty($cont_email_id))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('table_cont_person_email');?></h5>
            <p><?php echo $cont_email_id; ?></p>
        </li>
        <?php
        }
    ?>

    <?php if(!empty($cont_number))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('table_cont_person_mobile');?></h5>
            <p><?php echo $cont_number; ?></p>
        </li>
        <?php
        }
    ?>

    <?php if(!empty($reason))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('lable_head_reason');?></h5>
            <p><?php echo $reason; ?></p>
        </li>
        <?php
        }
    ?>
</ul>
<div class="clearfix"></div>

<!--UPLOAD DOCUMENTS LISTING-->
<?php
    if(!empty($document_path['0']))
    {
        ?>
        <!-- Upload Files-->
        <hr>
        <div class="attached-files mt-4">
            <h5 class=""><?php echo $this->lang->line('title_upload_docs');?></h5><br>
            <div class="files-list">
                <?php
                foreach($document_path as $key => $value) 
                {
                    //document_name
                    $imagePath  = base_url().''.$value;
                    $url        = explode('/', rtrim($value,'/'));
                    $file_type  = array_pop($url);
                    $Nameurl    = explode('.', rtrim($value,'.'));
                    $fileName   = array_pop($Nameurl);
                    $path       = FCPATH.$value;
                    if (file_exists($path)) 
                    {
                        ?>                            
                            <a href="<?php echo $imagePath;?>" title="click to download" target="_blank">
                            <b><?php echo $document_name[$key];?></b>&nbsp;&nbsp;&nbsp;
                            <?php 
                                if ($fileName == 'jpg' || $fileName == 'jpeg' || $fileName == 'png') 
                                {
                                    ?>                                 
                                        <img src="<?php echo $imagePath;?>" width="48"></a>
                                    <?php
                                }
                            ?>
                          <b><?php echo $file_type;?></a></b> <br/><br/>
                        <?php
                    }else
                    {
                        ?>
                        <div class="alert alert-info">
                          <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></strong> <?php echo $this->lang->line('title_file_not_exist');?>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>        
        </div>
        <?php
    }
?>
<div class="clearfix"></div><br>
<!-- APPROVE AND DISAPPROVE VENDORS BUTTONS -->
<?php
    if($showButtons)
    {
        ?>
        <div class="custom-panel-footer">
            <button title="Approve" class="btn btn-success" onclick="commonApprove('Are you sure?', 'You want to approve this vendor???', '<?php echo $approveUrl.$user_id;?>')"><?php echo $this->lang->line('button_approve');?></button>
            <button title="Disapprove" class="btn btn-danger" onclick="commonDisapprove('You Want to Disapprove this Vendor???', 'Enter the reason for disapprove vendor', 'You want disapprove this vendor', '<?php echo $disApproveUrl.$user_id;?>')"><?php echo $this->lang->line('button_disapprove');?></button>
        </div>
        <?php
    }
?>

<div class="clearfix"></div>
<?php
    if(!empty($activityData))
    {
        ?>
        <!--Activity Log-->
        <div class="time-line-custom" id = "activityLog">
            <div class="timeline">
                <article class="timeline-item">
                    <div class="text-left">
                        <div class="time-show first">
                            <a  class="btn btn-custom w-lg"><?php echo $this->lang->line('label_activity_log');?></a>
                        </div>
                    </div>
                    <?php
                    foreach ($activityData as $row) 
                    {
                        $log_id         = $row->log_id;
                        $log_timestamp  = $row->log_timestamp;
                        $activity_id    = $row->activity_id;
                        $activity_type  = $row->activity_type;
                        $activity_icon  = $row->activity_icon;
                        $activity_class = $row->activity_class;
                        $log_activity   = $row->log_activity;
                        $user_id        = $row->user_id;
                        $username       = $row->username;               
                       ?>
                        <article class="timeline-item">
                            <div class="timeline-desk">
                                <div class="panel">
                                    <div class="timeline-box">
                                        <span class="arrow"></span>
                                        <span class="timeline-icon bg-<?php echo $activity_class;?>"><i class="mdi <?php echo $activity_icon; ?> big"></i></span>
                                        <h4 class="text-custom"><?php echo date('F j, Y h:i A', strtotime($log_timestamp)); ?></h4>
                                        <p><?php echo $log_activity; ?></p>                               
                                    </div>
                                </div>
                            </div>
                        </article>
                        <?php
                    }
                    ?>
                </article>
            </div>
        </div>
        <!-- end activity log -->
        <?php
    }
?>