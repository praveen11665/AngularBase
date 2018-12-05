<?php
	//Collecting data from the controller
	foreach ($productData as $row) 
	{
		$deal_request_id      = $row->deal_request_id;
        $product_name         = $row->product_name;
        $deal_date            = $row->deal_date;
        $buyer_name           = $row->username;
        $seller_name          = $row->sellerName;
        $request_date         = $row->request_date;
        $user_id              = $row->user_id;
        $reason               = $row->reason;
        $role_name            = $row->role_name;
	}
?>
<!--VENDOR,COMPANY AND CONTACT PERSON INFORMATIONS -->
<h6 class="element-header">
	<?php echo ucfirst($seller_name);?>
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

    <?php if(!empty($deal_date))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('table_head_deal_date');?></h5>
            <p><?php echo date('d-M-Y h:i A', strtotime($deal_date)); ?></p>
        </li>
        <?php
        }
    ?>

    <?php if(!empty($buyer_name))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('label_buyer');?></h5>
            <p><?php echo $buyer_name; ?></p>
        </li>
        <?php
        }
    ?> 

    <?php if(!empty($seller_name))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('label_sellers');?></h5>
            <p><?php echo $seller_name; ?></p>
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

    <?php if(!empty($request_date))
        {
        ?>
        <li>
            <h5 class="m-b-5"><?php echo $this->lang->line('lable_head_deal_request_date');?></h5>
            <p><?php echo date('d-M-Y h:i A', strtotime($request_date)); ?></p>
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
		    <!--<button title="Approve" class="btn btn-success" onclick="confirmTrasactionApprove('<?php echo $approveUrl.$deal_request_id;?>')"><?php echo $this->lang->line('button_approve');?></button>
		    <button title="Disapprove" class="btn btn-danger" onclick="confirmTransactionDisapprove('<?php echo $disApproveUrl.$deal_request_id;?>')"><?php echo $this->lang->line('button_disapprove');?></button>-->

            <button title="Approve" class="btn btn-success" onclick="commonApprove('Are you sure?', 'You want to approve this Transaction???', '<?php echo $approveUrl.$deal_request_id;?>')"><?php echo $this->lang->line('button_approve');?></button>
            <button title="Disapprove" class="btn btn-danger" onclick="commonDisapprove('You Want to Disapprove this Transaction???', 'Enter the reason for disapprove transaction', 'You want disapprove this transaction', '<?php echo $disApproveUrl.$deal_request_id;?>')"><?php echo $this->lang->line('button_disapprove');?></button>
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