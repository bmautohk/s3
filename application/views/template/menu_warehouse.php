<TR>
	<TD vAlign=top bgColor=#eefafc colSpan=11 height="100%">
		<TABLE cellSpacing=0 cellPadding=0 width="100%">
			<TR>
				<TD bgColor=#fedCd7>
					<TABLE width="" cellPadding=10>
						<TR>
							<TD width="156" <?=$submenu == 'list' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/warehouse/list', '貨倉List', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'gift_list' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/warehouse/gift_list', 'Gift List', array('class'=>'big')); ?></span>
							</TD>
							<!--TD width="156" <?=$submenu == 'ingood' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/warehouse/ingood', '入貨管理', array('class'=>'big')); ?></span>
							</TD-->
							<TD width="156" <?=$submenu == 'container_return_list' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/warehouse/container_return_list', '返品、戻す商品', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'kaito_product_list' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/warehouse/kaito_product_list', '海渡商品', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'order_return_confirm' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/warehouse/order_return_confirm', '赤伝確認', array('class'=>'big')); ?></span>
							</TD>
						</TR>

					</TABLE>
				</TD>
			</TR>
		</table>
	</td>
</tr>

