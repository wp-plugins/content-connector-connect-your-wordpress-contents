<p><?php Echo $this->t('Please select the taxonomies you need to select and filter your associated posts.') ?></p>
<?php $arr_taxonomy_selection = (Array) $this->get_option('taxonomy_selection'); ?>
<?php ForEach ($this->get_post_types() AS $post_type) : ?>
  <h4><?php Echo $post_type->label ?></h4>
  <p>
    <input type="checkbox" id="taxonomy_selection_<?php Echo $post_type->name ?>_wp_user" disabled>
    <label for="taxonomy_selection_<?php Echo $post_type->name ?>_wp_user"><?php PrintF($this->t('%s by the creator'), $post_type->label) ?></label>
  </p>
  <?php ForEach($this->get_taxonomies($post_type->name) AS $taxonomy): ?>
    <p>
      <input type="checkbox" id="taxonomy_selection_<?php Echo $post_type->name ?>_<?php Echo $taxonomy->name ?>" disabled>
      <label for="taxonomy_selection_<?php Echo $post_type->name ?>_<?php Echo $taxonomy->name ?>"><?php PrintF($this->t('%1$s by %2$s'), $post_type->label, $taxonomy->labels->name) ?></label>
    </p>
  <?php EndForEach; ?>
  <p>
    <input type="checkbox" id="taxonomy_selection_<?php Echo $post_type->name ?>_explicitly" <?php Checked($post_type->name == 'post') ?> disabled>
    <label for="taxonomy_selection_<?php Echo $post_type->name ?>_explicitly"><?php PrintF($this->t('Select %s explicitly'), $post_type->label) ?></label>
  </p>
<?php EndForEach; ?>
<p class="pro-notice"><?php $this->Pro_Notice() ?></p>