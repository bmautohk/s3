<? echo Form::open("warehouse/gift_list", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'search'); ?>
<table cellspacing="0" cellpadding="0">
	<tr>
		<td>櫃號:</td>
		<td><? echo Form::input('container_no', $form->container_no, array('id'=>'container_no')); ?></td>
	</tr>
	<tr>
		<td>貨品編號:</td>
		<td><? echo Form::input('product_cd', $form->product_cd); ?></td>
	</tr>
	<tr>
		<td>貨品名稱:</td>
		<td><? echo Form::input('product_desc', $form->product_desc); ?></td>
	</tr>
	<tr>
		<td>Cust Code:</td>
		<td><? echo Form::select("customer_id", Model_Customer::getOptions(true), $form->customer_id); ?></td>
	</tr>
	<tr>
		<td>交貨日期:</td>
		<td>
			<? echo Form::input('delivery_date_from', $form->delivery_date_from, array('id'=>'delivery_date_from')); ?>
			-
			<? echo Form::input('delivery_date_to', $form->delivery_date_to, array('id'=>'delivery_date_to')); ?>
		</td>
	</tr>
	<tr>
		<td>入櫃日期:</td>
		<td>
			<? echo Form::input('container_input_date_from', $form->container_input_date_from, array('id'=>'container_input_date_from')); ?>
			-
			<? echo Form::input('container_input_date_to', $form->container_input_date_to, array('id'=>'container_input_date_to')); ?>
		</td>
	</tr>
</table>
<input type="submit" value="Search" />
<input type="button" onclick="goToExport(this)" value="Excel" />
<? echo Form::close(); ?>

<? echo $form->pager(); ?>
<table border="1">
	<tr>
		<td>Cust Code</td>
		<td>交貨日期</td>
		<td>入櫃日期</td>
		<td>櫃號</td>
		<td>運送貨量</td>
		<td>Brand name(pm.車種)</td>
		<td>Car Name(車型) </td>
		<td>Model Name(型號)</td>
		<td>Color</td>
		<td><? echo __('label.colour_no'); ?></td>
		<td>件數</td>
		<td>貨品編號</td>
		<td>貨品名稱</td>
		<td>Material</td>
		<td>Picture Reference 1</td>
		<td>Picture Reference 2</td>
		<td>Picture Reference 3</td>
		<td>Cost</td>
	</tr>
	<? foreach ($form->gifts as $gift) { ?>
	<tr>
		<td><?=$gift->cust_code ?></td>
		<td><?=$gift->delivery_date ?></td>
		<td><?=$gift->container_input_date ?></td>
		<td><?=$gift->container_no ?></td>
		<td><?=$gift->delivery_qty ?></td>
		<td><?=$gift->made ?></td>
		<td><?=$gift->model ?></td>
		<td><?=$gift->model_no ?></td>
		<td><?=$gift->colour ?></td>
		<td><?=$gift->colour_no ?></td>
		<td><?=$gift->qty ?></td>
		<td><?=$gift->product_cd ?></td>
		<td><?=$gift->product_desc ?></td>
		<td><?=$gift->material ?></td>
		<td><? echo GlobalFunction::giftPictureAnchor($gift->id, $gift->picture1); ?></td>
		<td><? echo GlobalFunction::giftPictureAnchor($gift->id, $gift->picture2); ?></td>
		<td><? echo GlobalFunction::giftPictureAnchor($gift->id, $gift->picture3); ?></td>
		<td><?=$gift->cost ?></td>
	</tr>
	<? } ?>
</table>

<script type="text/javascript">
	$(function() {
		$('#container_no').autocomplete({
			source: "<?=url::base() == '/' ? '' : url::base() ?>/factory/search_gift_container_no",
			minLength: 2
		});

		$("#delivery_date_from").datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});

		$("#delivery_date_to").datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});

		$("#container_input_date_from").datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});

		$("#container_input_date_to").datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
	});

	function goToExport(elem) {
		var form = $(elem).parent();
		var origAction = form.attr('action');
		form.attr('action', '<?=PATH_BASE ?>warehouse/gift_export');
		form.submit();

		form.attr('action', origAction);
	}
</script>