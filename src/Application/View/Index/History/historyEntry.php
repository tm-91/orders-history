<div class="h-entry-container">

<div class="entry-data date">
	<?= $entry->getDate('d.m.Y H:i:s'); ?>
</div>

<?php if ($addedData = $entry->getAddedData()): ?>
<div class="entry-data added">
	<div class="header">
		Dodano:
	</div>
	<div class="content">
		<?php 
//		foreach ($addedData as $key => $val) {
//			echo $key . ': ';
//			if (is_array($val)){
//				print_r($val);
//			} else {
//				echo $val;
//			}
//			echo '</br/>';
//		}
		// print_r($addedData); 
		 \Application\View\View::echoRec($addedData, $translations);
		?>
	</div>
</div>
<?php endif; ?>

<?php if ($editedData = $entry->getEditedData()): ?>
<div class="entry-data edited">
	<div class="header">
		Edytowano:
	</div>
	<div class="content">
		<?php 
//		foreach ($editedData as $key => $val) {
//			echo $key . ': ';
//			if (is_array($val)){
//				print_r($val);
//			} else {
//				echo $val;
//			}
//			echo '</br/>';
//		}
		// // print_r($addedData); 
		 \Application\View\View::echoRec($editedData, $translations);
		?>
	</div>
</div>
<?php endif; ?>

<?php if ($removedData = $entry->getRemovedData()): ?>
<div class="entry-data removed">
	<div class="header">
		Usunieto:
	</div>
	<div class="content">
		<?php 
//		foreach ($removedData as $key => $val) {
//			echo $key . ' : ';
//			if (is_array($val)){
//				print_r($val);
//			} else {
//				echo $val;
//			}
//			echo '</br/>';
//		}
		// print_r($addedData); 
		 \Application\View\View::echoRec($removedData, $translations);
		?>
	</div>
</div>
<?php endif; ?>

</div>