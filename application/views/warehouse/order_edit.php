<? echo Form::open("warehouse/order_edit_save", array('id'=>'form1')); ?>
<? echo Form::hidden('order_id', $form->order_id); ?>

<table>
	<tr>
		<td>Order No:</td>
		<td><? echo $form->order_id; ?></td>
	</tr>
	<tr>
		<td>発送方法:</td>
		<td><? echo Form::input('delivery_method', $form->delivery_method); ?></td>
	</tr>
</table>

<input type="submit" value="Save" />

<? echo Form::close(); ?>