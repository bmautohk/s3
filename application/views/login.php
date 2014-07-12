<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Ben Ben 3</title>
	<link type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet" />
	<? echo HTML::style("media/css/css.css"); ?>
	<? echo HTML::style("media/css/header.css"); ?>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
</head>
<body style="background-color:rgb(238, 250, 252)" leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">
<div id="main" style="margin-left:10px; margin-top:10px">
	<H1><font style="font-size:20px; font-family:Verdana; color:#2852A8">Login to Secure Area</font></H1>

	<? if(isset($errorMessage)) { ?>
		<div class="errorMsg">
			<div><? echo $errorMessage; ?></div>	
		</div>
	<? }?>
	
	<? echo Form::open("user/login", array('id'=>'form1')); ?>
		<p>
			<font face="Verdana" size="2" color="#2852A8"><STRONG>User Name:</STRONG><BR></font>
			<font color="#2852A8" face="Verdana">
				<? echo Form::input('username', HTML::chars(Arr::get($_POST, 'username'))); ?>
			</font>
		</p>
		
		<p>
			<font face="Verdana" size="2" color="#2852A8"><STRONG>Password:</STRONG><BR></font>
			<font color="#2852A8" face="Verdana">
				<? echo Form::password('password'); ?>
			</font>
		</p>

		<input type="submit" value="Login" />
	<? echo Form::close(); ?>
	
</div>
<? if (Kohana::$environment == Kohana::DEVELOPMENT) {?>
<div id="kohana-profiler">
	<?php echo View::factory('profiler/stats') ?>
</div>
<? } ?>

<script type="text/javascript">
	$(function() {
		$('input[name="username"]').focus();
	});
</script>

</body>
</html>