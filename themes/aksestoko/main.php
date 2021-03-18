<!-- <section class="section-home-header">
  <div class="content">
    <div class="container">
      <div class="row row-3">
        <div class="col-5 col-md-6" id="tespoint">
          <a href="javascript:void(0)">
            <img src="<?=$assets_at?>img/common/ic_header-trophy-light.png" class="img-fluid d-none d-md-inline-block" alt="Icon Truck">
            <small>Poin</small>
            <p>
              <img src="<?=$assets_at?>img/common/ic_header-trophy-light.png" class="img-fluid d-inline-block d-md-none" alt="Icon Trophy">
              <strong><?=$poin?></strong> pts
            </p>
          </a>
        </div>
        <div class="col-7 col-md-6">
          <a href="<?=base_url('aksestoko/home/reward')?>">
            <img src="<?=$assets_at?>img/common/ic_header-truck-light.png" class="img-fluid d-none d-md-inline-block" alt="Icon Truck">
            <small>Contractual Reward</small>
            <p>
              <img src="<?=$assets_at?>img/common/ic_header-truck-light.png" class="img-fluid d-inline-block d-md-none" alt="Icon Truck">
              <strong>1000</strong> ton / 2000 ton
            </p>
          </a>
        </div>
      </div>
    </div>
  </div>
</section> -->



<style type="text/css">
  .py-main {
    padding-top: 0 !important;
    padding-bottom: 5rem;
  }
  .section-products .container {
     margin-top: 0 !important;
      padding-top: 20px; 
}
.box-order-datepicker {
  margin-bottom: 0 !important;
}
span#basic-addon1:hover {
    background-color: #f7f7f7;
    padding-color:#B20838;
}
section.section-cover-dashboard {
    padding-top: 20px;
}

.input-group-prepend .input-group-text {
  border-radius: .25rem !important;
  border-top-left-radius: 0 !important;
  border-bottom-left-radius: 0 !important;
  border-left: hidden;
}

.label-text-promo{
  background: #b20838;
  color: white;
  padding: 6px;
  border-bottom-left-radius: 5px;
  border-top-left-radius: 5px;
  position: absolute;
  text-align: center;
  z-index: 111111;
  font-style: normal;
  font-size: 0.5rem;
  top: 15px;
  right: 0;
}

.img-promo{
  width: 100%;
  height: 100%;
}

/* MODAL */
/* The Modal (background) */
.modal.image-promo {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1111; /* Sit on top */
  padding-top: 7rem; /* Location of the box */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
}

/* Modal Content (image) */
img.modal-content {
  margin: auto;
  display: block;
  width: 80%;
  max-width: 700px;
}

/* Caption of Modal Image */
#caption {
  margin: auto;
  display: block;
  width: 80%;
  max-width: 700px;
  text-align: center;
  color: #ccc;
  padding: 10px 0;
  height: 150px;
}

/* Add Animation */
img.modal-content, #caption {  
  -webkit-animation-name: zoom;
  -webkit-animation-duration: 0.6s;
  animation-name: zoom;
  animation-duration: 0.6s;
}

@-webkit-keyframes zoom {
  from {-webkit-transform:scale(0)} 
  to {-webkit-transform:scale(1)}
}

@keyframes zoom {
  from {transform:scale(0)} 
  to {transform:scale(1)}
}

/* The Close Button */
.close {
  position: absolute;
  top: 35px;
  right: 35px;
  color: #fff;
  font-size: 40px;
  font-weight: bold;
  transition: 0.3s;
}

.close:hover,
.close:focus {
  color: #bbb;
  text-decoration: none;
  cursor: pointer;
}

/* 100% Image Width on Smaller Screens */
@media only screen and (max-width: 700px){
  img.modal-content {
    width: 100%;
  }
}
/* END MODAL */
</style>

