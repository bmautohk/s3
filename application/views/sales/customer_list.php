<? $hasRight = GlobalFunction::hasPrivilege('sales_customer', Model_RoleMatrix::PERMISSION_WRITE); ?>

<table border="1">
	<tr>
		<td>SalesCode</td>
		<td>公司名字</td>
		<td>郵政編號</td>
		<td>地址1</td>
		<td>地址2</td>
		<td>地址3</td>
		<td>送貨住所</td>
		<td>TEL</td>
		<td>Email</td>
		<td>代號cust code</td>
		<td>Ben1 Sales Group</td>
		<td>社長名字</td>
		<td>聯絡人名字</td>
		<td>備考</td>
		<td>Bank Acc (請求書)</td>
		<td>公司地址</td>
		<td>最近訂單日期</td>
		<td>最後更新資料日期</td>
		<? if ($hasRight) { ?>
		<td>Edit</td>
		<td>Delete</td>
		<? } ?>
	</tr>
	<? foreach ($form->customers as $customer) { ?>
	<tr>
		<td><?=$customer->created_by ?></td>
		<td><?=$customer->name ?></td>
		<td><?=$customer->postal_code ?></td>
		<td><?=$customer->address1 ?></td>
		<td><?=$customer->address2 ?></td>
		<td><?=$customer->address3 ?></td>
		<td><?=$customer->delivery_address ?></td>
		<td><?=$customer->tel ?></td>
		<td><?=$customer->email ?></td>
		<td><?=$customer->cust_code ?></td>
		<td><?=$customer->getS1SalesGroup() ?></td>
		<td><?=$customer->manager_name ?></td>
		<td><?=$customer->contact_person ?></td>
		<td><?=$customer->remark ?></td>
		<td><?=$customer->bank_account ?></td>
		<td><?=$customer->office_address_name ?></td>
		<td><?=$customer->last_order_date ?></td>
		<td><?=$customer->last_update_date ?></td>
		<? if ($hasRight) { ?>
		<td><? echo HTML::anchor('/sales/customer_edit/'.$customer->id, '更改資料'); ?></td>
		<td><a href="javascript:goDelete('<?=$customer->id ?>')">Delete</a></td>
		<? } ?>
	</tr>
	<? }?>
</table>

<? echo Form::open("sales/customer_delete", array('id'=>'delete_form'));
echo Form::hidden('customer_id', NULL, array('id'=>'customer_id'));
echo Form::close(); ?>

<script type="text/javascript">
function goDelete(customer_id) {
	if (confirm('Are your sure to delete the customer?')) {
		$('#customer_id').val(customer_id);
		$('#delete_form').submit();
	}
}
</script>