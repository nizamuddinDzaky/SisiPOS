<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= $title_at ?></title>
    <!-- Primary Meta Tags -->
    <meta name="title" content="<?= $title_at ?>">
    <meta name="description" content="<?= $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?> <?= $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:title" content="<?= $title_at ?>">
    <meta property="og:description" content="<?= $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?> <?= $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?>">
    <meta property="og:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->header_bg : $assets_at . 'img/bg-masthead.jpg' ?>">
    <meta property="og:image:width" content="500">
    <meta property="og:image:height" content="250">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= current_url() ?>">
    <meta property="twitter:title" content="<?= $title_at ?>">
    <meta property="twitter:description" content="<?= $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?> <?= $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?>">
    <meta property="twitter:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->header_bg : $assets_at . 'img/bg-masthead.jpg' ?>">

    <link rel="shortcut icon" href="<?= $assets_at ?>img/logo-at-short.png" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?= $assets_at ?>ico/apple-touch-icon.png">
    <link rel="stylesheet" href="<?= $assets_at ?>css/main.css">
    <link rel="stylesheet" href="<?= $assets_at ?>css/custom.css">
    <!--Lightbox CSS-->
    <link rel="stylesheet" type="text/css" href="<?= $assets_at ?>plugins/lightbox/css/lightbox.css">
    <!-- END -->

    <!-- Custom Search Css -->
    <link rel="stylesheet" type="text/css" href="<?= $assets_at ?>css/custom-search/component.css">


    <link href="<?= $assets_at ?>guide/css/hopscotch.css" rel="stylesheet" />

    <?php if (SOCKET_NOTIFICATION) { ?>
        <!-- SOCKET.IO -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
        <script>
            var socket = io.connect('<?= getenv('SOCKET_URL') ?>');
            socket.emit("sendClientInfo", {
                company_id: '<?= $this->session->userdata("company_id"); ?>',
                user_id: '<?= $this->session->userdata("user_id"); ?>',
                company: '<?= $this->session->userdata("company_name"); ?>',
                client_type: 'aksestoko',
                code: '<?= $this->session->userdata("username"); ?>',
                name: '<?= $this->session->userdata("company_name"); ?>',
                token: '<?= SOCKET_TOKEN ?>'
            });

            socket.on('message', function(data) {
                console.log(data);
            });

            socket.on('error', function(data) {
                console.error(data);
            });
        </script>
        <!-- END SOCKET -->
    <?php } ?>

    <script src="<?= $assets_at ?>guide/js/hopscotch.js"></script>

    <script src="<?= $assets_at ?>plugins/js/hopscotch.js"></script>

    <!--Bootstrap 3.7.7 dependency-->
    <script src="<?= $assets_at ?>plugins/jquery-3.3.1/jquery.min.js"></script>
    <script src="<?= $assets_at ?>plugins/modernizr-2.6.1/modernizr.min.js"></script>
    <script src="<?= $assets_at ?>plugins/bootstrap-3.3.7/js/bootstrap.min.js"></script>

    <!--Bootstrap 4.1.0 dependency-->
    <script src="<?= $assets_at ?>plugins/popper-1.14.0/popper.min.js"></script>

    <!--Lightbox JS-->
    <script src="<?= $assets_at ?>plugins/lightbox/js/lightbox.js"></script>

    <!-- Mask -->
    <!-- <script src="<?= $assets_at ?>js/jquery.mask.min.js"></script> -->

    <!-- Custom Search -->
    <script src="<?= $assets_at ?>js/custom-search/modernizr.custom.js"></script>

    <style>
        /* input[type='radio']:after {
        width: 18px;
        height: 18px;
        border-radius: 15px;
        top: 1px;
        left: 17px;
        position: absolute;
        background-color: #d1d3d1;
        content: '';
        display: inline-block;
        visibility: visible;
        border: 2px solid white;
    } */

        /* input[type='radio']:checked:after {
        width: 18px;
        height: 18px;
        border-radius: 15px;
        top: 1px;
        left: 17px;
        position: absolute;
        background-color: #B20838;
        content: '';
        display: inline-block;
        visibility: visible;
        border: 2px solid white;
    } */
        /* input[type="radio"] {
            height: 20px !important;
        }

        input[type="checkbox"] {
            height: 20px !important;
        } */
    </style>

