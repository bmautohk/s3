<?
	$order = $form->order;
	$depositSettle = $form->depositSettle;
?>
<table border="1">
	<tr>
		<td><? echo __('label.order_no'); ?></td>
		<td><? echo __('label.order_date'); ?></td>
		<td><? echo __('label.cust_code'); ?></td>
		<td>deposit 金額</td>
		<td>殘金</td>
	</tr>
	<tr>
		<td><?=$order->id ?></td>
		<td><?=$order->order_date ?></td>
		<td><?=$order->customer->cust_code ?></td>
		<td><?=$order->deposit_amt ?></td>
		<td><?=$order->deposit_amt - $form->totalInputDeposit ?></td>
	</tr>
</table>
	
<? echo Form::open("sales/deposit_settlement", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'add'); ?>
<? echo Form::hidden('order_id', $form->order_id); ?>
	<table>
		<tr>
			<td>入金:</td>
			<td><? echo Form::input('settle_amt', $depositSettle->settle_amt); ?></td>
		</tr>
		<tr>
			<td>送金手數費:</td>
			<td><? echo Form::input('fee', $depositSettle->fee); ?></td>
		</tr>
		<tr>
			<td>入金日期:</td>
			<td><? echo Form::input('settle_date', $depositSettle->settle_date, array('id'=>'settle_date')); ?></td>
		</tr>
		<tr>
			<td>Remark:</td>
			<td><? echo Form::textarea('remark', $depositSettle->remark, array('rows'=>'3')); ?></td>
		</tr>
		<tr>
			<td>輸入日期 :</td>
			<td><?=date('Y-m-d') ?></td>
		</tr>
	</table>
	
	<input type="submit" value="記入" />
<? echo Form::close(); ?>

	<hr />
	<div>入金紀錄</div>
	<table border="1">
		<tr>
			<td><? echo __('label.order_no'); ?></td>
			<td><? echo __('label.cust_code'); ?></td>
			<td>入金</td>
			<td>送金手數費</td>
			<td>入金日期</td>
			<td>Remark</td>
			<td>輸入日期 </td>
		</tr>
		<? foreach ($form->depositSettleHistory as $history) { ?>
		<tr>
			<td><?=$form->order_id ?></td>
			<td><?=$order->customer->cust_code ?></td>
			<td><?=$history->settle_amt ?></td>
			<td><?=$history->fee ?></td>
			<td><?=$history->settle_date ?></td>
			<td><?=$history->remark ?></td>
			<td><?=date("Y-m-d", strtotime($history->create_date)) ?></td>
		</tr>
		<? } ?>
	</table>

<script type="text/javascript">
	$(function() {
		$( "#settle_date" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
	});
</script>