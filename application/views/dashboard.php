<?php
  if($this->session->userdata('from_date'))
  {
    $from_date              = $this->session->userdata('from_date');
    $to_date                = $this->session->userdata('to_date'); 

    $fromDate               = $this->session->userdata('from_date');
    $toDate                 = $this->session->userdata('to_date');    
  }else
  {
    $from_date              = '';
    $to_date                = '';
    $fromDate               = date('Y-m-d', strtotime('today - 10 days'));
    $toDate                 = date('Y-m-d');
  } 

  //Taken Count records for waiting for approvel buyer and seller
  $sellerwfa                = $this->prefs->count_wfa('4', '',  $from_date, $to_date);  //SellerCounts
  $buyerwfa                 = $this->prefs->count_wfa('5', '',  $from_date, $to_date);  //BuyerCounts
  $approvedSeller           = $this->prefs->count_wfa('4', '1', $from_date, $to_date);  //Approved SellerCounts
  $approvedBuyer            = $this->prefs->count_wfa('5', '1', $from_date, $to_date);  //Approved BuyerCounts
  $disApprovedSeller        = $this->prefs->count_wfa('4', '2', $from_date, $to_date);  //Disapproved BuyerCounts
  $disApprovedBuyer         = $this->prefs->count_wfa('5', '2', $from_date, $to_date);  //Disapproved BuyerCounts

  //DEAL
  $newdeals                 = $this->prefs->dealCounts('0', $from_date, $to_date);
  $approveddeals            = $this->prefs->dealCounts('1', $from_date, $to_date);
  $disApproveddeals         = $this->prefs->dealCounts('2', $from_date, $to_date); 

  //TRANSACTION
  $newtransaction           = $this->prefs->transactionCounts('0', $from_date, $to_date);
  $approvedtransaction      = $this->prefs->transactionCounts('1', $from_date, $to_date);
  $disApprovedtransaction   = $this->prefs->transactionCounts('2', $from_date, $to_date);  

  //TAKEN RECORDS FROM DEAL REQUEST
  $transactionData          = $this->prefs->getTransactionData();

  foreach ($transactionData as $row) 
  {
    $transactionDate                          = date('d-M,y', strtotime($row->request_date));
    $totalTransactionDate[$transactionDate][] = $row->totalCount;
  }

  $dateArr    = date_range($fromDate, $toDate);    
  foreach ($dateArr as $key => $date) 
  {
    $totalObsCount              = array_sum($totalTransactionDate[$date]);
    $lineChartDate['label'][]   = '"'.$date.'"'; 
    $lineChartDate['data'][]    =  ($totalObsCount) ? $totalObsCount : '0';
  }
