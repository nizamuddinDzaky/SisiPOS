<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* 
 * Copyright (c) 2018 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */

 ?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#unit_name").keyup(function(){
             var code = $(this).val().substr(0,30); 
                code = code.replace(/\s+/g, '-');
                code = code.replace(/[0-9]+/,'');
                $("#unit_code").val(code);
        });
//    $('#UnitForm').submit( function( e ) {
//            $.ajax({
//              url: 'products/add_unit',
//              type: 'POST',
//              data: new FormData( this ),
//              processData: false,
//              contentType: false,
//              success: function(data){
//                  LoadUnit();
//                  $("#InfoUnit").html(data);
//                 $('#myModal').modal('hide');
//              }
//            });
//        e.preventDefault();
//      });
    });
</script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_unit'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'UnitForm');
        echo form_open("system_settings/add_unit", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('unit_name', 'name'); ?>
                <?= form_input('name', set_value('name'), 'class="form-control tip" id="unit_name" required="required"'); ?>
                <input name="code" id="unit_code" type="hidden"/>
            </div>
            <div class="form-group">
                <?= lang('base_unit', 'base_unit'); ?>
                <?php 
                $opts[0] = lang('select').' '.lang('unit');
                foreach ($base_units as $bu) {
                    $opts[$bu->id] = $bu->name .' ('.$bu->code.')';
                }
                ?>
                <?= form_dropdown('base_unit', $opts, set_value('base_unit'), 'class="form-control tip" id="base_unit" style="width:100%;"'); ?>
            </div>
            <div id="measuring" style="display:none;">
                <div class="form-group">
                    <?= lang('operator', 'operator'); ?>
                    <?php
                    $oopts = array('*' => lang('*'), '/' => lang('/'), '+' => lang('+'), '-' => lang('-'),);
                    ?>
                    <?= form_dropdown('operator', $oopts, set_value('operator'), 'class="form-control tip" id="operator" style="width:100%;"'); ?>
                </div>
                <div class="form-group">
                    <?= lang('operation_value', 'operation_value'); ?>
                    <?= form_input('operation_value', set_value('operation_value'), 'class="form-control tip" id="operation_value"'); ?>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_unit', lang('add_unit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#base_unit').change(function(e) {
            var bu = $(this).val();
            if(bu > 0)
                $('#measuring').slideDown();
            else
                $('#measuring').slideUp();
        });
    });
</script>