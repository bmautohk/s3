<? $hasWrite = GlobalFunction::hasPrivilege('accountant_shipping_fee_delivery_note', Model_RoleMatrix::PERMISSION_WRITE); ?>

<? echo Form::open("accountant/shipping_fee_delivery_note", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'scan', array('id'=>'action')); ?>
<table>
	<tr>
		<td>Cust Code:</td>
		<td><? echo Form::select('customer_id', Model_Customer::getOptions(true), $form->customer_id, array('onchange'=>'customerChange()')); ?></td>
	</tr>
</table>
<input type="submit" value="Scan" <?=$form->customer_id == 0 || !$hasWrite ? 'disabled="disabled"' : '' ?> />
<? echo Form::close(); ?>

Pending for creating 請求書:<br>
<table border="1">
	<tr>
		<td>Cust Code</td>
		<td>品番</td>
		<td>品目</td>
		<td>合計請求金額（税込・円）</td>
		<td>備考</td>
	</tr>
	<? foreach ($form->pendingShippingFees as $shippingFee) { ?>
	<tr>
		<td><?=$shippingFee->cust_code ?></td>
		<td><?=$shippingFee->container_no ?></td>
		<td><?=$shippingFee->description ?></td>
		<td><?=GlobalFunction::displayJPYNumber($shippingFee->amount) ?></td>
		<td><?=$shippingFee->remark ?></td>
	</tr>
	<? } ?>
</table>

<br />

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
