<p><?php Echo $this->t('Please select the sections where you want to have an association meta box.') ?></p>

<?php ForEach ($this->get_post_types() AS $post_type) : ?>
  <p>
    <input type="checkbox" id="association_ui_type_<?php Echo $post_type->name ?>" <?php Checked(In_Array($post_type->name, Array('page') )) ?> disabled>
    <label for="association_ui_type_<?php Echo $post_type->name ?>"><?php Echo $post_type->labels->name ?></label>
  </p>
<?php EndForEach; ?>

<p><?php Echo $this->t('After you have done all your selections of course you can hide the boxes. Your selections will be stored. So you could hide the meta boxes from your client.') ?></p>
<p class="pro-notice"><?php $this->Pro_Notice() ?></p>