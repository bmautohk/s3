<?
$customerOptions = Model_Customer::getOptions(true);
$orderStatusOptions = array(''=>'All', 'A'=>'未完成', 'C'=>'完成'); 
?>

<table cellspacing="0" cellpadding="0" width="100%">
	<tbody>

		<tr>
			<td valign="top" bgcolor="#eefafc">
				<? echo Form::open("factory/list/factory/".$form->factory, array('id'=>'form1')); ?>
				<label>By Order No.:</label>
				
					<? echo Form::input('search_order_id', $form->search_order_id); ?><br/><br/>
		
					<label>櫃號 :</label><? echo Form::input('search_container_no', $form->search_container_no, array('id'=>'search_container_no')); ?>
					<br /><br />
					
					<label>客戶編號:</label><? echo Form::select("search_customer_id", $customerOptions, $form->search_customer_id); ?>
					<br /><br />
					
					<label>貨品編號:</label><? echo Form::input('search_product_cd', $form->search_product_cd); ?>
					<br /><br />
					
					<label>下單日期:</label>
					<? echo Form::input('search_order_date_from', $form->search_order_date_from, array('id'=>'search_order_date_from')); ?>
					-
					<? echo Form::input('search_order_date_to', $form->search_order_date_to, array('id'=>'search_order_date_to')); ?>
					<br /><br />
					
					<label>高原最新的批核日期:</label>
					<? echo Form::input('search_translator_last_update_date_from', $form->search_translator_last_update_date_from, array('id'=>'search_translator_last_update_date_from')); ?>
					-
					<? echo Form::input('search_translator_last_update_date_to', $form->search_translator_last_update_date_to, array('id'=>'search_translator_last_update_date_to')); ?>
					<br /><br />

					<label>訂單情況(item lv):</label><? echo Form::select("search_status", $orderStatusOptions, $form->search_status); ?>
					<input type="submit" value="找" />
					<input type="button" onclick="goToExport(this)" value="excel" />
				<? echo Form::close(); ?>
			</td>
		</tr>

		<? if (isset($form->orderProducts)) { ?>
		<tr>
			<td>
				<? echo $form->pager(); ?>
				<table border="1">
					<tr>
						<td style="width:48px"></td>
						<td></td>
						<td>Order No.</td>
						<td>訂單情況(item lv)</td>
						<td>高原第一次批核日期</td>
						<td>高原最新的批核日期</td>
						<td>客戶編號</td>
						<td>貨品編號</td>
						<td>進倉數量</td>
						<td>已出貨數量</td>
						<td>kaito staff 分貨qty</td>
						<td>工場佘數</td>
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
						<td><? echo __('label.order_type'); ?></td>
					</tr>
					<? foreach ($form->orderProducts as $orderProduct) { ?>
					<tr>
						<td>
							<? if ($orderProduct->factory_delivery_qty < $orderProduct->factory_qty) { ?>
								<input type="button" value="出貨" onclick="location.href='<?=URL::site('factory/shipping/factory/'.$form->factory.'/'.$orderProduct->id) ?>'" />
							<? } ?>
						</td>
						<td>
							<input type="button" value="進倉数量" onclick="location.href='<?=URL::site('factory/entry/factory/'.$form->factory.'/'.$orderProduct->id) ?>'" />
						</td>
						<td><?=$orderProduct->order_id ?></td>
						<td><?=$orderProduct->factory_status == 99 ? '完成' : '未完成' ?></td>
						<td><?=$orderProduct->translator_first_update_date ?></td>
						<td><?=$orderProduct->translator_last_update_date ?></td>
						<td><?=$orderProduct->cust_code ?></td>
						<td><?=$orderProduct->product_cd ?></td>
						<td><?=$orderProduct->factory_entry_qty ?></td>
						<td><?=$orderProduct->factory_delivery_qty ?></td>
						<td><?=$orderProduct->factory_qty ?></td>
						<td><?=$orderProduct->factory_qty - $orderProduct->factory_delivery_qty ?></td>
						<td><?=$orderProduct->kaito ?></td>
						<td><?=$orderProduct->product_desc ?></td>
						<td><?=$orderProduct->made ?></td>
						<td><?=$orderProduct->model ?></td>
						<td><?=$orderProduct->model_no ?></td>
						<td><?=$orderProduct->accessory_remark ?></td>
						<td><?=$orderProduct->year ?></td>
						<td><?=$orderProduct->colour ?></td>
						<td><?=$orderProduct->colour_no ?></td>
						<td><?=$orderProduct->pcs ?></td>
						<td><?=$orderProduct->material ?></td>
						<td><?=$orderProduct->translator_remark ?></td>
						<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture1); ?></td>
						<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture2); ?></td>
						<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture3); ?></td>
						<td><?=$orderProduct->containerSummary->container_no_list ?></td>
						<td><?=$orderProduct->order_type_description?></td>
					</tr>
					<? } ?>
				</table>
			</td>
		</tr>
		<? } ?>
	</tbody>
</table>


<script type="text/javascript">
	$(function() {
		$('#search_container_no').autocomplete({
			source: "<?=url::base() == '/' ? '' : url::base() ?>/factory/search_container_no",
			minLength: 2
		});

		$( "#search_order_date_from" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});

		$( "#search_order_date_to" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});

		$( "#search_translator_last_update_date_from" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});

		$( "#search_translator_last_update_date_to" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});

	});

	function goToExport(elem) {
		var form = $(elem).parent();
		var origAction = form.attr('action');
		form.attr('action', '<?=PATH_BASE ?>factory/export/factory/<?=$form->factory ?>');
		form.submit();

		form.attr('action', origAction);
	}
</script>