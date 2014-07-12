<? echo Form::open("admin/supplier", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'add'); ?>

<table>
	<tr>
		<td>Supplier CD:</td>
		<td><? echo Form::input('supplier_code', $model->supplier_code); ?></td>
	</tr>
	<tr>
		<td>Supplier Name:</td>
		<td><? echo Form::input('supplier_name', $model->supplier_name); ?></td>
	</tr>
</table>
<input type="submit" value="Add" />
<? echo Form::close(); ?>

<table border=2>
	<tr>
		<td>Supplier CD</td>
		<td>Supplier Name</td>
	</tr>
	<? foreach ($suppliers as $supplier) {?>
	<tr>
		<td><?=$supplier->supplier_code ?></td>
		<td><?=$supplier->supplier_name ?></td>
	</tr>
	<? }?>
</table>