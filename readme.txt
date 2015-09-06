=== Widgetized CPTs ===
Contributors: jcastaneda
Tags: widget, custom-post-type
Requires at least: 2.8
Tested up to: 4.4-alpha-33934

== Description ==
Display a list of the latests content from a custom post type in a widgetized area of your theme. All you have to do is pick they type and how many to list.

== Frequently Asked Questions ==

= Why does it only display my pages? =

By default, this plugins will show pages if there are no custom post types found.

= Why does it only show my titles? =

It is the default behaviour of the the plugin. You can change by using the provided filter within the plugin and including the code in your theme's functions file. Bare in mind best practice is you use a child theme just in case the theme does get updated. You can use something like:
`
add_filter( 'wcpts_item', 'my_custom_content_block' );
function my_custom_content_block(){
	$content = get_the_content();
	$string = '<div class="my-content"';
	$string .= $content;
	$string .= '</div>';
	
	return $string;
}
`

== Changelog ==

= 0.1.0 =

* Initial release
