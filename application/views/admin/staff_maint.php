<? echo Form::open("admin/staff_save", array('id'=>'form1')); ?>
<? echo Form::hidden('action', $form->action); ?>
<table>
	<tr>
		<td>User Name:</td>
		<td><?
		if ($form->action == Model_Admin_UserForm::ACTION_ADD) {
			echo Form::input('username', $form->username);
		} else {
			echo $form->username;
			echo Form::hidden('username', $form->username);
		}
		?>
		</td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><? echo Form::password('password', $form->password); ?></td>
	</tr>
	<tr>
		<td>Role:</td>
		<td><? echo Form::select("role_code", Model_Role::getRoleOptions(), $form->role_code); ?></td>
	</tr>
</table>
<? if ($form->action == Model_Admin_UserForm::ACTION_ADD) { ?>
	<input type="submit" value="Add" />
<? } else { ?>
	<input type="submit" value="Save" />
<? } ?>
<? echo Form::close(); ?>