<h1>Form validation tests</h1>

<ul class="errors">
<?php foreach ($sf_request->getErrorNames() as $name): ?>
  <li class="<?php echo $name ?>"><?php echo $sf_request->getError($name) ?></li>
<?php endforeach; ?>
</ul>

<?php echo form_tag('validation/simple') ?>
  <?php echo input_tag('first_name') ?>
  <?php echo input_tag('last_name') ?>
  <?php echo submit_tag('submit') ?>
</form>