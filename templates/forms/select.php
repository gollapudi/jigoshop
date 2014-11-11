<?php
use Jigoshop\Helper\Render;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $multiple boolean Is field supposed to accept multiple values?
 * @var $value mixed Currently selected value(s).
 * @var $disabled bool Is field disabled?
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 * @var $hidden boolean Whether the field is hidden.
 * @var $size int Size of form widget.
 */
?>
<div class="form-group <?php echo $id; ?>_field clearfix<?php $hidden and print ' not-active'; ?>">
	<label for="<?php echo $id; ?>" class="col-sm-<?php echo 12 - $size; ?> control-label">
		<?php echo $label; ?>
		<?php if(!empty($tip)): ?>
			<a href="#" data-toggle="tooltip" class="badge" data-placement="top" title="<?php echo $tip; ?>">?</a>
		<?php endif; ?>
	</label>
	<div class="col-sm-<?php echo $size; ?>">
		<select id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="form-control" placeholder="<?php echo $placeholder; ?>"<?php $multiple and print ' multiple="multiple"'; ?><?php $disabled and print ' disabled'; ?>>
			<?php foreach($options as $option => $item): ?>
				<?php if(is_array($item)): ?>
					<optgroup label="<?php echo $option; ?>">
						<?php foreach($item as $subvalue => $sublabel): ?>
							<?php Render::output('forms/select/option', array('label' => $sublabel, 'value' => $subvalue, 'current' => $value)); ?>
						<?php endforeach; ?>
					</optgroup>
				<?php else: ?>
					<?php Render::output('forms/select/option', array('label' => $item, 'value' => $option, 'current' => $value)); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
		<?php if(!empty($description)): ?>
			<span class="help-block"><?php echo $description; ?></span>
		<?php endif; ?>
	</div>
</div>
<!-- TODO: Get rid of this and use better asset script. -->
<script type="text/javascript">
	/*<![CDATA[*/
	jQuery(function($){
		$("select#<?php echo $id; ?>").select2();
	});
	/*]]>*/
</script>
