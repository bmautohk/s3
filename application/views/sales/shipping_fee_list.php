<?
$hasRight = GlobalFunction::hasPrivilege('sales_shipping_fee', Model_RoleMatrix::PERMISSION_WRITE);

echo Form::open("sales/shipping_fee", array('id'=>'form1', 'method'=>'get'));
echo Form::hidden('action', 'search', array('id'=>'action'));
?>
	<label>Cust Code:</label><? echo Form::select('customer_id', Model_Customer::getOptions(true), $form->customer_id); ?>
	<input type="button" onclick="search()" value="Search" />
	
	<? if ($hasRight) { ?>
		<input type="button" onclick="add()" value="Add" />
	<? } ?>
<? echo Form::close(); ?>

<div style="width:400px">
<? echo $form->pager(); ?>
	<table border="1">
		<tr>
			<td>Cust Code</td>
			<td>品番</td>
			<td>品目</td>
			<td>合計請求金額（税込・円）</td>
			<td>備考</td>
		</tr>
		<? foreach ($form->shippingFees as $shippingFee) { ?>
		<tr>
			<td><?=$shippingFee->cust_code ?></td>
			<td><?=$shippingFee->container_no ?></td>
			<td><?=$shippingFee->description ?></td>
			<td><?=$shippingFee->amount ?></td>
			<td><?=$shippingFee->remark ?></td>
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