<?
$customerOptions = Model_Customer::getOptions(false);
$orderTypeOptions = Model_OrderType::getOptions(false); 
?>

<? echo Form::open("", array('id'=>'form1', 'method'=>'POST', 'enctype'=>'multipart/form-data')); ?>
	<table>
		<tr>
			<td>Cust Code</td>
			<td><? echo Form::select("customer_id", $customerOptions); ?></td>
		</tr>
		<tr>
			<td>Order 種類</td>
			<td>
				<? echo Form::select('order_type_id', $orderTypeOptions); ?>
			</td>
		</tr>
		<tr>
			<td>File</td>
			<td>
				<input type="file" name="uplFile" />
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" onclick="onImport()" value="Import" />
			</td>
		</tr>
	</table>
<? echo Form::close(); ?>

<div>You can click <a href="<?= URL::site('download/order_template.xlsx') ?>">here</a> to download template.</div>

<script type="text/javascript">

function onImport() {
	if (!$("input[name='uplFile']").val()) {
		alert('Please select a file.');
		return;
	}

	$.ajax({
	    url: '<?= URL::site('sales/order_import_save') ?>',
	    type: 'POST',
	    cache: false,
	    data: new FormData($('#form1')[0]),
	    processData: false,
	    contentType: false
	}).done(function(res) {
		var json = JSON.parse(res);
		if (json.errors.length == 0) {
			alert('Import success!');
		} else {
			var msg = '';
			json.errors.forEach(function(error) {
				msg += error + '\n';
			});
			alert(msg);
		}
		
	}).fail(function(res) {
		alert(res['responseText']);
	});
}

</script>