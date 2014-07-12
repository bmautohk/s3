<?
$orderProduct = $form->orderProduct; 
?>

<table border="1">
	<tr>
		<td>Order No.</td>
		<td>訂單情況(item lv)</td>
		<td>高原第一次批核日期</td>
		<td>高原最新的批核日期</td>
		<td>客戶編號</td>
		<td>貨品編號</td>
		<td>進倉數量</td>
		<td>已出貨數量</td>
		<td>kaito staff 分貨qty</td>
		<td>cost海渡價</td>
		<td>貨品名稱</td>
		<td>Brand name(pm.車種)</td>
		<td>Car Name(車型)</td>
		<td>Model Name(型號)</td>
		<td><? echo __('label.accessory_remark'); ?></td>
		<td><? echo __('label.year'); ?></td>
		<td>color</td>
		<td><? echo __('label.colour_no'); ?></td>
		<td>件數</td>
		<td>材質</td>
		<td>高元remark</td>
		<td>pic1</td>
		<td>pic2</td>
		<td>pic3</td>
		<td>櫃號(multi)</td>
	</tr>
	<tr>
		<td><?=$orderProduct->order_id ?></td>
		<td><?=$orderProduct->factory_status == 99 ? '完成' : '未完成' ?></td>
		<td><?=$orderProduct->translator_first_update_date ?></td>
		<td><?=$orderProduct->translator_last_update_date ?></td>
		<td><?=$orderProduct->cust_code ?></td>
		<td><?=$orderProduct->product_cd ?></td>
		<td><?=$orderProduct->factory_entry_qty ?></td>
		<td><?=$orderProduct->factory_delivery_qty ?></td>
		<td><?=$orderProduct->factory_qty ?></td>
		<td><?=$orderProduct->kaito ?></td>
		<td><?=$orderProduct->productMaster->product_desc ?></td>
		<td><?=$orderProduct->productMaster->made ?></td>
		<td><?=$orderProduct->productMaster->model ?></td>
		<td><?=$orderProduct->productMaster->model_no ?></td>
		<td><?=$orderProduct->productMaster->accessory_remark ?></td>
		<td><?=$orderProduct->productMaster->year ?></td>
		<td><?=$orderProduct->productMaster->colour ?></td>
		<td><?=$orderProduct->productMaster->colour_no ?></td>
		<td><?=$orderProduct->productMaster->pcs ?></td>
		<td><?=$orderProduct->productMaster->material ?></td>
		<td><?=$orderProduct->translator_remark ?></td>
		<td></td>
		<td></td>
		<td></td>
		<td><?=$orderProduct->containerSummary->container_no_list ?></td>
	</tr>
</table>

<? echo Form::open("factory/entry/factory/".$form->factory, array('id'=>'form1')); ?>
<? echo Form::hidden('order_product_id', $form->order_product_id); ?>
<table>
	<tr>
		<td>現在進倉數量:</td>
		<td><? echo Form::input('factory_entry_qty', $form->factory_entry_qty); ?></td>
	</tr>
</table>
<input type="submit" value="提交" />
<? echo Form::close(); ?>

進倉記綠
<table border="2">
	<tr>
		<td>進倉日期</td>
		<td>進倉數量</td>
	</tr>
	<? foreach ($form->factoryEntryHistory as $history) {?>
	<tr>
		<td><?=$history->create_date ?></td>
		<td><?=$history->factory_entry_qty ?></td>
	</tr>
	<? } ?>
</table>