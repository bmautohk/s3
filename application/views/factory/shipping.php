<?
$orderProduct = $form->orderProduct;
$container = $form->container;
$containerHistory = $form->containerHistory;
?>

<table border="1">
	<tr>
		<td>Order No.</td>
		<td>訂單情況(item lv)</td>
		<td>高原第一次批核日期</td>
		<td>高原最新的批核日期</td>
		<td>客戶編號</td>
		<td>貨品編號</td>
		<td>進倉數量</td>
		<td>已出貨數量</td>
		<td>kaito staff 分貨qty</td>
		<td>cost海渡價</td>
		<td>貨品名稱</td>
		<td>Brand name(pm.車種)</td>
		<td>Car Name(車型)</td>
		<td>Model Name(型號)</td>
		<td><? echo __('label.accessory_remark'); ?></td>
		<td><? echo __('label.year'); ?></td>
		<td>color</td>
		<td><? echo __('label.colour_no'); ?></td>
		<td>件數</td>
		<td>材質</td>
		<td>高元remark</td>
		<td>pic1</td>
		<td>pic2</td>
		<td>pic3</td>
		<td>櫃號(multi)</td>
	</tr>
	<tr>
		<td><?=$orderProduct->order_id ?></td>
		<td><?=$orderProduct->factory_status == 99 ? '完成' : '未完成' ?></td>
		<td><?=$orderProduct->translator_first_update_date ?></td>
		<td><?=$orderProduct->translator_last_update_date ?></td>
		<td><?=$orderProduct->cust_code ?></td>
		<td><?=$orderProduct->product_cd ?></td>
		<td><?=$orderProduct->factory_entry_qty ?></td>
		<td><?=$orderProduct->factory_delivery_qty ?></td>
		<td><?=$orderProduct->factory_qty ?></td>
		<td><?=$orderProduct->kaito ?></td>
		<td><?=$orderProduct->productMaster->product_desc ?></td>
		<td><?=$orderProduct->productMaster->made ?></td>
		<td><?=$orderProduct->productMaster->model ?></td>
		<td><?=$orderProduct->productMaster->model_no ?></td>
		<td><?=$orderProduct->productMaster->accessory_remark ?></td>
		<td><?=$orderProduct->productMaster->year ?></td>
		<td><?=$orderProduct->productMaster->colour ?></td>
		<td><?=$orderProduct->productMaster->colour_no ?></td>
		<td><?=$orderProduct->productMaster->pcs ?></td>
		<td><?=$orderProduct->productMaster->material ?></td>
		<td><?=$orderProduct->translator_remark ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture1); ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture2); ?></td>
		<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture3); ?></td>
		<td><?=$orderProduct->containerSummary->container_no_list ?></td>
	</tr>
</table>

<? echo Form::open("factory/shipping_save/factory/".$form->factory, array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'go_to_warehouse', array('id'=>'action')); ?>
<? echo Form::hidden('order_product_id', $form->order_product_id); ?>
<table>
	<tr>
		<td>void/取消</td>
		<td><? echo Form::select('is_accept', $form->getAcceptVoidOptions(), $form->is_accept, array('onchange'=>'statusChange(this)')); ?></td>
	</tr>
	<tr class="tr_accept">
		<td>今次交貨數量</td>
		<td><? echo Form::input('container[delivery_qty]', $form->container->delivery_qty); ?> 餘下量: <?= $orderProduct->factory_qty - $orderProduct->factory_delivery_qty ?> qty</td>
	</tr>
	<tr class="tr_accept">
		<td>予定交貨日期</td>
		<td><? echo Form::input('container[delivery_date]', $form->container->delivery_date, array('id'=>'delivery_date')); ?></td>
	</tr>
	<tr class="tr_accept">
		<td>入櫃日期</td>
		<td><? echo Form::input('container[container_input_date]', $form->container->container_input_date, array('id'=>'container_input_date')); ?></td>
	</tr>
	<tr class="tr_accept">
		<td>櫃號</td>
		<td><? echo Form::input('container[container_no]', $form->container->container_no); ?></td>
	</tr>
	<tr class="tr_void" style="display:none">
		<td>void 備注 </td>
		<td><? echo Form::textarea('factory_remark', $form->factory_remark, array('rows'=>3)); ?></td>
	</tr>
</table>
<div id="div_accept">
	<input type="button" id="btn_draft" value="草稿" />
	<input type="submit" value="去日本倉管員" />
</div>
<div id="div_reject">
	<input type="submit" value="退回高原" />
</div>
<? echo Form::close(); ?>

出貨記綠
<table border="2">
	<tr>
		<td>入櫃日期</td>
		<td>予定交貨日期</td>
		<td>運送貨量</td>
		<td>櫃號</td>
	</tr>
	<? foreach ($containerHistory as $history) {?>
	<tr>
		<td><?=$history->container_input_date ?></td>
		<td><?=$history->delivery_date ?></td>
		<td><?=$history->orig_delivery_qty ?></td>
		<td><?=$history->container_no ?></td>
	</tr>
	<? } ?>
</table>


<script type="text/javascript">
	$(function() {
		statusChange($('select[name="is_accept"]'));
		
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

		$('#btn_draft').click(function() {
			$('#action').val('draft');
			$('#form1').submit();
		});
	});

	function statusChange(elem) {
		if ($(':selected', elem).val() == 1) {
			// Accept
			$('.tr_accept').css('display', '');
			$('.tr_void').css('display', 'none');
			$('#div_accept').css('display', '');
			$('#div_reject').css('display', 'none');
		} else {
			// Reject
			$('.tr_accept').css('display', 'none');
			$('.tr_void').css('display', '');
			$('#div_accept').css('display', 'none');
			$('#div_reject').css('display', '');
		}
	}
</script>