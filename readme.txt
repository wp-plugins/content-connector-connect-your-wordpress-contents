=== Associated Posts Lite ===
Contributors: dhoppe
Tags: post, posts, page, pages, associate, association, attach, list, admin, content, cms, category, categories, tag, tags, author, post-page-associator, associated-posts
Requires at least: 3.1
Tested up to: 3.5
Stable tag: trunk

The award-winning Associated Posts Lite (formerly Post Page Associator) Plugin enables you to display posts on a page.


== Description ==

This plugin enables you to associate posts and pages with each other. You can easily select posts in the "Edit Page" Mode and attach them to this page. This Plugin is the subsequent version of "Post Page Associator".

Btw: Associated Posts is also available as [Premium Plugin](http://dennishoppe.de/en/wordpress-plugins/associated-posts-pro).


= Handling =
The handling is very easy. When you are going to edit a page you will see a box with the title "Associated Posts". There you can choose posts which should attached to this page. In this version you cannot set the number of posts which should be shown on the page or other settings like the post order. These options are available in the [Pro Version](http://dennishoppe.de/en/wordpress-plugins/associated-posts-pro).


= Settings =
You can change the association settings in WP Admin Panel &raquo; Settings &raquo; Associated Posts.


= Shortcode =
In case you won't have the associated posts at the end of your page you can use the <code>[associated_posts]</code> shortcode anywhere in your pages content. So the posts will be shown at the place you inserted the shortcode. (The shortcode has no parameters.)


= Customization =
If you need a customized template of the associated posts. E.g. as list or with author, date, time or meta data feel free to send me an e-mail. For a small fee I will write a customized template for you. Don't be shy. ;)


= How to write an own customization =
A template is a php file which renders the output of the associated posts (a WP Query). You can find example template files in the plugin folder (templates/). You can store these templates in:

* plugin templates folder (or a sub folder) (inadvisable)
* your theme folder (or a sub folder)

The default header of a template looks like that:
<code>
/*
AP Template: Example Template
Description: This is the description.
Version: 1.0
Author: Your name
Author URI: http://example.com
Author E-Mail: yourname@example.com
*/
</code>

The only required information in the header is the "AP Template" line. So the Plugin knows this is an AP Template.


= For Theme Designers =
Feel free to create a template and add it to your theme. The plugin will find it automaticaly. You can find a working example file of a template in the plugin directory (*templates/title-excerpt-thumbnail.php*). Just copy it in your template directory and modify it until it fits your themes design.

If you want to disable the auto append feature of the plugin you can use the '*associated_posts_auto_append*' filter.
Just add this line of code to your *functions.php*:
<code>
Add_Filter ('associated_posts_auto_append', Create_Function('',' return False; ') );
</code>


= For real developers ;) =
As a real developer you can easily access to the associated posts via functions:

<code>
Global $wp_plugin_associated_posts;
$wp_plugin_associated_posts->Get_Associated_Posts ($page_id = Null){
/* $page_id: the id of the page which associated posts you want to read.
             if $page_id = Null, the plugin will read from current page.

   returns:  By default the function returns a WP_Query Object.
             The object is very well documented in the Codex.
             If there are no posts this function returns false.
*/
}
</code>

Real developers love the clout of their code. And as a real WordPress developer you know about the magic of hooks and filters. The Associated Posts Plugin uses a filter with the name '*associated_posts_template*'. You can use this filter to set the template file of the associated posts. (You can overwrite the users template option with this filter.) You can find an example file of this template in the plugin directory (*templates/title-excerpt-thumbnail.php*).


= Questions =
I know you have many questions – my mailbox is the proof. ;) But unfortunately I cannot give support for free plugins. There is a separate support package available for the [Pro Version](http://dennishoppe.de/en/wordpress-plugins/associated-posts-pro) of this plugin. Please use it. Of course you can hire me for consulting, support, programming and customizations at any time.


= In the Press =
* Post-Page-Associator has been granted the "Famous Software" Award! [To the post &raquo;](http://download.famouswhy.com/post_page_associator/)
* [Tom Altman](http://tomaltman.com/) said "*Why are posts and pages so oil and water in WordPress?  This plugin bridges the gap and makes them more like chocolate and peanut butter.*" [To the post &raquo;](http://tomaltman.com/post-page-association/)
* [Annie Stasse](http://www.penseelibre.fr/) posted "Association des pages avec billets, catégories, mots-clés". [To the post &raquo;](http://www.penseelibre.fr/association-des-pages-avec-billets-categories-mots-cles/)
* Nancy Golliday made this video: "Inserting Images and Using Featured Image in WordPress Post Page Associator". [To the post &raquo;](http://www.youtube.com/watch?v=9CjbWQRiZ1I)
* How to Use Wordpress as a Full CMS by Melody Clark. [To the post &raquo;](http://www.associatedcontent.com/article/5798146/how_to_use_wordpress_as_a_full_cms.html)
* 19 Must Have Plugins for a WordPress Blog. [To the post &raquo;](http://techpatel.com/19-must-have-plugins-for-a-wordpress-blog/)
* [CMSMind](http://www.cmsmind.com/) wrote "How to link Posts to a Page in WordPress" [To the post &raquo;](http://www.cmsmind.com/appending-posts-to-a-page/)
* [MONDOLINGUA](http://www.mondolingua.com/dcs/) schrieb "Artikel komfortabel auf bestimmten Seiten anzeigen" [To the post &raquo;](http://www.mondolingua.com/dcs/2011/09/wordpress-artikel-komfortabel-auf-bestimmten-seiten-anzeigen-post-page-associator/)


= Language =
* This Plugin is available in English.
* Dieses Plugin ist in Deutsch verfügbar. ([Dennis Hoppe](http://dennishoppe.de/))
* Cette extension est disponible en Français. ([Quentin Turquet](http://www.tradpress.fr/))


= Translate this plugin =
If you have translated this plugin in your language feel free to send me the language file (.po file) via E-Mail with your name and this translated sentence: "This plugin is available in %YOUR_LANGUAGE_NAME%." So I can add it to the plugin. Of course you get a backlink to your website!

You can find the *Translation.pot* file in the *language/* folder in the plugin directory.

* Copy it.
* Rename it (to your language code).
* Translate everything.
* Send it via E-Mail to &lt;Mail [@t] DennisHoppe [dot] de&gt;.
* Thats it. Thank you! =)


== Installation ==
Installation as usual.

1. Unzip and Upload all files to a sub directory in "/wp-content/plugins/".
1. Activate the plugin through the "Plugins" menu in WordPress.
1. Go to edit a page.
1. There is a new box with title "Associated Posts". Try it out! ;)


== Frequently Asked Questions ==
I am still collecting frequently asked questions. ;)


== Screenshots ==

1. Screenshot of the post selection box
2. Editor with [associated_posts] shortcode
3. Edit Mode of a static page
4. Associated Posts Widget
5. The options page


== Changelog ==

= 0.9.7 =
* Sticky posts will be displayed on top of your selection now.

= 0.9.6 =
* Fixed some incompatibilities that prevented the users from open the option boxes on the options page

= 0.9.5 =
* Add option to enable page numbers below multiple page contents.

= 0.9.4 =
* Added French translation by [Quentin Turquet](http://www.tradpress.fr/)

= 0.9.3 =
* Increased filter priority for content filter to avoid incompatibilities with other content filtering plugins

= 0.9.2 =
* Added more taxonomies

= 0.9.1 =
* Removed the "Delete Template" Button. (It had has no functionality.)
* Added more screenshots

= 0.9 =
* Completely rewritten and relaunched
