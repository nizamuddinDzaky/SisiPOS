<style type="text/css">
span#basic-addon1:hover {
    background-color: #f7f7f7;
}

.input-group-prepend .input-group-text {
  border-radius: .25rem !important;
  border-top-left-radius: 0 !important;
  border-bottom-left-radius: 0 !important;
  border-left: hidden;
}
</style>

<section class="section-cover-red" style="min-height: auto !important; padding-top: 4.5rem !important;">
  <div class="container container-md">
    <ol class="breadcrumb">
      <li><a href="<?=base_url(aksestoko_route("aksestoko/home/main"))?>" class="active"><i class="fal fa-angle-left"></i> Kembali</a></li>
    </ol>
  </div>
</section>

<section class="section-orders-header p-0">
  <div class="container container-md">
    <div class="heading">
      <h2 class="title-pemesanan">Pemesanan</h2>
    </div>
    <div class="row">
      <div class="col-12">
        <ul class="nav nav-tabs" role="tablist">
          <li role="presentation" class="active"><a href="#onGoing" class="dalam-proses-tab" aria-controls="onGoing" role="tab" data-toggle="tab" aria-expanded="true">Dalam Proses</a></li>
          <li role="presentation" class=""><a href="#completed" class="selesei-tab" aria-controls="completed" role="tab" data-toggle="tab" aria-expanded="false">Selesai</a></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="section-orders-content">
  <div class="container container-md">
    <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="onGoing">

        <!-- FORM SEARCH -->
        <!-- <div class="box p-box box-order-datepicker">
          <h3 class="box-subtitle">Masukan ID Pesanan Anda</h3>
          <div class="row">
            <div class="col-md-12">
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fal fa-search text-primary"></i></span>
                </div>
                <input type="text" class="form-control" placeholder="Masukan ID Pesanan Anda disini" id="search-on-going" aria-describedby="basic-addon1" required=""> 
              </div>
            </div>
          </div>
        </div> -->
        <div class="box p-box box-order-datepicker">
          <div class="row">
            <div class="col-md-12">
              <div class="input-group mb-3">
                <input type="text" id="search-on-going" class="form-control" placeholder="Cari Pesanan ..." aria-describedby="basic-addon1" required=""> 
                <div class="input-group-prepend">
                  <span class="input-group-text btn" id="basic-addon1"><i class="fal fa-search text-primary"></i></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- START: Order Empty -->
        <div id="onGoing-data">
          <div id="no-order-ongoing" style="visibility: hidden">
            <div class="box-cart">
              <img src="<?=$assets_at?>img/common/order-empty.png" class="img-fluid" alt="Order Empty">
              <p>Tidak ada pesanan dalam proses</p>
            </div>
          </div>
        </div>
        <div class="clearfix mt-3 text-center" id="pagination-link-on-going">
          
        </div>
        <!-- <div class="tempat-pagination" ></div> -->
      </div>
      <div role="tabpanel" class="tab-pane" id="completed">
        <!-- <div class="box p-box box-order-datepicker">
          <h3 class="box-subtitle">Masukan ID Pesanan Anda</h3>
          <div class="row">
            <div class="col-md-12">
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fal fa-search text-primary"></i></span>
                </div>
                <input type="text" class="form-control" placeholder="Masukan ID Pesanan Anda disini" id="search-completed" aria-describedby="basic-addon1" required=""> 
              </div>
            </div>
          </div>
        </div> -->
        <div class="box p-box box-order-datepicker">
          <div class="row">
            <div class="col-md-12">
              <div class="input-group mb-3">
                <input type="text" id="search-completed" class="form-control" placeholder="Cari Pesanan ..." aria-describedby="basic-addon1" required=""> 
                <div class="input-group-prepend">
                  <span class="input-group-text btn" id="basic-addon1"><i class="fal fa-search text-primary"></i></span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- START: Order Empty -->
        <div id="completed-data">
          <div id="no-order-completed" style="visibility: hidden">
            <div class="box-cart">
              <img src="<?=$assets_at?>img/common/order-empty.png" class="img-fluid" alt="Order Empty">
              <p>Tidak ada pesanan yang telah selesai</p>
            </div>
          </div>
        </div>



        <!-- END: Order Empty -->

        <!-- START: Any Order -->
          

          <div class="clearfix mt-3 text-center" id="pagination-link-complete">
            
          </div>
        <!-- End: Any Order -->
      </div>




    </div>
  </div>
</section>


<div id="loader-section">
  <div class="loaderBox">
    <div class="loaderKotak">
      <div class="loaderNew">   
        <span class="boxLoader"></span>   
          <span class="boxLoader"></span>  
          <div class="codeLoader"> 
            <img class="image-loader" src="<?=$assets_at?>loader/loader.png">
          </div>    
        <span class="txtLoader"><i>Ditunggu ya</i></span>
      </div>
    </div>
  </div>
