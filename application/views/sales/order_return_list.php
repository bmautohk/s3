<?
$hasRight = GlobalFunction::hasPrivilege('sales_order_return', Model_RoleMatrix::PERMISSION_WRITE);
 
echo Form::open("sales/order_return", array('id'=>'form1', 'method'=>'get'));
echo Form::hidden('action', 'search', array('id'=>'action'));
?>
	<label>Cust Code:</label><? echo Form::select('customer_id', Model_Customer::getOptions(true), $form->customer_id); ?>
	<input type="button" onclick="search()" value="<? echo __('button.search'); ?>" />
	
	<? if ($hasRight) { ?>
		<input type="button" onclick="add()" value="<? echo __('button.add'); ?>" />
	<? } ?>
<? echo Form::close(); ?>

<div style="">
	<? echo $form->pager(); ?>
	<table border="1">
		<tr>
			<td><? echo __('label.cust_code'); ?></td>
			<td><? echo __('label.order_return.return_date'); ?></td>
			<td><? echo __('label.product_cd'); ?></td>
			<td><? echo __('label.order_return.return_qty'); ?></td>
			<td><? echo __('label.order_return.return_pay_rmb'); ?> </td>
			<td><? echo __('label.order_return.return_pay_jpy'); ?> </td>
			<td><? echo __('label.order_return.remark'); ?></td>
		</tr>
		<? foreach ($form->orderReturns as $history) { ?>
		<tr>
			<td><?=$history->cust_code ?></td>
			<td><?=$history->return_date ?></td>
			<td><?=$history->product_cd ?></td>
			<td><?=$history->return_qty ?></td>
			<td><?=$history->return_pay ?></td>
			<td><?=$history->getReturnPayJPY() ?></td>
			<td><?=$history->remark ?></td>
		</tr>
		<? } ?>
	</table>
</div>

<script type="text/javascript">
function search() {
	$('#action').val('search');
	$('#form1').submit();
}

function add() {
	$('#action').val('add');
	$('#form1').submit();
}
</script>