<section class="section-cover-red" style="min-height: auto !important; padding-top: 4.5rem !important;">
    <div class="container">
      <ol class="breadcrumb">
        <li><a href="<?=base_url(aksestoko_route("aksestoko/home/select_supplier"))?>" class="active"><i class="fal fa-angle-left"></i> Kembali</a></li>
      </ol>
    </div>
  </section>
    <section class="section-cover-dashboard" style="padding-top: 0!important;">
      <div class="container">
        <div class="heading-w-link text-white p-0">
          <h2 class="animated fadeInUp" id="daftar-product-title">Daftar Produk</h2>
        </div>
        <div class="">
          <p class="white animated fadeInUp">Pada Distributor <a class="active bold" style="cursor:pointer !important" data-toggle="modal" data-target="#modalSupplier"><?=$supplier->company?> <i class="fal fa-info-circle"></i></a></p>
        </div>
      </div>
    <div class="container animated fadeInDown" style="padding-bottom: 20px;">
      <div class="box p-box box-order-datepicker">
        <div class="row">
          <div class="col-md-12">
            <div class="input-group mb-3">
              <input type="text" id="product-search" class="form-control" placeholder="Cari Produk .." aria-describedby="basic-addon1" required=""> 
              <div class="input-group-prepend">
                <span class="input-group-text btn" id="basic-addon1"><i class="fal fa-search text-primary"></i></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </section>
    <section class="section-products py-main">
      <div class="container margin-top-minus">
        <div class="product-list-home">

          <div class="row" id="product-data"></div>
          <div class="clearfix mt-3 text-center" id="pagination-link"></div>

        </div>
      </div>
    </section>
    <?php if (count($promotion)>0){ ?>
      <div class="section-featured-banners animated fadeInUp delayp1 py-5">
        <div class="container">
          <div class="heading-w-link text-white mb-4">
            <h2 class="animated fadeInUp" style="color: #333;">Berita Baru</h2>
          </div>

          <div class="featured-banner-list">
            <img src="<?=$assets_at?>img/common/ic_banner_sliderleft.png" id="prev-carousel" class="arrow-prev" alt="Arrow down hint"/>
            <img src="<?=$assets_at?>img/common/ic_banner_sliderright.png" id="next-carousel" class="arrow-next" alt="Arrow down hint"/>
            <div class="owl-carousel owl-theme owl-dots-solid">

              <?php
              
              foreach ($promotion as $prom) {?>

                <div class="featured-banner-item">
                <!-- <div class="featured-banner-img " style="background: url('<?php echo base_url()."assets/uploads".$prom->url_image ?>') no-repeat center; background-size: cover;"> -->
                   <div class="featured-banner-img panel-body" style="text-align: center; overflow: hidden; padding: 0;">
                  
                    <!-- <img class="img-promo" src="<?php #echo base_url()."assets/uploads".$prom->url_image ?>" style="height:auto;"> -->
                    <img class="img-promo" src="<?php echo filter_var($prom->url_image, FILTER_VALIDATE_URL) ? $prom->url_image : base_url("assets/uploads".$prom->url_image) ?>" style="height:auto;">
                    <?php if($prom->type_news == 'promo'): ?>
                      <h6 class="label-text-promo">PROMOSI</h6>
                    <?php endif ?>
                  </div>
                  <div class="featured-banner-info">
                    <h3><?= $prom->name?></h3>
                    <?php if($prom->type_news == 'promo'): ?>
                      Kode Promo : <span class="font-weight-bold"><?=$prom->code_promo?></span> 
                      <p> <a href="javascript:void(0)" class="text-blue2 salinKodePromo" id="salinKodePromo" data-promo="<?=$prom->code_promo?>"><i class="fal fa-copy mr-1" ></i> Salin Kode</a></p>
                    <?php endif ?>
                    
                    <?php if($prom->type_news == 'promo'): ?>
                      <p>
                        <?=strlen($prom->description) > 0 ? $prom->description : '-' ?>
                      </p>
                    <?php else: ?>
                      <p></p>
                      <?=strlen($prom->description) > 0 ? $prom->description : '-' ?>
                    <?php endif ?>
                    <?php if($prom->type_news == 'promo'): ?>
                      <small>Min Pembelian Rp <?= number_format($prom->min_pembelian, 2, ',', '.');?> | Max Potongan Rp <?= number_format($prom->max_total_disc, 2, ',', '.');?> | Berlaku sampai <?=$my_controller->__convertDate($prom->end_date)?></small>
                    <?php endif ?>
                  </div>
                </div>      
              <?php
              } ?>

            </div>
          </div>

        </div>
      </div>
    <?php } ?>

    <!-- The Modal Show Image Promo-->
    <div id="modalPromo" class="modal image-promo">
      <span class="close" onclick="closeModalPromo()">&times;</span>
      <img class="modal-content" id="img01">
      <div id="caption"></div>
    </div>
<!-- 
<div id="loader-section" style="top: 0; width: 100vw; height: 100vh; position: fixed; background-color: rgba(0, 0, 0, 0.4); z-index:1028">
  <div class="loader" >
    <center>
        <img class="loading-image" src="<?=$assets_at?>loader/Ellipsis.gif" alt="loading..">
    </center>
  </div>    
</div> -->

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

