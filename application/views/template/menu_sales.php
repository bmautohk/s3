<TR>
	<TD vAlign=top bgColor=#eefafc colSpan=11 height="100%">
		<TABLE cellSpacing=0 cellPadding=0 width="100%">
			<TR>
				<TD bgColor=#fedCd7>
					<TABLE width="" cellPadding=10>
						<TR>
							<TD width="156" <?=$submenu == 'order_list' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/sales/order_list', '訂單list', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'order_add' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/sales/order_add', '訂單', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'order_return' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/sales/order_return', '客人退貨', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'shipping_fee' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/sales/shipping_fee', '経費', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'deposit_settlement' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/sales/deposit_settlement', 'Deposit入金', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'customer_list' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/sales/customer_list', '客戶列表', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'customer_add' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/sales/customer_add', '客戶管理', array('class'=>'big')); ?></span>
							</TD>
						</TR>
					</TABLE>
				</TD>
			</TR>
		</table>
	</td>
</tr>

