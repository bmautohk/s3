<? $user = Auth_ORM2::instance()->get_user(); ?>
<tr>
	<td background="<?=PATH_BASE ?>media/images/admin_head_bg.gif" colspan="0" height="34">&nbsp;&nbsp;
		<? echo HTML::anchor('/', '<b>Whole Sales</b>', array('class'=>'big')); ?>
		User Name: <? echo HTML::anchor('/main/setting', $user->username); ?>
		(<? echo HTML::anchor('/user/logout', 'Logout'); ?>)
	</td>
	<td valign="top" align="right" background="<?=PATH_BASE ?>media/images/admin_head_bg.gif" height="34">&nbsp;
		<a href="<?=PATH_BASE ?>">Main page</a>
	</td>
</tr>
<tr bgcolor="white">
	<td colspan="2">
		<ol id="toc">
			<? if (array_key_exists('sales', $user->mainMenu)) { ?>
				<li><? echo HTML::anchor('sales', '<span>Sales</span>', array('class'=>($controller == 'sales' ? 'active' : 'inactive'))); ?></li>
			<? } ?>
			
			<? if (array_key_exists('kaitostaff', $user->mainMenu)) { ?>
				<li><? echo HTML::anchor('kaitostaff', '<span>大步哥</span>', array('class'=>($controller == 'kaitostaff' ? 'active' : 'inactive'))); ?></li>
			<? } ?>
			
			<? if (array_key_exists('auditor', $user->mainMenu)) { ?>
				<li><? echo HTML::anchor('auditor', '<span>Auditor</span>', array('class'=>($controller == 'auditor' ? 'active' : 'inactive'))); ?></li>
			<? } ?>
			
			<? if (array_key_exists('translator', $user->mainMenu)) { ?>
				<li><? echo HTML::anchor('translator', '<span>高原</span>', array('class'=>($controller == 'translator' ? 'active' : 'inactive'))); ?></li>
			<? } ?>
			
			<? if (array_key_exists('factory', $user->mainMenu)) { ?>
				<li><? echo HTML::anchor('factory', '<span>工場</span>', array('class'=>($controller == 'factory' ? 'active' : 'inactive'))); ?></li>
			<? } ?>
			
			<? if (array_key_exists('warehouse', $user->mainMenu)) { ?>
				<li><? echo HTML::anchor('warehouse', '<span>倉管員</span>', array('class'=>($controller == 'warehouse' ? 'active' : 'inactive'))); ?></li>
			<? } ?>
			
			<? if (array_key_exists('accountant', $user->mainMenu)) { ?>
				<li><? echo HTML::anchor('accountant', '<span>入金管理</span>', array('class'=>($controller == 'accountant' ? 'active' : 'inactive'))); ?></li>
			<? } ?>
			
			<? if (array_key_exists('admin', $user->mainMenu)) { ?>
				<li><? echo HTML::anchor('admin', '<span>Admin</span>', array('class'=>($controller == 'admin' ? 'active' : 'inactive'))); ?></li>
			<? } ?>
		</ol>
	</td>
</tr>
