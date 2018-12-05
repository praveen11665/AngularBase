<div ng-app="myapp">
	<div ng-controller="myCtrl">
		<?php
			if($search_view)
			{
				?>
				<div class="row searchView">
					<div class="col-lg-12">
						<div class="element-wrapper">
							<h6 class="element-header"><?php echo $search_title;?></h6>
				    		<div class="element-box">
								<?php echo $search_view;?>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
		?>
		<?php
			if($report_view)
			{
				?>
					<div class="row">
					    <div class="col-lg-12">
					    	<div class="element-wrapper">
					    		<h6 class="element-header"><?php echo $list_title;?></h6>
						    	<div class="form-desc"><?php echo $list_description;?></div>
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
										/****** TABLE GENRETE*******/
									 	echo $this->table->generate();
									?>    				
								</div>
					    	</div>
					    </div>
					</div>
				<?php
			}else
			{
				?>
				<div class="row">
				    <div class="col-lg-8">
				    	<div class="element-wrapper slimscroll">
				    		<h6 class="element-header"><?php echo $list_title;?></h6>
					    	<div class="form-desc"><?php echo $list_description;?></div>
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
										/****** TABLE GENRETE*******/
									 	echo $this->table->generate();
									?>    				
							</div>
				    	</div>
				    </div>
				    <?php
				    	if($form_view)
				    	{
				    		?>
						    <div class="col-lg-4">
						    	<div class="element-wrapper slimscroll">
						    		<div class="element-box">
						    			<div class="">
						    				<span id="showContent">
						    					<div ng-if="formTitle">
						    						<h6 class="element-header"><?php echo $form_title;?></h6>
						    					</div>
												<?php echo $form_view;?>
											</span>
										</div>
									</div>
						    	</div>
						    </div>
				    		<?php
				    	}
				    	else
					    {
					        ?>
					        <div class="col-lg-4">
						    	<div class="element-wrapper slimscroll">
						    		<div class="element-box">
								        <div class="">
								            <span id="showContent">
								                <center><h2 class="viewTitle"><?php echo $view_title;?></h2></center>
								            </span>
								        </div>
								    </div>
								</div>
							</div>
					        <?php
					    }
				    ?>
				</div>
				<?php
			}
		?>
	</div>
</div>
<?php
    require_once(APPPATH."views/base/common_js.php");
?>