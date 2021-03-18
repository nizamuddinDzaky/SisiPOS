<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_feedback_statement') ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'feedbackForm');
        echo form_open_multipart("system_settings/add_feedback", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label class="control-label" for="category"><?= lang("category"); ?></label>
                <select name="category" id="category" class="form-control input-tip select" style="width:100%;" required>
                    <option value="0"><?= lang('unassign') ?></option>
                    <?php foreach($category as $row){ ?>
                        <option value="<?= $row->id ?>"><?= $row->category ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label class="control-label" for="statement"><?= lang("statement"); ?></label>
                <?= form_input('question', '', 'class="form-control" id="statement" required="required"'); ?>
                <input name="code" id="statement" type="hidden" />
            </div>
            <div class="row">
                <div class="form-group col-md-3">
                    <label class="control-label" for="type"><?= lang("type"); ?></label>
                    <?php $typeList = array('text'=>lang('text'), 'rating'=>lang('rating'), 'choice'=>lang('choice'), 'checkbox'=>lang('checkbox'));
                            echo form_dropdown('type', $typeList, "", 'id="type" class="form-control input-tip select" style="width:100%;" required '); ?>
                    <br>
                    <div class="form-group">
                        <label>
                            <input name="is_active" type="checkbox" checked/> <?php echo $this->lang->line("active"); ?>
                        </label>
                    </div>
                </div>
                <div id="hidethis" class="form-group col-md-9">
                    <div id="options">
                        <label class="control-label" for="options"><?= lang("options"); ?></label>
                        <div id="row_0">
                            <div class="row">
                                <div class="col-md-10">
                                    <input type="text" name="option[]" class="form-control" value="" />
                                </div>
                                <div class="col-md-2">
                                    <button type="button" name="remove" id="0" class="btn btn-danger btn_remove">X</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a id="add"> + <?= lang("add_option"); ?> </a>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_statement', lang('add_statement'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>

<script>
    $(document).ready(function(){  
        var i=1;  
        $('#add').click(function(){  
            i++;  
            $('#options').append('<div id="row_'+i+'">'+
                                    '<div class="row">'+
                                        '<div class="col-md-10">'+
                                            '<input type="text" name="option[]" class="form-control" />'+
                                        '</div>'+
                                        '<div class="col-md-2">'+
                                            '<button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>');
        });  
        $(document).on('click', '.btn_remove', function(){  
            var button_id = $(this).attr("id");   
            $('#row_'+button_id+'').remove();  
        });

        $('#type').change(function() {
            let status = $(this).val();
            if (status === "choice" || status === "checkbox" ) {
                $('#hidethis').show()
            } else {
                $('#hidethis').hide()
            }
        }).change();
    });  
 </script>