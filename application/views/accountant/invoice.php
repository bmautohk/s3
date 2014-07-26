<?
$hasWrite = GlobalFunction::hasPrivilege('accountant_invoice', Model_RoleMatrix::PERMISSION_WRITE);

$customerOptions = Model_Customer::getOptions(); 
?>

<? echo Form::open("accountant/invoice", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'scan', array('id'=>'action')); ?>
<table>
	<tr>
		<td>Cust CD:</td>
		<td><? echo Form::select('customer_id', $customerOptions, $form->customer_id, array('onchange'=>'customerChange()')); ?></td>
	</tr>
	<tr>
		<td>Date From:</td>
		<td>
			<? 	if ($form->isFirstInvoice) {
					echo Form::input('bill_date_from', $form->bill_date_from, array('id'=>'bill_date_from'));
				} else {
					echo Form::input('bill_date_from', $form->bill_date_from, array('disabled'=>'disabled'));
				}
			?>
			
		</td>
	</tr>
	<tr>
		<td>Date to:</td>
		<td><? echo Form::input('bill_date_to', $form->bill_date_to, array('id'=>'bill_date_to')); ?></td>
	</tr>
	<tr>
		<td>Due Date:</td>
		<td><? echo Form::input('due_date', $form->due_date, array('id'=>'due_date')); ?></td>
	</tr>
</table>

<input type="submit" value="scan" <?=!$hasWrite ? 'disabled="disabled"' : '' ?> /><br>
<? echo Form::close(); ?>
				
<div>when accountant click scan all container lv item will group to the invoice and displayed as below</div>
				
<? if (isset($form->invoices)) { ?>
<table border="1">
	<tr>
		<td>Invoice id</td>
		<td>Invoice No</td>
		<td>Cust CD</td>
		<td>本月金額</td>
		<td>已付金額</td>
		<td>Last Print Date</td>
		<td>Re-Print</td>
	</tr>
	<? foreach ($form->invoices as $invoice) { ?>
	<tr>
		<td><?=$invoice->id ?></td>
		<td><?=$invoice->invoice_no ?></td>
		<td><?=$invoice->cust_code ?></td>
		<td><?=GlobalFunction::displayJPYNumber($invoice->total_amt) ?></td>
		<td><?=GlobalFunction::displayJPYNumber($invoice->settle_amt) ?></td>
		<td><?=$invoice->last_print_date ?></td>
		<td><input type="button" value="<?=$invoice->last_print_date == NULL ? 'Print' : 'Reprint' ?>" onclick="javascript:print(<?=$invoice->id ?>)"/></td>
	</tr>
	<? } ?>
</table>
<? } ?>

<script type="text/javascript">
	$(function() {
		$( "#bill_date_from" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
		
		$( "#bill_date_to" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});

		$( "#due_date" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
	});

	function print(invoice_id) {
		window.open("invoice_print/" + invoice_id);
	}

	function customerChange() {
		$('#action').val('customer_change');
		$('#form1').submit();
	}
</script>