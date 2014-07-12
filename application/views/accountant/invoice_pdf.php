<?
$invoice = $form->invoice; 
?>

<style>
	table {
		border-collapse:collapse;
		border-width: 0px;
	}
	
	th, td.detail, th.detail {
		border:1px solid black;
	}

	tr.rate, td.rate {
		border-width: 0px;
	}
</style>

<table style="width: 750px">
	<tr>
		<td class="detail">前回請求額</td>
		<td class="detail">御入金額</td>
		<td class="detail">繰越金額</td>
		<td class="detail">今回御買上額</td>
		<td class="detail">消費稅</td>
		<td class="detail">源泉徵收稅額</td>
		<td class="detail">今回請求金額</td>
	</tr>
	<tr>
		<td class="detail">￥<?=GlobalFunction::displayJPYNumber($invoice->last_month_amt) ?></td>
		<td class="detail">￥<?=GlobalFunction::displayJPYNumber($invoice->last_month_settle) ?></td>
		<td class="detail">￥<?=GlobalFunction::displayJPYNumber($invoice->last_month_amt - $invoice->last_month_settle) ?></td>
		<td class="detail">￥<?=GlobalFunction::displayJPYNumber($invoice->current_month_amt) ?></td>
		<td class="detail">￥<?=GlobalFunction::displayJPYNumber($invoice->total_tax) ?></td>
		<td class="detail"></td>
		<td class="detail">￥<?=GlobalFunction::displayJPYNumber($invoice->total_amt) ?></td>
	</tr>
</table>

<div style="height:20px"></div>

<table style="width: 750px">
	<thead>
	<tr nobr="true">
		<th>日付/伝票番号</th>
		<th>品番/詳細</th>
		<th>數量</th>
		<th>単価  (RMB / ￥)</th>
		<th>金額  (RMB / ￥)</th>
		<th>備考</th>
	</tr>
	</thead>
	<? foreach ($form->products as $product) { ?>
	<tr nobr="true">
		<td class="detail">
			<? if ($product->delivery_note_no != NULL) {
				echo date('Y/m/d', strtotime($product->delivery_note_create_date)); ?><br />
			<?	echo $product->delivery_note_no; 
			} ?>
		</td>
		<td class="detail"><?=$product->description ?></td>
		<td class="detail"><?=$product->qty ?></td>
		<td class="detail">
			<? if ($product->market_price != '') {
				echo GlobalFunction::displayNumber($product->market_price_rmb).'元 / '.GlobalFunction::displayJPYNumber($product->market_price).'円';
			} ?>
		</td>
		<td class="detail"><? echo GlobalFunction::displayNumber($product->total_rmb).'元 / '.GlobalFunction::displayJPYNumber($product->total).'円'; ?></td>
		<td class="detail"><?=$product->remark ?></td>
	</tr>
	<? } ?>
</table>
