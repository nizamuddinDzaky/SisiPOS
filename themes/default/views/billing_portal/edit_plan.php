<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Form Examples area start-->
<div class="form-example-area">
    <div class="container">
        <div class="row">
            <form id="myForm" data-action="<?= site_url('billing_portal/plan/edit/'.$id)?>">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="form-example-wrap">
                    <div class="cmp-tb-hd">
                        <h2>Edit Plan (<?php echo $plan->name ?>)</h2> 
                    </div>
                    <div class="data-table-list">
                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">Module</th>
                                        <th class="text-center">Privilege</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center"><?= lang("master_data"); ?></td>
                                        <td class="text-center">
                                            <div class="form-example-int form-example-st">
                                                <div class="fm-checkbox">
                                                    <label><input type="checkbox" value="1" class="i-checks" name="master_data" <?php echo $plan->master ? "checked" : ''; ?> ></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-center"><?= lang("pos_sales"); ?></td>
                                        <td class="text-center">
                                            <div class="form-example-int form-example-st">
                                                <div class="fm-checkbox">
                                                    <label><input type="checkbox" value="1" class="i-checks" name="pos" <?php echo $plan->pos ? "checked" : ''; ?> ></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-center"><?= lang("purchases"); ?></td>
                                        <td class="text-center">
                                            <div class="form-example-int form-example-st">
                                                <div class="fm-checkbox">
                                                    <label><input type="checkbox" value="1" class="i-checks" name="purchases" <?php echo $plan->purchases ? "checked" : ''; ?> ></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-center"><?= lang("sales"); ?></td>
                                        <td class="text-center">
                                            <div class="form-example-int form-example-st">
                                                <div class="fm-checkbox">
                                                    <label><input type="checkbox" value="1" class="i-checks" name="sales" <?php echo $plan->sales ? "checked" : ''; ?> ></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-center"><?= lang("quotes"); ?></td>
                                        <td class="text-center">
                                            <div class="form-example-int form-example-st">
                                                <div class="fm-checkbox">
                                                    <label><input type="checkbox" value="1" class="i-checks" name="quotes" <?php echo $plan->quotes ? "checked" : ''; ?> ></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-center"><?= lang("expenses"); ?></td>
                                        <td class="text-center">
                                            <div class="form-example-int form-example-st">
                                                <div class="fm-checkbox">
                                                    <label><input type="checkbox" value="1" class="i-checks" name="expenses" <?php echo $plan->expenses ? "checked" : ''; ?> ></label>
                                                </div>
                                            </div>
                                    </tr>

                                    <tr>
                                        <td class="text-center"><?= lang("reports"); ?></td>
                                        <td class="text-center">
                                            <div class="form-example-int form-example-st">
                                                <div class="fm-checkbox">
                                                    <label><input type="checkbox" value="1" class="i-checks" name="reports" <?php echo $plan->reports ? "checked" : ''; ?> ></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-center"><?= lang("transfers"); ?></td>
                                        <td class="text-center">
                                            <div class="form-example-int form-example-st">
                                                <div class="fm-checkbox">
                                                    <label><input type="checkbox" value="1" class="i-checks" name="transfers" <?php echo $plan->transfers ? "checked" : ''; ?> ></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><?= lang("limitation"); ?></td>
                                        <td class="text-center">
                                            <input type="number" class="form-control number-only" name="limitation" value="<?php echo $plan->limitation ? $plan->limitation : NULL; ?>" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><?= lang("user"); ?></td>
                                        <td class="text-center">
                                            <input type="number" class="form-control number-only unit" name="users" value="<?php echo $plan->users ? $plan->users : NULL; ?>" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><?= lang("warehouse"); ?></td>
                                        <td class="text-center">
                                            <input type="number" class="form-control number-only unit" name="warehouses" value="<?php echo $plan->warehouses ? $plan->warehouses : NULL; ?>" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><?= lang("price"); ?></td>
                                        <td class="text-center">
                                            <input type="number" class="form-control number-only unit" name="price" value="<?php echo $plan->price ? (int)$plan->price : NULL; ?>" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><?= lang("description_plan"); ?></td>
                                        <td class="text-center">
                                            <input type="text" class="form-control number-only" name="description_plan" value="<?php echo $plan->description ? $plan->description : NULL; ?>" >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-example-int mg-t-15">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12"></div>
                            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12" style="float: right;">
                                <button id="submit_button" type="submit" class="btn btn-success notika-btn-success">Update</button>
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