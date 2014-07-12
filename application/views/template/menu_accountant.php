<TR>
	<TD vAlign=top bgColor=#eefafc colSpan=11 height="100%">
		<TABLE cellSpacing=0 cellPadding=0 width="100%">
			<TR>
				<TD bgColor=#fedCd7>
					<TABLE width="" cellPadding=10>
						<TR>
							<TD width="156" <?=$submenu == 'delivery_note' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/accountant/delivery_note', '納品書打印', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'invoice' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/accountant/invoice', '請求書打印', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'shipping_fee_delivery_note' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/accountant/shipping_fee_delivery_note', '經費請求書打印', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'deposit_settlement' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/accountant/deposit_settlement', 'deposit 確認', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'shipping_fee_settlement' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/accountant/shipping_fee_settlement', '經費入金確認', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'invoice_settlement' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/accountant/invoice_settlement', 'invoice入金確認', array('class'=>'big')); ?></span>
							</TD>
						</TR>

					</TABLE>
				</TD>
			</TR>
		</table>
	</td>
</tr>

