<?php
	//Collecting data from the controller
	foreach ($productData as $row) 
	{
		$product_id 		  = $row->product_id;
		$product_name 		  = $row->product_name;
		$tbt_cut_points 	  = $row->tbt_cut_points;
		$density 		      = $row->density;
		$viscosity 			  = $row->viscosity;
		$user_agreement_terms = $row->user_agreement_terms;
		$user_id 		      = $row->user_id;
		$username 		      = $row->username;
		$role_name 	          = $row->role_name;
		$updated_on 		  = $row->updated_on;
        $reason               = $row->reason;
	}
?>
<!--VENDOR,COMPANY AND CONTACT PERSON INFORMATIONS -->
<h6 class="element-header">
	<?php echo ucfirst($username);?>
	<label class="pull-right badge" style="background-color: #254afc; color: white;"><?php echo $role_name;?></label>
</h6>
<ul class="task-dates list-inline m-b-0">
    <?php if(!empty($product_name))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('table_head_product_name');?></h5>
            <p><?php echo $product_name; ?></p>
        </li>
        <?php
        }
    ?>

    <?php if(!empty($tbt_cut_points))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('table_head_tbt_cut_points');?></h5>
            <p><?php echo $tbt_cut_points; ?></p>
        </li>
        <?php
        }
    ?>

    <?php if(!empty($density))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('table_head_density');?></h5>
            <p><?php echo $density; ?></p>
        </li>
        <?php
        }
    ?>        

    <?php if(!empty($viscosity))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('table_head_viscosity');?></h5>
            <p><?php echo $viscosity; ?></p>
        </li>
        <?php
        }
    ?>    

    <?php if(!empty($updated_on))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('label_updated_on');?></h5>
            <p><?php echo date('d-M-Y h:i A', strtotime($updated_on)); ?></p>
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

<ul class="list-inline">
    <?php if(!empty($user_agreement_terms))
        {
        ?>
        <li>
            <h5 class=""><?php echo $this->lang->line('table_head_user_agreement_terms');?></h5>
            <div class="row">
                <div class="col-md-12">                    
                    <p class=""><?php echo $user_agreement_terms; ?></p>
                </div>                
            </div>
        </li>
        <?php
        }
    ?>
</ul>

<div class="clearfix"></div>


<!-- APPROVE AND DISAPPROVE PRODUCT BUTTONS -->
<?php
	if($showButtons)
	{
		?>
		<div class="custom-panel-footer">
            <button title="Approve" class="btn btn-success" onclick="commonApprove('Are you sure?', 'You want to approve this Deal???', '<?php echo $approveUrl.$product_id;?>')"><?php echo $this->lang->line('button_approve');?></button>
            <button title="Disapprove" class="btn btn-danger" onclick="commonDisapprove('You Want to Disapprove this Deal???', 'Enter the reason for disapprove deal', 'You want disapprove this deal', '<?php echo $disApproveUrl.$product_id;?>')"><?php echo $this->lang->line('button_disapprove');?></button>
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