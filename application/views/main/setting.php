<? echo Form::open("main/setting", array('id'=>'form1')); ?>
<table>
	<tr>
		<td>Current Password:</td>
		<td><? echo Form::password('current_password'); ?></td>
	</tr>
	<tr>
		<td>New Password:</td>
		<td><? echo Form::password('new_password'); ?></td>
	</tr>
	<tr>
		<td>Re-enter Password:</td>
		<td><? echo Form::password('new_password2'); ?></td>
	</tr>
</table>
<input type="submit" value="Save" />
<? echo Form::close(); ?>