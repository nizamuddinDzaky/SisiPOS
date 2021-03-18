<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_payment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("products/add_payment/" . $inv->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <?php if ($Owner || $Admin) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang("reference_no", "reference_no"); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment_ref), 'class="form-control tip" id="reference_no"'); ?>
                    </div>
                </div>

                <input type="hidden" value="<?php echo $inv->id; ?>" name="consignment_id"/>
            </div>
            <div class="clearfix"></div>
            <div id="payments">

                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("amount", "amount_1"); ?>
                                        <input name="amount-paid" type="text" id="amount_1"
                                               value="<?= $this->sma->formatDecimal($inv->total - $inv->paid) ?>"
                                               class="pa form-control kb-pad amount" required="required"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by" required="required">
                                        <?= $this->sma->paid_opts(false, true); ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="clearfix"></div>
                        <div class="pcc_1" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="pcc_no" type="number" id="pcc_no_1" class="form-control"
                                               placeholder="<?= lang('cc_no') ?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">

                                        <input name="pcc_holder" type="text" id="pcc_holder_1" class="form-control"
                                               placeholder="<?= lang('cc_holder') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type"
                                                placeholder="<?= lang('card_type') ?>">
                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                            <option value="MasterCard"><?= lang("MasterCard"); ?></option>
                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                            <option value="Discover"><?= lang("Discover"); ?></option>
                                        </select>
                                        <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <?php
                                        $bln=array(
                                        0=> lang('month'),1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                                        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember');
                                        echo form_dropdown('pcc_month', $bln, '','class="form-control" id="pcc_month_1" '); ?>
<!--                                        <input name="pcc_month" type="text" id="pcc_month_1" class="form-control"
                                               placeholder="<?= lang('month') ?>"/>-->
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">

                                        <input name="pcc_year" type="number" id="pcc_year_1" class="form-control"
                                               placeholder="<?= lang('year') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">

                                        <input name="pcc_ccv" type="number" id="pcc_cvv2_1" class="form-control"
                                               placeholder="<?= lang('cvv2') ?>" maxlength="3" min="0" max="999"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pcheque_1" style="display:none;">
                            <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                <input name="cheque_no" type="text" id="cheque_no_1" class="form-control cheque_no"/>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>

            </div>

            <div class="form-group">
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_payment', lang('add_payment'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            localStorage.setItem('paid_by', p_val);
            $('#rpaidby').val(p_val);
            if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').show();
                $('#amount_1').focus();
            } else if (p_val == 'CC') {
                $('.pcheque_1').hide();
                $('.pcash_1').hide();
                $('.pcc_1').show();
                $('#pcc_no_1').focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_1').hide();
                $('.pcash_1').hide();
                $('.pcheque_1').show();
                $('#cheque_no_1').focus();
            } else {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').hide();
            }
        });
        $('#pcc_no_1').change(function (e) {
            var pcc_no = $(this).val();
            localStorage.setItem('pcc_no_1', pcc_no);
            var CardType = null;
            var ccn1 = pcc_no.charAt(0);
            if (ccn1 == 4)
                CardType = 'Visa';
            else if (ccn1 == 5)
                CardType = 'MasterCard';
            else if (ccn1 == 3)
                CardType = 'Amex';
            else if (ccn1 == 6)
                CardType = 'Discover';
            else
                CardType = 'Visa';

            $('#pcc_type_1').select2("val", CardType);
        });
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
    });
</script>
<script>
    var inputQuantity = [];
    $(function() {
        $("#pcc_cvv2_1").each(function(i) {
            inputQuantity[i]=this.defaultValue;
            $(this).data("idx",i); // save this field's index to access later
        });
        $("#pcc_cvv2_1").on("keyup", function (e) {
            var $field = $(this),
            val=this.value,
            $thisIndex=parseInt($field.data("idx"),10); // retrieve the index
//            window.console && console.log($field.is(":invalid"));
//            $field.is(":invalid") is for Safari, it must be the last to not error in IE8
        if (this.validity && this.validity.badInput || isNaN(val) || $field.is(":invalid") ) {
            this.value = inputQuantity[$thisIndex];
            return;
        } 
        if (val.length > Number($field.attr("maxlength"))) {
          val=val.slice(0,3);
          $field.val(val);
        }
        inputQuantity[$thisIndex]=val;
      });      
    });

</script>
