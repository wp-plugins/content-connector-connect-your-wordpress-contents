jQuery(document).ready(function($){
  var $metabox = $('div#wp_plugin_associated_posts.postbox');

  // Make toggle boxes slideable
  $metabox.find('h4.toggle-title').click(function(){
    var $this = $(this);
    var $togglebox = $this.next('.toggle-box');
    if ($this.hasClass('open')){
      $togglebox.slideUp();
      $this.removeClass('open').addClass('closed');
    }
    else {
      $togglebox.slideDown();
      $this.removeClass('closed').addClass('open');
    }
  }).addClass('closed');
  
  // Find active selection boxes
  $metabox.find('.toggle-box input:checkbox, .toggle-box select').change(function(){
    var $togglebox = $(this).parents('.toggle-box:first');
    if ($togglebox.find('select').val() != '' && $togglebox.find('input:checked').length > 0){
      $togglebox.prev('.toggle-title').find('.active').fadeIn();
    }
    else {
      $togglebox.prev('.toggle-title').find('.active').fadeOut();
    }
  }).change();

});