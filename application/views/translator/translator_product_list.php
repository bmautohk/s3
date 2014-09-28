<? echo Form::open("translator/list/factory/".$form->factory); ?>
	<? echo Form::hidden('search_action', 'search'); ?>
	<table>
		<tr>
			<td>By Order No.:</td>
			<td><? echo Form::input('search_order_id', $form->search_order_id); ?></td>
		</tr>
		<tr>
			<td>Search by date:</td>
			<td>
				<? echo Form::input('search_order_date_from', $form->search_order_date_from, array('id'=>'search_order_date_from')); ?>
				-
				<? echo Form::input('search_order_date_to', $form->search_order_date_to, array('id'=>'search_order_date_to')); ?>
			</td>
		</tr>
		
		<tr>
			<td>Search by all column:</td>
			<td><? echo Form::input('search_keyword', $form->search_keyword, array('disabled'=>'disabled')); ?></td>
		</tr>
		
		<tr>
			<td>Part No.(品番):</td>
			<td><? echo Form::input('search_product_cd', $form->search_product_cd); ?></td>
		</tr>
		
		<tr>
			<td>Product Name(per items):</td>
			<td><? echo Form::input('search_product_desc', $form->search_product_desc); ?></td>
		</tr>
		
		<tr>
			<td>Brand name(pm.車種):</td>
			<td><? echo Form::input('search_made', $form->search_made); ?></td>
		</tr>
		
		<tr>
			<td>Car Name(車型):</td>
			<td><? echo Form::input('search_model', $form->search_model); ?></td>
		</tr>
		
		<tr>
			<td>Model Name(型號):</td>
			<td><? echo Form::input('search_model_no', $form->search_model_no); ?></td>
		</tr>
		
		<tr>
			<td>大步分貨量:</td>
			<td><? echo Form::input('search_factory_qty', $form->search_factory_qty); ?></td>
		</tr>

		<tr>
			<td>未注文 :</td>
			<td>
				<?echo Form::select('search_is_complete', array(''=>'All', 'N'=>'未注文', 'Y'=>'注文'), $form->search_is_complete) ?>
			</td>
		</tr>
		
		<tr>
			<td>退單 :</td>
			<td>
				<?echo Form::select('search_is_reject', array(''=>'All', 'Y'=>'Yes', 'N'=>'No', ), $form->search_is_reject) ?>
			</td>
		</tr>
	</table>
	<input type="submit" value="Search" />
	<input type="button" onclick="goToExport(this)" value="Export" />
<? echo Form::close(); ?>

