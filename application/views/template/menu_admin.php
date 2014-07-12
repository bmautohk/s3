<TR>
	<TD vAlign=top bgColor=#eefafc colSpan=11 height="100%">
		<TABLE cellSpacing=0 cellPadding=0 width="100%">
			<TBODY>
				<TR>
					<TD bgColor=#fedCd7>
						<TABLE width="" cellPadding=10>
							<TBODY>
								<TR>
									<TD width="156" <?=$submenu == 'staff' ? 'class=\'submenu\'' : '' ?>>
										<span class=""><? echo HTML::anchor('/admin/staff', 'staff 管理', array('class'=>'big')); ?></span>
									</TD>
									<TD width="156" <?=$submenu == 'role_matrix' ? 'class=\'submenu\'' : '' ?>>
										<span class=""><? echo HTML::anchor('/admin/role_matrix', '權限管理', array('class'=>'big')); ?></span>
									</TD>
									<TD width="156" <?=$submenu == 'supplier' ? 'class=\'submenu\'' : '' ?>>
										<span class=""><? echo HTML::anchor('/admin/supplier', '供應商管理', array('class'=>'big')); ?></span>
									</TD>
									<TD <?=$submenu == 'bank' ? 'class=\'submenu\'' : '' ?>>
										<span class=""><? echo HTML::anchor('/admin/bank', 'Bank Account管理', array('class'=>'big')); ?></span>
									</span>
									</TD>
									<TD width="156" <?=$submenu == 'profit' ? 'class=\'submenu\'' : '' ?>>
										<span class=""><? echo HTML::anchor('/admin/profit', '1.5 管理', array('class'=>'big')); ?></span>
									</TD>
									<TD width="156" <?=$submenu == 'profit_config' ? 'class=\'submenu\'' : '' ?>>
										<span class="cat cat"><? echo HTML::anchor('/admin/profit_config', 'Profit設定', array('class'=>'big')); ?></span>
									</TD>
									<TD width="156" <?=$submenu == 'rate' ? 'class=\'submenu\'' : '' ?>>
										<span class=""><? echo HTML::anchor('/admin/rate', 'rate 管理', array('class'=>'big')); ?></span>
									</TD>
								</TR>
							</TBODY>
						</TABLE>
					</TD>
				</TR>
			</tbody>
		</table>
	</td>
</tr>
