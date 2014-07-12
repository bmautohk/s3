<html>
<head>
	<style>
		table {
			border-collapse:collapse;
		}
		table, td, th {
			border:1px solid black;
		}
	</style>
</head>
<body>
<div>納品書</div>

<div style="height: 150px; ">
	<div style="width: 400px; height: 150px; border:1px solid; display:block; float:left">
		<br />
		<? echo $form->customer->name; ?><br />
		<? echo $form->customer->address1; ?><br />
		<? echo $form->customer->address2; ?><br />
		<? echo $form->customer->address3; ?><br />
		Tel: <? echo $form->customer->tel; ?>
	</div>
	
	<div style="width: 50px; float:left; height: 150px; ">
	</div>
	
	<div style="width: 400px; height: 150px; float:left">
		<div style="border:1px solid; float:left; padding: 10px">売上日 <?=date('Y-m-d') ?></div>
		<div style="width: 50px; height: 10px; float:left"></div>
		<div style="border:1px solid; float:left; padding: 10px">No <?=str_pad($form->deliveryNote->id, 8, '0', STR_PAD_LEFT) ?></div>
	</div>
</div>

<br />

<div style="">
	<table style="width: 1000px">
		<tr>
			<td colspan="3">品番/詳細</td>
			<td>数量</td>
			<td>單價</td>
			<td>金額</td>
			<td>備考</td>
		</tr>
		<? foreach ($form->products as $product) { ?>
		<tr>
			<td colspan="3"><?=$product->product_cd ?><br /><?=$product->product_desc ?></td>
			<td><?=$product->delivery_qty ?></td>
			<td><?=$product->market_price ?></td>
			<td><?=$product->total ?></td>
			<td></td>
		</tr>
		<? } ?>
		<tr>
			<td>オーダーNo</td>
			<td>稅抜額</td>
			<td><?=$form->total - $form->totalTax ?></td>
			<td>消費稅額</td>
			<td><?=$form->totalTax ?></td>
			<td>合計</td>
			<td><?=$form->total ?></td>
		</tr>
	</table>
</div>

</body>
</html>