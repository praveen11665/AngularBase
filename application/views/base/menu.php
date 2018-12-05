<?php
    //Taken Count records for waiting for approvel buyer and seller
    $sellerwfa = $this->prefs->count_wfa('4');  //SellerCounts
    $buyerwfa  = $this->prefs->count_wfa('5');  //BuyerCounts
    $totalwfa  = $sellerwfa + $buyerwfa;  //total Waiting for approvel

    //Deal Counts
    $newdeals = $this->mcommon->specific_record_counts('product', array('is_delete' => '0', 'approved_status' => '0'));
?>
<!-- Navigation Menu-->
<ul class="navigation-menu">
    <li class="nav-menu">
        <a title="Dashboard" href="<?php echo base_url(); ?>"><i class="icon-home"></i> <?php echo $this->lang->line('menu_dashboard');?></a>
    </li>     
    <li class="has-submenu">
        <a href="#"><i class="icon-settings"></i> <?php echo $this->lang->line('menu_settings');?></a>
        <ul class="submenu">
            <li><a href="<?php echo base_url('modules/roles/add');?>"><?php echo $this->lang->line('submenu_role_management');?></a></li>
            <li><a href="<?php echo base_url('modules/users/add');?>"><?php echo $this->lang->line('submenu_user_management');?></a></li>
            <li><a href="<?php echo base_url('master/setting/add');?>"><?php echo $this->lang->line('submenu_company_settings');?></a></li>
        </ul>
    </li> 
    <li class="has-submenu">
        <a href="#"><i class="icon-grid"></i> <?php echo $this->lang->line('menu_master');?></a>
        <ul class="submenu">
            <li><a href="<?php echo base_url('master/country/add');?>"><?php echo $this->lang->line('submenu_country');?></a></li>
            <li><a href="<?php echo base_url('master/state/add');?>"><?php echo $this->lang->line('submenu_state');?></a></li>
            <li><a href="<?php echo base_url('master/city/add');?>"><?php echo $this->lang->line('submenu_city');?></a></li>
            <li><a href="<?php echo base_url('master/documentList/add');?>"><?php echo $this->lang->line('submenu_documents');?></a></li>
            <li><a href="<?php echo base_url('master/notification/add');?>"><?php echo $this->lang->line('submenu_notification');?></a></li>
        </ul>
    </li> 
    <li class="has-submenu">
        <a href="#"><i class="icon-people"></i> <?php echo $this->lang->line('menu_waiting_for_approval');?> <span class="notificationMenu"> <?php echo $totalwfa;?></span></a>
        <ul class="submenu">
            <li><a href="<?php echo base_url('seller/add');?>"><?php echo $this->lang->line('submenu_wfa_seller');?> <span class="notificationMenu"><?php echo $sellerwfa;?></span> </a></li>
            <li><a href="<?php echo base_url('buyer/add');?>"><?php echo $this->lang->line('submenu_wfa_buyer');?> <span class="notificationMenu"><?php echo $buyerwfa;?></span></a></li>
        </ul>
    </li>

    <li class="has-submenu">
        <a href="#"><i class="icon-user-following"></i> <?php echo $this->lang->line('menu_approved_vendors');?></a>
        <ul class="submenu">
            <li><a href="<?php echo base_url('sellerlist/add');?>"><?php echo $this->lang->line('submenu_av_seller');?></a></li>
            <li><a href="<?php echo base_url('buyer_list/add');?>"><?php echo $this->lang->line('submenu_av_buyer');?></a></li>
        </ul>
    </li>

    <li class="has-submenu">
        <a href="#"><i class="icon-docs"></i> <?php echo $this->lang->line('menu_deals');?> <span class="notificationMenu"> <?php echo $newdeals;?></span></a>
        <ul class="submenu">
            <li><a href="<?php echo base_url('new_deals/add');?>"><?php echo $this->lang->line('submenu_new_deals');?> <span class="notificationMenu"><?php echo $newdeals;?></span></a></li>  
            <li><a href="<?php echo base_url('deal_list/add');?>"><?php echo $this->lang->line('submenu_deal_list');?></a></li>
        </ul>
    </li>  

    <li class="has-submenu">
        <a href="#"><i class="icon-magic-wand"></i> <?php echo $this->lang->line('menu_transactions');?></a>
        <ul class="submenu">
            <li><a href="<?php echo base_url('new_transaction/add');?>"><?php echo $this->lang->line('submenu_new_transaction');?></a></li>
            <li><a href="<?php echo base_url('transaction_list/add');?>"><?php echo $this->lang->line('submenu_transaction_list');?></a></li>
        </ul>
    </li>

    <li class="has-submenu">
        <a href="#"><i class="icon-chart"></i> <?php echo $this->lang->line('menu_report');?></a>
        <ul class="submenu">
            <li><a href="<?php echo base_url('report/registration');?>"><?php echo $this->lang->line('submenu_registration_report');?></a></li>
            <li><a href="<?php echo base_url('report/dealList');?>"><?php echo $this->lang->line('submenu_deal_report');?></a></li>
            <li><a href="<?php echo base_url('report/transaction');?>"><?php echo $this->lang->line('submenu_transaction_report');?></a></li>
        </ul>
    </li>  

</ul> 
<!-- End navigation menu -->