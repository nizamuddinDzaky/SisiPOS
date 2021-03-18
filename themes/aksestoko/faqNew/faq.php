<!doctype html>
<html>
    <head>
        <title>FAQ - AksesToko</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href='<?=$assets_at?>faq/style.css' rel='stylesheet' type='text/css'>
        <link href='<?=$assets_at?>faq/bootstrap/css/bootstrap.css' rel='stylesheet' type='text/css'>
        <link rel="shortcut icon" href="<?=$assets_at?>img/logo-at-short.png" type="image/x-icon">

        <script src="<?=$assets_at?>faq/abc.js"></script>
        
        <script>
        $(document).ready(function(){
            $('#search').keyup(function(){
            
            // Search text
            var text = $(this).val();
            
            // Hide all content class element
            $('.content').hide();

            // Search 
            $('.content .title:contains("'+text+'")').closest('.content').show();
            $('.content .card-body:contains("'+text+'")').closest('.content').show();
            
            });
        });

        $.expr[":"].contains = $.expr.createPseudo(function(arg) {
            return function( elem ) {
            return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
            };
        });
        $(document).ready(function() {
          $('.item:first-child').addClass('first');
        });
        </script>


    <style>
      @media (min-width: 480px){
        .bx-livechat-wrapper {
            height: 430px !important;
        }
      }
      @media (max-width: 500px){
        .heading-size {
          font-size: 19px;
        }
        #search{
          padding: 10px;
          font-size: 12px;
        }
        .title{
          font-size:15px;
        }
        .card-body{
          font-size:13px;
        }
        .heading.text-center.putih.hide-mobile.pt-20.pb-20 {
          font-size: 13px;
        }
      }
      
    </style>
    </head>
    <body>

    <?php if (SERVER_QA) { ?>
      <div id="snackbar">QA SERVER</div>
    <?php } ?>

        <nav class="navbar navbar-expand-lg bg-merah">
            <div class="col-md-3"></div>
            <div class="col">
                <div class="heading text-center putih  pt-20 pb-20 hide-mobile">
                    <label class="heading-size">Bagaimana Kami Bisa Membantu Anda ?</label>
                </div>
                <input type='text' class="border-radius-50" id='search' placeholder='Cari Disini ...'>
                <div class="heading text-center putih hide-mobile pt-20 pb-20">
                    <label class="">Anda juga dapat menelusuri topik di bawah ini untuk menemukan apa yang Anda cari.</label>
                </div>
            </div>
            <div class="col-md-3"></div>
        </nav>


        <!--Accordion wrapper-->
       
        <div class="container" style="margin-bottom: 80px;">
            <div class="accordion md-accordion" id="accordionEx" role="tablist" aria-multiselectable="true">

              <?php if (count($faq)>0){foreach($faq as $f){ ?>
              <!-- Accordion card -->
                <div class='content'>
                  <div class="card">

                    <!-- Card header -->
                    <div class="card-header" role="tab" id="headingOne1" style="background-color: #424242;">
                      <a data-toggle="collapse" data-parent="#accordionEx" href="#tab<?=$f->id?>" aria-expanded="true"
                        aria-controls="collapseOne1">
                        <h5 class="title mb-0 putih">
                          #<?=str_pad($f->id, 5, '0', STR_PAD_LEFT)?> - <?=$f->title?> <i class="fas fa-angle-down rotate-icon"></i>
                        </h5>
                      </a>
                    </div>

                    <!-- Card body -->
                    <div id="tab<?=$f->id?>" class="collapse show" role="tabpanel" aria-labelledby="headingOne1"
                      data-parent="#accordionEx">
                      <div class="card-body">
                        <?= $f->caption?>
                      </div>
                    </div>

                  </div>
                </div>
                <!-- Accordion card -->
               <?php } } ?>
               

            </div>
            <!-- Accordion wrapper -->
        </div>

       <!--  <footer>
          <nav class="navbar navbar-expand-lg bg-hitam footer"> -->
            <!-- <div class="container"> -->
             <!--  <div class="col-md-12">
                <div class="heading putih text-center hide-mobile pt-20 pb-20">
                      <label class="">2019 © Aksestoko</label>
                  </div>
              </div> -->
              <!-- <div class="col-md-6"> -->
                 <!--  <div class="heading text-center putih  pt-20 pb-20 hide-mobile">
                      <label class="heading-size">Bagaimana Kami Bisa Membantu Anda ?</label>
                  </div>
                  <input type='text' class="border-radius-50" id='search' placeholder='Tulis Sesuatu Untuk Menemukan Jawaban'>
                  <div class="heading text-center putih hide-mobile pt-20 pb-20">
                      <label class="">Anda juga dapat menelusuri topik di bawah ini untuk menemukan apa yang Anda cari.</label>
                  </div> -->
             <!--  </div> -->
              
           <!--  </div> -->
        <!--   </nav>
        </footer> -->
    </body>

   <script src="<?=$assets_at?>faq/bootstrap/js/bootstrap.js"></script>

    <script>
            (function(w,d,u){
                    var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
                    var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
            })(window,document,'https://cdn.bitrix24.id/b11907515/crm/site_button/loader_3_wclev2.js');
    </script>
</html>