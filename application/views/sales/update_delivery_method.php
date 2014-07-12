<div id="div_message"></div>

<? echo Form::open("sales/update_delivery_method", array('id'=>'delivery_method_form'));
	echo Form::hidden('order_id', $form->order_id); 
?>
<table>
	<tr>
		<td>Order No:</td>
		<td><?=$form->order_id ?></td>
	</tr>
	<tr>
		<td>発送方法:</td>
		<td>
			<? echo Form::select('delivery_method_id', Model_DeliveryMethod::getOptions(), $form->delivery_method_id); ?>
			<? echo Form::input('delivery_method', $form->delivery_method); ?>
		</td>
	</tr>
</table>
<input type="button" value="Save" onclick="goSave()" />
<? echo Form::close(); ?>

<script type="text/javascript">
	function goSave() {
		$.post('<?=PATH_BASE ?>sales/update_delivery_method',
				$('#delivery_method_form').serialize(),
				function(data) {
					if (data != '') {
						$('#div_message').html('The record is saved successfully.');
						$('#div_message').attr('class', 'successMsg');

						// Update parent
						refresh_delivery_method(<?=$form->order_id ?>, data);
					} else {
						$('#div_message').html('Fail to save the record.');
						$('#div_message').attr('class', 'errorMsg');
					}
				}
		);
	}
</script>
