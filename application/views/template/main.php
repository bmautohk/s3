<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Ben Ben 3</title>
	<? echo HTML::style("media/css/jquery-ui.css"); ?>
	<? echo HTML::style("media/css/css.css"); ?>
	<? echo HTML::style("media/css/header.css"); ?>
	<? echo HTML::script("media/js/jquery.min.js") ?>
	<? echo HTML::script("/media/js/jquery-ui.min.js") ?>
</head>
<body style="background-color:rgb(238, 250, 252)" leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">
<table cellspacing="0" cellpadding="0" width="100%" border="0">
	<?
	$controller = Request::initial()->controller();
	
	if (!isset($submenu)) {
		$submenu = '';
	}

	echo View::factory('template/header', array('controller'=>$controller));
	echo View::factory('template/menu_'.$controller, array('submenu'=>$submenu));
	?>
</table>

<? if(isset($errors)) { ?>
	<div class="errorMsg">
	<? foreach($errors as $error) {?>
			<div><? echo $error; ?></div>	
	<? } ?>
	</div>
<? }?>

<? if(isset($success)) { ?>
	<div class="successMsg"><? echo $success; ?></div>
<? }?>

<div id="main">	
	<? echo $content; ?>
</div>

<? if (Kohana::$environment == Kohana::DEVELOPMENT) {?>
<div id="kohana-profiler">
	<?php echo View::factory('profiler/stats') ?>
</div>
<? } ?>

</body>
</html>
