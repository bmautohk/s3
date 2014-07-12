<?
$orderProduct = $form->orderProduct;
$warehouseReturn = $form->warehouseReturn; 
?>

<? echo Form::open("warehouse/return_save", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'save'); ?>
<? echo Form::hidden('order_product_id', $form->order_product_id); ?>
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td>還貨日期:</td>
		<td><?=date('Y-m-d')?></td>
	</tr>
	<tr>
		<td>Order No.:</td>
		<td><?=$orderProduct->order_id ?></td>
	</tr>
	<tr>
		<td>Part No.:</td>
		<td><?=$orderProduct->product_cd ?></td>
	</tr>
	<tr>
		<td>Cust Code:</td>
		<td><?=$orderProduct->order->customer->cust_code ?></td>
	</tr>
	<tr>
		<td>櫃號:</td>
		<td><? echo Form::input('container_no', $warehouseReturn->container_no); ?></td>
	</tr>
	<tr>
		<td>借出數量:</td>
		<td><?=$orderProduct->warehouse_borrow_qty - $orderProduct->warehouse_return_qty ?></td>
	</tr>
	<tr>
		<td>還貨數量:</td>
		<td><? echo Form::input('qty', $warehouseReturn->qty); ?></td>
	</tr>
	<tr>
		<td>倉管remark:</td>
		<td><? echo Form::textarea('remark', $warehouseReturn->remark, array('rows'=>3)); ?></td>
	</tr>
</table>
<input type="submit" value="提交" />
<? echo Form::close(); ?>

<br /><br />

還貨紀錄
<table border="1">
<tr>
	<td>還貨日期</td>
	<td>還貨數量</td>
	<td>櫃號</td>
	<td>倉管remark</td>

</tr>
<? foreach ($form->returnHistory as $history) { ?>
<tr>
	<td><?=$history->return_date ?></td>
	<td><?=$history->qty ?></td>
	<td><?=$history->container_no ?></td>
	<td><?=$history->remark ?></td>
</tr>
<? } ?>
</table>
