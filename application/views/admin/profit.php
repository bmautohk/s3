<table cellspacing="0" cellpadding="0" width="100%">
	<tbody>
		<tr>
			<td valign="top" bgcolor="#eefafc"><br>
				<table border=2>
					<tr>
						<td>Type</td>
						<td>Value</td>
						<td></td>
					</tr>
					<? foreach ($form->configs as $config) {?>
					<tr>
						<td><?=$config->description ?></td>
						<td><?=$config->value ?></td>
						<td><? echo HTML::anchor('/admin/profit_edit/'.$config->code, 'Edit'); ?></td>
					</tr>
					<? } ?>
				</table>
			</td>
		</tr>
	</tbody>
</table>
