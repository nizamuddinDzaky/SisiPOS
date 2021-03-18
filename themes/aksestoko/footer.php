<?php if (SERVER_QA) { ?>
  <div id="snackbar">QA SERVER</div>
<?php } ?>
<?php
if ($m == "product" && $v == "view" || $m == "order" && $v == "cart" || $m == "order" && $v == "checkout") {
  echo '<footer class="footer-hidden-mobile">';
} else {
  echo '<footer>';
}
?>


<div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-3 mt-5">
          <img class="rounded d-block ml-3 px-4" src="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_2 : $assets_at . 'img/logo-at-putih.png' ?>" onerror="this.src='<?= $assets_at ?>img/logo-at-putih.png'" alt="Logo" width="200">
          <div class="mt-3 ml-3 px-4">
            <a target="_blank" href="<?=  $cms ? $cms->footer_link_wa : 'https://wa.me/'?>" class="text-dark d-inline-block">
              <span class="fa-stack">
                <i class="fab fa-whatsapp fa-stack-1x fa-inverse"></i>
              </span>
            </a>
            <a target="_blank" href="<?=  $cms ? $cms->footer_link_fb : 'https://www.facebook.com/aksestokoid'?>" class="text-dark d-inline-block">
              <span class="fa-stack">
                <i class="fab fa-facebook-f fa-stack-1x fa-inverse"></i>
              </span>
            </a>
            <a target="_blank" href="<?=  $cms ? $cms->footer_link_twitter : 'https://twitter.com/'?>" class="text-dark d-inline-block">
              <span class="fa-stack">
                <i class="fab fa-twitter fa-stack-1x fa-inverse"></i>
              </span>
            </a>
            <a target="_blank" href="<?=  $cms ? $cms->footer_link_ig : 'https://www.instagram.com/aksestokoid'?>" class="text-dark d-inline-block">
              <span class="fa-stack">
                <i class="fab fa-instagram fa-stack-1x fa-inverse"></i>
              </span>
            </a>
          </div>
        </div>
        <div class="col-lg-2 col-md-2 mt-5">
          <h5 class="text-white mb-2 px-4 ml-3">Memulai</h5>
          <ul class="text-white" style="list-style-type: none;">
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/auth/signup'))?>" class="text-white">
                  Daftar
                </a>
              </li>
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/auth/signin'))?>" class="text-white">
                  Masuk
                </a>
              </li>
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/auth/profile'))?>" class="text-white">
                  Akun
                </a>
              </li>
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/order'))?>" class="text-white">
                  Pesanan
                </a>
              </li>
            </ul>
        </div>
        
        <div class="col-lg-2 col-md-2">
          <div class="mt-5">
            <h5 class="text-white mb-2 px-4 ml-3">Bantuan</h5>
            <ul class="text-white" style="list-style-type: none;">
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/home/faq'))?>" class="text-white">
                  FAQ
                </a>
              </li>
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/home/cs'))?>" class="text-white">
                  Layanan Pelanggan
                </a>
              </li>
              <li class="mt-1">
                <a href="javascript:void(0)" onclick="$('.b24-widget-button-block').click();" class="text-white">
                  Live Chat
                </a>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-lg-3 col-md-3 mt-5">
          <h5 class="text-white mb-2 px-4 ml-3">Tentang Kami</h5>
          <ul class="text-white" style="list-style-type: none;">
              <li class="mt-1">
                <a href="https://sig.id" target="_blank" class="text-white">
                  PT Semen Indonesia (Persero) Tbk (SIG)
                </a>
              </li>
              <li class="mt-1">
                <a href="https://sisi.id" target="_blank" class="text-white">
                  PT Sinergi Informatika Semen Indonesia (SISI)
                </a>
              </li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-2 mt-5">
          <h5 class="text-white mb-2 px-4 ml-3">Aplikasi</h5>
          <div class="ml-3 mt-3 px-4">
            <a target="_blank" href="https://play.google.com/store/apps/details?id=id.sisi.aksestokomobile"> 
              <img src="<?=$assets_at . 'img/gplay-id.png'?>" alt="gplay" width="150">            
            </a>
          </div>
        </div>
      </div>
    </div>
</footer>

<footer class="bg-dark p-0 <?=$m == "product" && $v == "view" || $m == "order" && $v == "cart" || $m == "order" && $v == "checkout" ? 'footer-hidden-mobile' : ''?>" style="background-color: #23282df5 !important;">
  <div class="container">
    <div class="row p-4">
        <div class="col-12 text-center">
          <small class="text-white mx-auto d-block"> <?=  $cms ? $cms->footer_right : '© '.date('Y').' PT Sinergi Informatika Semen Indonesia, anak usaha dari PT Semen Indonesia TBK. All rights reserved.'?></small>
        </div>
    </div>
  </div>
