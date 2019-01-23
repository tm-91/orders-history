<div class="entry_container">
	<div class="entry_date_wrapper">
		<div class="entry_date entry_column">
			<?= $entry->getDate('d.m.Y H:i:s'); ?>
		</div>
	</div>
	<div class="entry_data_wrapper">

		<?php if ($addedData = $entry->getAddedData()): ?>
		<div class="entry_data entry_column added">
			<div class="header">
                <h2>Dodano</h2>
			</div>
			<div class="content">
				<?php
				 \Application\View\View::echoRec($addedData, $this->translations);
				?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ($editedData = $entry->getEditedData()): ?>
		<div class="entry_data entry_column edited">
			<div class="header">
				Edytowano
			</div>
			<div class="content">
				<?php
				 \Application\View\View::echoRec($editedData, $this->translations);
				?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ($removedData = $entry->getRemovedData()): ?>
		<div class="entry_data entry_column removed">
			<div class="header">
                Usunieto
			</div>
			<div class="content">
				<?php
				 \Application\View\View::echoRec($removedData, $this->translations);
				?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>