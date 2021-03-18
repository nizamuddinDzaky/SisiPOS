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
        .bg-text-thanks{
            margin: auto;
            text-align: center;
            margin-top: 20%;
        }
    </style>
</head>
<body style="background: #a3d2fc;">
    <div class="bg-text-thanks">
        <h4><?= lang('thanks') ?></h4>
        <a href="javascript:void(0)" class="btn btn-warning" onclick="close_window()"><?= lang('close_window') ?></a>
    </div>
</body>
<script>
    function close_window(){
        window.close();
    }
</script>
</html>