</footer>

<!-- Modal Aproval Cart -->
<div class="modal fade modal-delete" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-center" id="myModalLabel">Hapus Item dalam Keranjang Belanja</h4>
      </div>
      <div class="modal-body">
        <div class="">
          <div class="row">
            <div class="col-6 col-md-4">
              <div class="product-img" style="background: url(<?= $assets_at ?>img/products/img_product-1_white.jpg); background-size: cover; height: 150px;">
              </div>
            </div>
            <div class="col-6 col-md-8">
              <div class="card-body">
                <h4 class="card-title">---</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Tidak</button>
        <button type="button" class="btn btn-primary remove">Iya</button>
      </div>
    </div>
  </div>
</div>



<!-- Modal Menuju ke Forca POS -->
<div class="modal fade" id="myModaltoPOS" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-center" id="myModaltoPOSLabel">Menuju Forca POS</h4>
      </div>
      <div class="modal-body">
        <div class="">
          <div class="row">
            <div class="col-12">
              <div class="card-body text-center">
                <p>Anda akan diarahkan ke halaman Forca POS untuk Toko LT. Apakah Anda yakin?</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Tidak</button>
        <a href="<?= base_url(aksestoko_route('aksestoko/auth/send_session')) ?>" class="btn btn-primary">Iya</a>
      </div>
    </div>
  </div>
</div>

<!-- Konfirmasi Barang Terima -->
<div class="modal fade" tabindex="-1" role="dialog" id="terimaBarang">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body p-box">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"><i class="fal fa-times"></i></span>
        </button>
        <h4 class="modal-title mb-2">Konfirmasi Terima Barang</h4>
        <div class="">
          <div class="box p-box mb-2">
            <div class="subheading">
              <h3 class="box-subtitle">
                Detail
              </h3>
            </div>
            <table class="table table-striped table-credit">
              <tbody>
                <tr>
                  <td>Nama Barang</td>
                  <td><strong>Sari Roti</strong></td>
                </tr>
                <tr>
                  <td>Jumlah Kirim</td>
                  <td><strong>60 TON</strong></td>
                </tr>
                <tr>
                  <td>Jumlah Terima</td>
                  <td><strong>30 TON</strong></td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td><strong>Rp21.800.000</strong></td>
                </tr>
              </tbody>
            </table>

            <div class="row">
              <div class="col-md-12">
                <div class="subheading">
                  <h6>Catatan</h6>
                </div>
                <div class="form-group">
                  <textarea class="form-control" placeholder="Catatan" name="note" rows="5" id="comment" style="height: 80px;"></textarea>
                </div>
              </div>
            </div>

            <a href="https://aksestoko.antikode.com/payment-methods/credit-payment" class="btn btn-success btn-block">Konfirmasi</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<!-- END MODAL -->


<script type="text/javascript">
  $("a.logout").click(function(e) {
      var action = $(this).attr('url')
      sessionStorage.removeItem("popup-promo");
      sessionStorage.removeItem("count_popup");
      window.location.href = action;
  });
</script>

<?php if (count($popup_promo) > 0) { ?>
  <!-- Modal Promo -->
  <div id="popup-promo" class="modal fade">
    <div class="modal-dialog-promo-imgvidio" style="height: 100%;padding: 17px!important;">
      <div class="modal-content" style="background: transparent;border: 0;">
        <div class="modal-header" style="border: none!important;">
          <h4 class="modal-title" style="color:transparent">&nbsp;</h4>
          <button type=" button " class="close-modal-slider close promo-vidio-image" data-dismiss="modal " aria-hidden="true " style="display: block">&times;</button>
        </div>
        <div class="modal-body mb-0 p-0">
          <div class="isi_promo" style="background-color: white;"></div>
          <div class="text-center">
            <button class="btn btn-sm btn-primary mt-3" style="padding: 5px 10px;font-size: 12px;line-height: 1.5;border-radius: 10px;" id="close-all-promo">Tutup Semua</button>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php } ?>

