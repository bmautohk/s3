<?
$customerOptions = Model_Customer::getOptions(true);
?>

<? echo Form::open("warehouse/ingood", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'search', array('id'=>'action')); ?>
<? echo Form::hidden('container_id', '', array('id'=>'container_id')); ?>
<table cellspacing="0" cellpadding="0">
	<tr>
		<td>櫃號:</td>
		<td><? echo Form::input('container_no', $form->container_no, array('id'=>'container_no')); ?></td>
	</tr>
	<tr>
		<td>Part No 品番: </td>
		<td><? echo Form::input('order_id', $form->order_id); ?></td>
	</tr>
	<tr>
		<td>Order No.:</td>
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
<? echo Form::close(); ?>

<? if (isset($form->containers)) { ?>

<div style="width:1000px">
	<? echo $form->pager(); ?>
	<table border="1">
		<tr> 
			<td>交到入金管理員做納品書</td>
			<td>factory Del lv status</td>
			<td>Order No.</td>
			<td>Cust Code</td>
			<td>Part No.:(品番)</td>
			<td>qty</td>
			<td>今次交貨數量</td>
			<td>product name(per item</td>
			<td>交貨日期</td>
			<td>入櫃日期</td>
			<td>櫃號</td>
			<td>pic1</td>
			<td>pic2</td>
			<td>pic3</td>
		</tr>
		<? foreach ($form->containers as $container) { ?>
		<tr>
			<td>
				<? 
				if ($container->status == Model_Container::STATUS_INIT) { ?>
					<input type="button" value="納品書" onclick="location.href='<?=URL::site('warehouse/ingood_add_delivery_note/'.$container->id) ?>'" />
				<? } ?>
			</td>
			<td></td>
			<td><?=$container->order_id ?></td>
			<td><?=$container->cust_code ?></td>
			<td><?=$container->product_cd ?></td>
			<td><?=$container->source == Model_Container::SOURCE_FACTORY ? $container->factory_qty : $container->jp_qty ?></td>
			<td><?=$container->delivery_qty ?></td>
			<td><?=$container->product_desc ?></td>
			<td><?=$container->delivery_date ?></td>
			<td><?=$container->container_input_date ?></td>
			<td><?=$container->container_no ?></td>
			<td><? echo GlobalFunction::orderProductPictureAnchor($container->order_id, $container->picture1); ?></td>
			<td><? echo GlobalFunction::orderProductPictureAnchor($container->order_id, $container->picture2); ?></td>
			<td><? echo GlobalFunction::orderProductPictureAnchor($container->order_id, $container->picture3); ?></td>
		</tr>
		<? } ?>
	</table>
</div>
<? } ?>

<script>
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
	
	function send_to_accountant(container_id) {
		$('#action').val('send_to_accountant');
		$('#container_id').val(container_id);
		$('#form1').submit();
	}
</script>
