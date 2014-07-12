<?
$orderProduct = $form->orderProduct;
$warehouseBorrow = $form->warehouseBorrow; 
?>

<? echo Form::open("warehouse/borrow_save", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'save'); ?>
<? echo Form::hidden('order_product_id', $form->order_product_id); ?>
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td>借出日期:</td>
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
		<td>借出數量:</td>
		<td><? echo Form::input('qty', $warehouseBorrow->qty); ?></td>
	</tr>
	<tr>
		<td>倉管remark:</td>
		<td><? echo Form::textarea('remark', $warehouseBorrow->remark, array('rows'=>3)); ?></td>
	</tr>
</table>
<input type="submit" value="提交" />
<? echo Form::close(); ?>

<br /><br />

借出紀錄
<table border="1">
<tr>
	<td>借貨日期</td>
	<td>借貨數量</td>
	<td>倉管remark</td>

</tr>
<? foreach ($form->borrowHistory as $history) { ?>
<tr>
	<td><?=$history->borrow_date ?></td>
	<td><?=$history->qty ?></td>
	<td><?=$history->remark ?></td>
</tr>
<? } ?>
</table>
