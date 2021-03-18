<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Form Examples area start-->
<div class="form-example-area">
    <div class="container">
        <div class="row">
            <form id="myForm" data-action="<?= site_url('billing_portal/plugin/edit/'.$getAddons_row->id)?>">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="form-example-wrap">
                    <div class="cmp-tb-hd">
                        <h2>Edit Plugin</h2>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-support"></i>
                            </div>
                            <div class="nk-int-st">
                                <input type="text" value="<?= $getAddons_row->name ?>" class="form-control" placeholder="Name" name="name" >
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-dollar"></i>
                            </div>
                            <div class="nk-int-st">
                                <input type="number" value="<?= (int)$getAddons_row->price ?>" class="form-control" placeholder="Price" name="price" >
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="form-group ic-cmp-int" style="padding-top: 10px;">
                            <div class="form-ic-cmp">
                                <div class="toggle-select-act fm-cmp-mg">
                                    <div class="nk-toggle-switch">
                                        <input id="ts1" type="checkbox" name="is_active" >
                                        <label for="ts1" class="ts-helper"></label>
                                        <label for="ts1" class="ts-label"> &nbsp; Is Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-example-int mg-t-15">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12"></div>
                            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12" style="float: right;">
                                <button id="submit_button" type="submit" class="btn btn-success notika-btn-success">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
        
    </div>
</div>
<!-- Form Examples area End-->

<script type="text/javascript">
    $(document).ready(function() {
        var on = '<?= $getAddons_row->is_active ?>';
        if(on == 'on'){
            $('#ts1').attr('checked','checked');
        }
    });
    
</script>