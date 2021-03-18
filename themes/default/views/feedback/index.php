<!DOCTYPE html>
<html>
<head>
    <title><?= $page_title ?> - <?= $Settings->site_name ?></title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>help/css/style.css">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>help/css/jquery-ui.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>	
	<script src="<?= $assets ?>help/js/jquery-ui.js"></script>
    <script src="<?= $assets ?>help/js/jquery-3.3.1.min.js"></script>
    
    <style>
        .header-question{
            text-align: center; 
            padding: 60px; 
            background: url('<?=$assets?>help/bg-form-header.jpg'); 
            background-size: 100%; background-position: center; 
            margin-bottom: 30px;
            -webkit-box-shadow: 1px 2px 17px -5px rgba(29,76,84,1);
            -moz-box-shadow: 1px 2px 17px -5px rgba(29,76,84,1);
            box-shadow: 1px 2px 17px -5px rgba(29,76,84,1);
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
        }
        .box-question{
            padding: 20px;
            background: white;
            border-radius: 5px;
            -webkit-box-shadow: 1px 2px 17px -5px rgba(29,76,84,1);
            -moz-box-shadow: 1px 2px 17px -5px rgba(29,76,84,1);
            box-shadow: 1px 2px 17px -5px rgba(29,76,84,1);
        }

        .label-range{
            width: 100%;
            text-align: center;
        }

        .box-label-survey{
            width: 100%;
            height: auto;
            background: white;
            border-radius: 5px;
            -webkit-box-shadow: 1px 2px 17px -5px rgba(29,76,84,1);
            -moz-box-shadow: 1px 2px 17px -5px rgba(29,76,84,1);
            box-shadow: 1px 2px 17px -5px rgba(29,76,84,1);
        }

        .label-survey{
            padding: 20px;
            background: #1955c2;
            width: 100%;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            color: white;
        }
    </style>
</head>
<body style="background: #a3d2fc;">
	<section>
		<div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <div class="header-question">
                    <img class="logo-forca" src="<?=$assets?>help/logo-sm.png" alt="Penjualan"/>
                </div>
                <div class="box-label-survey">
                    <label class="label-survey" for="question"><b><?= lang('customer_survey') ?></b></label>
                    <p style="padding: 20px; padding-top: 5px;"><?= lang('introduction') ?></p>
                </div>
                <div class="body_feedback">
                    <form method="post" action="<?= base_url('welcome/feedback') ?>">
                        <?php $num=0; foreach($question as $row){ $num++;?>
                            <input class="form-control" type="hidden" name="<?= 'question_type_'.$num ?>" value="<?= $row->type ?>">
                            <?php if($row->type == 'text'){ ?>
                                <div class="form-group box-question">
                                    <label class="control-label" for="question"><b><?= $row->question ?></b></label>
                                    <input class="form-control" type="hidden" name="<?= 'question_'.$num ?>" value="<?= $row->id ?>">
                                    <textarea class="form-control" name="<?= 'answer_'.$num ?>" placeholder="Tulis jawaban disini..." required></textarea>
                                </div>
                            <?php }else if($row->type == 'rating'){ ?>
                                <div class="form-group box-question">
                                    <label class="control-label" for="question"><b><?= $row->question ?></b></label>
                                    <input class="form-control" type="hidden" name="<?= 'question_'.$num ?>" value="<?= $row->id ?>">
       
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="control-label" style="margin-top: .60em;">Sangat Tidak Setuju</label>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="control-label label-range">1</label>
                                            <br>
                                            <input class="form-control" type="radio" name="<?= 'answer_'.$num ?>" value="Sangat Tidak Setuju" required>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="control-label label-range">2</label>
                                            <br>
                                            <input class="form-control" type="radio" name="<?= 'answer_'.$num ?>" value="Tidak Setuju" required>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="control-label label-range">3</label>
                                            <br>
                                            <input class="form-control" type="radio" name="<?= 'answer_'.$num ?>" value="Cukup Setuju" required>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="control-label label-range">4</label>
                                            <br>
                                            <input class="form-control" type="radio" name="<?= 'answer_'.$num ?>" value="Setuju" required>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="control-label label-range">5</label>
                                            <br>
                                            <input class="form-control" type="radio" name="<?= 'answer_'.$num ?>" value="Sangat Setuju" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="control-label" style="margin-top: .60em;">Sangat Setuju</label>
                                        </div>
                                    </div>
                                </div>
                            <?php }else if($row->type == 'choice'){ ?>
                                <div class="form-group box-question">
                                    <label class="control-label" for="question"><b><?= $row->question ?></b></label>
                                    <input class="form-control" type="hidden" name="<?= 'question_'.$num ?>" value="<?= $row->id ?>">
                                    <?php foreach($row->option_list as $row_2){?>
                                        <br>
                                        <label class="control-label" for="answer_<?= $row_2->id ?>">
                                            <input type="radio" id="answer_<?= $row_2->id ?>" name="answer_<?= $num ?>" value="<?= $row_2->option ?>" required> <?= $row_2->option ?>
                                        </label>
                                    <?php } ?>
                                </div>
                            <?php }else if($row->type == 'checkbox'){ ?>
                                <div class="form-group box-question">
                                    <label class="control-label" for="question"><b><?= $row->question ?></b></label>
                                    <input class="form-control" type="hidden" name="<?= 'question_'.$num ?>" value="<?= $row->id ?>">
                                    <?php foreach($row->option_list as $row_2){?>
                                        <br>
                                        <label class="control-label" for="answer_<?= $row_2->id ?>">
                                            <input type="checkbox" id="answer_<?= $row_2->id ?>" name="answer_<?= $num ?>[]" value="<?= $row_2->option ?>"> <?= $row_2->option ?>
                                        </label>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <input class="form-control" type="hidden" name="num" value="<?= $num ?>">
                        <div class="modal-footer">
                            <input type="submit" name="submit" class="btn btn-primary" value="<?= lang('Submit') ?>"/>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
    </section>
</body>
</html>