</div>



  <!--Modal Satu-->
  <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body p-box">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"><i class="fal fa-times"></i></span>
        </button>
        
        <h4 class="modal-title mb-2">Terima Barang</h4>
        <div class="row">
            <table class="table table-hover maintable">
              <thead class="small thtable">
                <tr>
                  <th style="vertical-align:middle; text-align: center;">No SPJ</th>
                  <th style="vertical-align:middle; text-align: center;">Status</th>
                  <th style="vertical-align:middle; text-align: center;">Tanggal Dikirim</th>
                  <th style="vertical-align:middle; text-align: center;">Dikirim Oleh</th>
                  <th style="vertical-align:middle; text-align: center;">Aksi</th>
                </tr>
              </thead>
              <tbody class="small thtable" id="data-delivery">
                  <tr>
                    <td style="vertical-align:middle" colspan="5">Tidak ada data pengiriman untuk diterima</td>
                  </tr>
                  
              </tbody>
            </table>
            
        </div>
      </div>
    </div>
  </div>
</div>


<!-- <script src="<?=$assets_at?>plugins/jquery-3.3.1/jquery.min.js"></script> -->
<script type="text/javascript">
  $(document).ready(function(){

  $("#loader-section").show();

  $("#search-on-going").change(function(){
    load_ongoing_order_data(1);
  });

  function load_ongoing_order_data(page){
    $("#loader-section").show();
    let search = $('#search-on-going').val();
    if (search != '') {
      search = '?search='+search;
    }else{
      search='';
    }    
    
    $.ajax({
      url: "<?= base_url(aksestoko_route('aksestoko/order/orders_on_going_data/'))?>"+page+search,
      method : "GET",
      dataType : "json",
      success : function(data){
        // $('#loader-section').hide();
        $("#onGoing-data").hide().html(data.orders).fadeIn(1000);
        
        $("#pagination-link-on-going").html(data.pagination);
      },
      error: function(data, e){
        $('#loader-section').hide();
        let html = '<div id="no-order-ongoing">' +
          '<div class="box-cart">' +
            '<img src="<?=$assets_at?>img/common/order-empty.png" class="img-fluid" alt="Order Empty">' +
            '<p>Tidak ada pesanan dalam proses</p>' +
          '</div>' +
        '</div>';
        $("#onGoing-data").hide().html(html).fadeIn(1000);
        $("#pagination-link-on-going").html('');
      }
    })
  }
    
    load_ongoing_order_data(1);
    $(document).on("click","#pagination-link-on-going a", function(event){
      event.preventDefault();
      var page = $(this).data("ci-pagination-page");
      load_ongoing_order_data(page);
    });


    $('#search-completed').change(function(){
      load_complete_order_data(1);
    });

    function load_complete_order_data(page){
      $("#loader-section").show();
      let search = $('#search-completed').val();
      if (search != '') {
        search = '?search='+search;
      }else{
        search='';
      }
      $.ajax({
        url: "<?= base_url(aksestoko_route('aksestoko/order/orders_complete_data/'))?>"+page+search,
        method : "GET",
        dataType : "json",
        success : function(data){
          $("#completed-data").html(data.orders);
          $("#pagination-link-complete").html(data.pagination);
          
        },
        error: function(data, e){
          
          let html = '<div id="no-order-completed">' +
            '<div class="box-cart">' +
              '<img src="<?=$assets_at?>img/common/order-empty.png" class="img-fluid" alt="Order Empty">' +
              '<p>Tidak ada pesanan yang telah selesai</p>' +
            '</div>' +
          '</div>';
          $("#completed-data").hide().html(html).fadeIn(1000);
          $("#pagination-link-complete").html('');
        }
      })

    }

    load_complete_order_data(1);
    $(document).on("click","#pagination-link-complete a", function(event){
      $("#loader-section").show();
      event.preventDefault();
      
      var page = $(this).data("ci-pagination-page");
      load_complete_order_data(page);

    })

    $(document).ajaxStop(function() {
      $('#loader-section').fadeOut(200);


        <?php
          if (!$guide->order) {
        ?>
            if (isStart) hopscotch.startTour(tour);
            

        <?php
          }
        ?>

    });

  $(document).on("click",".btn-received", function(event){
    let orderId = $(this).data('id')
    $("#loader-section").show();
      $.ajax({
          url: "<?= base_url(aksestoko_route('aksestoko/order/get_delivery/')) ?>"+orderId,
          method : "GET",
          dataType : "json",
          success : function(data){
            $('#data-delivery').html(data.data_delivery);  
            $('#modal1').modal('show');
            // console.log(data);
          },
      })
      event.preventDefault();
    })
  });

   var isStart = true;
        // Define the tour!
        var tour = {
            id: "order",
            onClose: function(){
                hopscotch.endTour(tour);
                callAjax();
            },
            onEnd : function(){
                        callAjax()
            },
            
            steps: [

                {
                    title: "Konfirmasi Pembayaran",
                    content: "Klik tombol konfirmasi",
                    target: "a#konfirmasi_pembayaran",
                    placement: "top",
                },
                {
                    title: "Terima Barang",
                    content: "Terima Barang",
                    target: "a#terima-barang",
                    placement: "top",
                },
                {
                    title: "Lihat Detail Order",
                    content: "Lihat Detail Order",
                    target: "a#lihat_detail",
                    placement: "left",
                    
                },
                {
                    title: "Order Status Selesei",
                    content: "Melihat order status selesei",
                    target: "a#completed",
                    placement: "bottom",
                },
            ]
            
        };

        function callAjax(){
          isStart = false;
          $.ajax({
            url      : '<?= base_url(aksestoko_route('aksestoko/auth/set_guide/order/1')); ?>',
            type     : 'GET',
          }) 
          
        }
</script>