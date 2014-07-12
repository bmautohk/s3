<? 
echo Form::open("accountant/shipping_fee_settlement", array('id'=>'form1', 'method'=>'get'));
echo Form::hidden('action', 'search', array('id'=>'action'));
echo Form::hidden('delivery_note_id', '', array('id'=>'delivery_note_id'));
?>
	<table>
		<tr>
			<td>Cust Code:</td>
			<td><? echo Form::select("customer_id", Model_Customer::getOptions(true), $form->customer_id); ?></td>
		</tr>
		<tr>
			<td>請求日期:</td>
			<td>
				<? echo Form::input('create_date_from', $form->create_date_from, array('id'=>'create_date_from')); ?>
				-
				<? echo Form::input('create_date_to', $form->create_date_to, array('id'=>'create_date_to')); ?>
			</td>
		</tr>
	</table>
	
	<input type="button" onclick="search()" value="<? echo __('button.search'); ?>" />
<? echo Form::close(); ?>

<div style="width:400px; text-aligh:auto">
	<? echo $form->pager(); ?>
	<table border="1">
		<tr>
			<td>輸入経費請求書 ID</td>
			<td><? echo __('label.cust_code'); ?></td>
			<td>金額</td>
			<td>請求日期 </td>
			<td>確認日期 </td>
			<td>確認</td>
		</tr>
		<? foreach ($form->shippingFeeDeliveryNotes as $deliveryNote) { ?>
		<tr>
			<td><?=$deliveryNote->id ?></td>
			<td><?=$deliveryNote->cust_code ?></td>
			<td><?=$deliveryNote->total_amt ?></td>
			<td><?=date("Y-m-d", strtotime($deliveryNote->create_date)) ?></td>
			<td><?=$deliveryNote->settle_date != null ? date("Y-m-d", strtotime($deliveryNote->settle_date)) : ''  ?></td>
			<td>
				<? if ($deliveryNote->is_settle == Model_ShippingFeeDeliveryNote::SETTLE_NO) { ?>
					<input type="button" value="確認" onclick="confirm(<?=$deliveryNote->id ?>)" />
				<? } ?>
			</td>
		</tr>
		<? } ?>
	</table>
</div>

<script type="text/javascript">
$(function() {
	$( "#create_date_from" ).datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: "both",
		buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
		buttonImageOnly: true
	});
	
	$( "#create_date_to" ).datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: "both",
		buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
		buttonImageOnly: true
	});
});

function search() {
	$('#action').val('search');
	$('#form1').submit();
}

function confirm(id) {
	$('#action').val('confirm');
	$('#delivery_note_id').val(id);
	$('#form1').attr('method', 'post');
	$('#form1').submit();
}

</script>