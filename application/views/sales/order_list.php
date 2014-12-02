<?
$customerOptions = Model_Customer::getOptions(true);
$orderStatusOptions = array('A'=>'未完成', 'C'=>'完成', Model_Order::STATUS_VOID=>'Void'); // 'I'=>'tem 終了' 
$isSales = Auth::instance()->get_user()->isSales();
?>

<div id="search_form">
	<span> Search</span>
	
	<? echo Form::open("sales/order_search", array('id'=>'form1', 'method'=>'GET'));
		echo Form::hidden('action', '', array('id'=>'action'));
		echo Form::hidden('order_product_id', '', array('id'=>'order_product_id'));
	?>
		<table>
			<tr>
				<td>By Cust Code</td>
				<td><? echo Form::select("customer_id", $customerOptions, $form->customer_id); ?></td>
			</tr>
			
			<tr>
				<td>Order No.</td>
				<td><? echo Form::input("search_order_id", $form->search_order_id); ?></td>
			</tr>
			
			<tr>
				<td><? echo __('label.product_cd'); ?></td>
				<td><? echo Form::input("product_cd", $form->product_cd); ?></td>
			</tr>
			
			<tr>
				<td><? echo __('label.container_no'); ?></td>
				<td><? echo Form::input("container_no", $form->container_no); ?></td>
			</tr>
			
			<tr>
				<td><? echo __('label.order_type'); ?></td>
				<td><? echo Form::select("order_type_id", Model_OrderType::getOptions(true), $form->order_type_id); ?></td>
			</tr>
			
			<? if (!$isSales) { ?>
			<tr>
				<td>Sales Code</td>
				<td><? echo Form::select("username", Model_User::getOptions(true), $form->username); ?></td>
			</tr>
			<? } ?>
			
			<tr>
				<td>By Order Status</td>
				<td><? echo Form::select("status", $orderStatusOptions, $form->status); ?></td>
			</tr>
			<tr>
				<td>By Market Price</td>
				<td><? echo Form::input("market_price", $form->market_price); ?></td>
			</tr>

			<tr>
				<td><? echo __('label.order_date'); ?></td>
				<td>
					<? echo Form::input('order_date_from', $form->order_date_from, array('id'=>'order_date_from')); ?>
					-
					<? echo Form::input('order_date_to', $form->order_date_to, array('id'=>'order_date_to')); ?>
				</td>
				<td>
					<input type="submit" value="Search" onclick="onSearch()" />
					<input type="button" onclick="onExport()" value="Export" />
				</td>
			</tr>
		</table>
	<? echo Form::close(); ?>
</div>

