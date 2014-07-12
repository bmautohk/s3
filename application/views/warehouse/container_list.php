<table border="1">
	<tr> 
		<td>交到入金管理員做納品書</td>
		<td>返品</td>
		<td>Order No.</td>
		<td>Cust Code</td>
		<td>Part No.:(品番)</td>
		<td>qty</td>
		<td>今次交貨數量</td>
		<td>實際交貨數量(交貨數量 - 返品 + 借出)</td>
		<td>product name(per item)</td>
		<td>交貨日期</td>
		<td>入櫃日期</td>
		<td>櫃號</td>
		<td>pic1</td>
		<td>pic2</td>
		<td>pic3</td>
	</tr>
	<? foreach ($form->containers as $container) { ?>
	<tr>
		<td>
			<? if ($container->status == Model_Container::STATUS_INIT) { ?>
				<input type="button" value="納品書" onclick="location.href='<?=URL::site('warehouse/add_delivery_note/'.$container->id) ?>'" />
			<? } ?>
		</td>
		<td>
			<? if ($container->status == Model_Container::STATUS_INIT) { ?>
				<input type="button" value="返品" onclick="location.href='<?=URL::site('warehouse/container_return/'.$container->id) ?>'" />
			<? } ?>
		</td>
		<td><?=$container->order_id ?></td>
		<td><?=$container->cust_code ?></td>
		<td><?=$container->product_cd ?></td>
		<td><?=$container->factory_qty ?></td>
		<td><?=$container->orig_delivery_qty ?></td>
		<td><?=$container->delivery_qty ?></td>
		<td><?=$container->product_desc ?></td>
		<td><?=$container->delivery_date ?></td>
		<td><?=$container->container_input_date ?></td>
		<td><?=$container->container_no ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($container->order_id, $container->picture1); ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($container->order_id, $container->picture2); ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($container->order_id, $container->picture3); ?></td>
	</tr>
	<? } ?>
</table>