<?
$invoice = $form->invoice; 
?>

<style>
	table {
		border-collapse:collapse;
		border-width: 0px;
	}
	
	td.detail, th.detail {
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
		<td class="detail">￥<?=GlobalFunction::displayNumber($invoice->last_month_amt) ?></td>
		<td class="detail">￥<?=GlobalFunction::displayNumber($invoice->last_month_settle) ?></td>
		<td class="detail">￥<?=GlobalFunction::displayNumber($invoice->last_month_amt - $invoice->last_month_settle) ?></td>
		<td class="detail">￥<?=GlobalFunction::displayNumber($invoice->current_month_amt) ?></td>
		<td class="detail">￥<?=GlobalFunction::displayNumber($invoice->total_tax) ?></td>
		<td class="detail"></td>
		<td class="detail">￥<?=GlobalFunction::displayNumber($invoice->total_amt) ?></td>
	</tr>
</table>

<div style="height:20px"></div>

<table style="width: 750px">
	<tr>
		<td class="detail">日付/伝票番号</td>
		<td class="detail">品番/詳細</td>
		<td class="detail">數量</td>
		<td class="detail">單價  (RMB / ￥)</td>
		<td class="detail">金額  (RMB / ￥)</td>
		<td class="detail">備考</td>
	</tr>
	<? foreach ($form->products as $product) { ?>
	<tr>
		<td class="detail">
			<?=date('Y/m/d', strtotime($product->delivery_note_create_date)) ?><br />
			<?=$product->delivery_note_no ?>
		</td>
		<td class="detail"><?=$product->description ?></td>
		<td class="detail"><?=$product->qty ?></td>
		<td class="detail">
			<? if ($product->market_price != '') {
				echo GlobalFunction::displayNumber($product->market_price_rmb).'元 / '.GlobalFunction::displayNumber($product->market_price).'円';
			} ?>
		</td>
		<td class="detail"><? echo GlobalFunction::displayNumber($product->total_rmb).'元 / '.GlobalFunction::displayNumber($product->total).'円'; ?></td>
		<td class="detail"><?=$product->remark ?></td>
	</tr>
	<? } ?>
</table>

<div style="height:20px"></div>

<div style="text-align:right">
	レート有効期間当月末まで <br />
	1元 = <?=$invoice->rmb_to_jpy_rate ?>円&nbsp;&nbsp;&nbsp;<br />
	1元 = <?=$invoice->rmb_to_usd_rate ?>US$
</div>

