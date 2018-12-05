

<div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Website Settings</h4>
        <p class="text-muted font-11">Manage website dynamic contents</p>
    </div>    
</div>

<!-- /.start form -->
<div class="row">
  <div class="col-md-12">
    <div class="white-box">
      <?php
      if($this->session->flashdata('res'))
      {
        ?>
        <div class="alert alert-<?php echo $this->session->flashdata('res_type'); ?> successmessage">
          <?php   echo $this->session->flashdata('res'); ?>
        </div>
        <?php
      }
      ?>
      <?php echo form_open("MaruncmsSetting");?>

      <div class="row">
        <div class="col-sm-12 col-xs-12">
          <?php
          foreach ($table_data as $key => $value) 
          {
              ?>
              <div class="form-group fg-line">
                  <label><?php echo ucfirst(str_replace("_", " ", $value['key'])); ?></label>
                  <textarea rows="5" name="<?php echo $value['key']; ?>" class="form-control"><?php echo $value['value']; ?></textarea>
                  <?php echo form_error($value['key']); ?>
              </div>
              <?php
          }
          ?>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12 col-xs-12 text-right">        
          <input type="hidden" name="update_settings" value="1">
          <button type="submit" name="update" class="btn btn-success waves-effect waves-light m-r-10 text-right">Submit</button>
        </div>
      </div>
      <?php echo form_close();?>
    </div>
  </div>
</div>
<!-- /.end form -->

<div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_country');?></label>
                    
                    ?>
                </div>  
            </div>



