<? echo Form::open("admin/role_matrix", array('id'=>'form1')); ?>
	<table border="1">
		<tr>
			<td></td>
			<? foreach ($form->roles as $role) { ?>
				<td><?=$role->role_name ?></td>
			<? } ?>
		</tr>
		
		<? foreach (Model_Admin_RoleMatrixForm::$pages as $page=>$page_name) { ?>
			<tr>
				<td><?=$page_name ?></td>
				<? foreach ($form->roles as $role) {
					if (isset($form->permissions[$role->role_code][$page])) {
						$permission = $form->permissions[$role->role_code][$page];
					} else {
						$permission = '';
					}
				?>
					<td><? echo Form::select('permissions['.$role->role_code.']['.$page.']', array(''=>'', 'R'=>'Read', 'W'=>'Write'), $permission); ?></td>
				<? } ?>
			</tr>
		<? } ?>
	</table>
	<input type="submit" value="Save" />
<? echo Form::close(); ?>