<? if (isset($form->orderProducts)) {
	echo Form::open("translator/save/factory/".$form->factory, array('id'=>'form1'));
	
	echo Form::hidden('action', '', array('id'=>'action'));
	echo Form::hidden('search_order_date_from', $form->search_order_date_from);
	echo Form::hidden('search_order_date_to', $form->search_order_date_to);
	echo Form::hidden('search_keyword', $form->search_keyword);
	echo Form::hidden('search_is_complete', $form->search_is_complete);
	?>
	
	<? echo $form->pager(); ?>
	<table border="1">
		<tr>
			<td><input type="checkbox" id="selectall" /></td>
			<td><? echo __('label.order_no'); ?></td>
			<td style="width: 74px"><? echo __('label.order_date'); ?></td>
			<td>退單</td>
			<td>Cust Code</td>
			<td>Part No.:(品番)</td>
			<td>qty</td>
			<td>marketprice</td>
			<td>參考價格</td>
			<td>cost海渡價</td>
			<td>product name(per items)</td>
			<td>Brand name(pm.車種)</td>
			<td>Car Name(車型)</td>
			<td>Model Name(型號)</td>
			<td><? echo __('label.accessory_remark'); ?></td>
			<td><? echo __('label.year'); ?></td>
			<td>color</td>
			<td><? echo __('label.colour_no'); ?></td>
			<td>pieces(per items)</td>
			<td>material(per items)</td>
			<td>subtotal</td>
			<td>deposit amt</td>
			<td>profit</td>
			<td>tax included稅</td>
			<td>delivery fee (per item)送料</td>
			<td>container no.</td>
			<td>交貨日期</td>
			<td>入櫃日期</td>
			<td><?=$form->getFactoryDescription() ?></td>
			<td>Sales Remark</td>
			<td>Kaito Staff Remark</td>
			<td>Auditor Remark</td>
			<td>高原 Remark</td>
			<td>Void Remark</td>
			<td>工場退単日期</td>
			<td>pm 設定了的供應商</td>
			<td>発送方法</td>
			<td>picture1</td>
			<td>picture2</td>
			<td>picture3</td>
			<td><? echo __('label.order_type'); ?></td>
		</tr>
		
		<? foreach ($form->orderProducts as $idx=>$orderProduct) {
			$isEnable = $orderProduct->isSaveEnable();
		?>
				<tr <? if ($orderProduct->isRejectedByNextStep()) { echo "class='highlight'"; } ?>>
					<td>
					<?
					if ($isEnable) {
						echo Form::checkbox('orderProducts['.$idx.'][selected]', 1, $orderProduct->selected);
						echo Form::hidden('orderProducts['.$idx.'][id]', $orderProduct->id);
					}
					?>
					</td>
					<td><?=$orderProduct->order_id ?></td>
					<td><?=date('Y-m-d', strtotime($orderProduct->order->order_date)) ?></td>
					<td></td>
					<td><?=$orderProduct->order->customer->cust_code ?></td>
					<td><?=$orderProduct->product_cd ?></td>
					<td><?=$orderProduct->qty ?></td>
					<td><?=$orderProduct->market_price ?></td>
					<td></td>
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
					<td><?=$orderProduct->getSubTotal() ?></td>
					<td></td>
					<td><?=$orderProduct->profit ?></td>
					<td><?=$orderProduct->getTaxDescription() ?></td>
					<td><?=$orderProduct->delivery_fee ?></td>
					<td><?=$orderProduct->containerSummary->container_no_list ?></td>
					<td><?=$orderProduct->containerSummary->delivery_date_list ?></td>
					<td><?=$orderProduct->containerSummary->container_input_date_list ?></td>
					<td><?=$orderProduct->factory_qty ?></td>
					<td><?=$orderProduct->order->remark ?></td>
					<td><?=$orderProduct->kaito_remark ?></td>
					<td><?=$orderProduct->factory_auditor_remark ?></td>
					<td><? echo Form::textarea('orderProducts['.$idx.'][translator_remark]', $orderProduct->translator_remark, array('rows'=>2, 'cols'=>30, !$isEnable? 'disabled' : '')); ?></td>
					<td><?=$orderProduct->factory_remark ?></td>
					<td><?=$orderProduct->factory_to_translator_date ?></td>
					<td><?=$orderProduct->supplier ?></td>
					<td>
						<? if ($isEnable) { ?>
						<div class="div_delivery_method_<?=$orderProduct->order_id ?>">
							<? echo HTML::anchor('#', Model_Order::getDisplayDeliveryMethod($orderProduct->delivery_method_description, $orderProduct->delivery_method), array('onclick'=>'goUpdateDeliveryMethod('.$orderProduct->order_id.')')); ?>
						</div>
						<? } else { ?>
							<div class="div_readonly_delivery_method_<?=$orderProduct->order_id ?>">
							<? echo Model_Order::getDisplayDeliveryMethod($orderProduct->delivery_method_description, $orderProduct->delivery_method); ?>
							</div>
						<? } ?>
					</td>
					<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture1); ?></td>
					<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture2); ?></td>
					<td><? echo GlobalFunction::orderProductPictureAnchor($orderProduct->order_id, $orderProduct->order->picture3); ?></td>
					<td><?=$orderProduct->order_type_description?></td>
				</tr>
			<? } ?>
	</table>
	
	<input type="button" value="去工場" onclick="goToFactory()" />
	
	<input type="button" value="退回 Auditor" onclick="backToAuditor()" />

	<? echo Form::close(); ?>
<? } ?>

<div id="dialog-form" title="更改発送方法">
	
</div>

<script type="text/javascript">
	$(function() {
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

		$('#selectall').click(function() {
			if ($(this).prop('checked')) {
				$('input:checkbox[name$="[selected]"]').prop('checked', true);
			} else {
				$('input:checkbox[name$="[selected]"]').prop('checked', false);
			}
		});

		$('input:checkbox[name$="[selected]"]').click(function() {
			checkAllSelected();
		});

		if ($('input:checkbox[name$="[selected]"]').size() > 0) {
			checkAllSelected();
		}

		$("#dialog-form").dialog({
			autoOpen: false,
			height: 300,
			width: 350,
			modal: true,
			position: {my: "center top", at: "center top"}
		});
	});

	function checkAllSelected() {
		if ($('input:checkbox[name$="[selected]"]:not(:checked)').size() > 0) {
			$('#selectall').prop('checked', false);
		} else {
			$('#selectall').prop('checked', true);
		}
	}

	function goToFactory() {
		$('#action').val('go_to_factory');
		$('#form1').submit();
	}

	function backToAuditor() {
		$('#action').val('back_to_auditor');
		$('#form1').submit();
	}

	function goToExport(elem) {
		var form = $(elem).parent();
		var origAction = form.attr('action');
		form.attr('action', '<?=PATH_BASE ?>translator/export/factory/<?=$form->factory ?>');
		form.submit();

		form.attr('action', origAction);
	}

	function goUpdateDeliveryMethod(order_id) {
		$.post('<?=PATH_BASE ?>sales/update_delivery_method_init',
				{order_id: order_id},
				function(data) {
					$('#dialog-form').html(data);
					$('#dialog-form').dialog('open');
				}
		);
	}

	function refresh_delivery_method(order_id, new_delivery_method) {
		$('.div_delivery_method_' + order_id + ' a').html(new_delivery_method);
		$('.div_readonly_delivery_method_' + order_id).html(new_delivery_method);
	}
</script>