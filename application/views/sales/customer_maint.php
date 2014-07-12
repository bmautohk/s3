<? $customer = $form->customer; ?>
<div id="customer_add_form">
	<? echo Form::open("sales/customer_save", array('id'=>'form1')); ?>
		<? echo Form::hidden('customer_id', $form->customer_id); ?>
		
		<table>
			<tr>
				<td>公司名字:</td>
				<td><? echo Form::input('name', $customer->name); ?></td>
			</tr>
			
			<tr>
				<td>郵政編號:</td>
				<td><? echo Form::input('postal_code', $customer->postal_code); ?></td>
			</tr>
			
			<tr>
				<td>地址1:</td>
				<td><? echo Form::input('address1', $customer->address1); ?></td>
			</tr>
			
			<tr>
				<td>地址2:</td>
				<td><? echo Form::input('address2', $customer->address2); ?></td>
			</tr>
			
			<tr>
				<td>地址3:</td>
				<td><? echo Form::input('address3', $customer->address3); ?></td>
			</tr>
			
			<tr>
				<td>送貨住所:</td>
				<td><? echo Form::input('delivery_address', $customer->delivery_address); ?></td>
			</tr>
			
			<tr>
				<td>TEL:</td>
				<td><? echo Form::input('tel', $customer->tel); ?></td>
			</tr>
			
			<tr>
				<td>Email:</td>
				<td><? echo Form::input('email', $customer->email); ?></td>
			</tr>
			
			<tr>
				<td>代號cust code:</td>
				<td><? echo Form::input('cust_code', $customer->cust_code); ?></td>
			</tr>
			
			<tr>
				<td>社長名字:</td>
				<td><? echo Form::input('manager_name', $customer->manager_name); ?></td>
			</tr>
			
			<tr>
				<td>聯絡人名字:</td>
				<td><? echo Form::input('contact_person', $customer->contact_person); ?></td>
			</tr>
			
			<tr>
				<td>備考:</td>
				<td><? echo Form::input('remark', $customer->remark); ?></td>
			</tr>
			
			<tr>
				<td>Bank Acc (請求書):</td>
				<td><? echo Form::input('bank_account', $customer->bank_account, array('style'=>'width:600px')); ?></td>
			</tr>
			
			<tr>
				<td>公司地址 <br>(納品書, 請求書, 輸入經費請求書):</td>
				<td>
					<?
					foreach (Model_OfficeAddress::getOptions(true) as $id=>$label) { 
						echo Form::radio('office_address_id', $id, $customer->office_address_id == $id);
						echo $label;
					} ?>
				</td>
			</tr>
			
			<tr>
				<td>Bank Account (輸入經費請求書):</td>
				<td><? echo Form::select('bank_account_id', Model_BankAccount::getOptions(), $customer->bank_account_id); ?></td>
			</tr>
			
			<? if ($customer->id != 0) {?>
			<tr>
				<td>最近訂單日期:</td>
				<td><?=$customer->last_order_date ?></td>
			</tr>
			
			<tr>
				<td>最後更新資料日期:</td>
				<td><?=$customer->last_update_date ?></td>
			</tr>
			<? } ?>
		</table>

		<input type="submit" value="確定" />
	<? echo Form::close(); ?>
</div>
