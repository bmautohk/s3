<table cellspacing="0" cellpadding="0" width="100%">
	<tbody>
		<tr>
			<td valign="top" bgcolor="#eefafc">
				<? echo Form::open("admin/rate", array('id'=>'form1')); ?>
				<? echo Form::hidden('action', 'save'); ?>
				
				<label>Rate from:</label>
				<? echo Form::select("rate_from", array('RMB'=>'RMB'), $form->rate_from); ?> 
				
				<label>Rate to:</label>
				<? echo Form::select("rate_to", array('USD'=>'USD', 'JPY'=>'JPY'), $form->rate_to); ?>
				<br>
				
				<label>Date From:</label>
				<? echo Form::input('date_from', $form->date_from, array('id'=>'date_from')); ?>
				
				<label>Date To:</label>
				<? echo Form::input('date_to', $form->date_to, array('id'=>'date_to')); ?><br>
				
				<label>Rate</label>
				<? echo Form::input('rate', $form->rate); ?>
				<br>
				
				<input type="submit" value="Add">
			<? echo Form::close(); ?>

			<br>
			<table border=2>
				<tr>
					<td>Rate From</td>
					<td>Rate To</td>
					<td>Date From</td>
					<td>Date To</td>
					<td>Rate</td>
					<td>Delete</td>
				</tr>
				<? foreach ($rates as $rate) {?>
				<tr>
					<td><?=$rate->rate_from ?></td>
					<td><?=$rate->rate_to ?></td>
					<td><?=$rate->date_from ?></td>
					<td><?=$rate->date_to ?></td>
					<td><?=$rate->rate ?></td>
					<td><? echo HTML::anchor('/admin/rate_delete/'.$rate->id, 'Delete'); ?></td>
				</tr>
				<? } ?>
				</table>
			</td>
		</tr>
	</tbody>
</table>

<script>
	$(function() {
		$( "#date_from" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
		$( "#date_to" ).datepicker({
			dateFormat: 'yy-mm-dd',
			showOn: "both",
			buttonImage: "<?=PATH_BASE ?>media/images/calendar.gif",
			buttonImageOnly: true
		});
	});
</script>