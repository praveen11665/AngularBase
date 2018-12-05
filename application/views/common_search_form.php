<?php
    $countryDropdown    = $this->mcommon->Dropdown('countries', array('country_id as Key', 'country_name as Value'), array('status' => '1', 'is_delete' => '0'));  
    $stateDropdown      = $this->mcommon->Dropdown('states', array('state_id as Key', 'name as Value'), array('status' => '1', 'is_delete' => '0'));   
    $cityDropdown       = $this->mcommon->Dropdown('cities', array('city_id as Key', 'name as Value'), array('status' => '1', 'is_delete' => '0'));   
    $statusDropdown     = $this->mcommon->Dropdown('registration_status', array('reg_id as Key', 'status as Value'), array('reg_id !=' => '0'));
    $newStatusDropdown  = $this->mcommon->Dropdown('registration_status', array('reg_id as Key', 'status as Value'));
    $userDropdown       = $this->mcommon->Dropdown('users', array('user_id as Key', 'username as Value'), array('banned' => '0','auth_level' => '4'));
    $productDropdown    = $this->mcommon->Dropdown('product', array('product_id as Key', 'product_name as Value'), array('approved_status' => '1', 'is_delete' => '0'));
    $buyerDropdown      = $this->mcommon->Dropdown('users', array('user_id as Key', 'username as Value'), array('banned' => '0', 'auth_level' => '5'));

    if($this->session->userdata('from_date') || $this->session->userdata('to_date'))
    {
        $from_date              =  $this->session->userdata('from_date');
        $to_date                =  $this->session->userdata('to_date');        
        $country_id             =  $this->session->userdata('country_id');
        $state_id               =  $this->session->userdata('state_id');
        $city_id                =  $this->session->userdata('city_id');
        $status_id              =  $this->session->userdata('status_id');
        $user_id                =  $this->session->userdata('user_id');
        $product_id             =  $this->session->userdata('product_id');
        $buyer_id               =  $this->session->userdata('buyer_id');
    }   
?>
<form action="<?php echo base_url($ActionUrl);?>" method="post" autocomplete="off">
    <div class="row">
        <?php
        if(in_array('from_date', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_from_date');?></label>
                    <input type="text" name="from_date" id="from_date" value="<?php echo $from_date;?>" class="single-daterange-from form-control"/>
                </div> 
            </div> 
        <?php
        }
        ?>
        <?php
        if(in_array('to_date', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_to_date');?></label>
                    <input type="text" name="to_date" id="to_date" value="<?php echo $to_date;?>" class="single-daterange-to form-control" cannot-input/>
                </div>  
            </div>
        <?php
        }
        ?> 
        <?php
        if(in_array('country', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_country');?></label>
                    <?php
                        $extraAttr="id='country_id' class='form-control select2'";
                        echo form_dropdown('country_id', $countryDropdown, $country_id, $extraAttr);
                    ?>
                </div>  
            </div>
        <?php
        }
        ?>  
        <?php
        if(in_array('state', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_state');?></label>
                    <?php
                        $extraAttr="id='state_id' class='form-control select2'";
                        echo form_dropdown('state_id', $stateDropdown, $state_id, $extraAttr);
                    ?>
                </div>  
            </div>
        <?php
        }
        ?>   
        <?php
        if(in_array('city', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_city');?></label>
                    <?php
                        $extraAttr="id='city_id' class='form-control select2'";
                        echo form_dropdown('city_id', $cityDropdown, $city_id, $extraAttr);
                    ?>
                </div>  
            </div>
        <?php
        }
        ?> 
        <?php
        if(in_array('products', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_products');?></label>
                    <?php
                        $extraAttr="id='product_id' class='form-control select2'";
                        echo form_dropdown('product_id', $productDropdown, $product_id, $extraAttr);
                    ?>
                </div>  
            </div>
        <?php
        }
        ?>  
        <?php
        if(in_array('users', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_sellers');?></label>
                    <?php
                        $extraAttr="id='user_id' class='form-control select2'";
                        echo form_dropdown('user_id', $userDropdown, $user_id, $extraAttr);
                    ?>
                </div>  
            </div>
        <?php
        }
        ?>            
        <?php
        if(in_array('buyers', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_buyer');?></label>
                    <?php
                        $extraAttr="id='buyer_id' class='form-control select2'";
                        echo form_dropdown('buyer_id', $buyerDropdown, $buyer_id, $extraAttr);
                    ?>
                </div>  
            </div>
        <?php
        }
        ?> 
        <?php
        if(in_array('status', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_status');?></label>
                    <?php
                        $extraAttr="id='status_id' class='form-control select2'";
                        echo form_dropdown('status_id', $statusDropdown, $status_id, $extraAttr);
                    ?>
                </div>  
            </div>
        <?php
        }
        ?> 
        <?php
        if(in_array('newstatus', $filterArr))
        {
        ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label><?php echo $this->lang->line('label_status');?></label>
                    <?php
                        $extraAttr="id='status_id' class='form-control select2'";
                        echo form_dropdown('status_id', $newStatusDropdown, $status_id, $extraAttr);
                    ?>
                </div>  
            </div>
        <?php
        }
        ?>                                 
        <!--<div class="col-md-2">
            <div class="form-group">
                <label style="visibility: hidden;">Button</label><br>
                <button class="btn btn-success" type="submit" name="submit">Search</button>
            </div>
        </div>-->
    </div>
    <hr />
    <div class="form-buttons-w text-right">
        <a href="<?php echo base_url($ActionUrl);?>" class="btn btn-danger"><?php echo $this->lang->line('label_reset');?></a>
        <button class="btn btn-success" type="submit" name="submit">Search</button>
    </div>
</form>