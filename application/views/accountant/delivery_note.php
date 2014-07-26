<? $isEnableReturn = Kohana::$environment == Kohana::DEVELOPMENT; 
$hasWrite = GlobalFunction::hasPrivilege('accountant_delivery_note', Model_RoleMatrix::PERMISSION_WRITE);
?>

<? echo Form::open("accountant/delivery_note", array('id'=>'form1')); ?>
<? echo Form::hidden('action', 'scan', array('id'=>'action'));
echo Form::hidden('delivery_note_id', '', array('id'=>'delivery_note_id'));
 ?>
<table>
	<tr>
		<td>Cust Code:</td>
		<td><? echo Form::select('customer_id', Model_Customer::getOptions(true), $form->customer_id, array('onchange'=>'customerChange()')); ?></td>
	</tr>
	<? if ($form->customer_id != 0) { ?>
		<tr>
			<td>打印日期:</td>
			<td><? echo Form::input('print_date', $form->print_date, array('id'=>'print_date')); ?></td>
		</tr>
		<tr>
			<td>発送方法:</td>
			<td>
				<? echo Form::select('delivery_method_id', Model_DeliveryMethod::getOptions(), $form->delivery_method_id); ?>
				<? echo Form::input('delivery_method', $form->delivery_method); ?>
			</td>
		</tr>
		<tr>
			<td>送貨地址:</td>
			<td><? echo Form::select('order_id_for_delivery_address', $form->delivery_address_options, $form->order_id_for_delivery_address, array('id'=>'stl_order_id_for_delivery_address')); ?></td>
		</tr>
		<tr style="height:20px">
			<td>受取人:</td>
			<td><div id="div_s1_client_name"></div></td>
		</tr>
		<tr style="height:20px">
			<td>Tel:</td>
			<td><div id="div_tel"></div></td>
		</tr>
		<tr style="height:20px">
			<td>郵政編號:</td>
			<td><div id="div_postal_code"></div></td>
		</tr>
		<tr>
			<td>S1 Remark:</td>
			<td><? echo Form::textarea('s1_remark', $form->s1_remark, array('rows'=>3)); ?></td>
		</tr>
	<? } ?>
</table>
<input type="submit" value="Scan" <?=$form->customer_id == 0 || !$hasWrite ? 'disabled="disabled"' : '' ?> />
<input type="button" value="Return" onclick="javascript:backToPrevStep()" <?=!$hasWrite ? 'disabled="disabled"' : '' ?> />

<br />

Pending for creating 納品書:<br>
<table border="1">
	<tr>
		<td><input type="checkbox" id="selectall" /></td>
		<td>Cust Code</td>
		<td>Order No.</td>
		<td>品番/詳細</td>
		<td>数量</td>
		<td>單價 (RMB)</td>
		<td>金額 (RMB)</td>
		<td>櫃號</td>
		<td>受取人</td>
		<td>Tel</td>
		<td>郵政編號</td>
		<td>発送方法</td>
		<td>送貨地址</td>
		<td>納品書remark</td>
	</tr>
	<? foreach ($form->containers as $idx=>$container) { ?>
	<tr>
		<td><? echo Form::checkbox('container_id[]', $container->id); ?></td>
		<td><?=$container->cust_code ?></td>
		<td><?=$container->order_id ?></td>
		<td><?=$container->product_cd ?><br /><?=$container->product_desc ?></td>
		<td><?=$container->delivery_qty ?></td>
		<td><?=$container->market_price ?></td>
		<td><?=$container->delivery_qty * $container->market_price ?></td>
		<td><?=$container->container_no ?></td>
		<td><?=$container->s1_client_name ?></td>
		<td><?=$container->tel ?></td>
		<td><?=$container->postal_code ?></td>
		<td><? echo Model_Order::getDisplayDeliveryMethod($container->delivery_method_description, $container->delivery_method); ?></td>
		<td>
			<? 
			if ($container->delivery_address1 != '') {
				echo $container->delivery_address1.'<br>';
			}
			
			if ($container->delivery_address2 != '') {
				echo $container->delivery_address2.'<br>';
			}
			
			if ($container->delivery_address3 != '') {
				echo $container->delivery_address3.'<br>';
			}
			?>
		</td>
		<td>
			<? echo Form::textarea('delivery_note_remarks['.$container->id.']', isset($form->delivery_note_remarks[$container->id]) ? $form->delivery_note_remarks[$container->id] : '', array('rows'=>3)); ?>
		</td>
	</tr>
	<? } ?>
	
	<? foreach ($form->pendingOrderReturns as $orderReturn) { ?>
	<tr>
		<td><? echo Form::checkbox('order_return_id[]', $orderReturn->id)?></td>
		<td><?=$orderReturn->cust_code ?></td>
		<td></td>
		<td><?=$orderReturn->product_cd ?><br /><?=$orderReturn->product_desc ?></td>
		<td><?=$orderReturn->return_qty ?></td>
		<td><?=$orderReturn->return_pay * -1 ?></td>
		<td><?=$orderReturn->return_qty * $orderReturn->return_pay * -1 ?></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td>
			<?=$orderReturn->remark ?>
			<br /><? echo Form::textarea('delivery_note_order_return_remarks['.$orderReturn->id.']', isset($form->delivery_note_order_return_remarks[$orderReturn->id]) ? $form->delivery_note_order_return_remarks[$orderReturn->id] : '', array('rows'=>3)); ?>
		</td>
	</tr>
	<? } ?>
</table>
<? echo Form::close(); ?>

