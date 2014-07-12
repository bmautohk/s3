<?

?>
<style>
	table {
		border-collapse:collapse;
		border-width: 0px;
	}
	
	th.header {
		background-color: rgb(188, 208, 241);
		border-style: solid;
		border-width: 0px 0px 0px 1px;
		color: white;
		border-color: black rgb(188, 208, 241) black rgb(188, 208, 241);
	}

	td.detail {
		border-width: 0px 1px 0px 1px;
		border-style: solid;
		border-color: black rgb(188, 208, 241) black rgb(188, 208, 241);
	}
	
	td.total1 {
		background-color: rgb(238, 238, 238);
		border-width: 1px 1px 0px 1px;
		border-style: solid;
		border-color: black rgb(188, 208, 241) black rgb(188, 208, 241);
		text-align:right;
	}
	
	td.total2 {
		background-color: rgb(188, 208, 241);
		border-width: 1px 1px 0px 1px;
		border-style: solid;
		border-color: black rgb(188, 208, 241) black rgb(188, 208, 241);
		text-align:right;
	}
	
	td.label {
		text-align:right;
	}
	
	.number {
		text-align:right;
	}
</style>
	
<table style="width: 1000px">
	<tr>
		<th class="header" style="width:40px">品番</th>
		<th class="header">品目</th>
		<th class="header" style="width:40px">数量</th>
		<th class="header" style="width:80px">単価</th>
		<th class="header" style="width:80px">金額</th>
		<th class="header">備考</th>
	</tr>
	<? foreach ($form->shippingFees as $shippingFee) { ?>
		<tr>
			<td class="detail"><?=$shippingFee->container_no ?></td>
			<td class="detail"><?=$shippingFee->description ?></td>
			<td class="detail">1</td>
			<td class="detail number"><?=$shippingFee->amount ?></td>
			<td class="detail number"><?=$shippingFee->amount ?></td>
			<td class="detail"><?=$shippingFee->remark ?></td>
		</tr>
	<? } ?>
	<tr>
		<td colspan="4" class="total1">合計</td>
		<td class="total1 number"><?=$form->total ?></td>
		<td class="total1"></td>
	</tr>
	<tr>
		<td colspan="4" class="total1"></td>
		<td class="total1 number"><?=$form->total ?></td>
		<td class="total1"></td>
	</tr>
	<tr>
		<td colspan="4" class="total2">税込み合計額</td>
		<td class="total2 number"><?=$form->total ?>円</td>
		<td class="total2"></td>
	</tr>
</table>

<div>
	ご入金期限　請求書到着後一週間以内でお願い致します。
</div>
