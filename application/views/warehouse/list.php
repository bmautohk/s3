<?
$customerOptions = Model_Customer::getOptions(true);
$orderOption = Model_Warehouse_SearchForm::getOrderOptions();
?>

<? echo Form::open("warehouse/list", array('id'=>'form1')); ?>
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
		<td>借出數量 (>=):</td>
		<td><? echo Form::input('borrow_qty', $form->borrow_qty); ?></td>
	</tr>
	<tr>
		<td><? echo __('label.order_date'); ?>:</td>
		<td>
			<? echo Form::input('order_date_from', $form->order_date_from, array('id'=>'order_date_from')); ?>
			-
			<? echo Form::input('order_date_to', $form->order_date_to, array('id'=>'order_date_to')); ?>
		</td>
	</tr>
	<tr>
		<td>順序</td>
		<td><? echo Form::select("order", $orderOption, $form->order); ?></td>
	</tr>
</table>
<input type="submit" value="Search" />
<input type="button" onclick="goToExport(this)" value="Excel" />
<? echo Form::close(); ?>

<? if (isset($form->orderProducts)) { ?>
<? echo $form->pager(); ?>
<table border="1">
	<tr>
		<td style="width:50px"></td>
		<td style="width:50px"></td>
		<td>交到入金管理員做納品書</td>
		<td>item lv status</td>
		<td>櫃號(multiple)</td>
		<td>Order No.</td>
		<td>Cust Code</td>
		<td>Part No.:(品番)</td>
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
		<td>已從工厰/ben給客人的數量(納品書已寄出)</td>
		<td>已從工厰/ben運到日本的數量(納品書未寄出)</td>
		<td>已從倉庫借出量</td>
		<td>已還貨給倉庫量</td>
		<td>pm 設定了的供應商</td>
		<td>発送方法</td>
		<td>pic1</td>
		<td>pic2</td>
		<td>pic3</td>
		<td><? echo __('label.order_type'); ?></td>
	</tr>
	<? foreach ($form->orderProducts as $orderProduct) { ?>
	<tr <? if ($orderProduct->is_reject == Model_OrderProduct::IS_REJECT_YES) { echo "class='highlight'"; } ?>>
		<td>
			<? if ($orderProduct->isEnableReturn()) { ?>
				<input type="button" value="還貨" onclick="location.href='<?=URL::site('warehouse/return/'.$orderProduct->id) ?>'" />
			<? } ?>
		</td>
		<td>
			<? if ($orderProduct->isEnableBorrow()) { ?>
				<input type="button" value="借出" onclick="location.href='<?=URL::site('warehouse/borrow/'.$orderProduct->id) ?>'" />
			<? } ?>
		</td>
		<td>
			<? if ($orderProduct->factory_status < Model_OrderProduct::STATUS_DELIVERY_NOTE_GENERATED) { ?>
				<input type="button" value="納品書" onclick="location.href='<?=URL::site('warehouse/container_list/'.$orderProduct->id) ?>'" />
			<? } ?>
		</td>
		<td><?=$orderProduct->getItemLevelStatusDescription() ?></td>
		<td><?=$orderProduct->containerSummary->container_no_list ?></td>
		<td><?=$orderProduct->order_id ?></td>
		<td><?=$orderProduct->cust_code ?></td>
		<td><?=$orderProduct->product_cd ?></td>
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
		<td><?=$orderProduct->factory_delivery_note_qty ?></td>
		<td><?=$orderProduct->factory_delivery_qty - $orderProduct->factory_delivery_note_qty ?></td>
		<td><?=$orderProduct->warehouse_borrow_qty ?></td>
		<td><?=$orderProduct->warehouse_return_qty ?></td>
		<td><?=$orderProduct->productMaster->supplier ?></td>
		<td>
			<? if ($orderProduct->factory_status < Model_OrderProduct::STATUS_DELIVERY_NOTE_GENERATED) { ?>
			<div class="div_delivery_method_<?=$orderProduct->order_id ?>">
				<? echo HTML::anchor('#', Model_Order::getDisplayDeliveryMethod($orderProduct->delivery_method_description, $orderProduct->delivery_method), array('onclick'=>'goUpdateDeliveryMethod('.$orderProduct->order_id.')')); ?>
			</div>
			<? } else { ?>
				<div class="div_readonly_delivery_method_<?=$orderProduct->order_id ?>">
				<? echo Model_Order::getDisplayDeliveryMethod($orderProduct->delivery_method_description, $orderProduct->delivery_method); ?>
				</div>
			<? } ?>
		</td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture1); ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture2); ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture3); ?></td>
		<td><?=$orderProduct->order_type_description?></td>
	</tr>
	<? } ?>
</table>
<? } ?>

<div id="dialog-form" title="更改発送方法">
	
</div>

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

		$("#dialog-form").dialog({
			autoOpen: false,
			height: 300,
			width: 350,
			modal: true,
			position: {my: "center top", at: "center top"}
		});
	});

	function goToExport(elem) {
		var form = $(elem).parent();
		var origAction = form.attr('action');
		form.attr('action', '<?=PATH_BASE ?>warehouse/export');
		form.submit();

		form.attr('action', origAction);
	}

	function goUpdateDeliveryMethod(order_id) {
		$.post('<?=PATH_BASE ?>sales/update_delivery_method_init',
				{order_id: order_id},
				function(data) {
					$('#dialog-form').html(data);
					$('#dialog-form').dialog('open');
				}
		);
	}

	function refresh_delivery_method(order_id, new_delivery_method) {
		$('.div_delivery_method_' + order_id + ' a').html(new_delivery_method);
		$('.div_readonly_delivery_method_' + order_id).html(new_delivery_method);
	}
</script>