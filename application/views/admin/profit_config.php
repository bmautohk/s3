<? echo Form::open("admin/profit_config", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'save'); ?>
<table border=2>
	<tr>
		<td>Code</td>
		<td>Value</td>
	</tr>
	<? foreach ($form->profitConfigs as $idx=>$profitConfig) {?>
	<tr>
		<td><?=$profitConfig->code ?></td>
		<td><? echo Form::input('profitConfig['.$profitConfig->code.'][value]', $profitConfig->value); ?></td>
	</tr>
	<? }?>
</table>

<input type="submit" value="Save" />
<? echo Form::close(); ?>