<!-- Rating Plugins -->
    <!-- CSS -->
    <link rel="stylesheet" href="<?= $assets_at ?>plugins/rating/dist/themes/fontawesome-stars-o.css">
    <!-- <link rel="stylesheet" href="<?= $assets_at ?>plugins/rating/css/main.css"> -->
    <link rel="stylesheet" href="<?= $assets_at ?>plugins/rating/css/examples.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
        <style>
            @font-face {
                font-family: 'Glyphicons Halflings';
                src: url('https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/fonts/glyphicons-halflings-regular.eot');
                src: url('https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype'), url('https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/fonts/glyphicons-halflings-regular.woff') format('woff'), url('https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/fonts/glyphicons-halflings-regular.ttf') format('truetype'), url('https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/fonts/glyphicons-halflings-regular.svg#glyphicons-halflingsregular') format('svg');
            }
    </style>
  
  <!-- End Rating Plugins -->

    <!-- End -->
</head>

<body>

    <section class="py-main section-survey">
        <div class="container">
            <div class="heading text-center">
                <img class="m-4" src="<?=base_url('assets/uploads/cms/') . $cms->logo_1?>" onerror="this.src='<?=$assets_at?>img/logo-at.png?v=<?=FORCAPOS_VERSION?>'" alt="logo" width="250">
                <!-- <h2>Lorem Ipsum</h2>
                <p>Lorem Ipsum Dolor Ismet</p> -->
            </div>

            <div class="content">
                <form class="needs-validation mt-4" action="<?= base_url('aksestoko/survey/form') ?>" method="POST">
                <div class="row">
                    <div class="col-12 col-md-12">
                        <div class="form-group">
                            <div class="box-label-survey">
                                <label class="label-survey" for="question"><b>Survei Pelanggan</b></label>
                                <p style="padding: 20px; padding-top: 5px;">
                                Terima kasih telah menggunakan AksesToko. Kami ingin mendengar pendapat dan pengalaman anda, sehingga kami dapat meningkatkan kualitas dan pelayanan yang lebih baik melalui survei singkat ini.
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php $num = 0;
                    foreach ($question as $row) {
                        $num++; ?>
                        <input class="form-control" type="hidden" name="<?= 'question_type_' . $num ?>" value="<?= $row->type ?>">
                        <?php if ($row->type == 'text') { ?>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <div class="box-question">
                                        <label class="control-label" for="question"><b><?= $row->question ?></b></label>
                                        <input class="form-control" type="hidden" name="<?= 'question_' . $num ?>" value="<?= $row->id ?>">
                                        <textarea type="text" name="<?= 'answer_' . $num ?>" class="form-control" id="" placeholder="Tulis jawaban disini .." rows="3" value="" required="" style="resize: none;"><?= $this->session->flashdata('value')['description'] ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php } else if ($row->type == 'rating') { ?>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <div class="box-question">
                                        <label class="control-label" for="question"><b><?= $row->question ?></b></label>
                                        <input class="form-control" type="hidden" name="<?= 'question_' . $num ?>" value="<?= $row->id ?>">

                                        <div class="row">
                                            <div class="col col-fullwidth">
                                                <div class="star-ratings">
                                                    <div class="stars stars-example-fontawesome-o" style="width:100%;">
                                                        <!-- <select id="example-fontawesome-o" name="rating" data-current-rating="Cukup Setuju" autocomplete="off"> -->
                                                        <select id="example-fontawesome-o" data-quest_id="<?=$num?>" name="rating" class="ratingCustom" autocomplete="off" required>
                                                            <!-- <option value=""></option> -->
                                                            <option name="<?= 'answer_' . $num ?>" data-quest_id="<?=$num?>" value="Sangat Tidak Setuju" >Sangat Tidak Setuju</option>
                                                            <option name="<?= 'answer_' . $num ?>" data-quest_id="<?=$num?>" value="Tidak Setuju" >Tidak Setuju</option>
                                                            <option namw="<?= 'answer_' . $num ?>" data-quest_id="<?=$num?>" value="Cukup Setuju" >Cukup Setuju</option>
                                                            <option name="<?= 'answer_' . $num ?>" data-quest_id="<?=$num?>" value="Setuju" >Setuju</option>
                                                            <option name="<?= 'answer_' . $num ?>" data-quest_id="<?=$num?>" value="Sangat Setuju">Sangat Setuju</option>
                                                        </select>
                                                        <span class="title current-rating" data-quest_id="<?=$num?>">Silahkan beri penilaian
                                                            <span class="value"></span>
                                                        </span>
                                                        <span class="title your-rating hidden" data-quest_id="<?=$num?>">
                                                            Penilaian anda: <span style="font-weight: 500;" class="value"></span>&nbsp;
                                                            <!-- <a href="javascript:;" class="clear-rating" data-quest_id="<?=$num?>" style="color:#50E3C2;">
                                                                <i class="fa fa-times-circle"></i>
                                                            </a> -->
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>


                                            <!-- <div class="col-md-3">
                                                <label class="control-label" style="margin-top: .60em;">Sangat Tidak Setuju</label>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="control-label label-range">1</label>
                                                <br>
                                                <input class="form-control" type="radio" name="<?= 'answer_' . $num ?>" value="Sangat Tidak Setuju" required>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="control-label label-range">2</label>
                                                <br>
                                                <input class="form-control" type="radio" name="<?= 'answer_' . $num ?>" value="Tidak Setuju" required>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="control-label label-range">3</label>
                                                <br>
                                                <input class="form-control" type="radio" name="<?= 'answer_' . $num ?>" value="Cukup Setuju" required>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="control-label label-range">4</label>
                                                <br>
                                                <input class="form-control" type="radio" name="<?= 'answer_' . $num ?>" value="Setuju" required>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="control-label label-range">5</label>
                                                <br>
                                                <input class="form-control" type="radio" name="<?= 'answer_' . $num ?>" value="Sangat Setuju" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="control-label" style="margin-top: .60em;">Sangat Setuju</label>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } else if ($row->type == 'choice') { ?>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <div class="box-question">
                                        <label class="control-label" for="question"><b><?= $row->question ?></b></label>
                                        <input class="form-control" type="hidden" name="<?= 'question_' . $num ?>" value="<?= $row->id ?>">
                                        <?php foreach ($row->option_list as $row_2) { ?>
                                            <label class="control-label" for="answer_<?= $row_2->id ?>" style="display: block;">
                                                <input type="radio" id="answer_<?= $row_2->id ?>" name="answer_<?= $num ?>" value="<?= $row_2->option ?>" required>
                                                <span ><?= $row_2->option ?></span>
                                            </label>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } else if ($row->type == 'checkbox') { ?>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <div class="box-question">
                                        <label class="control-label" for="question"><b><?= $row->question ?></b></label>
                                        <input class="form-control" type="hidden" name="<?= 'question_' . $num ?>" value="<?= $row->id ?>">
                                        <?php foreach ($row->option_list as $row_2) { ?>
                                            <label class="control-label" for="answer_<?= $row_2->id ?>" style="display: block;">
                                                    <input type="checkbox" id="answer_<?= $row_2->id ?>" name="answer_<?= $num ?>[]" value="<?= $row_2->option ?>"> <?= $row_2->option ?>
                                            </label>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <input class="form-control" type="hidden" name="num" value="<?= $num ?>">
                </div>
                <div class="clearfix mt-4">
                    <button type="submit" class="btn btn-primary btn-block"><?= lang('Submit') ?></button>
                </div>
                </form>
            </div>
        </div>
    </section>

<script>
    $(document).ready(function(){

    });
    $('.ratingCustom').prop("selectedIndex", -1);

    $(function() {
    function ratingEnable() {
        $('#example-1to10').barrating('show', {
            theme: 'bars-1to10'
        });

        $('#example-movie').barrating('show', {
            theme: 'bars-movie'
        });

        $('#example-movie').barrating('set', 'Mediocre');

        $('#example-square').barrating('show', {
            theme: 'bars-square',
            showValues: true,
            showSelectedRating: false
        });

        $('#example-pill').barrating('show', {
            theme: 'bars-pill',
            initialRating: 'A',
            showValues: true,
            showSelectedRating: false,
            allowEmpty: null,
            emptyValue: '-- no rating selected --',
            onSelect: function(value, text) {
                alert('Selected rating: ' + value);
            }
        });

        $('#example-reversed').barrating('show', {
            theme: 'bars-reversed',
            showSelectedRating: true,
            reverse: true
        });

        $('#example-horizontal').barrating('show', {
            theme: 'bars-horizontal',
            reverse: true,
            hoverState: false
        });

        $('#example-fontawesome').barrating({
            theme: 'fontawesome-stars',
            showSelectedRating: false
        });

        $('#example-css').barrating({
            theme: 'css-stars',
            showSelectedRating: false
        });

        $('#example-bootstrap').barrating({
            theme: 'bootstrap-stars',
            showSelectedRating: false
        });

        var currentRating = $('.ratingCustom');

         $('.clear-rating').on('click', function(event) {
            var val = $(this).attr('data-quest_id');
            $('.stars-example-fontawesome-o')
                .find('.current-rating[data-quest_id='+val+']')
                .removeClass('hidden')
                .end()
                .find('.your-rating[data-quest_id='+val+']')
                .addClass('hidden');
        });

        $('.ratingCustom').barrating({
            theme: 'fontawesome-stars-o',
            showSelectedRating: false,
            initialRating: currentRating,
            onSelect: function(value, text, event) {
                var num = this.$elem.find('option:selected').data('quest_id');
                if (!value) {
                    $('#example-fontawesome-o-'+num)
                        .barrating('clear');
                } else {
                    $('.stars-example-fontawesome-o .current-rating[data-quest_id='+num+']')
                        .addClass('hidden');

                    $('.stars-example-fontawesome-o .your-rating[data-quest_id='+num+']')
                        .removeClass('hidden')
                        .find('span')
                        .html(value);
                }
            },
            onClear: function(value, text) {
                $('.stars-example-fontawesome-o')
                    .find('.current-rating')
                    .removeClass('hidden')
                    .end()
                    .find('.your-rating')
                    .addClass('hidden');
            }
        });
    }

    function ratingDisable() {
        $('select').barrating('destroy');
    }

    $('.rating-enable').click(function(event) {
        event.preventDefault();

        ratingEnable();

        $(this).addClass('deactivated');
        $('.rating-disable').removeClass('deactivated');
    });

    $('.rating-disable').click(function(event) {
        event.preventDefault();

        ratingDisable();

        $(this).addClass('deactivated');
        $('.rating-enable').removeClass('deactivated');
    });

        ratingEnable();
    });

</script>


<!-- Plugin Rating jQuery -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>
    window.jQuery || document.write('<script src="<?= $assets_at ?>plugins/rating/js/vendor/jquery-1.11.2.min.js"><\/script>')
</script>
    
<script src="<?= $assets_at ?>plugins/rating/dist/jquery.barrating.min.js"></script>
<script src="<?= $assets_at ?>plugins/rating/js/examples.js"></script>

</body>

</html>