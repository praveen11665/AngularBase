<div class="row">
	<div class="col-lg-12">
		<div class="element-wrapper">
			<h6 class="element-header"><?php echo $this->lang->line('label_web_site_setting');?> </h6>
    		<div class="element-box">
    			<?php
					if($this->session->flashdata('msg') != '')
					{
						?>
							<div class="alert alert-<?php echo $this->session->flashdata('alertType');?>" id="alert-message">
							    <?php echo $this->session->flashdata('msg'); ?>
							</div>
						<?php
					}
				?>
				<form action="<?php echo base_url($ActionUrl);?>" method="post" name="myform">
	    			<?php
	    				foreach ($settingData as $row) 
	    				{
	    					?>
	    					<div class="row">
	    						<div class="col-lg-12">	    							
			    					<div class="form-group">
								        <label><?php echo $row->key;?></label>
								        <input type="hidden" name="key[]" value="<?php echo $row->ws_id;?>">
								        <textarea name="value[]" id="content" class="form-control" required><?php echo $row->key_value;?></textarea>
										<span class="help-block"><?php echo form_error('value')?></span>
								    </div>
	    						</div>	    						
	    					</div>
	    					<?php	    					
	    				}
	    			?>
	    			<hr>
	    			<div class="form-buttons-w text-right">
				        <a href="<?php echo base_url('master/setting/add'); ?>" class="btn btn-danger"><?php echo $this->lang->line('label_cancel');?></a>
				        <button class="btn btn-success" type="submit"><?php echo $this->lang->line('label_update');?></button>
				    </div>	
    			</form>
			</div>
		</div>
	</div>
</div>