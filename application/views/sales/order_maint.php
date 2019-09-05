<?
$order = $form->order;
$customerOptions = Model_Customer::getOptions();
$orderTypeOptions = Model_OrderType::getOptions(); 
$isTaxOptions = Model_OrderProduct::getTaxOptions();
$isShippingFeeOptions = Model_OrderProduct::getShippingFeeOptions();
$isTempOrderType = $form->isTempOrderType();

$isSaveEnable = $form->isSaveEnable();
?>

<? if(isset($warnings)) { ?>
	<div class="warningMsg">
	<? foreach($warnings as $warning) {?>
			<div><? echo $warning; ?></div>	
	<? } ?>
	</div>
<? }?>

<?
$displayRate = 'Rate: RMB<->USD '.$order->rmb_to_usd_rate.', RMB<->JPY '.$order->rmb_to_jpy_rate.' (Date: '.$order->order_date.')';
?>
<div id="order_add_form">
	<? echo Form::open("sales/order_save", array('id'=>'form1', 'enctype'=>'multipart/form-data')); ?>
		<? echo Form::hidden('action', '', array('id'=>'action'));
		echo Form::hidden('order_id', $order->id);
		?>
		
		<table>
			<tr>
				<td colspan="2"><? echo $displayRate; ?></td>
			</tr>
			<? if (!empty($order->id)) { ?>
			<tr>
				<td>Order No:</td>
				<td><? echo $order->id; ?></td>
			</tr>
			<? }?>
			<tr>
				<td>Cust Code:</td>
				<td><? echo Form::select('customer_id', $customerOptions, $order->customer_id); ?></td>
			</tr>
			
			<tr>
				<td>Order 種類:</td>
				<td>
					<? if (!empty($order->id)) {
						$orderTYpe = new Model_OrderType($order->order_type_id);
						echo $orderTYpe->description;
					} else {
						echo Form::select('order_type_id', $orderTypeOptions, $order->order_type_id, array('id'=>'order_type_id')); 
					}?>
				</td>
			</tr>
		</table>

		<br>
		
		<? if ($isSaveEnable) {?>
			<input type="button" value="Add Row" onclick="addRow()" />
			<input type="button" value="Calculate Profit" onclick="calculateProfit()" />
			<input type="button" value="Calculate Total" onclick="calculateTotal()"/>
		<? }?>
		
		<table border="1" id="tbl_order_product">
			<tr>
				<td></td>
				<td></td>
				<td>Partno.:</td>
				<td>数量</td>
				<td>売値(RMB)</td>
				<td>tax included稅</td>
				<td>輸入経費</td>
				<td>delivery fee (per item)國內送料 (￥)</td>
				<td>參考價格</td>
				<td>在庫數量(參考)</td>
				<td>海渡價</td>
				<td><? echo __('label.business_price'); ?> (RMB)</td>
				<td>product name</td>
				<td>Brand name(pm.車種)</td>
				<td>Car Name(車型)</td>
				<td>Model Name(型號)</td>
				<td>color</td>
				<td>Color No</td>
				<td>pieces(per items)</td>
				<td>material(per items)</td>
				<td>商品说明</td>
				<td>年式</td>
				<td>subtotal<br>(RMB / ￥ / USD)</td>
				<td>profit <? echo HTML::image('media/images/tooltip.gif', array('id'=>'profit_tip', 'title'=>'')); ?><br>(RMB / ￥)</td>
				<td>供應商</td>
				<td>大步哥Remark</td>
			</tr>

			<?
			$rowNo = 1;
			if (!$isTempOrderType) {
				foreach ($form->orderProducts as $idx=>$orderProduct) {
					$subTotals = $orderProduct->getSubTotalWithDifferentCurrency($order->rmb_to_jpy_rate, $order->rmb_to_usd_rate, $form->taxRate);
					 
					if ($orderProduct->productMaster->loaded()) {
						$product = $orderProduct->productMaster;
					} else {
						$product = $form->tempProductMasters[$idx];
					}
			?>
				<tr>
					<td><a href='#' onclick="deleteRow(this)"><? echo HTML::image('media/images/cross.png', array('width'=>'15')); ?></a></td>
					<td><?=$rowNo++ ?><? echo Form::hidden('orderProducts['.$idx.'][id]', $orderProduct->id)?></td>
					<td><? echo Form::input('orderProducts['.$idx.'][product_cd]', $orderProduct->product_cd, array('onchange'=>'getProduct(this)')); ?></td>
					<td><? echo Form::input('orderProducts['.$idx.'][qty]', $orderProduct->qty, array('size'=>'5','id'=>'orderProducts['.$idx.'][qty]')); ?></td>
					<td><? echo Form::input('orderProducts['.$idx.'][market_price]', $orderProduct->market_price, array('size'=>'5')); ?></td>
					<td><? echo Form::select('orderProducts['.$idx.'][is_tax]', $isTaxOptions, $orderProduct->is_tax); ?></td>
					<td><? echo Form::select('orderProducts['.$idx.'][is_shipping_fee]', $isShippingFeeOptions, $orderProduct->is_shipping_fee); ?></td>
					<td><? echo Form::input('orderProducts['.$idx.'][delivery_fee]', $orderProduct->delivery_fee); ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td><?=$product->other ?></td>
					<td><?=$product->product_desc ?></td>
					<td><?=$product->made ?></td>
					<td><?=$product->model ?></td>
					<td><?=$product->model_no ?></td>
					<td><?=$product->colour ?></td>
					<td><?=$product->colour_no ?></td>
					<td><?=$product->pcs ?></td>
					<td><?=$product->material ?></td>
					<td><?=$product->accessory_remark ?></td>
					<td><?=$product->year ?></td>
					<td><?=GlobalFunction::displayNumber($subTotals[0]).' / '.GlobalFunction::displayJPYNumber($subTotals[1]).' / '. GlobalFunction::displayNumber($subTotals[2]) ?></td>
					<td><?=GlobalFunction::displayNumber($orderProduct->profit) ?> / <?=$orderProduct->getFormatProfit(Model_OrderProduct::CURRENCY_JPY, $order->rmb_to_jpy_rate) ?></td>
					<td><?=$product->supplier ?></td>
					<td><?=$orderProduct->kaito_remark ?></td>
				</tr>
				<? } ?>				
			<? }  else {
				foreach ($form->orderProducts as $idx=>$orderProduct) {
					$subTotals = $orderProduct->getSubTotalWithDifferentCurrency($order->rmb_to_jpy_rate, $order->rmb_to_usd_rate, $form->taxRate);
					$tempProductMaster = $form->tempProductMasters[$idx];
					?>
				<tr>
					<td><a href='#' onclick="deleteRow(this)"><? echo HTML::image('media/images/cross.png', array('width'=>'15')); ?></a></td>
					<td><?=$rowNo++ ?><? echo Form::hidden('orderProducts['.$idx.'][id]', $orderProduct->id)?></td>
					<td><? echo Form::input('orderProducts['.$idx.'][product_cd]', $orderProduct->product_cd); ?></td>
					<td><? echo Form::input('orderProducts['.$idx.'][qty]', $orderProduct->qty, array('size'=>'5')); ?></td>
					<td><? echo Form::input('orderProducts['.$idx.'][market_price]', $orderProduct->market_price, array('size'=>'5')); ?></td>
					<td><? echo Form::select('orderProducts['.$idx.'][is_tax]', $isTaxOptions, $orderProduct->is_tax); ?></td>
					<td><? echo Form::select('orderProducts['.$idx.'][is_shipping_fee]', $isShippingFeeOptions, $orderProduct->is_shipping_fee); ?></td>
					<td><? echo Form::input('orderProducts['.$idx.'][delivery_fee]', $orderProduct->delivery_fee); ?></td>
					<td></td>
					<td></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][kaito]', $tempProductMaster->kaito); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][business_price]', $tempProductMaster->business_price); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][product_desc]', $tempProductMaster->product_desc); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][made]', $tempProductMaster->made); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][model]', $tempProductMaster->model); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][model_no]', $tempProductMaster->model_no); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][colour]', $tempProductMaster->colour); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][colour_no]', $tempProductMaster->colour_no); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][pcs]', $tempProductMaster->pcs); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][material]', $tempProductMaster->material); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][accessory_remark]', $tempProductMaster->accessory_remark); ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][year]', $tempProductMaster->year); ?></td>
					<td><?=GlobalFunction::displayNumber($subTotals[0]).' / '.GlobalFunction::displayJPYNumber($subTotals[1]).' / '. GlobalFunction::displayNumber($subTotals[2]) ?></td>
					<td><?=GlobalFunction::displayNumber($orderProduct->profit) ?> / <?=$orderProduct->getFormatProfit(Model_OrderProduct::CURRENCY_JPY, $order->rmb_to_jpy_rate) ?></td>
					<td><? echo Form::input('tempProductMasters['.$idx.'][supplier]', $tempProductMaster->supplier); ?></td>
					<td><?=$orderProduct->kaito_remark ?></td>
				</tr>
			<? 	}
			} ?>
			
			<? 
			if (isset($form->readOnlyOrderProducts)) {
			foreach ($form->readOnlyOrderProducts as $idx=>$orderProduct) {
				$subTotals = $orderProduct->getSubTotalWithDifferentCurrency($order->rmb_to_jpy_rate, $order->rmb_to_usd_rate, $form->taxRate);
				?>
				<tr>
					<td></td>
					<td><?=$rowNo++ ?></td>
					<td><? echo $orderProduct->product_cd; ?></td>
					<td><? echo $orderProduct->qty; ?></td>
					<td><? echo $orderProduct->market_price; ?></td>
					<td><? echo $orderProduct->getTaxDescription(); ?></td>
					<td><? echo $orderProduct->getShippingFeeDescription(); ?></td>
					<td><? echo $orderProduct->delivery_fee; ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td><?=$orderProduct->productMaster->business_price ?></td>
					<td><?=$orderProduct->productMaster->product_desc ?></td>
					<td><?=$orderProduct->productMaster->made ?></td>
					<td><?=$orderProduct->productMaster->model ?></td>
					<td><?=$orderProduct->productMaster->model_no ?></td>
					<td><?=$orderProduct->productMaster->colour ?></td>
					<td><?=$orderProduct->productMaster->colour_no ?></td>
					<td><?=$orderProduct->productMaster->pcs ?></td>
					<td><?=$orderProduct->productMaster->material ?></td>
					<td><?=$orderProduct->productMaster->accessory_remark ?></td>
					<td><?=$orderProduct->productMaster->year ?></td>
					<td><?=GlobalFunction::displayNumber($subTotals[0]).' / '.GlobalFunction::displayJPYNumber($subTotals[1]).' / '. GlobalFunction::displayNumber($subTotals[2]) ?></td>
					<td><?=GlobalFunction::displayNumber($orderProduct->profit) ?> / <?=$orderProduct->getFormatProfit(Model_OrderProduct::CURRENCY_JPY, $order->rmb_to_jpy_rate) ?></td>
					<td><?=$orderProduct->productMaster->supplier ?></td>
					<td><?=$orderProduct->kaito_remark ?></td>
				</tr>
			<? }}?>
		</table>
		
		<br>
		
		<table width="100%">
			<tr>
				<td width="20%">目標送貨日期:</td>
				<td><? echo Form::input('delivery_date', $order->delivery_date, array('id'=>'delivery_date')); ?></td>
			</tr>
			
			<tr>
				<td>Remark:</td>
				<td><? echo Form::textarea('remark', $order->remark, array('rows'=>3)); ?></td>
			</tr>
			
			<tr>
				<td>Picture Reference 1:</td>
				<td>
					<? echo GlobalFunction::orderProductPictureAnchor($order->id, $order->picture1); ?>
					<input type="file" name="picture1" />
				</td>
			</tr>
			
			<tr>
				<td>Picture Reference 2:</td>
				<td>
					<? echo GlobalFunction::orderProductPictureAnchor($order->id, $order->picture2); ?>
					<input type="file" name="picture2" />
				</td>
			</tr>
			
			<tr>
				<td>Picture Reference 3:</td>
				<td>
					<? echo GlobalFunction::orderProductPictureAnchor($order->id, $order->picture3); ?>
					<input type="file" name="picture3" >
				</td>
			</tr>
			
			<tr>
				<td>受取人:</td>
				<td><? echo Form::input('s1_client_name', $order->s1_client_name); ?></td>
			</tr>
			
			<tr>
				<td>Tel:</td>
				<td><? echo Form::input('tel', $order->tel); ?></td>
			</tr>
			
			<tr>
				<td>郵政編號:</td>
				<td><? echo Form::input('postal_code', $order->postal_code); ?></td>
			</tr>
			
			<tr>
				<td>送貨地址:</td>
				<td>
					<? echo Form::input('delivery_address1', $order->delivery_address1, array('size'=>'150')); ?><br>
					<? echo Form::input('delivery_address2', $order->delivery_address2, array('size'=>'150')); ?><br>
					<? echo Form::input('delivery_address3', $order->delivery_address3, array('size'=>'150')); ?><br>
				</td>
			</tr>
			
			<tr>
				<td>発送方法:</td>
				<td>
					<? echo Form::select('delivery_method_id', Model_DeliveryMethod::getOptions(), $order->delivery_method_id, array('id'=>'delivery_method_id')); ?>
					<? echo Form::input('delivery_method', $order->delivery_method); ?>
				</td>
			</tr>
			
			<tr>
				<td>Deposit Amount 頭金:</td>
				<td>RMB<? echo Form::input('deposit_amt', $order->deposit_amt, array('id'=>'deposit_amt')); ?><span id="div_deposit_amt">(￥<?=ceil($order->deposit_amt * $order->rmb_to_jpy_rate) ?>)</span></td>
			</tr>
				<tr>
				<td>Sub Total 總金:</td>
				<td><div id="subTotalYen">Yen:</div><div id="subTotalRMB">RMB:</div></span></td>
			</tr>
		</table>

		<? if ($isSaveEnable) {
			if (!empty($order->id)) {?>
				<input type="button" value="打印"  onclick="print()" />
				<input type="button" value="貯存"  onclick="saveOnly()" />
				<input type="button" value="儲存及送給kaito staff" onclick="submit_to_kaito_staff()" />
			<? } else { ?>
				<input type="button" value="打印貯存"  onclick="save()" />
			<? } ?>
		<? } else { ?>
			<input type="button" value="打印"  onclick="print()" />
		<? } ?>
	<? echo Form::close(); ?>