<? if (isset($form->orderProducts)) { 
	$total_rmb = 0;
	$total_jpy = 0;
	$total_usd = 0;
	$totalProfit_rmb = 0;
	$totalProfit_jpy = 0;
	$totalProfit_usd = 0;
	?>
<div id="list_form">
	<? echo $form->pager(); ?>
	<table border="1" class="tbl_view" style="width:2500px">
		<tr>
			<td style="width:39px"></td>
			<td style="width:39px"></td>
			<td style="width:39px"></td>
			<td style="width:39px"></td>
			<td><? echo __('label.order_no'); ?></td>
			<td><? echo __('label.order_date'); ?></td>
			<td><? echo __('label.kaito_remark'); ?></td>
			<td><? echo __('label.cust_code'); ?></td>
			<td><? echo __('label.product_cd'); ?></td>
			<td><? echo __('label.qty'); ?></td>
			<td><? echo __('label.market_price'); ?></td>
			<td><? echo __('label.reference_price'); ?></td>
			<td><? echo __('label.business_price'); ?></td>
			<td><? echo __('label.product_desc'); ?></td>
			<td style="width:45px"><? echo __('label.made'); ?></td>
			<td><? echo __('label.model'); ?></td>
			<td><? echo __('label.model_no'); ?></td>
			<td><? echo __('label.accessory_remark'); ?></td>
			<td><? echo __('label.year'); ?></td>
			<td><? echo __('label.colour'); ?></td>
			<td><? echo __('label.colour_no'); ?></td>
			<td><? echo __('label.pcs').'<br />'.__('label.sales.per_item'); ?></td>
			<td><? echo __('label.material').'<br/>'.__('label.sales.per_item'); ?></td>
			<td>商品说明</td>
			<td>年式</td>
			<td><? echo __('label.subtotal'); ?><br>(RMB / ¥ / USD)</td>
			<td style="width:70px"><? echo __('label.payment'); ?></td>
			<td><? echo __('label.profit'); ?><br>(RMB / ¥)</td>
			<td><? echo __('label.is_tax'); ?></td>
			<td><? echo __('label.delivery_fee'); ?></td>
			<td style="width:30px"><? echo __('label.jp_delivery_note_qty'); ?></td>
			<td><? echo __('label.factory_entry_qty'); ?></td>
			<td><? echo __('label.factory_delivery_qty'); ?></td>
			<td><? echo __('label.container_no'); ?></td>
			<td><? echo __('label.factory_delivery_qty'); echo __('label.sales.per_item'); ?></td>
			<td><? echo __('label.delivery_date'); echo __('label.sales.per_item'); ?></td>
			<td><? echo __('label.container_input_date'); echo __('label.sales.per_item'); ?></td>
			<td><? echo __('label.sales_remark'); ?></td>
			<td><? echo __('label.jp_auditor_remark'); ?></td>
			<td><? echo __('label.factory_auditor_remark'); ?></td>
			<td><? echo __('label.sales.propose_delivery_date'); ?></td>
			<td><? echo __('label.picture1'); ?></td>
			<td><? echo __('label.picture2'); ?></td>
			<td><? echo __('label.picture3'); ?></td>
			<td><? echo __('label.order_type'); ?></td>
			<td><? echo __('label.sales.transfer_to'); ?></td>
			<td><? echo __('label.order_status'); ?></td>
		</tr>
		
		<? foreach ($form->orderProducts as $orderProduct) {
			$subTotals = $orderProduct->getSubTotalWithDifferentCurrency($orderProduct->rmb_to_jpy_rate, $orderProduct->rmb_to_usd_rate, $form->taxRate);
			$total_rmb += $subTotals[0];
			$total_jpy += $subTotals[1];
			$total_usd += $subTotals[2];
			
			$totalProfit_rmb += $orderProduct->profit;
			$totalProfit_jpy += ceil($orderProduct->profit * $orderProduct->rmb_to_jpy_rate);
			
			$tr_class = "";
			if ($orderProduct->is_reject == Model_OrderProduct::IS_REJECT_YES && $orderProduct->factory_status == Model_OrderProduct::STATUS_SALES) {
				$tr_class = "highlight";
			}
			
			if ($orderProduct->jp_status == Model_OrderProduct::STATUS_CANCEL) {
				$tr_class = "cancel";
			}
		?>
		<tr class='<?=$tr_class ?>'>
			<td>
				<? if ($orderProduct->order->confirm_deposit_amt < $orderProduct->order->deposit_amt) { ?>
					<input type="button" value="Deposit入金" onclick="location.href='<?=URL::site('sales/deposit_settlement?action=add&order_id='.$orderProduct->order_id) ?>'" />
				<? } ?>
			</td>
			<td>
				<? if ($orderProduct->isEnableSalesEdit()) { ?>
					<input type="button" value="Edit" onclick="location.href='<?=URL::site('sales/order_edit/'.$orderProduct->order_id) ?>'" />
				<? } ?>
			</td>
			<td>
				<? if ($orderProduct->isEnableCancel()) { ?>
					<input type="button" value="Cancel" onclick="onCancel(<?=$orderProduct->id?>)" />
				<? } ?>
			</td>
			<td>
				<input type="button" value="打印"  onclick="print(<?=$orderProduct->order_id ?>)" />
			</td>
			<td><?=$orderProduct->order_id ?></td>
			<td><?=$orderProduct->order->order_date ?></td>
			<td><?=$orderProduct->kaito_remark ?></td>
			<td><?=$orderProduct->cust_code ?></td>
			<td><?=$orderProduct->product_cd ?></td>
			<td><?=$orderProduct->qty ?></td>
			<td><?=$orderProduct->market_price ?></td>
			<td></td>
			<td><?=$orderProduct->business_price ?></td>
			<td><?=$orderProduct->product_desc ?></td>
			<td><?=$orderProduct->made ?></td>
			<td><?=$orderProduct->model ?></td>
			<td><?=$orderProduct->model_no ?></td>
			<td><?=$orderProduct->accessory_remark ?></td>
			<td><?=$orderProduct->year ?></td>
			<td><?=$orderProduct->colour ?></td>
			<td><?=$orderProduct->colour_no ?></td>
			<td><?=$orderProduct->pcs ?></td>
			<td><?=$orderProduct->material ?></td>
			<td><?=$orderProduct->accessory_remark ?></td>
			<td><?=$orderProduct->year ?></td>
			<td><?=GlobalFunction::displayNumber($subTotals[0]).' / '.GlobalFunction::displayJPYNumber($subTotals[1]).' / '. GlobalFunction::displayNumber($subTotals[2]) ?></td>
			<td></td>
			<td><?=GlobalFunction::displayNumber($orderProduct->profit) ?> / <?=$orderProduct->getFormatProfit(Model_OrderProduct::CURRENCY_JPY, $orderProduct->rmb_to_jpy_rate) ?></td>
			<td><?=$orderProduct->getTaxDescription() ?></td>
			<td><?=$orderProduct->delivery_fee ?></td>
			<td><?=$orderProduct->jp_delivery_note_qty ?></td>
			<td><?=$orderProduct->factory_entry_qty ?></td>
			<td><?=$orderProduct->factory_delivery_qty ?></td>
			<td><?=$orderProduct->containerSummary->container_no_list ?></td>
			<td><?=$orderProduct->containerSummary->delivery_qty_list ?></td>
			<td><?=$orderProduct->containerSummary->delivery_date_list ?></td>
			<td><?=$orderProduct->containerSummary->container_input_date_list ?></td>
			<td><?=$orderProduct->order->remark ?></td>
			<td><?=$orderProduct->jp_auditor_remark ?></td>
			<td><?=$orderProduct->factory_auditor_remark ?></td>
			<td><?=$orderProduct->propose_delivery_date ?></td>
			<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->picture1); ?></td>
			<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->picture2); ?></td>
			<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->picture3); ?></td>
			<td><?=$orderProduct->order_type_description ?></td>
			<td><?=$orderProduct->getProcessingStep() ?></td>
			<td><?=$orderProduct->getItemLevelStatusDescription() ?></td>
		</tr>
		<? } ?>
		
		<tr>
			<td colspan="22"></td>
			<td><?=GlobalFunction::displayNumber($total_rmb).' / '.GlobalFunction::displayJPYNumber($total_jpy).' / '.GlobalFunction::displayNumber($total_usd) ?></td>
			<td></td>
			<td><?=GlobalFunction::displayNumber($totalProfit_rmb).' / '.GlobalFunction::displayJPYNumber($totalProfit_jpy) ?></td>
		</tr>
	</table>
</div>
<? } ?>

<script type="text/javascript">
$(function() {
	$( "#order_date_from" ).datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: "both",
		buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
		buttonImageOnly: true
	});
	
	$( "#order_date_to" ).datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: "both",
		buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
		buttonImageOnly: true
	});
});

function onSearch() {
	$('#action').val('');
}

function onExport() {
	$('#action').val('export');
	$('#form1').submit();
}

function onCancel(order_product_id) {
	$('#form1').attr('method', 'post');
	$('#action').val('cancel');
	$('#order_product_id').val(order_product_id);
	$('#form1').submit();
}

function print(order_id) {
	window.open("<?='http://'.$_SERVER['HTTP_HOST'].URL::site('sales/quotation_print') ?>" + "/" + order_id);
}
</script>