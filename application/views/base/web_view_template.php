<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title><?php echo (isset($title))? $title : $this->dbvars->app_name.' - '.$this->dbvars->meta_title;?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta content="ERP" name="description" />
        <meta content="zoot" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon --> 
        <link rel="shortcut icon" href="<?php echo base_url(); ?>global/assets/images/favicon.png">
        <script src="<?php echo base_url(); ?>global/assets/js/modernizr.min.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/plugins/moment/moment.js"></script>

        <link href="<?php echo base_url(); ?>global/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>global/assets/css/style.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>global/assets/css/custom.css" rel="stylesheet" type="text/css" />
        <script src="<?php echo base_url(); ?>global/assets/js/jquery.min.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/js/popper.min.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/js/bootstrap.min.js"></script> 
       
        <!-- App js -->
        <script src="<?php echo base_url(); ?>global/assets/js/jquery.app.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/js/dataTables.bootstrap4.min.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>global/assets/plugins/bootstrap-select/js/bootstrap-select.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>global/assets/plugins/moment/moment.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/plugins/bootstrap-timepicker/bootstrap-timepicker.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/plugins/clockpicker/js/bootstrap-clockpicker.min.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
        <script src="<?php echo base_url(); ?>global/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
        
        <!--DATATABLE Button-->        
        <script src="<?php echo base_url(); ?>global/assets/js/button/dataTables.select.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>global/assets/js/button/dataTables.buttons.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>global/assets/js/button/jszip.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>global/assets/js/button/pdfmake.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>global/assets/js/button/vfs_fonts.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>global/assets/js/button/buttons.html5.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>global/assets/js/button/buttons.print.min.js" type="text/javascript"></script>
        <!--SELECT2 CSS-->
        <link href="<?php echo base_url(); ?>global/assets/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" />
        <link href="<?php echo base_url(); ?>global/assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
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
            }
        ?>
    </body>
    <?php
            require_once(APPPATH."views/base/common_js.php");
        ?>
</html>