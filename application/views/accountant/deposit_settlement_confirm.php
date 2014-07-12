<? $depositSettle = $form->depositSettle; ?>
<table border="1">
	<tr>
		<td><? echo __('label.order_no'); ?></td>
		<td><? echo __('label.cust_code'); ?></td>
		<td>入金</td>
		<td>送金手數費</td>
		<td>入金日期</td>
		<td>Remark</td>
	</tr>
	<tr>
		<td><?=$depositSettle->order_id ?></td>
		<td><?=$depositSettle->cust_code ?></td>
		<td><?=$depositSettle->settle_amt ?></td>
		<td><?=$depositSettle->fee ?></td>
		<td><?=$depositSettle->settle_date ?></td>
		<td><?=$depositSettle->remark ?></td>
	</tr>
</table>

<? echo Form::open("accountant/deposit_settlement", array('id'=>'form1'));
echo Form::hidden('action', 'confirm', array('id'=>'action'));
echo Form::hidden('deposit_settle_id', $form->deposit_settle_id)?>

<table>
	<tr>
		<td>Remarks:</td>
		<td><? echo Form::textarea('remark', $form->remark, array('rows'=>3)); ?></td>
	</tr>
</table>

<input type="submit" value="確認" />

<? echo Form::close(); ?>