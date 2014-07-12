<?
$customerOptions = Model_Customer::getOptions(true); 
?>

<? echo Form::open("accountant/picking_list", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'search'); ?>
<table>
	<tr>
		<td>櫃號:</td>
		<td><? echo Form::input('container_no', $form->container_no); ?></td>
	</tr>
	<tr>
		<td>Cust Code:</td>
		<td><? echo Form::select('customer_id', $customerOptions, $form->customer_id); ?></td>
	</tr>
</table>
<input type="submit" />
<? echo Form::close(); ?>

<? if (isset($form->containers)) { ?>
借出紀錄
<table border="1">
	<tr>
		<td></td>
		<td></td>
		<td></td>
		<td>櫃號</td>
		<td>Part No.:(品番)</td>
		<td>Order No.</td>
		<td>Cust Code</td>
		<td>交貨數量</td>
		<td>己還的數量/借出數量/出貨給了客人數量</td>
	</tr>

	<? foreach ($form->containers as $container) { ?>
	<tr>
		<td><input type="button" value="出貨" /></td>
		<td><input type="button" value="輸入經費入金 (accountant 專用)" /></td>
		<td><input type="button" value="輸入經費(sales 專用)" /></td>
		<td><?=$container->container_no ?></td>
		<td><?=$container->orderProduct->product_cd ?></td>
		<td><?=$container->orderProduct->order_id ?></td>
		<td><?=$container->cust_code ?></td>
		<td><?=$container->delivery_qty ?></td>
		<td><?=$container->orderProduct->warehouse_borrow_qty ?>/<?=$container->orderProduct->warehouse_return_qty ?>/<?=$container->delivery_qty ?></td>
	</tr>
	<? } ?>
</table>					
<? } ?>