</div>

<script type="text/javascript">
	totalRowNo = <?=sizeOf($form->orderProducts) ?>;
	isTempOrderType = <?=$form->isTempOrderType() ? 'true' : 'false' ?>;
	taxRate = <?=$form->taxRate ?>;
	rmb_to_jpy_rate = <?=$order->rmb_to_jpy_rate ?>;
	rmb_to_usd_rate = <?=$order->rmb_to_usd_rate ?>;

	$(function() {
		$("#delivery_date").datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
		
		$('#profit_tip').tooltip({
			content: function() {
				return 'profit = [(売値  + 日本国内運費) * A - kaito cost * B] * 数量 <br />-----------------------------<br />Rate: RMB <-> JPY';
			}
		});

		$('#deposit_amt').change(function() {
			var jpy = Math.ceil($('#deposit_amt').val() * <?=isset($order->rmb_to_jpy_rate) ? $order->rmb_to_jpy_rate : 0  ?>);
			$('#div_deposit_amt').html('(￥' + jpy + ')');
		});

		$('#order_type_id').change(function() {
			if (isTempOrderType) {
				$('#action').val('order_type_change');
				$('#form1').submit();
			} else if ($('option:selected', this).val() == <?=Model_OrderType::ID_TEMP ?>) {
				$('#action').val('order_type_change');
				$('#form1').submit();
			}
		});

		initSubTotal();

		if (!isTempOrderType) {
			initAutocomplete();
		} else {
			initTempProductAutocomplete();
		}
		
		<? if ($form->isPrintQuotation) { ?>
			print();
		<? } ?>
	});
	
	function addRow() {
		var idx = totalRowNo;
		totalRowNo++;

		var rowHtml = '';
		if (!isTempOrderType) {
			rowHtml = '<tr><td><a href="#" onclick="deleteRow(this)"><? echo HTML::image('media/images/cross.png', array('width'=>'15')); ?></a></td>' +
				'<td>' + totalRowNo + '</td>' +
				'<td><input type="text" name="orderProducts[' + idx + '][product_cd]" onchange="getProduct(this)" /></td>' +
				'<td><input type="text" name="orderProducts[' + idx + '][qty]" size="5" /></td>' +
				'<td><input type="text" name="orderProducts[' + idx + '][market_price]" size="5" /></td>' +
				'<td><select name="orderProducts[' + idx + '][is_tax]"><option value="1">拔</option><option value="0">込</option></select></td>' +
				'<td><select name="orderProducts[' + idx + '][is_shipping_fee]"><option value="0">拔</option><option value="1">込</option></select></td>' +
				'<td><input type="text" name="orderProducts[' + idx + '][delivery_fee]" /></td>' +
				'<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>' +
				'<td></td><td></td><td></td></tr>';
		} else {
			rowHtml = '<tr><td><a href="#" onclick="deleteRow(this)"><? echo HTML::image('media/images/cross.png', array('width'=>'15')); ?></a></td>' +
			'<td>' + totalRowNo + '</td>' +
			'<td><input type="text" name="orderProducts[' + idx + '][product_cd]" onchange="getProduct(this)" /></td>' +
			'<td><input type="text" name="orderProducts[' + idx + '][qty]" size="5" /></td>' +
			'<td><input type="text" name="orderProducts[' + idx + '][market_price]" size="5" /></td>' +
			'<td><select name="orderProducts[' + idx + '][is_tax]"><option value="1">拔</option><option value="0">込</option></select></td>' +
			'<td><select name="orderProducts[' + idx + '][is_shipping_fee]"><option value="0">拔</option><option value="1">込</option></select></td>' +
			'<td><input type="text" name="orderProducts[' + idx + '][delivery_fee]" /></td>' +
			'<td></td><td></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][kaito]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][business_price]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][product_desc]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][made]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][model]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][model_no]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][colour]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][colour_no]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][pcs]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][material]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][accessory_remark]" /></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][year]" /></td>' +
			'<td></td><td></td>' +
			'<td><input type="text" name="tempProductMasters[' + idx + '][supplier]" /></td>' +
			'<td></td></tr>';
		}

		$('#tbl_order_product tr:last').after(rowHtml);

		if (!isTempOrderType) {
			initAutocomplete();
		} else {
			initTempProductAutocomplete();
		}
		initSubTotal();
	}

	function deleteRow(rowElem) {
		$(rowElem).parents('tr').remove();
	}

	function initSubTotal() {
		$('input[name^="orderProducts"][name$="][qty]"]').change(function() {
			var qty = $(this).val();
			var market_price = $(this).closest('td').next().children('input:text[name$="[market_price]"]').val();
			var is_tax = $(this).closest('td').next().next().children('select[name$="[is_tax]"]').children(':selected').val();
			var delivery_fee = $(this).closest('td').next().next().next().next().children('input:text[name$="[delivery_fee]"]').val();
			
			updateSubtotal($(this).parent().siblings('td:nth-of-type(23)'), qty, market_price,is_tax,delivery_fee);
		});

		$('input[name^="orderProducts"][name$="][market_price]"]').change(function() {
			var qty = $(this).parent().prev().children('input:text[name$="[qty]"]').val();
			var is_tax = $(this).parent().next().children('select[name$="[is_tax]"]').children(':selected').val();
			var market_price = $(this).val();
			var delivery_fee = $(this).closest('td').next().next().next().children('input:text[name$="[delivery_fee]"]').val();

			updateSubtotal($(this).parent().siblings('td:nth-of-type(23)'), qty, market_price,is_tax,delivery_fee);
		});
		
		$('select[name$="[is_tax]"]').change(function()
		{
		var qty = $(this).parent().prev().prev().children('input:text[name$="[qty]"]').val();
		var is_tax = $(this).children(':selected').val();
		var market_price = $(this).closest('td').prev().children('input:text[name$="[market_price]"]').val();
		var delivery_fee = $(this).closest('td').next().next().children('input:text[name$="[delivery_fee]"]').val();

		updateSubtotal($(this).parent().siblings('td:nth-of-type(23)'), qty, market_price,is_tax,delivery_fee);
		});
		
		$('input[name^="orderProducts"][name$="][delivery_fee]"]').change(function()
		{
		
		var qty = $(this).parent().prev().prev().prev().prev().children('input:text[name$="[qty]"]').val();
		
		var is_tax = $(this).parent().prev().prev().children('select[name$="[is_tax]"]').children(':selected').val();
		
		var market_price = $(this).parent().prev().prev().prev().children('input:text[name$="[market_price]"]').val();
		
		var delivery_fee = $(this).val();
		
		updateSubtotal($(this).parent().siblings('td:nth-of-type(23)'), qty, market_price,is_tax,delivery_fee);
		});
	}

	function updateSubtotal(subtotal_elem, qty, market_price,is_tax,delivery_fee) {
		if (qty != '' && market_price != '') {
			if (is_tax == 0) {
				var subtotal_rmb = (qty * market_price + (Number(delivery_fee) / rmb_to_jpy_rate)).toFixed(2);
				var subtotal_jpy = Math.round(market_price * rmb_to_jpy_rate) * qty + Number(delivery_fee);
				var subtotal_usd = subtotal_rmb * rmb_to_usd_rate;
				subtotal_elem.html(subtotal_rmb + ' / ' + subtotal_jpy + ' / ' + subtotal_usd.toFixed(2));
			} else if (is_tax == 1) {
				var subtotal_rmb = (qty * market_price * (1 + taxRate) + (Number(delivery_fee) / rmb_to_jpy_rate)).toFixed(2);
				var subtotal_jpy = Math.round(Math.round(market_price * rmb_to_jpy_rate) * (1 + taxRate) * qty + Number(delivery_fee));
				var subtotal_usd = subtotal_rmb * rmb_to_usd_rate;
				subtotal_elem.html(subtotal_rmb + ' / ' + subtotal_jpy + ' / ' + subtotal_usd.toFixed(2));
			}
		}
	}

	function initAutocomplete() {
		$('input[name^="orderProducts"][name$="][product_cd]"]').autocomplete({
			source: "<?=PATH_BASE ?>product/search",
			select: function( event, ui ) {
				var elem = $(this).parent().siblings('td:nth-of-type(12)');
				assigProductInfo(elem, ui.item);
			}
		});
	}

	function initTempProductAutocomplete() {
		$('input[name^="orderProducts"][name$="][product_cd]"]').autocomplete({
			source: "<?=PATH_BASE ?>product/searchTempProduct",
			select: function( event, ui ) {
				var elem = $(this).parent().siblings('td:nth-of-type(12)');
				assigProductInfo(elem, ui.item);
			}
		});
	}

	function getProduct(elem) {
		$.ajax({
			dataType: "json",
			url: isTempOrderType ? '<?=PATH_BASE ?>product/search_temp_by_cd' : '<?=PATH_BASE ?>product/search_by_cd',
			data: {term: $(elem).val()},
			context: elem,
			success: function(data) {
				var elem = $(this).parent().siblings('td:nth-of-type(12)');
				assigProductInfo(elem, data);
			}
		});
	}

	function assigProductInfo (elem, data) {
		if (!isTempOrderType) {
			elem.html(data.other);
			elem = elem.next();

			elem.html(data.product_desc);
			elem = elem.next();

			elem.html(data.made);
			elem = elem.next();

			elem.html(data.model);
			elem = elem.next();

			elem.html(data.model_no);
			elem = elem.next();

			elem.html(data.colour);
			elem = elem.next();

			elem.html(data.colour_no);
			elem = elem.next();

			elem.html(data.pcs);
			elem = elem.next();

			elem.html(data.material);
			elem = elem.next();
			
			elem.html(data.accessory_remark);
			elem = elem.next();

			elem.html(data.year);
			elem = elem.next();
			
			elem = elem.next();
			elem = elem.next();

			elem.html(data.supplier);

		} else {
			elem.children("input[name$='[business_price]']").val(data.business_price);
			elem = elem.next();
			
			elem.children("input[name$='[product_desc]']").val(data.product_desc);
			elem = elem.next();

			elem.children("input[name$='[made]']").val(data.made);
			elem = elem.next();

			elem.children("input[name$='[model]']").val(data.model);
			elem = elem.next();

			elem.children("input[name$='[model_no]']").val(data.model_no);
			elem = elem.next();

			elem.children("input[name$='[colour]']").val(data.colour);
			elem = elem.next();

			elem.children("input[name$='[colour_no]']").val(data.colour_no);
			elem = elem.next();

			elem.children("input[name$='[pcs]']").val(data.pcs);
			elem = elem.next();

			elem.children("input[name$='[material]']").val(data.material);
			elem = elem.next();

			elem.children("input[name$='[accessory_remark]']").val(data.accessory_remark);
			elem = elem.next();

			elem.children("input[name$='[year]']").val(data.year);
			elem = elem.next();
			
			elem = elem.next();
			elem = elem.next();

			elem.children("input[name$='[supplier]']").val(data.supplier);
		}
	}

	function save() {
		$('#action').val('save');
		$('#form1').submit();
	}

	function saveOnly() {
		$('#action').val('save_only');
		$('#form1').submit();
	}

	function submit_to_kaito_staff() {
		$('#action').val('submit_to_kaito_staff');
		$('#form1').submit();
	}

	function calculateProfit() {
		$('#action').val('calculate_profit');
		$('#form1').submit();
	}
	
	function calculateTotal() {
	    var idx = totalRowNo;
		var qty_val=0;
		var marketprice_val=0;
		var is_tax_val=0;
		var subtotal=0;
		var jpsubtotal=0;
		for (i = 0; i < idx; i++) {
		qty_val=$('input[name="orderProducts['+i+'][qty]"]').val();
		//alert(qty_val);
		marketprice_val=$('input[name="orderProducts['+i+'][market_price]"]').val();
		//alert(marketprice_val);
		is_tax_val=$('select[name="orderProducts['+i+'][is_tax]"]').children(':selected').val();
		delivery_fee=$('input[name="orderProducts['+i+'][delivery_fee]"]').val();
		
				if (is_tax_val == 0) {
					subtotal = subtotal + ((qty_val * marketprice_val) + (delivery_fee / rmb_to_jpy_rate));
					jpsubtotal = jpsubtotal + ((qty_val*marketprice_val * <?=$order->rmb_to_jpy_rate ?> + Number(delivery_fee)));
				} else {
					subtotal = subtotal + ((qty_val * marketprice_val * (1 + taxRate)) + (delivery_fee / rmb_to_jpy_rate));
					jpsubtotal = jpsubtotal + Math.round(Math.round(marketprice_val * rmb_to_jpy_rate) * qty_val * (1 + taxRate) + Number(delivery_fee));
				}
				
				//jpsubtotal=Math.round(jpsubtotal);
		}
		$('#subTotalYen').text('RMB:' + subtotal.toFixed(2));
		$('#subTotalRMB').text('JPY:' + Math.round(jpsubtotal));
	}


	<? if (!empty($order->id)) {?>
		function print() {
			window.open('<?='http://'.$_SERVER['HTTP_HOST'].URL::site('sales/quotation_print/'.$order->id) ?>');
		}
	<? } ?>
</script>
