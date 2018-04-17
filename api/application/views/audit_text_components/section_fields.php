<?php

if (isset($section->fields)): ?>

	<table>
		<tbody>

	<?php foreach ($section->fields as $field): ?>

		<tr>
			<th><?= $field->question ?></th>
			<td>
				<?php if ($field->type == "images"):

					foreach ($field->value as $image):

						$image_record = $this->db->get_where("uploads",["filename" => $image])->row();
						$token = $this->ImageModel->generateUploadToken($image_record->id,4200); //6mon expiry
						
						?><img src="<?=base_url()?>/image/serve/<?=$token['token']?>/<?=$image?>" style="width:200px">
					
					<?php endforeach; 

					echo "<br>";

				else: ?>
					<?= strtoupper($field->value) ?><br>
				<?php endif;

				if ($field->hasNotes): ?>
					<strong>Notes:</strong><br>
					<?php if (isset($field->notes)) echo $field->notes; ?>
				<?php endif ?>
			</td>
		</tr>
			
	<?php endforeach; ?>

		</tbody>
	</table>

<?php endif;