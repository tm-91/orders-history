<div class="entry_container">
	<div class="entry_date_wrapper">
		<div class="entry_date entry_column">
			<?= $entry->getDate('d.m.Y H:i:s'); ?>
		</div>
	</div>
	<div class="entry_data_wrapper" style="display: none;">

		<?php if ($addedData = $entry->getAddedData()): ?>
		<div class="entry_data entry_column added">
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
		<div class="entry_data entry_column edited">
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
		<div class="entry_data entry_column removed">
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
</div>