<script type="text/javascript">
  $(window).on('load', function() {
    var count_promo = `<?= count($popup_promo) ?>`;
    if (!sessionStorage.getItem('popup-promo')) {
        var file = [];
        $.each(<?= json_encode($popup_promo) ?>, function (i, v) {
          if(v.image_popup != null){
            file.push({link:v.image_popup, type:'image'});
          }
          if(v.video_popup != null){
            file.push({link:v.video_popup, type:'video'});
          }
        });
        var count_popup = 0;
        if(sessionStorage.getItem('count_popup')){
          count_popup = parseInt(sessionStorage.getItem('count_popup'));
        }

        if(file.length > 0){
          sessionStorage.setItem('isi_popup-promo', JSON.stringify(file));
          if (file[count_popup].type == 'image' ) {
            var isi = "<img src='"+file[count_popup].link+"' class='img-fluid'>";
          } else {
            var video = file[count_popup].link.replace("watch?v=", "embed/");
            var isi = "<div class='yt-container'><iframe src='"+video+"?autoplay=1' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen ></iframe></div>";
          }
          $(".close-modal-slider").attr('isi_popup',file[count_popup].link);
          $('.isi_promo').html(isi);
        }

        if (sessionStorage.getItem('isi_popup-promo')) {
            var isi_popup = sessionStorage.getItem('isi_popup-promo');
        }

        $('#popup-promo').modal('show');
    } else {
        $('#popup-promo').modal('hide');
    }
  });

  $('#close-all-promo').click(function () {
    $('.isi_promo').html('');
    $("#popup-promo").modal('hide');
    sessionStorage.setItem('popup-promo', 'true');
  })

  $(".close-modal-slider").click(function() {
    if (sessionStorage.getItem('isi_popup-promo')) {
        var isi_popup = JSON.parse(sessionStorage.getItem('isi_popup-promo'));
        var count_popup = 0;
        if(sessionStorage.getItem('count_popup')){
          count_popup = parseInt(sessionStorage.getItem('count_popup')) + 1;
        } else {
          sessionStorage.setItem('count_popup', 1);
          count_popup = 1;
        }

        if(isi_popup.length > 1){
          $.each(isi_popup, function (i_, v_) {
            if(count_popup == isi_popup.length){
                $('.isi_promo').html('');
                $("#popup-promo").modal('hide');
                sessionStorage.setItem('popup-promo', 'true');
                return;
            }
            var isi = '';
            if (isi_popup[count_popup].type == 'image') {
              isi = "<img src='"+isi_popup[count_popup].link+"' class='img-fluid'>";
            } else {
              var video = isi_popup[count_popup].link.replace("watch?v=", "embed/");
              isi = "<div class='yt-container'><iframe src='"+video+"?autoplay=1' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen ></iframe></div>";
            }
            $(".close-modal-slider").attr('isi_popup','');
            $('.isi_promo').html('');

            $(".close-modal-slider").attr('isi_popup',isi_popup[count_popup].link);
            $('.isi_promo').html(isi);
            $('#popup-promo').modal('show');
            sessionStorage.setItem('count_popup', count_popup);
          });
        } else {
          $('.isi_promo').html('');
          $("#popup-promo").modal('hide');
          sessionStorage.setItem('popup-promo', 'true');
        }
    }
  });
</script>

<!--jQuery UI-->
<!--Has dependency on /css & /img-->
<script src="<?= $assets_at ?>plugins/jquery-ui-1.12.1/jquery-ui.js"></script>

<!--Viewport Checker-->
<!--For viewport animation-->
<script src="<?= $assets_at ?>plugins/viewportchecker-1.8.8/viewportchecker.min.js"></script>

<!--Parallax JS-->
<!--For parallax effect-->
<script src="<?= $assets_at ?>plugins/parallax-1.5.0/parallax.min.js"></script>

<!--Fancy Box-->
<!--For lightbox-->
<!--Has dependency on /css"-->
<script src="<?= $assets_at ?>plugins/fancybox-3.3.5/jquery.fancybox.min.js"></script>

<!--Progress Button-->
<!--For button progress animation-->
<!--Has dependency on /css-->
<script src="<?= $assets_at ?>plugins/progressbutton-1.0.0/progressButton.js"></script>
<script src="<?= $assets_at ?>plugins/progressbutton-1.0.0/modernizr.custom.js"></script>
<script src="<?= $assets_at ?>plugins/progressbutton-1.0.0/classie.js"></script>

<!--Select2-->
<!--For custom select-->
<!--Has dependency on /css"-->
<script src="<?= $assets_at ?>plugins/select2-4.0.6/select2.min.js"></script>

<!--DataTable-->
<!--For advanced table-->
<!--Has dependency on /css"-->
<script src="<?= $assets_at ?>plugins/datatables-1.10.16/datatables.min.js"></script>

<!--Slick Slider-->
<!--For advanced slider-->
<!--Has dependency on .main.scss"-->
<script src="<?= $assets_at ?>plugins/slick-1.8.1/slick.min.js"></script>

<!--Owl Carousel-->
<!--For custom carousel-->
<!--Has dependency on .main.scss"-->
<script src="<?= $assets_at ?>plugins/owl-carousel-2.2.1/owl.carousel.js"></script>

