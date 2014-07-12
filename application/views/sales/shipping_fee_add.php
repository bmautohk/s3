<?
	$shippingFee = $form->shippingFee;
?>
	
	<? echo Form::open("sales/shipping_fee", array('id'=>'form1')); ?>
	<? echo Form::hidden('action', 'save'); ?>
	<table>
		<tr>
			<td>Cust Code:</td>
			<td><? echo Form::select('customer_id', Model_Customer::getOptions(), $form->customer_id); ?></td>
		</tr>
		<tr>
			<td>品番:</td>
			<td><? echo Form::input('container_no', $shippingFee->container_no); ?></td>
		</tr>
		<tr>
			<td>品目:</td>
			<td><? echo Form::textarea('description', $shippingFee->description, array('rows'=>3)); ?></td>
		</tr>
		<tr>
			<td>単価:</td>
			<td>￥<? echo Form::input('amount', $shippingFee->amount); ?></td>
		</tr>
		<tr>
			<td>備考:</td>
			<td><? echo Form::textarea('remark', $shippingFee->remark, array('rows'=>3)); ?></td>
		</tr>
	</table>
	
	<input type="submit" value="入" />
	<? echo Form::close(); ?>
