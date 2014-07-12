<? $gift = $form->gift; ?>

<? if(isset($warnings)) { ?>
	<div class="warningMsg">
	<? foreach($warnings as $warning) {?>
			<div><? echo $warning; ?></div>	
	<? } ?>
	</div>
<? }?>

<? echo Form::open("factory/gift_save/factory/".$form->factory, array('id'=>'form1', 'enctype'=>'multipart/form-data')); ?>
<? echo Form::hidden('gift_id', $gift->id); ?>
<table>
	<tr>
		<td>Cust Code</td>
		<td><? echo Form::select("customer_id", Model_Customer::getOptions(), $gift->customer_id); ?></td>
	</tr>
	<tr>
		<td>交貨日期</td>
		<td><? echo Form::input('delivery_date', $gift->delivery_date, array('id'=>'delivery_date')); ?></td>
	</tr>
	<tr>
		<td>入櫃日期</td>
		<td><? echo Form::input('container_input_date', $gift->container_input_date, array('id'=>'container_input_date')); ?></td>
	</tr>
	<tr>
		<td>櫃號</td>
		<td><? echo Form::input('container_no', $gift->container_no); ?></td>
	</tr>
	<tr>
		<td>運送貨量</td>
		<td><? echo Form::input('delivery_qty', $gift->delivery_qty); ?></td>
	</tr>
	<tr>
		<td>Brand name(pm.車種)</td>
		<td><? echo Form::input('made', $gift->made); ?></td>
	</tr>
	<tr>
		<td>Car Name(車型)</td>
		<td><? echo Form::input('model', $gift->model); ?></td>
	</tr>
	<tr>
		<td>Model Name(型號)</td>
		<td><? echo Form::input('model_no', $gift->model_no); ?></td>
	</tr>
	<tr>
		<td>color</td>
		<td><? echo Form::input('colour', $gift->colour); ?></td>
	</tr>
	<tr>
		<td>Color No</td>
		<td><? echo Form::input('colour_no', $gift->colour_no); ?></td>
	</tr>
	<tr>
		<td>件數</td>
		<td><? echo Form::input('qty', $gift->qty); ?></td>
	</tr>
	<tr>
		<td>貨品編號</td>
		<td><? echo Form::input('product_cd', $gift->product_cd); ?></td>
	</tr>
	<tr>
		<td>貨品名稱</td>
		<td><? echo Form::input('product_desc', $gift->product_desc); ?></td>
	</tr>
	<tr>
		<td>material</td>
		<td><? echo Form::input('material', $gift->material); ?></td>
	</tr>
	<tr>
		<td>Picture Reference 1</td>
		<td>
			<? echo GlobalFunction::giftPictureAnchor($gift->id, $gift->picture1); ?>
			<? if ($gift->id == 0) { ?>
				<input type="file" name="picture1" />
			<? } ?>
		</td>
	</tr>
	<tr>
		<td>Picture Reference 2</td>
		<td>
			<? echo GlobalFunction::giftPictureAnchor($gift->id, $gift->picture2); ?>
			<? if ($gift->id == 0) { ?>
				<input type="file" name="picture2" />
			<? } ?>
		</td>
	</tr>
	<tr>
		<td>Picture Reference 3</td>
		<td>
			<? echo GlobalFunction::giftPictureAnchor($gift->id, $gift->picture3); ?>
			<? if ($gift->id == 0) { ?>
				<input type="file" name="picture3" />
			<? } ?>
		</td>
	</tr>
	<tr>
		<td>Cost</td>
		<td><? echo Form::input('cost', $gift->cost); ?></td>
	</tr>
</table>
<? if ($gift->id == 0) { ?>
	<input type="submit" value="提交" />
<? } ?>
<? echo Form::close(); ?>

<script type="text/javascript">
	$(function() {
		$( "#delivery_date" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
		$( "#container_input_date" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
	});
</script>