<!--Antikode Custom JS-->
<!--For component based JS customized by the author-->
<!--Some has dependency on /css-->
<!-- <script src="../assets/plugins/antikode-custom-1.0.0/animate.js"></script> -->
<!-- <script src="../assets/js/antikode-custom/navbar.js"></script> -->

<!--Main.js-->
<!--Main custom JS from author-->
<script src="<?= $assets_at ?>js/main.js"></script>
<script src="<?= $assets_at ?>js/bootstrap.js"></script>





<script>
  function openNav() {
    document.getElementById("mySidenav").style.width = "100%";
    document.getElementByClass("navbar-slide").style.display = "none";
  }

  function openNavNotif() {
    document.getElementById("mySidenavNotif").style.width = "100%";
    document.getElementByClass("navbar-slide").style.display = "none";
  }

  function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
  }

  function closeNavNotif() {
    document.getElementById("mySidenavNotif").style.width = "0";
  }

  // $(document).ready(function(){
  //   $("#navShoppingCart").click(function(){
  //     $("mySidenav").toggle("slide", { direction: "left" }, 1000);
  //   });
  // });

  // document.getElementById("mySidenav").onclick = function (){
  //   //alert(this.innerHTML);
  //   $(document.getElementById("navbar-slide")).animate({width: 'toggle'});
  // }




  // Navbar active state
  $('#navHome').addClass('active');
  // Hide Cart when Toogle Menu Click Dekstop
  $(document).ready(function() {
    $('.dropdown-toggle').click(function() {
      $('#cart-dropdown').fadeOut();
    });
  });
  // Klik Cart n Hide Toggle Menu to Slide Left on Moblie
  $(document).ready(function() {
    $('#navShoppingCart').click(function() {
      $('.navbar-slide').hide("slide", {
        direction: "left"
      }, 500);
    });
  });


  // Owl Carousel
  $('.owl-carousel').owlCarousel({
    loop: false,
    margin: 7.5,
    dots: false,
    responsive: {
      0: {
        items: 1
      },
      768: {
        margin: 20,
        items: 2
      }
    }
  });

  // $("#prev-carousel").hide();
  $('#prev-carousel').click(function() {
    $('.owl-prev').click();
  });

  $('#next-carousel').click(function() {
    $('.owl-next').click();
    // $('#prev-carousel').show();
  });

  // $('.product-item').click(function(){
  //   $('#hidden-id-supplier').val($(this).data("id"));
  // });

  $(document).ready(function() {
    $('.img-gallery').slick({
      infinite: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      dots: true,
      asNavFor: '.img-gallery-thumbnail'
    });
    $('.img-gallery-thumbnail').slick({
      infinite: true,
      slidesToShow: 5,
      slidesToScroll: 3,
      asNavFor: '.img-gallery',
      focusOnSelect: true
    });
  });


  // Back to previous page
  function goBack() {
    window.history.back();
  }

  // Select Payment
  function showDiv() {
    getSelectValue = document.getElementById("pilih_payment").value;
    if (getSelectValue == "kredit") {
      document.getElementById("hidden_div_payment_transfer").style.display = "block";
      document.getElementById("hidden_div_payment_kredit").style.display = "none";
      document.getElementById("hidden_div_payment_durasi").style.display = "block";
      document.getElementById("payment_durasi").required = true;
      document.getElementById("hidden_div_payment_virtual").style.display = "none";
    } else if (getSelectValue == "cash") {
      document.getElementById("hidden_div_payment_transfer").style.display = "block";
      document.getElementById("hidden_div_payment_kredit").style.display = "none";
      document.getElementById("hidden_div_payment_durasi").style.display = "none";
      document.getElementById("payment_durasi").required = false;
      document.getElementById("hidden_div_payment_virtual").style.display = "none";
    } else if (getSelectValue == "3") {
      document.getElementById("hidden_div_payment_transfer").style.display = "none";
      document.getElementById("hidden_div_payment_kredit").style.display = "none";
      document.getElementById("hidden_div_payment_durasi").style.display = "none";
      document.getElementById("payment_durasi").required = false;
      document.getElementById("hidden_div_payment_virtual").style.display = "block";
    } else {
      document.getElementById("hidden_div_payment_transfer").style.display = "none";
      document.getElementById("hidden_div_payment_kredit").style.display = "none";
      document.getElementById("hidden_div_payment_durasi").style.display = "none";
      document.getElementById("payment_durasi").required = false;
      document.getElementById("hidden_div_payment_virtual").style.display = "none";
    }
  }




  function readUrl(input) {
    if (input.files && input.files[0]) {
      let reader = new FileReader();
      reader.onload = (e) => {
        let imgData = e.target.result;
        let imgName = input.files[0].name;
        input.setAttribute("data-title", imgName);
        console.log(e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  }


  // promo btn
  $(".form-group").on("keyup", function() {
    if ($("#promoCode").val() !== "") {
      $('#btnPromo').removeClass('btn-disabled').removeAttr('disabled');
    }
  });

  $('#priceAfterPromo').hide();

  $('#btnPromo').click(function(e) {
    var kodepromo = document.getElementById("promoCode").value;
    console.log(kodepromo);
    $(kodepromo).attr('value', " ");
    $('#promoCode').val("Promo Code");
    $('#btnPromo').html("Delete");
    $('#priceAfterPromo').show();
    e.preventDefault();
  });

  $(function() {

    $(".button").on("click", function() {

      var $button = $(this);
      var oldValue = $button.parent().find("input").val();
      let minOrder = $button.parent().find("input").data('min-order');
      let multiple = $button.parent().find("input").data('multiple');

      if ($button.text() == "+") {
        var newVal = parseFloat(oldValue) + (multiple ? minOrder : 1);
      } else {
        // Don't allow decrementing below zero
        if (oldValue > (multiple ? minOrder : 1)) {
          var newVal = parseFloat(oldValue) - (multiple ? minOrder : 1);
        } else {
          newVal = (multiple ? minOrder : 1);
        }
      }

      $button.parent().find("input").val(newVal).change();

    });

    $('.input-qty').change(function() {
      if (parseFloat(this.value) > this.max){
        $(this).val(this.max)
      }
      if ($(this).val() < $(this).data('min-order')) {
        $(this).val($(this).data('min-order'))
      }
      let mod = $(this).val() % $(this).data('min-order');
      if ($(this).data('multiple') && mod != 0) {
        $(this).val((parseInt($(this).val()) - mod))
      }
      var price = parseFloat($(this).data('price'));
      var qty = parseInt($(this).val());
      res = formatMoney(price * qty);
      let unit = $('.unit-cart-' + $(this).data('id')).html();
      let this_id = $(this).data('id');

      $('#total-price-' + $(this).data('id')).html('Rp ' + res);
      $('.total-price-' + $(this).data('id')).html('Rp ' + res);
      $('#total-price-mobile-' + $(this).data('id')).html('Rp ' + res);
      $('.cart-header-' + $(this).data('id')).html($(this).val() + ' ' + unit);
      // console.log(res);
      // console.log($('#code-promo').val());
      $.ajax({
        url: "<?= base_url(aksestoko_route('aksestoko/order/update_cart')) ?>",
        method: "POST",
        data: {
          id: $(this).data('id'),
          qty: $(this).val(),
          code_promo: $('#code-promo').val()
        },
        dataType: "json",
        success: function(data) {
          if (data.max_stok != '') {
            alertCustom(data.max_stok);
            $('.input-qty[data-id=' + this_id + ']').val(parseInt(data.qty_before));
            res = formatMoney(price * data.qty_before);
            $('#total-price-' + this_id).html('Rp ' + res);
            $('.total-price-' + this_id).html('Rp ' + res);
          } else {
            $('#total-amount').html('Rp ' + formatMoney(data.totalAmount));
            $('#total-point').html(data.totalPoint + ' Pts');
            $('#total-qty').html(data.totalQty + '');
            if (data.status_promo) {
              $('#div-discount').show();
              $('#code-promo').attr('disabled', true);
              $('#btn-delete-promo').show();
              $('#btn-submit-promo').hide();
              set_promo(data.promo_data, data.totalAmount);
            } else {
              $('#btn-delete-promo').hide();
              $('#btn-submit-promo').show();
              $('#code-promo').attr('disabled', false);
              $('#code-promo').val('');
              $('#div-discount').hide();
            }
          }
        }
      })
    }).change();
  });
  // END CUSTOM
  $("#quantity").change(function() {
    let price = formatMoney($("#price").data("price") * $(this).val());
    $("#total_price").html("Rp " + price).change();
  }).change();

  $(".delete-cart").click(function() {
    let title = $(this).parent().find(".card-title").html();
    let imageUrl = $(this).parent().parent().find(".product-list-img").attr("src");
    let removeUrl = "<?= base_url(aksestoko_route('aksestoko/order/remove_item_cart')) ?>/" + $(this).data("id");
    $("#myModal").find(".card-title").html(title);
    $("#myModal").find(".product-img").css('background-image', 'url(' + imageUrl + ')');
    $("#myModal").find(".remove").click(function() {
      window.location.replace(removeUrl);
    })
  });

  $(".delete-alamat").click(function() {
    let title = $(this).parent().find(".card-title").html();
    let imageUrl = $(this).parent().parent().find(".product-list-img").attr("src");
    let removeUrl = "<?= base_url(aksestoko_route('aksestoko/auth/delete_address')) ?>/" + $(this).data("id");
    $("#deleteAlamat").find(".card-title").html(title);
    // $("#deleteAlamat").find(".product-img").css('background-image', 'url(' + imageUrl + ')');
    $("#deleteAlamat").find(".remove").click(function() {
      window.location.replace(removeUrl);
    })
  });



  // Jquery Profile Page
  $(document).ready(function() {
    if ($(window).width() < 767) {
      $("#editProfileMobile").click(function(e) {
        $('#editProfile').show("slide", {
          direction: "right"
        });
      });
      $("#editProfileMobileBack").click(function(e) {
        $('#editProfile').hide("slide", {
          direction: "right"
        });
      });

      $("#editAddressMobile").click(function(e) {
        $('#editAddress').show("slide", {
          direction: "right"
        });
      });
      $("#editAddressMobileBack").click(function(e) {
        $('#editAddress').hide("slide", {
          direction: "right"
        });
      });

      $("#salesPersonMobile").click(function(e) {
        $('#salesPerson').show("slide", {
          direction: "right"
        });
      });
      $("#salesPersonMobileBack").click(function(e) {
        $('#salesPerson').hide("slide", {
          direction: "right"
        });
      });

      $("#editPasswordMobile").click(function(e) {
        $('#editPassword').show("slide", {
          direction: "right"
        });
      });
      $("#editPasswordMobileBack").click(function(e) {
        $('#editPassword').hide("slide", {
          direction: "right"
        });
      });

      $("#tooltipMobile").click(function(e) {
        $('#editTooltip').show("slide", {
          direction: "right"
        });
      });
      $("#tooltipMobileBack").click(function(e) {
        $('#editTooltip').hide("slide", {
          direction: "right"
        });
      });

      $("#termOfUseMobile").click(function(e) {
        $('#termsOfUse').show("slide", {
          direction: "right"
        });
      });
      $("#termOfUseMobileBack").click(function(e) {
        $('#termsOfUse').hide("slide", {
          direction: "right"
        });
      });
      return;
    }
  });

  $('#secondStep').hide();
  $('#btnSaveAddress').click(function(e) {
    $('#secondStep').show();
    $('#firstStep').hide();
    e.preventDefault();
  });

  $('#addFormAddress').hide();
  $("#btnAdd").click(function() {
    $('#addFormAddress').show();
    $('#btnAdd').hide();
  });

  $('#editCurrentAddress').click(function() {
    $('#firstStep').show();
  });

  function formatMoney(amount, decimalCount = 0, decimal = ",", thousands = ".") {
    try {
      decimalCount = Math.abs(decimalCount);
      decimalCount = isNaN(decimalCount) ? 0 : decimalCount;

      const negativeSign = amount < 0 ? "-" : "";

      let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
      let j = (i.length > 3) ? i.length % 3 : 0;

      return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(0) : "");
    } catch (e) {
      console.log(e)
    }
  };

  $(function() {
    $("#alert").fadeTo(5000, 500).slideUp(500, function() {
      $("#alert").slideUp(500);
    });
  });


  var counter = 0

  function alertCustom(message, type = "info", zIndex = 1029) {
    let html = '<div id="alertCustom' + counter + '" class="" style="top: 0; width: 100%; position: fixed; z-index: ' + zIndex + '; margin-top: 80px">'
    html += '<div class="container" style="">'
    html += '<div class="alert alert-' + type + ' alert-dismissible show text-left mb-0" role="alert">'
    html += message
    html += '<button class="close" type="button" data-dismiss="alert" aria-label="Close">'
    html += '<span aria-hidden="true">×</span>'
    html += '</button>'
    html += '</div>'
    html += '</div>'
    html += '</div>'
    let $html = $(html)
    $("body").append($html)

    $("#alertCustom" + counter).fadeTo(2000, 1000).slideUp(1000, function() {
      $(this).slideUp(1000);
      $html.remove()
    });
  }
</script>

<?php if (SOCKET_NOTIFICATION) { ?>
  <!-- Socket Notification START -->
  <script src="<?= $assets ?>js/toastr/toastr.js"></script>
  <script>
    toastr.options = {
      "closeButton": true,
      "debug": false,
      "newestOnTop": false,
      "progressBar": true,
      "positionClass": "toast-bottom-right",
      "preventDuplicates": false,
      "onclick": true,
      "showDuration": "300",
      "hideDuration": "1000",
      "timeOut": "15000",
      "extendedTimeOut": "1000",
      "showEasing": "swing",
      "hideEasing": "linear",
      "showMethod": "fadeIn",
      "hideMethod": "fadeOut",
      "tapToDismiss": false
    }
  </script>
  <link rel="stylesheet" href="<?= $assets ?>js/toastr/toastr.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/push.js/1.0.5/push.min.js"></script>

  <script>
    var get_next_notifikasi = '<?= site_url() ?>aksestoko/socket_notifications/getNotifications?company_id=<?= $this->session->userdata('company_id'); ?>';
    $(document).ready(function() {
      Push.Permission.request(function() {}, function() {});

      <?php if ($this->session->flashdata('new_notif') || $this->socket_notification_model->new_notif) { ?>
        socket.emit('new_notif', {
          company_id: '<?= $this->session->userdata("send_to_company_id") ?? $this->socket_notification_model->send_to_company_id ?>',
          socket_notification_id: '<?= $this->session->userdata("socket_notification_id") ?? $this->socket_notification_model->socket_notification_id ?>'
        });
      <?php } ?>

      // $('<audio id="soundNotif"><source src="<?= $assets ?>/sounds/notif.mp3" type="audio/mpeg"></audio>').appendTo('body');
      socket.on('new_notif', function(data) {
        get_next_notifikasi = '<?= site_url() ?>aksestoko/socket_notifications/getNotifications?company_id=<?= $this->session->userdata('company_id'); ?>';
        reg_notifications(data);
      });

      reg_notifications('next');
      $("#tampil_lebih_page").on('click', function() {
        reg_notifications('next');
      });
    });

    function reg_notifications(data = null) {
      var socket_notification_id = data != 'next' ? data['data']['socket_notification_id'] : '';

      $.ajax({
        url: get_next_notifikasi,
        type: 'GET',
        data: {},
        success: function(data_notifications) {
          var parse_data_notifications = JSON.parse(data_notifications);
          var socket_notification_list = parse_data_notifications['data'];

          get_next_notifikasi = parse_data_notifications['next_url'];
          $(".total_new_notification").html(parse_data_notifications['total_unread']);
          if (data != 'next') {
            $("#list_notifications").html('');
          }

          for (var i = 0; i < socket_notification_list.length; i++) {
            if (data && socket_notification_id == socket_notification_list[i]['id']) {
              Push.Permission.request(
                function() {
                  Push.create(socket_notification_list[i]['title'], {
                    body: socket_notification_list[i]['message'],
                    icon: 'icon.png',
                    timeout: 30000,
                    onClick: function() {
                      window.location.href = getTypeSaleUrl(socket_notification_list[i]);
                    }
                  });
                },
                function() {
                  toastr.info(
                    socket_notification_list[i]['message'],
                    socket_notification_list[i]['title'], {
                      "saleId": socket_notification_list[i]['transaction_id'],
                      "link": getTypeSaleUrl(socket_notification_list[i])
                    }
                  );
                }
              );

              //$("#soundNotif")[0].play();
            }

            $("#list_notifications").append(
              '<div id="bodyNotiff_' + socket_notification_list[i]['id'] + '" class="bodyNotiff_' + socket_notification_list[i]['id'] + ' col-md-12 border-notif ' + (parseInt(socket_notification_list[i]['is_read']) != 0 ? '' : 'unread') + '">' +
              '<div id="bodyNotif_' + socket_notification_list[i]['id'] + '" class="body_notif ">' +

              getTypeSaleLink(socket_notification_list[i]) +
              // '<a target="_blank" href="<?= site_url() ?>aksestoko/order/view/'+socket_notification_list[i]['transaction_id']+'">'+
              '<i class="fa fa-heart"></i>' +
              '<span style="margin-left:5px;" id="messageNotif">' + socket_notification_list[i]['message'] + '</span>' +
              '</a>' +
              '<div class="row" style=" margin-top: 10px;" >' +
              '<div class="col-md-6">' +
              '<span style="color: #888888;font-size:11px;">' + socket_notification_list[i]['date'] + '</span>' +
              '</div>' +
              '<div class="col-md-6" style="text-align:right;">' +
              '<button type="submit" id="readNotif" onclick="set_read_notification(this, \'' + socket_notification_list[i]['id'] + '\')" class="readNotif">' +
              (parseInt(socket_notification_list[i]['is_read']) != 0 ? '' : '<span style="color: #b20838;">Baca</span>') +
              '</button>' +
              '</div>' +
              '</div>' +
              '</div>' +
              '</div>');


            $("#list_notificationsMobile").append(
              '<div id="bodyNotiff_' + socket_notification_list[i]['id'] + '" class="bodyNotiff_' + socket_notification_list[i]['id'] + ' col-md-12 border-notif ' + (parseInt(socket_notification_list[i]['is_read']) != 0 ? '' : 'unread') + '">' +
              '<div id="bodyNotif_' + socket_notification_list[i]['id'] + '" class="body_notif ">' +
              getTypeSaleLink(socket_notification_list[i]) +
              // '<a target="_blank" href="<?= site_url() ?>aksestoko/order/view/'+socket_notification_list[i]['transaction_id']+'">'+
              '<i class="fa fa-heart"></i>' +
              '<span style="margin-left:5px;" id="messageNotif">' + socket_notification_list[i]['message'] + '</span>' +
              '</a>' +
              '<div class="row" style=" margin-top: 10px;" >' +
              '<div class="col-6">' +
              '<span style="color: #888888;font-size:11px;">' + socket_notification_list[i]['date'] + '</span>' +
              '</div>' +
              '<div class="col-6" style="text-align:right;">' +
              '<button type="submit" id="readNotif" onclick="set_read_notification(this, \'' + socket_notification_list[i]['id'] + '\')" class="readNotif">' +
              (parseInt(socket_notification_list[i]['is_read']) != 0 ? '' : '<span style="color: #b20838;">Baca</span>') +
              '</button>' +
              '</div>' +
              '</div>' +
              '</div>' +
              '</div>');

            $("#list_notificationsPage").append(
              '<div id="bodyNotiff_' + socket_notification_list[i]['id'] + '" class="bodyNotiff_' + socket_notification_list[i]['id'] + ' col-md-12 border-notif ' + (parseInt(socket_notification_list[i]['is_read']) != 0 ? '' : 'unread') + '">' +
              '<div id="bodyNotif_' + socket_notification_list[i]['id'] + '" class="body_notif ">' +
              getTypeSaleLink(socket_notification_list[i]) +
              // '<a target="_blank" href="<?= site_url() ?>aksestoko/order/view/'+socket_notification_list[i]['transaction_id']+'">'+
              '<i class="fa fa-heart"></i>' +
              '<span style="margin-left:5px;" id="messageNotif">' + socket_notification_list[i]['message'] + '</span>' +
              '</a>' +
              '<div class="row" style=" margin-top: 10px;" >' +
              '<div class="col-6">' +
              '<span style="color: #888888;font-size:11px;">' + socket_notification_list[i]['date'] + '</span>' +
              '</div>' +
              '<div class="col-6" style="text-align:right;">' +
              '<button type="submit" id="readNotif" onclick="set_read_notification(this, \'' + socket_notification_list[i]['id'] + '\')" class="readNotif">' +
              (parseInt(socket_notification_list[i]['is_read']) != 0 ? '' : '<span style="color: #b20838;">Baca</span>') +
              '</button>' +
              '</div>' +
              '</div>' +
              '</div>' +
              '</div>');

          }

        }
      });
    }

    function getTypeSaleLink(notif) {
      if (notif["transaction_delivery_id"] != '') {
        return `<a data-toggle="modal" href="<?= site_url() ?>aksestoko/order/review/${notif['transaction_id']}/${notif['transaction_delivery_id']}">`;
      } else {
        return `<a href="<?= site_url() ?>aksestoko/order/view/${notif['transaction_id']}">`;
      }
    }

    function getTypeSaleUrl(notif) {
      if (notif["transaction_delivery_id"] != '') {
        return `<?= site_url() ?>aksestoko/order/review/${notif['transaction_id']}/${notif['transaction_delivery_id']}`;
      } else {
        return `<?= site_url() ?>aksestoko/order/view/${notif['transaction_id']}`;
      }
    }

    function set_read_notification(this_data, $notification_id) {
      $.ajax({
        url: '<?= site_url() ?>aksestoko/socket_notifications/setReadNotification?id=' + $notification_id,
        type: 'GET',
        data: {},
        success: function(data_notifications) {
          this_data.innerHTML = '';
          $('.bodyNotiff_' + $notification_id).removeClass('unread');

          var total_notif = $('#notif').html();
          $('#notif').html(parseInt(total_notif) - 1);
        }
      });
    }

    function set_read_all_notification() {
      $.ajax({
        url: '<?= site_url() ?>aksestoko/socket_notifications/setReadAllNotification?company_id=<?= $this->session->userdata('company_id'); ?>',
        type: 'GET',
        data: {},
        success: function(data_notifications) {
          get_next_notifikasi = '<?= site_url() ?>socket_notifications/getNotifications?company_id=<?= $this->session->userdata('company_id'); ?>';
          reg_notifications('next');

          $('#notif').html(0);
          $('.unread').removeClass('unread');
        }
      });
    }
  </script>
  <!-- Socket Notification END -->
<?php } ?>
</body>

</html>