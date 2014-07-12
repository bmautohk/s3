<? $model = $form->bank; ?>
<table cellspacing="0" cellpadding="0" width="100%">
	<tbody>
		<tr>
			<td valign="top" bgcolor="#eefafc">
				<? echo Form::open("admin/bank", array('id'=>'form1')); ?>
				<? echo Form::hidden('bank_id', $form->bank_id); ?>
				<table>
					<tr>
						<td>名:</td>
						<td><? echo Form::input('bank_name', $model->bank_name); ?></td>
					</tr>
					<tr>
						<td>分店:</td>
						<td><? echo Form::input('branch', $model->branch); ?></td>
					</tr>
					<tr>
						<td>種類:</td>
						<td><? echo Form::select("txn_type", Model_BankAccount::getTransactionTypeOptions(), $model->txn_type); ?></td>
					</tr>
					<tr>
						<td>Acc no.:</td>
						<td><? echo Form::input('account_no', $model->account_no); ?></td>
					</tr>
					<tr>
						<td>帳戶擁有人:</td>
						<td><? echo Form::input('owner', $model->owner); ?></td>
					</tr>
				</table>
				<? if (!empty($form->bank_id)) { ?>
					<input type="submit" value="Save" />
				<? } else { ?>
					<input type="submit" value="Add" />
				<? } ?>
				<? echo Form::close(); ?>
			</td>
		</tr>
		
		<? if (!empty($form->banks)) { ?>
		<tr>
			<td>
				<table border=2>
					<tr>
						<td>名</td>
						<td>分店</td>
						<td>種類</td>
						<td>Acc no.</td>
						<td>帳戶擁有人</td>
						<td>Edit</td>
					</tr>
					<? foreach ($form->banks as $bankAccount) {?>
					<tr>
						<td><?=$bankAccount->bank_name ?></td>
						<td><?=$bankAccount->branch ?></td>
						<td><?=$bankAccount->txn_type ?></td>
						<td><?=$bankAccount->account_no ?></td>
						<td><?=$bankAccount->owner ?></td>
						<td><? echo HTML::anchor('/admin/bank/'.$bankAccount->id, 'Edit'); ?></td>
					</tr>
					<? } ?>
				</table>
			</td>
		</tr>
		<? } ?>
	</tbody>
</table>
