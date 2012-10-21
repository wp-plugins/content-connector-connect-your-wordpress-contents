<?php
/*
Plugin Name: Associated Posts Lite
Plugin URI: http://dennishoppe.de/wordpress-plugins/associated-posts-pro
Description: The "Associated Posts" Plugin enables you to associate posts and pages with each other. You can easily select a set of posts and attach it to a page.
Version: 0.9.8.0
Author: Dennis Hoppe
Author URI: http://DennisHoppe.de
*/


// Plugin Class
If (!Class_Exists('wp_plugin_associated_posts')){
class wp_plugin_associated_posts {
  var $base_url;
  var $template_dir;
  var $arr_option_box;
  var $post_object_cache;
  var $saved_options = False;

  function __construct(){
    // Read base
    $this->base_url = get_bloginfo('wpurl').'/'.SubStr(RealPath(DirName(__FILE__)), Strlen(ABSPATH));

    // Template directory
    $this->template_dir = WP_CONTENT_DIR . '/associated-posts';

    // Option boxes
    $this->arr_option_box = Array( 'main' => Array(), 'side' => Array() );

    // Post object cache
    $this->post_object_cache = Array();

    // This Plugin supports post thumbnails
    If (Function_Exists('Add_Theme_Support')) Add_Theme_Support('post-thumbnails');

    // Hooks & Styles
    Add_Action ('widgets_init', Array($this, 'Load_TextDomain'));
    If (Is_Admin()){
      Add_Action ('admin_menu',      Array($this, 'Add_Options_Page')     );
      Add_Action ('admin_menu',      Array($this, 'Add_Post_Meta_Boxes')  );
      Add_Action ('save_post',       Array($this, 'Save_Meta_Box')        );
    }
    Else {
      Add_Action ('the_post',        Array($this, 'The_Post') );
      Add_Filter ('the_content',     Array($this, 'Filter_Content'), $this->get_option('content_filter_priority') );
      Add_Action ('wp_print_styles', Array($this, 'Add_Templates_Styles')   );

      Remove_Filter('the_content',   'do_shortcode', 11 );
      Add_Filter('the_content',      'do_shortcode', 20 );

      // Shortcodes
      Add_Shortcode ( 'associated_posts', Array($this, 'ShortCode')      );
    }

    // Set Globals link
    $GLOBALS[__CLASS__] = $this;
  }

  function Load_TextDomain(){
    $locale = Apply_Filters( 'plugin_locale', get_locale(), __CLASS__ );
    Load_TextDomain (__CLASS__, DirName(__FILE__).'/language/' . $locale . '.mo');
  }

  function t ($text, $context = ''){
    // Translates the string $text with context $context
    If ($context == '')
      return Translate ($text, __CLASS__);
    Else
      return Translate_With_GetText_Context ($text, $context, __CLASS__);
  }

  function Default_Options(){
    return Array(
      'posts_position' => 'bottom',
      'content_filter_priority' => 9
    );
  }

  function Save_Options(){
    // Check if this is a post request
    If (Empty($_POST)) return False;

    // Clean the Post array
    $_POST = StripSlashes_Deep($_POST);
    ForEach ($_POST AS $option => $value)
      If (!$value) Unset ($_POST[$option]);

    // Save Options
    Update_Option (__CLASS__, $_POST);

    return True;
  }

  function Get_Option($key = Null, $default = False){
    // Read Options
    $arr_option = Array_Merge (
      (Array) $this->Default_Options(),
      (Array) get_option(__CLASS__)
    );

    // Locate the option
    If ($key == Null)
      return $arr_option;
    ElseIf (IsSet ($arr_option[$key]))
      return $arr_option[$key];
    Else
      return $default;
  }

  function Add_Options_Page (){
    $handle = Add_Options_Page(
      $this->t('Associated Posts Options'),
      $this->t('Associated Posts'),
      'manage_options',
      __CLASS__,
      Array($this, 'Print_Options_Page')
    );

    // Add option boxes
    $this->Add_Option_Box ( $this->t('Position'), DirName(__FILE__).'/option-box-position.php' );
    $this->Add_Option_Box ( $this->t('Templates'), DirName(__FILE__).'/option-box-templates.php' );
    $this->Add_Option_Box ( $this->t('Add a Template'), DirName(__FILE__).'/option-box-add-template.php', 'main', 'closed' );
    $this->Add_Option_Box ( $this->t('Association Interface'), DirName(__FILE__).'/option-box-interface.php', 'side' );
    $this->Add_Option_Box ( $this->t('Selectable Taxonomies'), DirName(__FILE__).'/option-box-taxonomies.php', 'side' );
    $this->Add_Option_Box ( $this->t('Miscellaneous'), DirName(__FILE__).'/option-box-miscellaneous.php', 'side' );

    // Add JavaScript to this handle
    Add_Action ('load-' . $handle, Array($this, 'Load_Options_Page'));
  }

  function Add_Option_Box($title, $include_file, $column = 'main', $state = 'opened'){
    // Check the input
    If (!Is_File($include_file)) return False;
    If ( $title == '' ) $title = '&nbsp;';

    // Column (can be 'side' or 'main')
    If ($column != '' && $column != Null && $column != 'main')
      $column = 'side';
    Else
      $column = 'main';

    // State (can be 'opened' or 'closed')
    If ($state != '' && $state != Null && $state != 'opened')
      $state = 'closed';
    Else
      $state = 'opened';

    // Add a new box
    $this->arr_option_box[$column][] = Array(
      'title' => $title,
      'file'  => $include_file,
      'state' => $state
    );
  }

  function Load_Options_Page(){
    // Does the user saves options?
    $this->saved_options = $this->Save_Options();

    // Include JS
    WP_Enqueue_Script( 'dashboard' );
    WP_Enqueue_Script( 'associated-posts-options-page', $this->base_url . '/options-page.js' );

    // Include CSS
    WP_Admin_CSS( 'dashboard' );
    WP_Enqueue_Style ( 'associated-posts-options-page', $this->base_url . '/options-page.css' );

    // Remove incompatible JS Libs
    WP_Dequeue_Script('post');
  }

  function Print_Options_Page(){
    Include DirName(__FILE__) . '/options-page.php';
  }

  function Option_Page_Url($parameter = Array(), $htmlspecialchars = True){
    $url = Add_Query_Arg($parameter, Admin_URL('options-general.php?page=' . __CLASS__));
    If ($htmlspecialchars) $url = HTMLSpecialChars($url);
    return $url;
  }

  function Add_Post_Meta_Boxes(){
    // Register meta boxes
    ForEach ((Array) $this->get_post_types() AS $post_type){
      Add_Meta_Box(
        __CLASS__,
        $this->t('Associated Posts'),
        ($post_type->name == 'page') ? Array($this, 'Print_Meta_Box') : Array($this, 'Print_Meta_Notice_Box'),
        $post_type->name,
        'normal',
        'high'
      );
    }

    // Enqueue Meta Box Style and Scripts
    WP_Enqueue_Style( 'associated-posts-meta-box', $this->base_url . '/meta-box.css' );
    WP_Enqueue_Script( 'associated-posts-meta-box', $this->base_url . '/meta-box.js', Array('jquery') );
  }

  function Print_Meta_Box(){ Include DirName(__FILE__) . '/meta-box.php'; }
  function Print_Meta_Notice_Box(){ Include DirName(__FILE__) . '/meta-notice-box.php'; }

  function Find_Templates(){
    $arr_template = Array_Merge (
      (Array) Glob ( DirName(__FILE__) . '/templates/*.php' ),
      (Array) Glob ( DirName(__FILE__) . '/templates/*/*.php' ),

      (Array) Glob ( Get_StyleSheet_Directory() . '/*.php' ),
      (Array) Glob ( Get_StyleSheet_Directory() . '/*/*.php' ),

      Is_Child_Theme() ? (Array) Glob ( Get_Template_Directory() . '/*.php' ) : Array(),
      Is_Child_Theme() ? (Array) Glob ( Get_Template_Directory() . '/*/*.php' ) : Array()
    );

    // Filter to add template files - you can use this filter to add template files to the user interface
    $arr_template = (Array) Apply_Filters('associated_posts_template_files', $arr_template);

    // Check if there template files
    If (Empty($arr_template)) return False;

    $arr_result = Array();
    $arr_sort = Array();
    ForEach ($arr_template AS $index => $template_file){
      // Read meta data from the template
      If (!$arr_properties = $this->Get_Template_Properties($template_file))
        Continue;
      Else
        $arr_result[RealPath($template_file)] = $arr_properties;
        $arr_sort[RealPath($template_file)] = $arr_properties['name'];
    }
    Array_MultiSort($arr_sort, $arr_result);

    return $arr_result;
  }

  function Get_Template_Properties($template_file){
    // Check if this is a file
    If (!$template_file || !Is_File ($template_file) || !Is_Readable($template_file)) return False;

    // Read meta data from the template
    $arr_properties = get_file_data ($template_file, Array(
      'name' => 'AP Template',
      'description' => 'Description',
      'author' => 'Author',
      'author_uri' => 'Author URI',
      'author_email' => 'Author E-Mail',
      'version' => 'Version'
    ));

    // Check if there is a name for this template
    If (Empty($arr_properties['name']))
      return False;
    Else
      return $arr_properties;
  }

  function Get_Default_Template(){
    // Which file set the user as default?
    $template_file = $this->get_option('default_template_file');
    If (Is_File($template_file)) return $template_file;

    // Is there a template by the theme author
    $template_file = RealPath(Get_Query_Template( 'associated-posts' ));
    If (Is_File($template_file)) return $template_file;

    // Else:
    return RealPath(DirName(__FILE__) . '/templates/title-excerpt-thumbnail.php');
  }

  function Get_Post_Types(){
    return get_post_types(Array(
      'show_ui' => True
    ), 'objects');
  }

  function Get_Taxonomies ($post_type){
    $arr_taxonomy = get_taxonomies (Array(
      #'object_type' => Array($post_type),
      #'public' => True,
      #'show_ui' => True
    ), 'objects', 'and');

    ForEach ($arr_taxonomy AS $index => $taxonomy){
      If (!In_Array($post_type, $taxonomy->object_type))
        Unset ($arr_taxonomy[$index]);
    }

    return $arr_taxonomy;
  }

  function Get_All_Posts($post_type, $exclude = Array()){
    $post_query = new WP_Query(Array(
      'post_type' => $post_type,
      'posts_per_page' => -1,
      'post_status' => 'publish',
      'caller_get_posts' => true, // for WP < 3.1
      'ignore_sticky_posts' => True,
      'post__not_in' => (Array) $exclude,
      'cache_results' => False
    ));

    return $post_query->posts;
  }

  function Field_Name($option_name){
    // Generates field names for the meta box
    return __CLASS__ . '[' . $option_name . ']';
  }

  function Save_Meta_Box($post_id){
    // If this is an autosave we dont care
    If ( Defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    // Check if this request came from the edit page section
    If (IsSet($_POST[ __CLASS__ ]))
      // Save Meta data
      update_post_meta ($post_id, '_' . __CLASS__, (Array) ($_POST[ __CLASS__ ]) );
  }

  function Add_Templates_Styles(){
    // Find the template
    $arr_template_files = $this->Find_Templates();
    ForEach ($arr_template_files AS $template_file => $template_details){
      // If there is no style sheet we bail out
      If (!Is_File(DirName($template_file) . '/' . BaseName($template_file, '.php') . '.css')) Continue;

      // Locate the URL of the style sheet
      $style_sheet = get_bloginfo('wpurl') . '/' .
                     Str_Replace("\\", '/', SubStr(RealPath(DirName($template_file)), Strlen(ABSPATH))) . '/' .
                     BaseName($template_file, '.php') . '.css';

      // run the filter for the template file
      $style_sheet = Apply_Filters('associated_posts_style_sheet', $style_sheet);

      // Print the stylesheet link
      If ($style_sheet) WP_Enqueue_Style ( 'associated-posts-' . Sanitize_Title(BaseName($template_file, '.php')), $style_sheet );
    }

  }

  function ShortCode($attr = Null){
    Global $post;

    // Check the Singular Mode
    If ($this->get_option('show_only_on_singulars'))
      If (!Is_Page($post->ID) && !Is_Single($post->ID))
        return False;

    // Render the posts
    return $this->render_associated_posts();
  }

  function Render_Associated_Posts(){
    // Get the association settings
    If (!IsSet($GLOBALS['post']->associated_posts))
      return False;
    Else
      $meta = $GLOBALS['post']->associated_posts;

    // Uses template filter
    $template_file = Apply_Filters('associated_posts_template', $meta['template']);

    // If there is no valid template file we bail out
    If (!Is_File($template_file)) $template_file = $this->get_default_template();

    // Cache the current post
    Array_Push($this->post_object_cache, $GLOBALS['post']);

    // Include the template
    Ob_Start();
    Include $template_file;
    $result = Ob_Get_Contents();
    Ob_End_Clean();

    // Restore post data
    If (!Empty($this->post_object_cache)){
      $GLOBALS['post'] = Array_Pop($this->post_object_cache);
      Setup_PostData($GLOBALS['post']);
    }

    // return code
    return $result;
  }

  function Get_Association_Data($post_id = Null){
    // Get the post id
    If ($post_id == Null && Is_Object($GLOBALS['post']))
      $post_id = $GLOBALS['post']->ID;
    ElseIf ($post_id == Null && !Is_Object($GLOBALS['post']))
      return False;

    // Read meta data
    If (Get_Post_Type($post_id) != 'page') return False;
    $arr_meta = get_post_meta($post_id, '_' . __CLASS__, True);
    If (Empty($arr_meta) || !Is_Array($arr_meta)) return False;

    // Get post ids
    $arr_meta['post_ids'] = $this->get_associated_post_ids($arr_meta);

    // Return
    return $arr_meta;
  }

  function Get_Associated_Post_Ids ($meta_data){
    If (Empty($meta_data) || !Is_Array($meta_data) || Empty($meta_data['post_selection'])) return False;

    // Prepare result Array
    $arr_result_post_ids = (Array) $meta_data['post_selection']['post']['_explicitly']['selection'];

    // There are no posts we have to care about
    If (Empty($arr_result_post_ids)) return False;
    Else $arr_result_post_ids = Array_Unique ($arr_result_post_ids);

    return $arr_result_post_ids;
  }

  function Get_Query_Vars($association_data, $loop_protection = True){
    If (!Is_Array($association_data) || Empty($association_data)) return False;
    If (Empty($association_data['post_ids'])) return False;

    $post__in = (Array) $association_data['post_ids'];
    $sticky_posts = (Array) Get_Option('sticky_posts', Array());
    $post__not_in = Array_Diff($sticky_posts, $post__in);

    return Array (
      'post__in' => $post__in,
      'post__not_in' => $post__not_in,
      'posts_per_page' => -1,
      'post_type' => 'post',
      'tb' => True // We pretend this is a trackback query to avaid filters of exclude plugins
    );
  }

  function Get_Associated_Posts($post_id = False){
    If ($post_id){
      $association_data = $this->get_association_data($post_id);
    }
    ElseIf (IsSet($GLOBALS['post'])) {
      $association_data = $GLOBALS['post']->associated_posts;
    }
    Else
      Return False;

    If ($query_vars = $this->get_query_vars($association_data)){
			Add_Filter ('posts_results', Array($this, 'Filter_Posts_Result'), 999, 2);
			$query = New WP_Query($query_vars);
			Remove_Filter ('posts_results', Array($this, 'Filter_Posts_Result'), 999, 2);
      return $query;
    }
    Else Return False;
  }

  function Filter_Posts_Result($posts, &$wp_query){
		$wp_query->is_home = True;
		return $posts;
	}

  function The_Post(&$post){
    If (!IsSet($post->associated_posts))
      $post->associated_posts = $this->get_association_data();
  }

  function Filter_Content($content){
    // Append the ShortCode to the Content
    $content = Str_Replace('[associated-posts', '[associated_posts', $content);
    If ( StrPos($content, '[associated_posts]') === False && // Avoid double inclusion of the ShortCode
         StrPos($content, '[associated_posts ') === False && // Without closing bracket to find ShortCodes with attributes
         Apply_Filters('associated_posts_auto_append', True) && // You can use this filter to control the auto append feature
         $this->get_option('posts_position') != 'none' && // User can disable the auto append feature
         !post_password_required() // The user isn't allowed to read this post
       ){

      // Add the ShortCode to the current content
      If ($this->get_option('posts_position') == 'top')
        Return '[associated_posts] ' . $content;
      ElseIf ($this->get_option('posts_position') == 'bottom')
        Return Trim($content) . ' [associated_posts]';

    }
    Else
      // do not include the Shortcode in the content
      Return $content;
  }

  function Get_Post_Thumbnail($post_id = Null, $size = 'thumbnail'){
    /* Return Value: An array containing:
         $image[0] => attachment id
         $image[1] => url
         $image[2] => width
         $image[3] => height
    */
    If ($post_id == Null) $post_id = get_the_id();

    If (Function_Exists('get_post_thumbnail_id') && $thumb_id = get_post_thumbnail_id($post_id) )
      return Array_Merge ( Array($thumb_id), (Array) wp_get_attachment_image_src($thumb_id, $size) );
    ElseIf ($arr_thumb = $this->get_post_attached_image($post_id, 1, 'rand', $size))
      return $arr_thumb[0];
    Else
      return False;
  }

  function Get_Post_Attached_Image($post_id = Null, $number = 1, $orderby = 'rand', $image_size = 'thumbnail'){
    If ($post_id == Null) $post_id = get_the_id();
    $number = IntVal ($number);
    $arr_attachment = get_posts (Array( 'post_parent'    => $post_id,
                                        'post_type'      => 'attachment',
                                        'numberposts'    => $number,
                                        'post_mime_type' => 'image',
                                        'orderby'        => $orderby ));

    // Check if there are attachments
    If (Empty($arr_attachment)) return False;

    // Convert the attachment objects to urls
    ForEach ($arr_attachment AS $index => $attachment){
      $arr_attachment[$index] = Array_Merge ( Array($attachment->ID), (Array) wp_get_attachment_image_src($attachment->ID, $image_size));
      /* Return Value: An array containing:
           $image[0] => attachment id
           $image[1] => url
           $image[2] => width
           $image[3] => height
      */
    }

    return $arr_attachment;
  }

  function Pro_Notice(){
    PrintF (
      $this->t('Sorry, this feature is only available in the <a href="%s" target="_blank">Pro Version of Associated Posts</a>.'),
      $this->t('http://dennishoppe.de/en/wordpress-plugins/associated-posts-pro', 'Link to the authors website')
    );
  }


} /* End of Class */
New wp_plugin_associated_posts;
} /* End of If-Class-Exists-Condition */


// Associated Posts Widget
If (!Class_Exists('wp_widget_associated_posts')){
class wp_widget_associated_posts Extends WP_Widget {
  var $base_url;
  var $arr_option;
  var $AP;

  function __construct( $id_base = False, $name = False, $widget_options = Array(), $control_options = Array() ){
    // Catch the AP Plugin
    $this->AP = $GLOBALS['wp_plugin_associated_posts'];

    // Register Widget
    // Setup the Widget data
    parent::__construct (
      False,
      $this->t('Associated Posts'),
      Array('description' => $this->t('Displays the associated posts of the current post in the sidebar.'))
    );
    Add_Action('init', Array($this, 'Register_Widget'));

    // Read base_url
    $this->base_url = $this->AP->base_url;
  }

  function Register_Widget(){
    // Setup the Widget data
    $this->name = $this->t('Associated Posts');
    $this->widget_options['description'] = $this->t('Displays the associated posts of the current post in the sidebar.');
  }

  function t ($text, $context = ''){
    // Translates the string $text with context $context
    return $this->AP->t($text, $context);
  }

  function Default_Options(){
    // Default settings
    return Array(
      'title' => $this->t('Associated Posts'),
      'template_file' => Is_Object($this->AP) ? $this->AP->Get_Default_Template() : False
    );
  }

  function Load_Options($options){
    // Prepare $options array
    $options = (ARRAY) $options;

    // Delete empty values
    ForEach ($options AS $key => $value)
      If (!$value) Unset($options[$key]);

    // Load options
    $this->arr_option = Array_Merge ($this->Default_Options(), $options);
  }

  function Get_Option($key, $default = False){
    If (IsSet($this->arr_option[$key]) && $this->arr_option[$key])
      return $this->arr_option[$key];
    Else
      return $default;
  }

  function Set_Option($key, $value){
    $this->arr_option[$key] = $value;
  }

  function Widget ($args, $options){ return False; }

  function Form ($options){
    // Load options
    $this->Load_Options ($options); Unset ($options);

    // Show form
    If (!Is_Object($this->AP)) : ?>
      <p class="warning">
        <?php Echo $this->t('Please activate the Associated Posts Plugin!'); ?>
      </p>

    <?php Else : ?>
      <p class="pro-notice"><?php $this->AP->Pro_Notice() ?></p>
      <p>
        <label for="<?php Echo $this->get_field_id('title')?>"><?php Echo $this->t('Title') ?></label>:
        <input type="text" name="<?php Echo $this->get_field_name('title')?>" id="<?php Echo $this->get_field_id('title')?>" value="<?php Echo $this->get_option('title')?>">
      </p>

      <h3><?php Echo $this->t('Template') ?></h3>
      <?php ForEach ($this->AP->Find_Templates() AS $template_file => $arr_template) : ?>
      <p>
        <input type="radio" name="<?php Echo $this->get_field_name('template_file')?>" id="<?php Echo $this->get_field_id('template_file')?>_<?php Echo Sanitize_Title($template_file) ?>" value="<?php Echo $template_file ?>" <?php Checked($this->get_option('template_file'), $template_file)?>>
        <label for="<?php Echo $this->get_field_id('template_file')?>_<?php Echo Sanitize_Title($template_file) ?>"><?php Echo $arr_template['name'] ?></label> <small>(<em><?php Echo $arr_template['description'] ?></em>)</small>
      </p>
    <?php EndForEach; ?>

    <?php EndIf;
  }

  function Update ($new_settings, $old_settings){
    return $new_settings;
  }

} /* End of Class */
Add_Action ('widgets_init', Create_function ('','Register_Widget(\'wp_widget_associated_posts\');') );
} /* End of If-Class-Exists-Condition */
/* End of File */