<?
	$shippingFee = $form->shippingFee;
	$customer = new Model_Customer($shippingFee->customer_id);
?>

	<table>
		<tr>
			<td>Cust Code:</td>
			<td><?=$customer->cust_code ?></td>
		</tr>
		<tr>
			<td>品番:</td>
			<td><?=$shippingFee->container_no ?></td>
		</tr>
		<tr>
			<td>品目:</td>
			<td><?=$shippingFee->description ?></td>
		</tr>
		<tr>
			<td>合計請求金額（税込・円）:</td>
			<td>￥<?=$shippingFee->amount ?></td>
		</tr>
		<tr>
			<td>備考:</td>
			<td><?=$shippingFee->remark ?></td>
		</tr>
		<tr>
			<td>輸入日期:</td>
			<td><?=$shippingFee->create_date ?></td>
		</tr>
	</table>
	
	<input type="submit" onclick="window.history.back()" value="Back" />
