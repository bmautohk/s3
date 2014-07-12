<?
$customerOptions = Model_Customer::getOptions(true);
$orderOption = Model_Warehouse_SearchForm::getOrderOptions();
?>

<? echo Form::open("warehouse/kaito_product_list", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'search'); ?>
<table cellspacing="0" cellpadding="0">
	<tr>
		<td>櫃號:</td>
		<td><? echo Form::input('container_no', $form->container_no, array('id'=>'container_no')); ?></td>
	</tr>
	<tr>
		<td>Order No.:</td>
		<td><? echo Form::input('order_id', $form->order_id); ?></td>
	</tr>
	<tr>
		<td>Part No. (品番):</td>
		<td><? echo Form::input('product_cd', $form->product_cd); ?></td>
	</tr>
	<tr>
		<td>Cust Code:</td>
		<td><? echo Form::select("customer_id", $customerOptions, $form->customer_id); ?></td>
	</tr>
	<tr>
		<td><? echo __('label.order_date'); ?>:</td>
		<td>
			<? echo Form::input('order_date_from', $form->order_date_from, array('id'=>'order_date_from')); ?>
			-
			<? echo Form::input('order_date_to', $form->order_date_to, array('id'=>'order_date_to')); ?>
		</td>
	</tr>
</table>
<input type="submit" value="Search" />
<input type="button" onclick="goToExport(this)" value="Excel" />
<? echo Form::close(); ?>

<? if (isset($form->orderProducts)) { ?>
<? echo $form->pager(); ?>
<table border="1">
	<tr>
		<td>item lv status</td>
		<td>櫃號(multiple)</td>
		<td>Order No.</td>
		<td>Cust Code</td>
		<td>Part No.:(品番)</td>
		<td>qty</td>
		<td>marketprice</td>
		<td>參考價格</td>
		<td>cost海渡價</td>
		<td>product name(per items)</td>
		<td>Brand name(pm.車種)</td>
		<td>Car Name(車型)</td>
		<td>Model Name(型號)</td>
		<td><? echo __('label.accessory_remark'); ?></td>
		<td><? echo __('label.year'); ?></td>
		<td>color</td>
		<td><? echo __('label.colour_no'); ?></td>
		<td>pieces(per items)</td>
		<td>material(per items)</td>
		<td>subtotal</td>
		<td>deposit amt</td>
		<td>profit</td>
		<td>tax included稅</td>
		<td>delivery fee (per item)送料</td>
		<td>交貨日期</td>
		<td>入櫃日期</td>
		<td>庫存量</td>
		<td>總貨量</td>
		<td>厰/ben 數量(大步分貨量)</td>
		<td>已從工厰/ben給倉管員</td>
		<td>pm 設定了的供應商</td>
		<td>pic1</td>
		<td>pic2</td>
		<td>pic3</td>
	</tr>
	<? foreach ($form->orderProducts as $orderProduct) { ?>
	<tr>
		<td><?=$orderProduct->factory_status == Model_OrderProduct::STATUS_COMPLETE ? '完成' : '未完成' ?></td>
		<td><?=$orderProduct->containerSummary->container_no_list ?></td>
		<td><?=$orderProduct->order_id ?></td>
		<td><?=$orderProduct->cust_code ?></td>
		<td><?=$orderProduct->product_cd ?></td>
		<td><?=$orderProduct->qty ?></td>
		<td><?=$orderProduct->market_price ?></td>
		<td></td>
		<td><?=$orderProduct->kaito ?></td>
		<td><?=$orderProduct->productMaster->product_desc ?></td>
		<td><?=$orderProduct->productMaster->made ?></td>
		<td><?=$orderProduct->productMaster->model ?></td>
		<td><?=$orderProduct->productMaster->model_no ?></td>
		<td><?=$orderProduct->productMaster->accessory_remark ?></td>
		<td><?=$orderProduct->productMaster->year ?></td>
		<td><?=$orderProduct->productMaster->colour ?></td>
		<td><?=$orderProduct->productMaster->colour_no ?></td>
		<td><?=$orderProduct->productMaster->pcs ?></td>
		<td><?=$orderProduct->productMaster->material ?></td>
		<td><?=$orderProduct->getSubTotal() ?></td>
		<td><?=$orderProduct->order->deposit_amt ?></td>
		<td><?=$orderProduct->profit ?></td>
		<td><?=$orderProduct->getTaxDescription() ?></td>
		<td><?=$orderProduct->delivery_fee ?></td>
		<td><?=$orderProduct->containerSummary->delivery_date_list ?></td>
		<td><?=$orderProduct->containerSummary->container_input_date_list ?></td>
		<td></td>
		<td><?=$orderProduct->qty ?></td>
		<td><?=$orderProduct->factory_qty ?></td>
		<td><?=$orderProduct->factory_delivery_qty ?></td>
		<td><?=$orderProduct->productMaster->supplier ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture1); ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture2); ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture3); ?></td>
	</tr>
	<? } ?>
</table>
<? } ?>

<script type="text/javascript">
	$(function() {
		$('#container_no').autocomplete({
			source: "<?=url::base() == '/' ? '' : url::base() ?>/factory/search_container_no",
			minLength: 2
		});

		$("#order_date_from").datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
		
		$("#order_date_to").datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
	});

	function goToExport(elem) {
		var form = $(elem).parent();
		var origAction = form.attr('action');
		form.attr('action', '<?=PATH_BASE ?>warehouse/kaito_product_export');
		form.submit();

		form.attr('action', origAction);
	}
</script>