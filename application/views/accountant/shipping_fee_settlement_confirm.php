<? $deliveryNote = $form->shippingDeliveryNote; ?>
<table border="1">
	<tr>
		<td>輸入経費請求書 ID</td>
		<td><? echo __('label.cust_code'); ?></td>
		<td>金額</td>
		<td>請求日期 </td>
		<td>確認日期 </td>
	</tr>
	<tr>
		<td><?=$deliveryNote->id ?></td>
		<td><?=$deliveryNote->cust_code ?></td>
		<td><?=$deliveryNote->total_amt ?></td>
		<td><?=date("Y-m-d", strtotime($deliveryNote->create_date)) ?></td>
		<td><?=$deliveryNote->settle_date != null ? date("Y-m-d", strtotime($deliveryNote->settle_date)) : ''  ?></td>
	</tr>
</table>

<? echo Form::open("accountant/shipping_fee_settlement", array('id'=>'form1'));
echo Form::hidden('action', 'confirm', array('id'=>'action'));
echo Form::hidden('delivery_note_id', $form->delivery_note_id)?>

<table>
	<tr>
		<td>Remarks:</td>
		<td><? echo Form::textarea('remark', $form->remark, array('rows'=>3)); ?></td>
	</tr>
</table>

<input type="submit" value="確認" />

<? echo Form::close(); ?>