<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#BrandTable').dataTable({
            "aaSorting": [[3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getBrands') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, {"bSortable": false, "mRender": img_hl}, null, null, {"bSortable": false}]
        });
    });
</script>
<?= form_open('system_settings/brand_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-list"></i><?= lang('brands'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo site_url('system_settings/add_brand'); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                <i class="fa fa-plus"></i> <?= lang('add_brand') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('system_settings/import_brands'); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                <i class="fa fa-plus"></i> <?= lang('import_brands') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <!-- Sementara tombol delete disembunyikan 
                        <li class="divider"></li>
                        <li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_brands') ?>
                            </a>
                        </li>
                        -->
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                 <div class="table-responsive">
                    <table id="BrandTable" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th style="min-width:40px; width: 40px; text-align: center;">
                                    <?= lang("image"); ?>
                                </th>
                                <th><?= lang("code"); ?></th>
                                <th><?= lang("name"); ?></th>
                                <th style="width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty">
                                    <i style="color:green;"><sup id="InfoBrand"></sup></i>
                                    <?= lang('loading_data_from_server') ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
    function LoadBrand(){
    $.ajax({
            method : 'GET',
            url : '<?= site_url('products/getAllBrands') ?>',
            dataType : 'json',
            async: false,
            success : function(data){
                var html = '';
                var i;
                html+='<option value=""></option>';
                for(i=0; i<data.length; i++){
                    html += '<option value='+data[i].id+'>'+data[i].name+'</option>';
                }
                $('#brand').html(html);
            },
     });   
 }
 
// function LoadUnit(){
//    $.ajax({
//       method : 'GET',
//       url : '<?= site_url('products/getAllBaseUnits') ?>',
//       dataType : 'json',
//       async: false,
//       success : function(data){
//           var html = '';
//           var i;
//           html+='<option value=""></option>';
//           for(i=0; i<data.length; i++){
//               html += '<option value='+data[i].id+'>'+data[i].name+'</option>';
//           }
//           $('#unit').html(html);
//           setTimeout(function() {
//                <?= $_POST['unit'] ? "$('#unit').val('".$_POST['unit']."').change();" : "$('select[name=unit]').val('1').change();" ?>
//                
//           }, 500);
//       },
//   });
// }
// 
// function LoadCategory(idCategorySet){
//    $.ajax({
//       method : 'GET',
//       url : '<?= site_url('products/getAllCategories') ?>',
//       dataType : 'json',
//       async: false,
//       success : function(data){
//           var html = '';
//           var i;
//           html+='<option value=""></option>';
//           for(i=0; i<data.length; i++){
//               html += '<option value='+data[i].id+'>'+data[i].name+'</option>';
//           }
//           $('#category').html(html);
//           if(idCategorySet != null){
//                setTimeout(function() {
//                    $('#category').val(idCategorySet).change();
//               }, 500);
//            }
//       },
//   });  
// }
// 
// function loadSubcategory(parentCategory,idcategory){
//       $.ajax({
//            type: "get",
//            async: false,
//            url: "<?= site_url('products/getSubCategories') ?>/" + parentCategory,
//            dataType: "json",
//            success: function (scdata) {
//                if (scdata != null) {
//                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
//                        placeholder: "<?= lang('select_category_to_load') ?>",
//                        data: scdata
//                    });
//                } else {
//                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
//                        placeholder: "<?= lang('no_subcategory') ?>",
//                        data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
//                    });
//                }
//                setTimeout(function() {
//                    $('#subcategory').val(idcategory).change();
//                }, 500);
//            },
//            error: function () {
//                bootbox.alert('<?= lang('ajax_error') ?>');
//                $('#modal-loading').hide();
//            }
//        });      
// }
// 
 $(document).ready(function () {
        LoadBrand();
//        LoadUnit();
//        LoadCategory(idCategorySet);
//        if(idCatChildSet != ''){
//           loadSubcategory(idCategorySet,idCatChildSet); 
//        }
          <?= $_POST['brand'] ? "$('#brand').val('".$_POST['brand']."').change();" : "" ?>
    //    <?= $_POST['unit'] ? "$('#unit').val('".$_POST['unit']."').change();" : "" ?> 
//        window.onload=function (){
//            getBrands();
//        }
//        function getBrands(){    
                     
//        }
    });
</script>

