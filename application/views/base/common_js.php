<script type="text/javascript">
  $(document).ready(function ()
  {
    // Select2
    $('.select2').select2();

    //Date picker
    $('.single-daterange').daterangepicker({
        singleDatePicker: true,
        //showDropdowns: true
        locale: {
                  //format: 'YYYY-MM-DD'
                  format: 'DD-MM-YYYY'
                }
    });

    //From Date picker
    $('.single-daterange-from').daterangepicker({
      singleDatePicker: true,
      locale: {
                //format: 'YYYY-MM-DD'
                format: 'DD-MM-YYYY'
              }
    });

    $('.single-daterange-to').daterangepicker({
        singleDatePicker: true,
        locale: {
                  format: 'DD-MM-YYYY'
                }
    });   

    //To date Picker loaded based on from date
    $('.single-daterange-from').on('apply.daterangepicker', function(ev, picker) {
      var fromDate = picker.startDate.format('DD-MM-YYYY');
      $('.single-daterange-to').daterangepicker({
        singleDatePicker: true,
        minDate: fromDate,
        locale: {
                  format: 'DD-MM-YYYY'
                }
      });
    });  
      
    // Toggle Menu
    $('legend').click(function() {
        var $this = $(this);
        var parent = $this.parent();
        var contents = parent.contents().not(this);
        if (contents.length > 0) {
            $this.data("contents", contents.remove());
        } else {
            $this.data("contents").appendTo(parent);
        }
        return false;
    });

    //While scroll select2 reinitialize
    $(window).scroll(function (event) {
        var scroll = $(window).scrollTop();

        if(scroll > 0)
        {
          $(".select2").select2();
        }
    });

    //AJAX Call datatable conversion
    <?php
    if($dataTableUrl)
    {
      ?>
        var oTable = $('#dataTableId').dataTable
        ({
          "sScrollX"        : "100%", 
          "sScrollXInner"   : "100%",
          "bProcessing"     : true,
          responsive        : true,
          "sAjaxSource"     : '<?php echo base_url(); ?>index.php/<?php echo $dataTableUrl;?>',
          "bJQueryUI"       : true,
          "ordering"        : true,
          "sPaginationType" : "full_numbers",
          "iDisplayStart "  : 20,
          "oLanguage"       : {
                                "sProcessing"     : "<img src='<?php echo base_url(); ?>global/assets/ajax-loader_dark.gif'>"

                              },
          "fnInitComplete"  : function()
                             {
                                //oTable.fnAdjustColumnSizing();
                             },
          'fnServerData'    : function(sSource, aoData, fnCallback)
                              {
                                $.ajax
                                ({
                                  'dataType': 'json',
                                  'type'    : 'POST',
                                  'url'     : sSource,
                                  'data'    : aoData,
                                  'success' : fnCallback
                                });
                              },
          "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                                  $(nRow).attr('id', aData[1]);
                              }
        });

        var oTable = $('#reportTable').dataTable
        ({
          "dom"             : 'lBfrtip',
          "buttons"         : [ 'copy','excel', 'print'],
          "lengthMenu"      : [ [ 10, 25, 50,100, -1], [10,25, 50,100,"All"] ],
          "sScrollX"        : "100%", 
          "scrollY"         : "600px",
          "scrollCollapse"  : true,
          "sScrollXInner"   : "100%",
          "bProcessing"     : true,
          //"serverSide"      : true,
          "responsive"      : true,
          "sAjaxSource"     : '<?php echo base_url(); ?>index.php/<?php echo $dataTableUrl;?>',
          "bJQueryUI"       : true,
          "sPaginationType" : "full_numbers",
          "iDisplayStart "  : 20,
          "aaSorting"       : [],
              "oLanguage"   : {
          "sProcessing"     :   "<img src='<?php echo base_url(); ?>global/assets/ajax-loader_dark.gif'>"
                              },
          "fnInitComplete": function()
           {
              //oTable.fnAdjustColumnSizing();
           },
          'fnServerData': function(sSource, aoData, fnCallback)
          {
            $.ajax
            ({
              'dataType': 'json',
              'type'    : 'POST',
              'url'     : sSource,
              'data'    : aoData,
              'success' : fnCallback
            });
          }
        });
      <?php 
    }
    ?>

    //Table to Datatable Convertion
    var oTable = $('#dataTableView').dataTable
    ({
      "sScrollX"        : "100%",
      "sScrollY"        : "500px",            
      "sScrollXInner"   : "100%",
      "bScrollCollapse" :  true,
      "paging"          : true,              
      "bProcessing"     : true,
      responsive        : true,
      "bJQueryUI"       : true,
      "sPaginationType" : "full_numbers",
      "iDisplayStart "  : 20,
    });         

    //Alert message fade
    $("#alert-message").fadeTo(2000, 500).slideUp(500, function(){
      $("#alert-message").slideUp(500);
      $("#alert-message").remove();
    });
    
    // Check all and un-check all for HR/attendance
    $("#checkAll").click(function(){
      $(".checkbox").prop("checked", true);
    });

    $("#uncheckAll").click(function(){
      $(".checkbox").prop("checked", false);
    }); 
  });

  //Remove load screen page loaded.
  window.addEventListener("load", function(){
  var load_screen = document.getElementById("load_screen");
  document.body.removeChild(load_screen);
  });

  //Romove Datatable filter search manually
  function removeDatatableSearch() {
    var table       = $('#dataTableId').DataTable();
    var reportTable = $('#reportTable').DataTable();    
    $(".searchTableData").val("");
    table.search("").draw();
    reportTable.search("").draw();
  }

  //Add new popup
  function addNewPop(addFormUrl, pkey)
  {
    $.ajax({
      type: "GET",
      url: "<?php echo base_url();?>"+addFormUrl,
      data: {'pkey_id' : pkey},
      dataType:"html",
      success:  function(html1)
                {      
                  if(html1 != 'success')
                  {  
                    $(".modal-body").html(html1); // msg in modal body
                    $(".modal").modal("show"); // show modal instead alert box
                  }
                },
    });
  }

  //Only Given Numbers
  function isNumberKey(evt) 
  {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if ((charCode < 48 || charCode > 57))
      return false;

      return true;
  }

  //Only Given Alphabetic keys
  function isAlphaKey(evt) 
  {
    var charCode = (evt.which) ? evt.which : window.event.keyCode;

    if (charCode <= 13) {
        return true;
    }
    else {
        var keyChar = String.fromCharCode(charCode);
        var re = /^[a-zA-Z ]+$/
        return re.test(keyChar);
    }
  }

  //Add Multiple Table Row
  function addNewRow(content_id)
  {
    var row = $("#"+content_id+" tr:last");

    row.find("select").each(function(index)
    {
      $(this).select2('destroy');
    }); 

    row.clone().find("input, textarea, select, button, checkbox, radio").each(function()
    {
      i   = $(this).data('row') + 1;
      id  = $(this).data('name') + i;

      $(this).val('').attr({'id' : id, 'data-row' : i});
    }).end().appendTo("#"+content_id);
    $("select.select2").select2();
  }

  //Common Approve swal
  function commonApprove(title, text, url) 
  {
    if(arguments[0] != null)
    {
      swal({
          title: title,
          text: text,
          type: "success",
          showCancelButton: true,
          confirmButtonColor: '#DD6B55',
          confirmButtonText: "Yes",
          cancelButtonText:  "Cancel",
          closeOnConfirm: false,
          closeOnCancel: false
        },
        function(isConfirm)
        {
          if (isConfirm)
          {
            location.href = url;
          } else 
          {
            swal("This operation has been cancelled", "", "error");
          }
       });     
    }
    else
    {
      return false;
    }
    return;
  }

  //Common Disapprove Swal
  function commonDisapprove(title, text, successMsg, url) 
  {
    if(arguments[0] != null)
    {
      swal({
          title: title,
          text: text,
          type: "input",
          showCancelButton: true,
          confirmButtonColor: '#30b947',
          confirmButtonText: "Send",
          cancelButtonText:  "Cancel",
          closeOnConfirm: false,
          closeOnCancel: true,
       },
       function(inputValue)
       {
          if (inputValue === false) return false;

          if (inputValue === "")
          {
            swal.showInputError("Please enter the reasons");
            return false;
          }

          $.ajax
          ({
            type : "POST",
            url  : url,
            data : {'reason'  : inputValue},
            success : function(data)
            {
              swal("Success!", successMsg, "success");
              location.reload();
            },
          });
       });
    }
    else
    {
      return false;
    }
    return;
  }
</script>