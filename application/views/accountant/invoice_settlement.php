<? echo Form::open("accountant/invoice_settlement", array('id'=>'form1'));
echo Form::hidden('action', 'search'); ?>
<label>Cust Code:</label><? echo Form::select('search_customer_id', Model_Customer::getOptions(), $form->search_customer_id); ?>
<input type="submit" value="Search" />
<? echo Form::close(); ?>

<? if (isset($form->invoices)) {
	$invoices = $form->invoices;
	$invoiceSettle = $form->invoiceSettle;
?>
	<table border="1">
		<tr>
			<td>請求書編號</td>
			<td>發單日期</td>
			<td>cust code</td>
			<td>上月金額</td>
			<td>已付金額</td>
			<td>上月余額</td>
			<td>本月金額</td>
			<td>消費稅</td>
			<td>今回請求金額</td>
			<td>今回已付金額</td>
			<td>殘金</td>
			<td>BANK</td>
			<td>相關納品書</td>
		</tr>
		<? foreach ($invoices as $invoice) { ?>
		<tr>
			<td><?=$invoice->invoice_no ?></td>
			<td><?=date("Y-m-d", strtotime($invoice->create_date)) ?></td>
			<td><?=$invoice->customer->cust_code ?></td>
			<td><?=GlobalFunction::displayJPYNumber($invoice->last_month_amt) ?></td>
			<td><?=GlobalFunction::displayJPYNumber($invoice->last_month_settle) ?></td>
			<td><?=GlobalFunction::displayJPYNumber($invoice->last_month_amt - $invoice->last_month_settle) ?></td>
			<td><?=GlobalFunction::displayJPYNumber($invoice->current_month_amt) ?></td>
			<td><?=GlobalFunction::displayJPYNumber($invoice->total_tax) ?></td>
			<td><?=GlobalFunction::displayJPYNumber($invoice->total_amt) ?></td>
			<td><?=GlobalFunction::displayJPYNumber($invoice->settle_amt) ?></td>
			<td><?=GlobalFunction::displayJPYNumber($invoice->total_amt - $invoice->settle_amt) ?></td>
			<td><?=$invoice->bank_name ?></td>
			<td><?=$invoice->delivery_note_id_list ?></td>
		</tr>
		<? } ?>
	</table>
	
	<? echo Form::open("accountant/invoice_settlement", array('id'=>'form1')); ?>
	<? echo Form::hidden('action', 'save'); ?>
	<? echo Form::hidden('customer_id', $form->customer_id); ?>
	<table>
		<tr>
			<td>入金:</td>
			<td>￥<? echo Form::input('settle_amt', $invoiceSettle->settle_amt); ?></td>
		</tr>
		<tr>
			<td>送金手數費:</td>
			<td>￥<? echo Form::input('fee', $invoiceSettle->fee); ?></td>
		</tr>
		<tr>
			<td>入金日期:</td>
			<td><? echo Form::input('settle_date', $invoiceSettle->settle_date, array('id'=>'settle_date')); ?></td>
		</tr>
		<tr>
			<td>Remark:</td>
			<td><? echo Form::textarea('remark', $invoiceSettle->remark, array('rows'=>'3')); ?></td>
		</tr>
		<tr>
			<td>銀行名字 :</td>
			<td><? echo Form::select('bank_id', Model_BankAccount::getOptions(), $invoiceSettle->bank_id); ?></td>
		</tr>
		<tr>
			<td>輸入日期 :</td>
			<td><?=date('Y-m-d') ?></td>
		</tr>
	</table>
	
	<input type="submit" value="入" />
	<? echo Form::close(); ?>

	<hr />
	<div>invoice入金紀錄</div>
	<table border="1">
		<tr>
			<td>入金</td>
			<td>送金手數費</td>
			<td>入金日期</td>
			<td>Remark</td>
			<td>銀行名字 </td>
			<td>分店</td>
			<td>帳戶擁有人</td>
			<td>輸入日期 </td>
		</tr>
		<? foreach ($form->invoiceSettleHistory as $history) { ?>
		<tr>
			<td><?=GlobalFunction::displayJPYNumber($history->settle_amt) ?></td>
			<td><?=GlobalFunction::displayJPYNumber($history->fee) ?></td>
			<td><?=$history->settle_date ?></td>
			<td><?=$history->remark ?></td>
			<td><?=$history->bank_name ?></td>
			<td><?=$history->branch ?></td>
			<td><?=$history->owner ?></td>
			<td><?=date("Y-m-d", strtotime($history->create_date)) ?></td>
		</tr>
		<? } ?>
	</table>
<? } ?>

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