?>
<!-- Main content -->
<div class="main-content" ng-app="myapp" ng-controller="myCtrl">
  <!-- Top navbar -->
  <nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
    <div class="container-fluid">
      <!-- Brand -->
      <!--<a class="h4 mb-0 text-uppercase d-none d-lg-inline-block"><?php echo $this->lang->line('label_dashboard');?></a>-->
      <form class="form-inline mr-3 d-none d-md-flex ml-lg-auto" action="<?php echo base_url('dashboard');?>" method="post" autocomplete="off">
        <div class="form-group mb-0">
          <div class="input-group input-group-alternative">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-calendar"></i></span>
            </div>
            <input class="single-daterange-from form-control halfwidth" type="text" name="from_date" value="<?php echo $from_date;?>">
          </div>&nbsp;
          <div class="input-group input-group-alternative">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
            </div>
            <input class="single-daterange-to form-control halfwidth" type="text" name="to_date" value="<?php echo $to_date;?>" cannot-input>
          </div>&nbsp;
          <button class="btn btn-info"><i class="fas fa-search"></i></button>&nbsp;
          <a href="<?php echo base_url();?>" class="btn btn-danger">X</i></a>
        </div>
      </form>
    </div>
  </nav>
  <!-- Header -->
  <div class="header pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
      <div class="header-body">
        <!-- Card stats -->
        <!--!st Row Waiting vendors and New deals and transactions-->
        <div class="row">
          <!--Waiting For Approval - Seller-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_wfa_seller');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $sellerwfa;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                      <i class="fas fa-clock"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
                  <span class="text-nowrap">Since last month</span>
                </p>-->
              </div>
            </div>
          </div>
          <!--Waiting For Approval - Buyer-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_wfa_buyer');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $buyerwfa;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                      <i class="fas fa-clock"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-danger mr-2"><i class="fas fa-arrow-down"></i> 3.48%</span>
                  <span class="text-nowrap">Since last week</span>
                </p>-->
              </div>
            </div>
          </div>
          <!--New Deals-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_new_deals');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $newdeals;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                      <i class="fas fa-cart-plus"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
                  <span class="text-nowrap">Since last month</span>
                </p>-->
              </div>
            </div>
          </div>
          <!--New Transaction-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_new_transaction');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $newtransaction;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                      <i class="fas fa-credit-card"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-warning mr-2"><i class="fas fa-arrow-down"></i> 1.10%</span>
                  <span class="text-nowrap">Since yesterday</span>
                </p>-->
              </div>
            </div>
          </div>
        </div><br>

        <!--2nd Row approved vendors, deals and transactions-->
        <div class="row">
          <!--Approved Seller-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_approved_seller');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $approvedSeller;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-green text-white rounded-circle shadow">
                      <i class="fas fa-check"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-warning mr-2"><i class="fas fa-arrow-down"></i> 1.10%</span>
                  <span class="text-nowrap">Since yesterday</span>
                </p>-->
              </div>
            </div>
          </div>
          <!--Approved Buyer-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_approved_buyer');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $approvedBuyer;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-green text-white rounded-circle shadow">
                      <i class="fas fa-check"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> 12%</span>
                  <span class="text-nowrap">Since last month</span>
                </p>-->
              </div>
            </div>
          </div>           
          <!--Approved Deals-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_approved_deals');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $approveddeals;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-green text-white rounded-circle shadow">
                      <i class="fas fa-thumbs-up"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-danger mr-2"><i class="fas fa-arrow-down"></i> 3.48%</span>
                  <span class="text-nowrap">Since last week</span>
                </p>-->
              </div>
            </div>
          </div>
          <!--Approved Transaction-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_approved_transaction');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $approvedtransaction;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-green text-white rounded-circle shadow">
                      <i class="fas fa-thumbs-up"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> 12%</span>
                  <span class="text-nowrap">Since last month</span>
                </p>-->
              </div>
            </div>
          </div>
        </div><br>

        <!--3rd Row disapproved vendors, deals and transactions-->
        <div class="row">
          <!-- Disapproved Seller -->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_disapproved_seller');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $disApprovedSeller;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                      <i class="fas fa-user-times"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
                  <span class="text-nowrap">Since last month</span>
                </p>-->
              </div>
            </div>
          </div>
          <!--Disapproved Buyer-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_disapproved_buyer');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $disApprovedBuyer;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                      <i class="fas fa-user-times"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-danger mr-2"><i class="fas fa-arrow-down"></i> 3.48%</span>
                  <span class="text-nowrap">Since last week</span>
                </p>-->
              </div>
            </div>
          </div>
          <!--Disapproved Deals-->
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_disapproved_deals');?></h5> 
                    <span class="h2 font-weight-bold mb-0"><?php echo $disApproveddeals;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                      <i class="fas fa-thumbs-down"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
                  <span class="text-nowrap">Since last month</span>
                </p>-->
              </div>
            </div>
          </div>           
          <!--Disapproved transactions-->            
          <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $this->lang->line('label_disapproved_transaction');?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php echo $disApprovedtransaction;?></span>
                  </div>
                  <div class="col-auto">
                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                      <i class="fas fa-thumbs-down"></i>
                    </div>
                  </div>
                </div>
                <!--<p class="mt-3 mb-0 text-muted text-sm">
                  <span class="text-danger mr-2"><i class="fas fa-arrow-down"></i> 3.48%</span>
                  <span class="text-nowrap">Since last week</span>
                </p>-->
              </div>
            </div>
          </div>
        </div>
        <!-- Card ends -->
      </div>
    </div>
  </div>
  <!-- Page content -->
  <div class="container-fluid mt--7">
      <div class="row">
        <div class="col-xl-8 mb-5 mb-xl-0">
          <div class="card shadow">
            <div class="card-header bg-transparent">
              <div class="row align-items-center">
                <div class="col">
                  <h2 class="text-black mb-0"><?php echo $this->lang->line('label_date_wise_approved_transaction');?></h2>
                </div>
                <div class="col">
                 <!--Bar Chart-->
                </div>
              </div>
            </div>
            <div class="card-body card-chart-body">
              <!-- Chart -->
              <div class="chart">
                <!-- Chart wrapper -->
                  <canvas id="canvas_chart"></canvas>
                  <script>
                    var config = 
                    {
                        type: 'line',
                        data: 
                        {
                            labels: [<?php echo implode(',', $lineChartDate['label']);?>],
                            datasets: 
                            [{
                                label: "<?php echo $this->lang->line('label_total_transaction');?>",
                                backgroundColor: window.chartColors.red,
                                borderColor: window.chartColors.blue,
                                data: [<?php echo implode(',', $lineChartDate['data']);?>],
                                    fill: true,
                                    borderDash: [3, 3],
                                    pointRadius: 10,
                                    pointHoverRadius: 3,
                            }]
                        },
                        options: {
                            plugins: {
                                datalabels: {
                                    color: 'white',
                                    font: {
                                        weight: 'bold'
                                    },
                                    formatter: Math.round
                                }
                            }
                        }
                    };    
                    window.onload = function() 
                    {
                      var ctx       = document.getElementById("canvas_chart").getContext("2d");
                      window.myLine = new Chart(ctx, config);                  
                    };                 
                  </script>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-4">
          <div class="card shadow">
            <div class="card-header bg-transparent">
              <div class="row align-items-center">
                <div class="col">
                  <h2 class="mb-0"><?php echo $this->lang->line('label_total_approved_chart');?></h2>
                </div>
              </div>
            </div>
            <div class="card-body card-chart-body">
              <!-- Pie Chart -->
                <center><div id="piechart_3d" style="width: 500px; height: 450px;"></div></center>
            </div>
          </div>
        </div>
      </div>
</div>

<!--Pie Chart Script-->
<script type="text/javascript">
  google.charts.load("current", {packages:["corechart"]});
  google.charts.setOnLoadCallback(drawChart);
  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ['<?php echo $this->lang->line('label_approved_data');?>', '<?php echo $this->lang->line('label_total');?>'],
      ['<?php echo $this->lang->line('label_seller');?>', <?php echo $approvedSeller;?>],
      ['<?php echo $this->lang->line('label_buyer');?>', <?php echo $approvedBuyer;?>],
      ['<?php echo $this->lang->line('label_deals');?>', <?php echo $approveddeals;?>],
      ['<?php echo $this->lang->line('label_transaction');?>', <?php echo $approvedtransaction;?>]
    ]);

    var options = {        
        title: '',
        is3D: true,
        sliceVisibilityThreshold: 0,
        pieSliceText: 'percentage',
        backgroundColor: { fill:'transparent' }
    };

    var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
    chart.draw(data, options);
  }
</script>