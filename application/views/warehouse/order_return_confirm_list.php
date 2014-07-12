<? 
echo Form::open("warehouse/order_return_confirm", array('id'=>'form1', 'method'=>'get'));
echo Form::hidden('action', 'search', array('id'=>'action'));
echo Form::hidden('order_return_id', '', array('id'=>'order_return_id'));
?>
	<table>
		<tr>
			<td>Cust Code:</td>
			<td><? echo Form::select("customer_id", Model_Customer::getOptions(true), $form->customer_id); ?></td>
		</tr>
		<tr>
			<td>返品日期:</td>
			<td>
				<? echo Form::input('return_date_from', $form->return_date_from, array('id'=>'return_date_from')); ?>
				-
				<? echo Form::input('return_date_to', $form->return_date_to, array('id'=>'return_date_to')); ?>
			</td>
		</tr>
	</table>
	
	<input type="button" onclick="search()" value="<? echo __('button.search'); ?>" />
<? echo Form::close(); ?>

<div style="width:800px">
	<? echo $form->pager(); ?>
	<table border="1">
		<tr>
			<td><? echo __('label.cust_code'); ?></td>
			<td><? echo __('label.order_return.return_date'); ?></td>
			<td><? echo __('label.product_cd'); ?></td>
			<td><? echo __('label.order_return.return_qty'); ?></td>
			<td><? echo __('label.order_return.return_pay_rmb'); ?> </td>
			<td><? echo __('label.order_return.return_pay_jpy'); ?> </td>
			<td><? echo __('label.order_return.remark'); ?></td>
			<td>Status</td>
			<td style="width:50px">確認</td>
			<td>Cancel</td>
		</tr>
		<? foreach ($form->orderReturns as $history) { ?>
		<tr>
			<td><?=$history->cust_code ?></td>
			<td><?=$history->return_date ?></td>
			<td><?=$history->product_cd ?></td>
			<td><?=$history->return_qty ?></td>
			<td><?=$history->return_pay ?></td>
			<td><?=$history->getReturnPayJPY() ?></td>
			<td><?=$history->remark ?></td>
			<td><?=$history->getStatus() ?></td>
			<td>
				<? if (!$history->isConfirm()) { ?>
					<input type="button" value="確認" onclick="confirm(<?=$history->id ?>)" />
				<? } ?>
			</td>
			<td>
				<? if (!$history->isConfirm()) { ?>
					<input type="button" value="Cancel" onclick="cancel(<?=$history->id ?>)" />
				<? } ?>
			</td>
		</tr>
		<? } ?>
	</table>
</div>

<script type="text/javascript">
$(function() {
	$( "#return_date_from" ).datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: "both",
		buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
		buttonImageOnly: true
	});
	
	$( "#return_date_to" ).datepicker({
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
	$('#order_return_id').val(id);
	$('#form1').attr('method', 'post');
	$('#form1').submit();
}

function cancel(id) {
	$('#action').val('cancel');
	$('#order_return_id').val(id);
	$('#form1').attr('method', 'post');
	$('#form1').submit();
}
</script>