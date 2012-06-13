<?php

// Read Meta data for this post
$meta = $this->get_association_data();

// Print selectable taxonomies
ForEach ((Array) $this->get_post_types() AS $post_type => $_){
  // Get post type
  If (!$post_type = Get_Post_Type_Object($post_type)) Continue;
  $arr_taxonomy = Array_Merge( Array('_wp_user'), Array_Keys($this->get_taxonomies($post_type->name)), Array('_explicitly') );

  ForEach ((Array) $arr_taxonomy AS $taxonomy){
    // Handle the taxonomy
    If ( $taxonomy == '_explicitly' && $post_type->name == 'post' ) : ?>
      <h4 class="toggle-title">
        <?php PrintF($this->t('Select %s explicitly'), $post_type->label) ?>
        <span class="hidden active">(<?php Echo $this->t('Active') ?>)</span>
      </h4>
      <div class="toggle-box hide-if-js">
        <p class="select-post">
        <?php ForEach ((Array) $this->get_all_posts($post_type->name, $GLOBALS['post']->ID) AS $p) : ?>
        <span class="option-long">
          <input type="checkbox" name="<?php Echo $this->Field_Name('post_selection') ?>[<?php Echo $post_type->name ?>][<?php Echo $taxonomy ?>][selection][]" id="<?php Echo $post_type->name ?>_<?php Echo $taxonomy ?>_<?php Echo $p->ID ?>" value="<?php Echo $p->ID ?>" <?php Checked(In_Array($p->ID, (Array) @$meta['post_selection'][$post_type->name][$taxonomy]['selection'])) ?> />
          <label for="<?php Echo $post_type->name ?>_<?php Echo $taxonomy ?>_<?php Echo $p->ID ?>"><?php Echo ($p->post_title != '') ? $p->post_title : '<i>'.SPrintF($this->t('Post %s (Without title)'), $p->ID).'</i>' ?></label>
        </span>
        <?php EndForEach; ?>
        </p>
      </div>

    <?php EndIf;
  }
}
?>

<h4><?php _e('Settings') ?></h4>

<p class="order-by">
  <label for="ap_order_by"><?php Echo $this->t('Order posts by:') ?></label>
  <select name="<?php Echo $this->Field_Name('order_by') ?>" id="ap_order_by">
    <option value="date" selected><?php _e('Date') ?></option>
  </select>
</p>

<p class="order">
  <label for="ap_order"><?php Echo $this->t('Order:') ?></label>
  <select name="<?php Echo $this->Field_Name('order') ?>" id="ap_order">
    <option value="DESC" selected ><?php Echo $this->t('Descending') ?></option>
  </select>
</p>


<h4><?php Echo $this->t('Template') ?></h4>
<div class="template">
  <?php ForEach ( $this->find_templates() AS $file => $properties ) : ?>
  <p>
    <input type="radio" name="<?php Echo $this->Field_Name('template') ?>" id="template_<?php Echo Sanitize_Title($file) ?>" value="<?php Echo HTMLSpecialChars($file) ?>" <?php Checked($meta['template'], $file) ?> <?php Checked(!$meta['template'] && $file == $this->get_default_template()) ?> />
    <label for="template_<?php Echo Sanitize_Title($file) ?>">
    <?php If (Empty($properties['name'])) : ?>
      <em><?php Echo $file ?></em>
    <?php Else : ?>
      <strong><?php Echo $properties['name'] ?></strong>
    <?php EndIf; ?>
    <?php If ($properties['version']) : ?> (<?php Echo $properties['version'] ?>)<?php Endif; ?>
    <?php If ($properties['description']) : ?><br /><?php Echo $properties['description']; Endif; ?>
    </label>
  </p>
  <?php EndForEach; ?>
</div>
