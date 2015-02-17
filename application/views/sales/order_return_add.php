<div id="order_return_form">
	<? echo Form::open("sales/order_return", array('id'=>'form1')); ?>
		<? echo Form::hidden('action', 'add'); ?>

		<table>
			<tr>
				<td colspan="2">RMB<->JPY <?=$form->rmb_to_jpy_rate ?></td>
			</tr>
			<tr>
				<td><? echo __('label.cust_code'); ?></td>
				<td><? echo Form::select('customer_id', Model_Customer::getOptions(), $form->customer_id); ?></td>
			</tr>
			<tr>
				<td>商品番号:</td>
				<td><? echo Form::input('product_cd', $form->orderReturn->product_cd); ?></td>
			</tr>
			<tr>
				<td>返品数量:</td>
				<td><? echo Form::input('return_qty', $form->orderReturn->return_qty); ?></td>
			</tr>
			<tr>
				<td>返品金額 (<span style="font-weight: bold; color:red">税込単価　RMB</span>):</td>
				<td><? echo Form::input('return_pay', $form->orderReturn->return_pay, array('id'=>'txt_return_qty')); ?></td>
			</tr>
			<tr>
				<td>返品金額 (<span style="font-weight: bold; color:red">税込単価　￥</span>):</td>
				<td><div id="div_return_pay"><? echo isset($form->orderReturn->return_pay) ? round($form->orderReturn->return_pay * $form->rmb_to_jpy_rate) : ""; ?></div></td>
			</tr>
			<tr>
				<td>備註:</td>
				<td><? echo Form::textarea('remark', $form->orderReturn->remark, array('rows'=>'3')); ?></td>
			</tr>
		</table>
	
		<input type="submit" value="返品 (退貨)" id="btn_confirm" />

	<? echo Form::close(); ?>
</div>

<script type="text/javascript">
	$(function() {
		$('#txt_return_qty').change(function() {
			var jpy = Math.round($(this).val() * <?=$form->rmb_to_jpy_rate ?>);
			$('#div_return_pay').html(jpy);
		});

		$('#btn_confirm').click(function() {
			return confirm("Are you sure to save?");
		});
	});

	
</script>