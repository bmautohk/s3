<?
$gifts = $form->gifts;
$url = 'factory/gift_edit/factory/'.$form->factory.'/'; 
?>

<? echo HTML::anchor('factory/gift_add/factory/'.$form->factory, '<input type="button" value="Add" />'); ?>
<div style="width:1100px">
<? echo $form->pager(); ?>
</div>
<table border="1">
	<tr>
		<td></td>
		<td>Cust Code</td>
		<td>交貨日期</td>
		<td>入櫃日期</td>
		<td>櫃號</td>
		<td>運送貨量</td>
		<td>Brand name(pm.車種)</td>
		<td>Car Name(車型) </td>
		<td>Model Name(型號)</td>
		<td>color</td>
		<td><? echo __('label.colour_no'); ?></td>
		<td>件數</td>
		<td>貨品編號</td>
		<td>貨品名稱</td>
		<td>material</td>
		<td>Picture Reference 1</td>
		<td>Picture Reference 2</td>
		<td>Picture Reference 3</td>
		<td>cost</td>
	</tr>
	<? foreach ($gifts as $gift) { ?>
	<tr>
		<td><input type="button" value="View" onclick="location.href='<?=URL::site($url.$gift->id) ?>'" /></td>
		<td><?=$gift->cust_code ?></td>
		<td><?=$gift->delivery_date ?></td>
		<td><?=$gift->container_input_date ?></td>
		<td><?=$gift->container_no ?></td>
		<td><?=$gift->delivery_qty ?></td>
		<td><?=$gift->made ?></td>
		<td><?=$gift->model ?></td>
		<td><?=$gift->model_no ?></td>
		<td><?=$gift->colour ?></td>
		<td><?=$gift->colour_no ?></td>
		<td><?=$gift->qty ?></td>
		<td><?=$gift->product_cd ?></td>
		<td><?=$gift->product_desc ?></td>
		<td><?=$gift->material ?></td>
		<td><? echo GlobalFunction::giftPictureAnchor($gift->id, $gift->picture1); ?></td>
		<td><? echo GlobalFunction::giftPictureAnchor($gift->id, $gift->picture2); ?></td>
		<td><? echo GlobalFunction::giftPictureAnchor($gift->id, $gift->picture3); ?></td>
		<td><?=$gift->cost ?></td>
	</tr>
	<? } ?>
</table>
