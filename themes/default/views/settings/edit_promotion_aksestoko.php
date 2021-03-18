<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<style>
    .is_show{
        display: <?= $promotion->type_news == 'promo' ? 'block' : 'none' ?>;
    }
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_news', 'Edit News'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_promotion_aksestoko/".$id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            
            <div class="form-group">
                <label>
                    <?= lang("type_news", "type_news"); ?>
                    &nbsp;
                </label>
                <select name="type_news" class="form-control" required="required" disabled>
                    <option <?= $promotion->type_news == 'promo' ? 'selected' : '' ?> value="promo"><?= lang('promo') ?></option>
                    <option <?= $promotion->type_news == 'info' ? 'selected' : '' ?> value="info"><?= lang('info') ?></option>
                </select>
            </div>

            <div class="form-group is_show">
                <label>
                    <?= lang("code_voucher", "code_voucher"); ?>
                    &nbsp;
                    <div class="custom-control custom-radio custom-control-inline text-muted" id="tooltip-code-voucher" style="float: right;">
                        <a href="javascript:void(0);" id="icon-code-voucher" class="demo-button demo-button-click noselect"><i class="fa fa-question-circle"></i></a>
                    </div>
                </label>
                <div class="input-group">
                    <?php echo form_input('card_no', $promotion->code_promo, 'class="form-control" id="card_no" required="required" disabled'); ?>
                    <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?= lang("promo_name", "promo_name"); ?>
                <?= form_input('name', $promotion->name, 'class="form-control" id="bank_name" '); ?>
            </div>

            <div class="form-group">
                <?= lang("description", "description"); ?>
                <?= form_textarea('description', $promotion->description, 'class="form-control" id="bank_name" '); ?>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_date"><?php echo $this->lang->line("start_date"); ?></label>
                        <?php echo form_input('start_date', date('d/m/Y', strtotime(str_replace('-', '/', $promotion->start_date))), 'class="form-control input-tip date" id="start_date" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_date"><?php echo $this->lang->line("end_date"); ?></label>
                        <?php echo form_input('end_date', date('d/m/Y', strtotime(str_replace('-', '/', $promotion->end_date))), 'class="form-control input-tip date" id="end_date"'); ?>
                    </div>  
                </div>
            </div>

            <div class="row is_show">
                <div class="col-md-6">
                    <div class="form-group">
                        <?php $data = array(
                                        'type'  => 'number',
                                        'value' => (double)$promotion->quota,
                                        'name'  => 'quota',
                                        'class' => 'form-control',
                                        'disabled' => true,
                                        'step'=>"0.01"
                                    ); ?>
                        <label for="end_date">
                            <?php echo $this->lang->line("Quota"); ?>
                            &nbsp;
                            <div class="custom-control custom-radio custom-control-inline text-muted" id="tooltip-max-quota-promo" style="float: right;">
                                <a href="javascript:void(0);" id="icon-max-quota-promo" class="demo-button demo-button-click noselect"><i class="fa fa-question-circle"></i></a>
                            </div>
                        </label>
                        <?php echo form_input($data); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?php $data = array(
                                        'type'  => 'number',
                                        'name'  => 'max_tiap_toko',
                                        'value' => (double)$promotion->max_toko,
                                        'class' => 'form-control',
                                        'disabled' => true,
                                        'step'=>"0.01"
                                    ); ?>
                        <label for="end_date">
                            <?php echo $this->lang->line("max_toko"); ?>
                            &nbsp;
                            <div class="custom-control custom-radio custom-control-inline text-muted" id="tooltip-max-tiap-toko" style="float: right;">
                                <a href="javascript:void(0);" id="icon-max-tiap-toko" class="demo-button demo-button-click noselect"><i class="fa fa-question-circle"></i></a>
                            </div>
                        </label>
                        <?php echo form_input($data); ?>
                    </div>
                </div>
            </div>

            <div class="row is_show">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>
                            <?php echo $this->lang->line("type_promotion"); ?>
                            &nbsp;
                            <div class="custom-control custom-radio custom-control-inline text-muted" id="tooltip-type-promotion" style="float: right;">
                                <a href="javascript:void(0);" id="icon-type-promotion" class="demo-button demo-button-click noselect"><i class="fa fa-question-circle"></i></a>
                            </div>
                        </label>
                        <?= form_dropdown('tipe', ['1'=>'Nominal', '0'=>'Percentase', '-1' => '-'], $promotion->tipe , 'class="form-control" id="dropdown-bank" required="required" disabled'); ?>
                    </div>
                <!-- </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_date"><?php echo $this->lang->line("Region"); ?></label>
                        <?= form_dropdown('region', $options_region,$promotion->region, 'class="form-control" id="dropdown-bank" required="required"'); ?>
                    </div> -->
                </div>
            </div>
            <div class="form-group is_show">
                <label>
                    <?= lang("promotion_value", "promotion_value"); ?> *
                    &nbsp;
                    <div class="custom-control custom-radio custom-control-inline text-muted" id="tooltip-promotion-value" style="float: right;">
                        <a href="javascript:void(0);" id="icon-promotion-value" class="demo-button demo-button-click noselect"><i class="fa fa-question-circle"></i></a>
                    </div>
                </label>
                <?php $data = array(
                                'type'  => 'number',
                                'name'  => 'value',
                                'class' => 'form-control',
                                'value' => (double)$promotion->value,
                                'required' => true,
                                'disabled' => true,
                                'step'=>"0.01"
                            ); ?>
                <?php echo form_input($data); ?>
            </div>
            <div class="form-group <?=$promotion->tipe == 0 ? '' : 'hide'?> is_show" id="div-max-discount">
                <?= lang("max_discount", "max_discount"); ?> *
                <?php $data = array(
                                'type'  => 'number',
                                'name'  => 'max_discount',
                                'class' => 'form-control',
                                'value' => (double)$promotion->max_total_disc,
                                'required' => true,
                                'step'=>"0.01",
                                'id'=>'max-discount'
                            ); ?>
                <?php echo form_input($data); ?>
            </div>
            <div class="form-group is_show" id="div-min-pembelian">
                <?= lang("min_pembelian", "min_pembelian"); ?> *
                <?php $data = array(
                                'type'  => 'number',
                                'name'  => 'min_pembelian',
                                'class' => 'form-control',
                                'required' => true,
                                'step'=>"0.01",
                                'value' => (double)$promotion->min_pembelian,
                                'disabled' => true,
                                'id'=>'min-pembelian'
                            ); ?>
                <?php echo form_input($data); ?>
            </div>
            <div class="form-group">
                Logo
                <input id="banner_promotion" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file" accept="image/*">
                <span id="InfoImageBank"><i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width: 2000px, Height: 1000px, Dimension 2:1. Max File Size:" . $this->allowed_file_size ?>Kb</sup></i></span><br>
                <img src="<?php echo filter_var($promotion->url_image, FILTER_VALIDATE_URL) ? $promotion->url_image : base_url("assets/uploads".$promotion->url_image) ?>" style="width: 50%;">

            </div>

            <?php if ($this->Principal) { ?>
            <div class="form-group">
                <input type="checkbox" class="checkbox" name="is_popup" id="is_popup">
                <label for="is_popup" class="padding05"><?= lang('Popup') ?></label>
            </div>
            <div class="form-group div_img_popup">
                Image popup
                <input id="img_popup" type="file" data-browse-label="<?= lang('browse'); ?>" name="img_popup" data-show-upload="false" data-show-preview="false" class="form-control file" accept="image/*">
                <span id="info_img_popup"><i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width: 1000px, Height: 1000px, Dimension 1:1. Max File Size:2048" ?>Kb</sup></i></span><br>
                <img src="<?= $promotion->image_popup ?>" style="width: 50%;">
            </div>
            <div class="form-group div_video_popup">
                Link Youtube video popup
                <input id="video_popup" type="text" name="video_popup" class="form-control" value="<?= $promotion->video_popup ?>">
            </div>
            <?php } ?>
            
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_promo', lang('Edit News'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function () {

        $('#myModal').on('hidden.bs.modal', function () {
            var count_select = $('#dtFilter-filter-wrapper--PData-2');
            var parent_select = count_select.parent();

            console.log(parent_select[0].children.length);

            if(parent_select[0].children.length > 1){
                $('#dtFilter-filter-wrapper--PData-2').remove();
                $('#dtFilter-filter-wrapper--PData-9').remove();
            }
        });
        
        var description_max_toko            = '<?= lang('description_max_toko') ?>';
        var description_quota_voucher       = '<?= lang('description_quota_voucher') ?>';
        var description_code_voucher        = '<?= lang('description_code_voucher') ?>';
        var description_promotion_value     = '<?= lang('description_promotion_value') ?>';
        var description_type_promotion      = '<?= lang('description_type_promotion') ?>';

        var tooltip_description_max_toko = new jBox('Tooltip', {
            attach: '#icon-max-tiap-toko',
            target: '#tooltip-max-tiap-toko',
            theme: 'TooltipBorder',
            trigger: 'click',
            adjustTracker: true,
            closeOnClick: 'body',
            closeButton: 'box',
            animation: 'move',
            position: {
                x: 'left',
                y: 'top'
            },
            width: 300,
            outside: 'y',
            pointer: 'left:20',
            offset: {
                x: 25
            },
            content: description_max_toko,
            onOpen: function() {
                this.source.addClass('active').html('<i class="fa fa-question-circle"></i>');
            },
            onClose: function() {
                this.source.removeClass('active').html('<i class="fa fa-question-circle"></i>');
            }
        });

        var tooltip_description_quota_voucher = new jBox('Tooltip', {
            attach: '#icon-max-quota-promo',
            target: '#tooltip-max-quota-promo',
            theme: 'TooltipBorder',
            trigger: 'click',
            adjustTracker: true,
            closeOnClick: 'body',
            closeButton: 'box',
            animation: 'move',
            position: {
                x: 'left',
                y: 'top'
            },
            width: 300,
            outside: 'y',
            pointer: 'left:20',
            offset: {
                x: 25
            },
            content: description_quota_voucher,
            onOpen: function() {
                this.source.addClass('active').html('<i class="fa fa-question-circle"></i>');
            },
            onClose: function() {
                this.source.removeClass('active').html('<i class="fa fa-question-circle"></i>');
            }
        });

        var tooltip_description_code_voucher = new jBox('Tooltip', {
            attach: '#icon-code-voucher',
            target: '#tooltip-code-voucher',
            theme: 'TooltipBorder',
            trigger: 'click',
            adjustTracker: true,
            closeOnClick: 'body',
            closeButton: 'box',
            animation: 'move',
            position: {
                x: 'left',
                y: 'top'
            },
            width: 300,
            outside: 'y',
            pointer: 'left:20',
            offset: {
                x: 25
            },
            content: description_code_voucher,
            onOpen: function() {
                this.source.addClass('active').html('<i class="fa fa-question-circle"></i>');
            },
            onClose: function() {
                this.source.removeClass('active').html('<i class="fa fa-question-circle"></i>');
            }
        });

        var tooltip_description_promotion_value = new jBox('Tooltip', {
            attach: '#icon-promotion-value',
            target: '#tooltip-promotion-value',
            theme: 'TooltipBorder',
            trigger: 'click',
            adjustTracker: true,
            closeOnClick: 'body',
            closeButton: 'box',
            animation: 'move',
            position: {
                x: 'left',
                y: 'top'
            },
            width: 300,
            outside: 'y',
            pointer: 'left:20',
            offset: {
                x: 25
            },
            content: description_promotion_value,
            onOpen: function() {
                this.source.addClass('active').html('<i class="fa fa-question-circle"></i>');
            },
            onClose: function() {
                this.source.removeClass('active').html('<i class="fa fa-question-circle"></i>');
            }
        });

        var tooltip_description_type_promotion = new jBox('Tooltip', {
            attach: '#icon-type-promotion',
            target: '#tooltip-type-promotion',
            theme: 'TooltipBorder',
            trigger: 'click',
            adjustTracker: true,
            closeOnClick: 'body',
            closeButton: 'box',
            animation: 'move',
            position: {
                x: 'left',
                y: 'top'
            },
            width: 300,
            outside: 'y',
            pointer: 'left:20',
            offset: {
                x: 25
            },
            content: description_type_promotion,
            onOpen: function() {
                this.source.addClass('active').html('<i class="fa fa-question-circle"></i>');
            },
            onClose: function() {
                this.source.removeClass('active').html('<i class="fa fa-question-circle"></i>');
            }
        });

        $( "#myModal" ).scroll(function() {
            if(tooltip_description_max_toko.isOpen)
                tooltip_description_max_toko.close();

            if(tooltip_description_quota_voucher.isOpen)
                tooltip_description_quota_voucher.close();
            
            if(tooltip_description_code_voucher.isOpen)
                tooltip_description_code_voucher.close();
            
            if(tooltip_description_promotion_value.isOpen)
                tooltip_description_promotion_value.close();
            
            if(tooltip_description_type_promotion.isOpen)
                tooltip_description_type_promotion.close();
        });

         $('#banner_promotion').bind('change', function() {
         var file, img;
            if ((file = this.files[0])) {
                img = new Image();
                var maxWidth = <?= $this->Settings->twidth ?>;
                var maxHeight = <?= $this->Settings->theight ?>;
                 var maxSize = <?= $this->allowed_file_size ?>;
                img.src = _URL.createObjectURL(file);
            }
          //this.files[0].size gets the size of your file.

        });

        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
        $('#customer').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        var customer_points = 0;
        $('#customer').on('select2-close', function () {
            var selected_customer = $(this).val();
            $.ajax({
                type: "get", async: false,
                url: site.base_url + "customers/get_award_points/" + selected_customer,
                dataType: 'json',
                success: function (data) {
                    if (data != null) {
                        $('#award_points').html(data.ca_points);
                        $('#ca_points').val(data.ca_points);
                        customer_points = parseInt(data.ca_points);
                        if (data.ca_points > 0) {
                            $('#award-points-con').slideDown();
                        } else {
                            $('#award-points-con').slideUp();
                        }
                    } else {
                        $('#award-points-con').slideUp();
                    }
                }
            });
        });
        $(document).on('change', '#ca_points', function () {
            if (parseInt($(this).val()) <= customer_points) {
                $("[name='add_gift_card']").attr('disabled', false);
            } else {
                $("[name='add_gift_card']").attr('disabled', true);
            }
        });
        $(document).on('ifChecked', '#use_points', function (event) {
            $('#ca-points-con').slideDown();
        });
        $(document).on('ifUnchecked', '#use_points', function (event) {
            $('#ca-points-con').slideUp();
        });
        $('#genNo').click(function () {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
        $('#staff_points').on('ifChecked', function (event) {
            $('#customer-con').slideUp('fast');
            $('#staff-con').slideDown();
        });
        $('#staff_points').on('ifUnchecked', function (event) {
            $('#staff-con').slideUp('fast');
            $('#customer-con').slideDown();
        });
        $('#user').change(function () {
            var selected_user = $(this).val();
            $.ajax({
                type: "get", async: false,
                url: site.base_url + "sales/get_award_points/" + selected_user,
                dataType: 'json',
                success: function (data) {
                    if (data != null) {
                        $('#staff_award_points').html(data.sa_points);
                        $('#sa_points').val(data.sa_points);
                        if (data.sa_points > 0) {
                            $('#sa-points-con').slideDown();
                        } else {
                            $('#sa-points-con').slideUp();
                        }
                    } else {
                        $('#sa-points-con').slideUp();
                    }
                }
            });
        });

        $('select[name="type_news"]').change(function(){
            if(this.value == "info"){
                $('.is_show').hide();
                $('.is_required').removeAttr('required');
                $('.is_required').val('-1');
            }else{
                $('.is_show').show();
                $('.is_required').attr('required', 'required');
                $('.is_required').val('');
            }
        });

        var is_popup = '<?= $promotion->is_popup ?>';
        if(is_popup == '1'){
            $("#is_popup").iCheck("check");
            $('.div_img_popup').slideDown();
            $('.div_video_popup').slideDown();
        } else {
            $('.div_img_popup').slideUp();
            $('.div_video_popup').slideUp();
        }

        $('#is_popup').on('ifChecked', function () {
            $('.div_img_popup').slideDown();
            $('.div_video_popup').slideDown();
        });
        $('#is_popup').on('ifUnchecked', function () {
            $('.div_img_popup').slideUp();
            $('.div_video_popup').slideUp();
        }); 
    });

</script>    