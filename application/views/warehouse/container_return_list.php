<?
$customerOptions = Model_Customer::getOptions(true);
?>

<? echo Form::open("warehouse/container_return_list", array('id'=>'form1')); ?>
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
		<td>櫃號</td>
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
		<td>從工厰/ben的數量</td>
		<td>返品數量</td>
		<td>返品日期</td>
		<td>倉管員Remark</td>
	</tr>
	<? foreach ($form->orderProducts as $orderProduct) { ?>
	<tr>
		<td><?=$orderProduct->container_no ?></td>
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
		<td><?=$orderProduct->orig_delivery_qty ?></td>
		<td><?=$orderProduct->return_qty ?></td>
		<td><?=$orderProduct->return_date ?></td>
		<td><?=$orderProduct->return_remark ?></td>
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
		form.attr('action', '<?=PATH_BASE ?>warehouse/container_return_export');
		form.submit();

		form.attr('action', origAction);
	}
</script>