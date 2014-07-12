<?
$deliveryNote = $form->deliveryNote;
?>
<style>
	table {
		border-collapse:collapse;
		border-width: 0px;
	}

	th, td.detail {
		border:1px solid black;
	}
	
	td.label {
		text-align:right;
	}
	
	.qty {
		width: 50px;
	}
	
	.remark {
		width: 165px;
	}
</style>

<table style="width: 750px">
	<tr nobr="true">
		<td class="detail" style="text-align:right" colspan="2">小計  (RMB / ￥)</td>
		<td class="detail"><?=GlobalFunction::displayNumber($deliveryNote->total_detail_amt) ?>元  / <?=GlobalFunction::displayJPYNumber($form->totalDetailAmtJPY) ?>円</td>
		<td class="detail">消費稅額  (RMB / ￥)</td>
		<td class="detail"><?=GlobalFunction::displayNumber($deliveryNote->total_tax) ?>元  / <?=GlobalFunction::displayJPYNumber($form->totalTaxJPY) ?>円</td>
		<td class="detail">合計  (RMB / ￥)</td>
		<td class="detail"><?=GlobalFunction::displayNumber($deliveryNote->total_amt) ?>元  / <?=GlobalFunction::displayJPYNumber($form->totalAmtJPY) ?>円</td>
	</tr>
</table>

<div style="height:20px"></div>
	
<table style="width: 750px">
	<thead>
	<tr nobr="true">
		<th colspan="3">品番/詳細</th>
		<th class="qty">數量</th>
		<th>単価  (RMB / ￥)</th>
		<th>金額  (RMB / ￥)</th>
		<th class="remark">備考</th>
	</tr>
	</thead>
	<? foreach ($form->products as $product) { ?>
	<tr nobr="true">
		<td colspan="3" class="detail"><?=$product->description ?></td>
		<td class="detail qty"><?=$product->qty ?></td>
		<td class="detail"><? if ($product->market_price != '') { ?>
				<?=GlobalFunction::displayNumber($product->market_price) ?>元  / <?=GlobalFunction::displayJPYNumber($product->market_price_jpy) ?>円
			<? } ?>
		</td>
		<td class="detail"><?=GlobalFunction::displayNumber($product->total) ?>元  / <?=GlobalFunction::displayJPYNumber($product->total_jpy) ?>円</td>
		<td class="detail remark"><?=nl2br($product->remark) ?></td>
	</tr>
	<? } ?>
</table>
