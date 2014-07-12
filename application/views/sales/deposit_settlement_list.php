<? 
echo Form::open("sales/deposit_settlement", array('id'=>'form1', 'method'=>'get'));
echo Form::hidden('action', 'search', array('id'=>'action'));
?>
	<label><? echo __('label.order_no'); ?>:</label><? echo Form::input('order_id', $form->order_id); ?>
	<input type="button" onclick="search()" value="<? echo __('button.search'); ?>" />
	<input type="button" onclick="add()" value="<? echo __('button.add'); ?>" />
<? echo Form::close(); ?>

<div style="width:600px">
	<? echo $form->pager(); ?>
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
			<td><?=$history->order_id ?></td>
			<td><?=$history->cust_code ?></td>
			<td><?=$history->settle_amt ?></td>
			<td><?=$history->fee ?></td>
			<td><?=$history->settle_date ?></td>
			<td><?=$history->remark ?></td>
			<td><?=date("Y-m-d", strtotime($history->create_date)) ?></td>
		</tr>
		<? } ?>
	</table>
</div>

<script type="text/javascript">
function search() {
	$('#action').val('search');
	$('#form1').submit();
}

function add() {
	$('#action').val('add');
	$('#form1').submit();
}
</script>