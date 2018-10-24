<table>
	<?php echo "WPIS" ?>
	<thead>
		<tr>
			<td>
				Data edycji
			</td>
			<td>
				Dodano:
			</td>
			<td>
				Edytowano:
			</td>
			<td>
				Usunieto:
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<?php print_r($entry->getDate()); ?>
			</td>
			<td>
				<?php if ($data = $entry->getAddedData()) {
					print_r($data); 
				?>
			</td>
			<td>
				<?php if ($data = $entry->getEditedData()) {
					print_r($data); 
				?>
			</td>
			<td>
				<?php if ($data = $entry->getRemovedData()) {
					print_r($data); 
				?>
			</td>
		</tr>
	</tbody>
</table>