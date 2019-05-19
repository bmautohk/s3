<? 
echo Form::open("accountant/deposit_settlement", array('id'=>'form1', 'method'=>'get'));
echo Form::hidden('action', 'search', array('id'=>'action'));
echo Form::hidden('deposit_settle_id', '', array('id'=>'deposit_settle_id'));
?>
	<table>
		<tr>
			<td><? echo __('label.order_no'); ?>:</td>
			<td>
				<? echo Form::input('order_id', $form->order_id); ?>
				<input type="button" onclick="add()" value="<? echo __('button.add'); ?>" />
			</td>
		</tr>
		<tr>
			<td>Cust Code:</td>
			<td><? echo Form::select("customer_id", Model_Customer::getOptions(true), $form->customer_id); ?></td>
		</tr>
		<tr>
			<td>確認:</td>
			<td><? echo Form::select("is_confirm", array(''=>'All', 'Y'=>'完了', 'N'=>'未完'), $form->is_confirm); ?></td>
		</tr>
		<tr>
			<td>入金日期:</td>
			<td>
				<? echo Form::input('settle_date_from', $form->settle_date_from, array('id'=>'settle_date_from')); ?>
				-
				<? echo Form::input('settle_date_to', $form->settle_date_to, array('id'=>'settle_date_to')); ?>
			</td>
		</tr>
	</table>
	
	<input type="button" onclick="search()" value="<? echo __('button.search'); ?>" />
	<input type="button" onclick="goToExport(this)" value="Excel" />
<? echo Form::close(); ?>

<div style="width:800px">
	<? echo $form->pager(); ?>
	<table border="1">
		<tr>
			<td><? echo __('label.order_no'); ?></td>
			<td><? echo __('label.cust_code'); ?></td>
			<td>入金</td>
			<td>送金手數費</td>
			<td>入金日期</td>
			<td>Remark</td>
			<td>Remark (入金管理)</td>
			<td>輸入日期 </td>
			<td>訂單deposit</td>
			<td>餘下deposit</td>
			<td>確認</td>
		</tr>
		<? foreach ($form->depositSettleHistory as $history) { ?>
		<tr>
			<td><?=$history->order_id ?></td>
			<td><?=$history->cust_code ?></td>
			<td><?=GlobalFunction::displayNumber($history->settle_amt) ?></td>
			<td><?=$history->fee ?></td>
			<td><?=$history->settle_date ?></td>
			<td><?=$history->remark ?></td>
			<td><?=$history->accountant_remark ?></td>
			<td><?=date("Y-m-d", strtotime($history->create_date)) ?></td>
			<td><?=GlobalFunction::displayNumber($history->order->deposit_amt) ?></td>
			<td><?=GlobalFunction::displayNumber($history->order->deposit_amt - $history->order->confirm_deposit_amt) ?></td>
			<td>
				<? if ($history->is_confirm == Model_DepositSettle::CONFIRM_NO) { ?>
					<input type="button" value="確認" onclick="confirm(<?=$history->id ?>)" />
				<? } ?>
			</td>
		</tr>
		<? } ?>
	</table>
</div>

<script type="text/javascript">
$(function() {
	$( "#settle_date_from" ).datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: "both",
		buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
		buttonImageOnly: true
	});
	
	$( "#settle_date_to" ).datepicker({
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
	$('#deposit_settle_id').val(id);
	$('#form1').submit();
}

function goToExport(elem) {
	var form = $(elem).parent();
	var origAction = form.attr('action');
	form.attr('action', '<?=PATH_BASE ?>accountant/deposit_settlement_export');
	form.submit();

	form.attr('action', origAction);
}

function add() {
	$('#action').val('add');
	$('#form1').submit();
}
</script>