<? echo Form::open("accountant/shipping_fee_delivery_note", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'scan', array('id'=>'action')); ?>
<table>
	<tr>
		<td>Cust Code:</td>
		<td><? echo Form::select('customer_id', Model_Customer::getOptions(), $form->customer_id, array('onchange'=>'customerChange()')); ?></td>
	</tr>
</table>
<input type="submit" value="Scan" />
<? echo Form::close(); ?>

<? if (isset($form->shippingFeeDeliveryNotes)) { ?>
<div style="width:600px">
	<? echo $form->pager(); ?>
	<table border="1">
		<tr>
			<td>出貨單 id</td>
			<td>Cust CD</td>
			<td>Last Print Date</td>
			<td>Re-Print</td>
		</tr>
		<? foreach ($form->shippingFeeDeliveryNotes as $deliveryNote) { ?>
			<tr>
				<td><?=$deliveryNote->id ?></td>
				<td><?=$deliveryNote->cust_code ?></td>
				<td><?=$deliveryNote->last_print_date ?></td>
				<td><input type="button" value="<?=$deliveryNote->last_print_date == NULL ? 'Print' : 'Reprint' ?>" onclick="javascript:print(<?=$deliveryNote->id ?>)"/></td>
			</tr>
		<?} // End of foreach ?>
	</table>
</div>
<? } ?>

<script type="text/javascript">
	function print(delivery_note_id) {
		window.open("<?=PATH_BASE ?>accountant/shipping_fee_delivery_note_print/" + delivery_note_id);
	}

	function customerChange() {
		$('#action').val('customer_change');
		$('#form1').submit();
	}
</script>
