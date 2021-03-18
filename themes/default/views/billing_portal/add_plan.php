<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Form Examples area start-->
<div class="form-example-area">
    <div class="container">
        <div class="row">
            <form id="myForm" data-action="<?= site_url('billing_portal/plan/add')?>">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="form-example-wrap">
                    <div class="cmp-tb-hd">
                        <h2>Add Plan</h2>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-support"></i>
                            </div>
                            <div class="nk-int-st">
                                <input type="text" class="form-control" placeholder="Name Plan" name="name_plan" >
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-dollar"></i>
                            </div>
                            <div class="nk-int-st">
                                <input type="number" class="form-control unit" placeholder="Users Plan" name="users_plan" >
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-house"></i>
                            </div>
                            <div class="nk-int-st">
                                <input type="number" class="form-control unit" placeholder="Warehouses plan" name="warehouses_plan">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-dollar"></i>
                            </div>
                            <div class="nk-int-st">
                                <input type="number" class="form-control unit" placeholder="Price Plan" name="price_plan" >
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-edit"></i>
                            </div>
                            <div class="nk-int-st">
                                <textarea class="form-control" placeholder="Description" name="description_plan" style="height: 100px;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-app"></i>
                            </div>
                            <div class="nk-int-st">
                                <input type="number" class="form-control" placeholder="Limitation" name="limitation" >
                            </div>
                        </div>
                    </div>
                    
                    <div class="clearfix"></div><br>

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


<script>
$(document).ready(function() {
    $(".unit").keyup(function (event) {
        if(event.which != 8 && isNaN(String.fromCharCode(event.which))){
           event.preventDefault(); //stop character from entering input
        }
        var current = parseInt($(this).val(), 10) ;
        if(current <= 0 ){
            notify('danger', 'Nilai harus lebih dari 0');
            $(this).val('');
        }
    });
});
</script>