<!-- modal -->
<div class="modal fade" id="modalSupplier" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom: none!important; padding-bottom: 0px!important">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
          <img src="<?= $supplier->avatar == '' || !$supplier->avatar ? base_url('assets/uploads/logos/') . $supplier->logo : base_url('assets/uploads/avatars/') .$supplier->avatar ?>" onerror="this.src='<?= base_url('assets/uploads/logos/logo.png')?>'" class="img img-fluid" style="margin:auto" alt="logo" width="250">
          <!-- <h4 class="modal-title d-flex justify-content-center" id="myModalLabel"></h4> -->
      </div>
      <div class="modal-body">
        <div class="box p-box box-checkout-summary">
          <h3 class="box-subtitle" style="text-align: center;"><?= $supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name; ?></h3>
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label>Telepon</label>
                <p class="h6"><?=$supplier->phone?></p>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <label>Alamat</label>
                <p class="h6"><?=$supplier->address?></p>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <label>Kecamatan</label>
                <p class="h6"><?=$supplier->state?></p>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <label>Kabupaten</label>
                <p class="h6"><?=$supplier->city?></p>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <label>Provinsi</label>
                <p class="h6"><?=$supplier->country?></p>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <label>Kode Pos</label>
                <p class="h6"><?=$supplier->postal_code?></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</div>

    <script>
      // Get the modal
      var modal =  document.getElementById("modalPromo");

      // Get the image and insert it inside the modal - use its "alt" text as a caption
      var img = document.getElementsByClassName("img-promo"); 
      var modalImg = document.getElementById("img01");
      var captionText = document.getElementById("caption");

      for(var i = 0, x = img.length; i < x; i++) {
          img[i].onclick = function(){
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
              // console.log('target name should be here');
          }
      }
      

      // Get the <span> element that closes the modal
      var span = document.getElementsByClassName("close")[0];

      // When the user clicks on <span> (x), close the modal
      function closeModalPromo() { 
        modal.style.display = "none";
      }
      </script>

    <script>
      $(".salinKodePromo").click(function(){
        var $temp = $("<input>");
        $("body").append($temp);
        let kode = $(this).data("promo")
        $temp.val(kode).select();
        document.execCommand("copy");
        $temp.remove();
        counter++
        alertCustom("Kode Promo telah disalin : " + kode)
      })
    </script>


    <script>

        $(document).ready(function(){

          setPromoHeight();

          $('#product-search').change(function(){
            load_product_data(1);  
          });



          function load_product_data(page){
            $("#loader-section").show();
            let search = $('#product-search').val();
            if (search != '') {
              search = '?search='+search;
            }else{
              search='';
            }
            $.ajax({
              url: "<?= base_url(aksestoko_route('aksestoko/home/product_data/'))?>"+page+search,
              method : "GET",
              dataType : "json",
              success : function(data){
                $("#product-data").html('<div id="product-not-found" style="margin: auto;"><div class="box-cart"><img src="<?=$assets_at?>img/common/order-empty.png" class="img-fluid" alt="Produk Empty"><p>Produk tidak ditemukan</p></div></div>');
                if(data.product.length > 0) $("#product-data").html(data.product);
                $("#pagination-link").html(data.pagination);
              }
            })
          }

          load_product_data(1);
          $(document).on("click","#pagination-link a", function(event){
            $("#loader-section").show(); // Tampil Loader
            event.preventDefault();
            
            var page = $(this).data("ci-pagination-page");
            load_product_data(page);
          })

          $(document).ajaxStop(function() {
           $('#loader-section').fadeOut(200); // Loader Hidden


        <?php
          if (!$guide->dashboard) {
        ?>
            if (isStart) hopscotch.startTour(tour);
            

        <?php
          }
        ?>


          });
        });

        var isStart = true;
        // Define the tour!
        var tour = {
            id: "dashboard",
            onClose: function(){
                hopscotch.endTour(tour);
                callAjax();
            },
            onEnd : function(){
                        callAjax()
            },
            
            steps: [
                {
                    title: "Tambah Product",
                    content: "Klik tombol beli",
                    target: "button.btn.btn-sm.btn-success",
                    placement: "top",
                },
                {
                    title: "Menu Cart",
                    content: "Melihat pesanan anda",
                    target: "a#navShoppingCarttes",
                    placement: "left",
                },
                {
                    title: "Melihat menu lain",
                    content: "Tekan tombol ini untuk melihat beberapa menu lagi",
                    target: "a#navbarDropdown",
                    placement: "left",
                    multipage: false,
                    
                }
            ]
            
        };

        function callAjax(){
          isStart = false;
          $.ajax({
            url      : '<?= base_url(aksestoko_route('aksestoko/auth/set_guide/dashboard/1')); ?>',
            type     : 'GET',
          }) 
          
        }
        
      jQuery(window).resize(function() {
        setPromoHeight();
      });

      function setPromoHeight(){
        var width = $(".featured-banner-img").width();
        var height = width / 2;
        $(".featured-banner-img").height(height);
      }

    </script>
        <script>
        (function(w,d,u){
                var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
                var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn.bitrix24.id/b11907515/crm/site_button/loader_3_wclev2.js');
</script>