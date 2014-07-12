<TR>
	<TD vAlign=top bgColor=#eefafc colSpan=11 height="100%">
		<TABLE cellSpacing=0 cellPadding=0 width="100%">
			<TR>
				<TD bgColor=#fedCd7>
					<TABLE width="" cellPadding=10>
						<TR>
							<TD width="156" <?=$submenu == 'list_gz' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/auditor/list/factory/gz', '工場分貨確認', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'list_ben' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/auditor/list/factory/ben', 'Ben分貨確認', array('class'=>'big')); ?></span>
							</TD>
							<TD width="156" <?=$submenu == 'list_jp' ? 'class=\'submenu\'' : '' ?>>
								<span class="cat cat"><? echo HTML::anchor('/auditor/list/factory/jp', '國內分貨確認', array('class'=>'big')); ?></span>
							</TD>
						</TR>

					</TABLE>
				</TD>
			</TR>
		</table>
	</td>
</tr>

