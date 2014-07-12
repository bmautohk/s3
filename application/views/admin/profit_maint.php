<?
$config = $form->config;

echo Form::open("admin/profit_save", array('id'=>'form1'));
echo Form::hidden('code', $config->code); ?>
<table>
	<tr>
		<td>Description:</td>
		<td><? echo $config->description; ?></td>
	</tr>
	<tr>
		<td>Value:</td>
		<td><? echo Form::input('config[value]', $config->value); ?></td>
	</tr>
</table>
<input type="submit" value="Save" />
<? echo Form::close(); ?>