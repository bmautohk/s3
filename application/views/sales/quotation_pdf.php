<style>
	table {
		border-collapse:collapse;
		border-width: 0px;
	}
	
	td {
		text-align: center;
	}
	
	th {
		text-align: center;
		border:1px solid black;
	}
	
	td.detail, th.detail {
		border:1px solid black;
	}
	
	table th {
		background-color: grey;
	}
	
	td.label {
		text-align:right;
	}
	
	td.bank {
		text-align:left;
	}
	
</style>

<table style="width: 800px">
	<tr>
		<th>No.</th>
		<th>商品名</th>
		<th>メーカー</th>
		<th>車種</th>
		<th>型式</th>
		<th>色</th>
		<th>PCS</th>
		<th>数量</th>
		<th>単価 (RMB/Yen)</th>
		<th>合計 (RMB/Yen)</th>
		<th>備考</th>
	</tr>
	<?
	$totalRMB = 0;
	$totalJPY = 0;
	$totalExcludeTaxRMB = 0;
	$totalExcludeTaxJPY = 0;
	$totalTaxRMB = 0;
	$totalTaxJPY = 0;
	foreach ($form->orderProducts as $orderProduct) {
		$productPriceRMB = $orderProduct->market_price * $orderProduct->qty;
		$totalExcludeTaxRMB += $productPriceRMB;
		
		$marketPriceJPY = GlobalFunction::convertRMB2JPY($orderProduct->market_price, $form->order->rmb_to_jpy_rate);
		$productPriceJPY = $marketPriceJPY * $orderProduct->qty;
		$totalExcludeTaxJPY += $productPriceJPY;
		
		// Calculate tax
		if ($orderProduct->is_tax == Model_OrderProduct::TAX_INCLUDE) {
			$taxRMB = $productPriceRMB * $form->tax_rate;
			$taxJPY = GlobalFunction::roundJPY($productPriceJPY  * $form->tax_rate);
		} else {
			$taxRMB = 0;
			$taxJPY = 0;
		}
		
		$totalTaxRMB += $taxRMB;
		$totalTaxJPY += $taxJPY;
		
		$totalRMB += $productPriceRMB + $taxRMB;
		$totalJPY += $productPriceJPY + $taxJPY;
		?>
		<tr>
			<td class="detail"><?=$orderProduct->product_cd ?></td>
			<td class="detail"><?=$orderProduct->productMaster->product_desc ?></td>
			<td class="detail"><?=$orderProduct->productMaster->made ?></td>
			<td class="detail"><?=$orderProduct->productMaster->model ?></td>
			<td class="detail"><?=$orderProduct->productMaster->model_no ?></td>
			<td class="detail"><?=$orderProduct->productMaster->colour_no ?></td>
			<td class="detail"><?=$orderProduct->productMaster->pcs ?></td>
			<td class="detail"><?=$orderProduct->qty ?></td>
			<td class="detail"><?=GlobalFunction::displayNumber($orderProduct->market_price) ?> / <?=GlobalFunction::displayJPYNumber($marketPriceJPY)?></td>
			<td class="detail">
				<? echo GlobalFunction::displayNumber($productPriceRMB).' / '.GlobalFunction::displayJPYNumber($productPriceJPY); ?>
			</td>
			<td class="detail"></td>
		</tr>
		<? if ($orderProduct->delivery_fee != 0) {
			$deliveryFeeRMB = GlobalFunction::convertJPY2RMB($orderProduct->delivery_fee, $form->order->rmb_to_jpy_rate);
			
			$totalExcludeTaxRMB += $deliveryFeeRMB;
			$totalExcludeTaxJPY += $orderProduct->delivery_fee;
			
			$totalRMB += $deliveryFeeRMB;
			$totalJPY += $orderProduct->delivery_fee;
			?>
			<tr>
				<td class="detail">国内国外送料</td>
				<td class="detail"></td>
				<td class="detail"></td>
				<td class="detail"></td>
				<td class="detail"></td>
				<td class="detail"></td>
				<td class="detail"></td>
				<td class="detail"></td>
				<td class="detail"></td>
				<td class="detail"><?=GlobalFunction::displayNumber($deliveryFeeRMB) ?> / <?=GlobalFunction::displayJPYNumber($orderProduct->delivery_fee) ?></td>
				<td class="detail"></td>
			</tr>
		<? } ?>
	<? } ?>
	
	<tr>
		<td colspan="7"></td>
		<td colspan="2" class="label">小計（RMB/Yen）</td>
		<td class="detail"><?=GlobalFunction::displayNumber($totalExcludeTaxRMB) ?> / <?=GlobalFunction::displayJPYNumber($totalExcludeTaxJPY); ?></td>
	</tr>
	
	<tr>
		<td colspan="7"></td>
		<td colspan="2" class="label">消費稅額（RMB/Yen）</td>
		<td class="detail"><?=GlobalFunction::displayNumber($totalTaxRMB) ?> / <?=GlobalFunction::displayJPYNumber($totalTaxJPY); ?></td>
	</tr>

	<tr>
		<td colspan="7"></td>
		<td colspan="2" class="label">商品総額（RMB/Yen）</td>
		<td class="detail"><?=GlobalFunction::displayNumber($totalRMB) ?> / <?=GlobalFunction::displayJPYNumber($totalJPY) ?></td>
	</tr>
	
	<br><br>
		<? if ($form->order->deposit_amt != 0) { ?>
		<tr>
			<td class="detail">訂金</td>
			<td class="detail"></td>
			<td class="detail"></td>
			<td class="detail"></td>
			<td class="detail"></td>
			<td class="detail"></td>
			<td class="detail"></td>
			<td class="detail"></td>
			<td class="detail"></td>
			<td class="detail"><?=GlobalFunction::displayNumber($form->order->deposit_amt) ?> / <?=GlobalFunction::displayJPYNumber(GlobalFunction::convertRMB2JPY($form->order->deposit_amt, $form->order->rmb_to_jpy_rate)) ?></td>
			<td class="detail"></td>
		</tr>
	<? } ?>
		<br><br>
	
	<tr>
		<td colspan="10"></td>
		<td>お取引レート</td>
	</tr>
	
	<tr>
		<td colspan="7" rowspan="3" class="bank">
			<?=$form->order->customer->bank_account ?>
		</td>
		<td colspan="2" class="label">JPYの場合</td>
		<td class="detail">￥<?=GlobalFunction::displayJPYNumber($totalJPY) ?></td>
		<td class="detail"><?=$form->order->rmb_to_jpy_rate ?></td>
	</tr>
	
	<tr>
		<td colspan="2" class="label">USドルの場合</td>
		<td class="detail">US$<?=GlobalFunction::displayNumber(GlobalFunction::convertRMB2USD($totalRMB, $form->order->rmb_to_usd_rate)) ?></td>
		<td class="detail"><?=$form->order->rmb_to_usd_rate ?></td>
	</tr>
	
	<tr>
		<td colspan="2"></td>
		<td colspan="2">レートの有効期間：当月末迄</td>
	</tr>
</table>