<br>

<div>when accountant click scan all container lv item will group to the delivery note and displayed as below</div>

<? if (isset($form->deliveryNotes)) { ?>
<div style="width:600px">
	<? echo $form->pager(); ?>
	<table border="1">
		<tr>
			<td>出貨單 id</td>
			<td>出貨單 No.</td>
			<td>Cust CD</td>
			<td>Order No.</td>
			<td>品番/詳細</td>
			<td>数量</td>
			<td>單價 (RMB)</td>
			<td>金額 (RMB)</td>
			<td>櫃號</td>
			<td>Last Print Date</td>
			<td>Re-Print</td>
			<? if ($isEnableReturn) { ?>
			<td>Return</td>
			<? } ?>
		</tr>
		<? foreach ($form->deliveryNotes as $deliveryNote) {
			$isFirst = true;
			$noOfDetetail = sizeOf($form->deliveryNoteDetails[$deliveryNote->id]);
			
			if ($noOfDetetail > 0) {
				foreach ($form->deliveryNoteDetails[$deliveryNote->id] as $deliveryNoteDetail) { ?>
				<tr>
					<? if ($isFirst) { ?>
						<td rowspan="<?=$noOfDetetail ?>"><?=$deliveryNote->id ?></td>
						<td rowspan="<?=$noOfDetetail ?>"><?=$deliveryNote->delivery_note_no ?></td>
						<td rowspan="<?=$noOfDetetail ?>"><?=$deliveryNote->cust_code ?></td>
					<? } ?>
					<td><?=$deliveryNoteDetail->order_id ?></td>
					<td><?=$deliveryNoteDetail->product_cd ?><br /><?=$deliveryNoteDetail->product_desc ?></td>
					<td><?=$deliveryNoteDetail->qty ?></td>
					<td><?=$deliveryNoteDetail->market_price ?></td>
					<td><?=$deliveryNoteDetail->total ?></td>
					<td><?=$deliveryNoteDetail->container_no ?></td>
					<? if ($isFirst) { ?>
						<td rowspan="<?=$noOfDetetail ?>"><?=$deliveryNote->last_print_date ?></td>
						<td rowspan="<?=$noOfDetetail ?>"><input type="button" value="<?=$deliveryNote->last_print_date == NULL ? 'Print' : 'Reprint' ?>" onclick="javascript:print(<?=$deliveryNote->id ?>)"/></td>
						
						<? if ($isEnableReturn) { ?>
						<td rowspan="<?=$noOfDetetail ?>">
							<? if ($deliveryNote->invoice_id == NULL ) { ?>
								<input type="button" value="Return" onclick="javascript:returnDeliveryNote(<?=$deliveryNote->id ?>)"/>
							<? } ?>
						</td>
						<? } ?>
					<? $isFirst = false;
					}
				} ?>
				</tr>
			<? } else { ?>
				<tr>
					<td><?=$deliveryNote->id ?></td>
					<td><?=$deliveryNote->delivery_note_no ?></td>
					<td><?=$deliveryNote->cust_code ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><?=$deliveryNote->last_print_date ?></td>
					<td><input type="button" value="<?=$deliveryNote->last_print_date == NULL ? 'Print' : 'Reprint' ?>" onclick="javascript:print(<?=$deliveryNote->id ?>)"/></td>
					
					<? if ($isEnableReturn) { ?>
					<td>
						<? if ($deliveryNote->invoice_id == NULL ) { ?>
							<input type="button" value="Return" onclick="javascript:returnDeliveryNote(<?=$deliveryNote->id ?>)"/>
						<? } ?>
					</td>
					<? } ?>
				</tr>
			<? } ?>
			
		<?} // End of foreach ?>
	</table>
</div>
<? } ?>

<script type="text/javascript">
	$(function() {
		// Select all
		$('#selectall').prop('checked', false);
		$('#form1 input:checkbox').prop('checked', true);

		$('#selectall').click(function() {
			if ($(this).prop('checked')) {
				$('#form1 input:checkbox').prop('checked', true);
			} else {
				$('#form1 input:checkbox').prop('checked', false);
			}
		});

		$('#form1 input:checkbox').click(function() {
			checkAllSelected();
		});

		$("#print_date").datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});

		$('#stl_order_id_for_delivery_address').change(function() {
			var val = $('option:selected', this).val();
			if (val == '') {
				$('#div_s1_client_name').html('');
				$('#div_tel').html('');
				$('#div_postal_code').html('');
			} else {
				$.getJSON('<?=PATH_BASE ?>accountant/order/' + val, function(data) {
					$('#div_s1_client_name').html(data.s1_client_name);
					$('#div_tel').html(data.tel);
					$('#div_postal_code').html(data.postal_code);
				});
			}
		});
	});

	function checkAllSelected() {
		if ($('#form1 input:checkbox[id!="selectall"]:not(:checked)').size() > 0) {
			$('#selectall').prop('checked', false);
		} else {
			$('#selectall').prop('checked', true);
		}
	}
				
	function print(delivery_note_id) {
		window.open("<?=PATH_BASE ?>accountant/delivery_note_print/" + delivery_note_id);
	}

	function returnDeliveryNote(delivery_note_id) {
		$('#delivery_note_id').val(delivery_note_id);
		$('#action').val('return');
		$('#form1').submit();
	}

	function backToPrevStep() {
		$('#action').val('back_to_prev_step');
		$('#form1').submit();
	}
	
	function customerChange() {
		$('#action').val('customer_change');
		$('#form1').submit();
	}
</script>
