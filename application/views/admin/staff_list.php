<input type="button" value="Create New User" onclick="location.href='<?=URL::site('/admin/staff_new') ?>'" />
<table border=2>
	<tr>
		<td>User Name</td>
		<td>Role</td>
		<td></td>
		<td></td>
	</tr>
	<? foreach ($form->users as $user) {?>
	<tr>
		<td><?=$user->username ?></td>
		<td><?=$user->role_name ?></td>
		<td><? echo HTML::anchor('/admin/staff_edit/'.$user->username, 'Edit'); ?></td>
		<td><a href="javascript:goDelete('<?=$user->username ?>')">Delete</a></td>
	</tr>
	<? } ?>
</table>

<? echo Form::open("admin/staff_delete", array('id'=>'form1'));
	echo Form::hidden('username', '');
echo Form::close(); ?>

<script type="text/javascript">
	function goDelete(username) {
		if (confirm('Are your sure to delete the user?')) {
			$('#form1 input[name="username"]').val(username);
			$('#form1').submit();
		}
	}
</script>