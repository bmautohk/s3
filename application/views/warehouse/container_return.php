<?
$container = $form->container;
$containerReturn = $form->inputContainerReturn; 
?>

<? echo Form::open("warehouse/container_return_save", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'save'); ?>
<? echo Form::hidden('container_id', $form->container_id); ?>
<div style="margin-left:20px">
	<table cellspacing="0" cellpadding="0" width="100%">
		<tr>
			<td>返品日期:</td>
			<td><?=date('Y-m-d')?></td>
		</tr>
		<tr>
			<td>交貨數量:</td>
			<td><?=$container->orig_delivery_qty ?></td>
		</tr>
		<tr>
			<td>實際交貨數量:</td>
			<td><?=$container->delivery_qty ?></td>
		</tr>
		<tr>
			<td>交貨日期:</td>
			<td><?=$container->delivery_date ?></td>
		</tr>
		<tr>
			<td>入櫃日期:</td>
			<td><?=$container->container_input_date ?></td>
		</tr>
		<tr>
			<td>櫃號</td>
			<td><?=$container->container_no ?></td>
		</tr>
		<tr>
			<td>返回數量:</td>
			<td><? echo Form::input('container_return[qty]', $containerReturn->qty); ?></td>
		</tr>
		<tr>
			<td>倉管remark:</td>
			<td><? echo Form::textarea('container_return[remark]', $containerReturn->remark, array('rows'=>3)); ?></td>
		</tr>
	</table>
	<input type="submit" value="提交" />
	<input type="button" value="Back" onclick="location.href='<?=URL::site('warehouse/container_list/'.$form->container->order_product_id) ?>'" />
	<? echo Form::close(); ?>
	
	<br /><br />
	
	返品紀錄
	<table border="1">
	<tr>
		<td>返品日期</td>
		<td>數量</td>
		<td>倉管remark</td>
	
	</tr>
	<? foreach ($form->containerReturnHistories as $history) { ?>
	<tr>
		<td><?=$history->create_date ?></td>
		<td><?=$history->qty ?></td>
		<td><?=$history->remark ?></td>
	</tr>
	<? } ?>
	</table>
</div>
