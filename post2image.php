<?php
/**
 * Plugin Name: Post To Image
 * Description: Post To Image
 * Author:      Evolink
 * Version:     1.0
 */

add_action('admin_menu', 'post2image_setting_menu');

function post2image_setting_menu() {
    add_submenu_page( 'tools.php', 'post2image', 'Post To Image', 'manage_options', 'wp-post2image', 'post2image_setting_page' ); 

}

function post2image_setting_page() {
    echo '<div class="wrap">';
    post2image_admin();
    echo '</div>';
}

function post2image_admin() {
echo <<<TXT
<h1>Post To Image: how to use</h1>
<p>Insert <code>[post2image]</code> shortcode in any post.<br>
Image will be generated and inserted in your post.</p>

<p>Defaul path (folders will be created if not exist):<br>
<code>https://{your_domain.zone}/wp-content/uploads/{folder}/{prefix}{slug}.jpg</code></p>

<p>Here is attributes and it defaul values:<br>
<code>[post2image folder="post2image" prefix="post2image-" c_back="#ddd" c_text="#222" title="{title}" alt="{title}" crop="2500"]</code></p>
TXT;
}

// Registering shortcode [post2image]
add_shortcode('post2image', 'post2image_shortcode');

require "post2image_lib.php"; 

function post2image_shortcode( $atts ) {
	
	
	global $post;
    $post_name = $post->post_name;
    $post_title = $post->post_title;
	$post_date = get_the_date();
	$content = get_the_content();
	
	
	$attr_alt = trim($atts['alt']);
	$attr_title = trim($atts['title']);
	
	if(empty($attr_alt)) $attr_alt = '{title}';
	if(empty($attr_title)) $attr_title = '{title}';
	
	$attr_alt = str_replace('{words}', round(strlen($content)/6,0), $attr_alt);
	$attr_title = str_replace('{words}', round(strlen($content)/6,0), $attr_title);
	$attr_alt = str_replace('{title}', $post_title, $attr_alt);
	$attr_title = str_replace('{title}', $post_title, $attr_title);
	
	// $title = "«" . $post_title . "» Essay Example (".round(strlen($content)/6,0)." words)";
	// title="«{title}» Essay Example ({words} words)"
	
	$folder = $atts['folder'];
	$prefix = $atts['prefix'];
	$c_back = $atts['bg'];
	$c_text = $atts['text'];
	$substr = $atts['crop'];
	if(!preg_match('/^[0-9]+$/',$substr)) $substr = 2500;
	
	$content = $attr_title . "\r\n".
	"-------------------------------------\r\n\r\n".
	strip_tags(substr($content,0,$substr))."...";
	
	
	if(!preg_match('/^[A-Za-z0-9-_]+$/',$folder)) $folder = 'post2image';
	if(!preg_match('/^[A-Za-z0-9-_]+$/',$prefix)) $prefix = 'post2image-';
	if(!preg_match('/^\#[1234567890ABCDEFabcdef]{3,6}$/',$c_back)) $c_back = '#ddd';
	if(!preg_match('/^\#[1234567890ABCDEFabcdef]{3,6}$/',$c_text)) $c_text = '#222';
	
	$post2image = new Priler\Text2Image\Magic( $content );
	
	//return plugin_dir_path( __FILE__ ) . 'PTSerif.ttf';
	
	$post2image->set_mode('smart');
	$post2image->add_font('PTSerif', WP_PLUGIN_DIR . '/post2image/PTSerif.ttf'); 
	$post2image->font = $post2image->get_font('PTSerif');
	$post2image->padding = 120;
	$post2image->width = 1200;
	$post2image->text_size = 24;
	$post2image->background_color = $c_back;
	$post2image->text_color = $c_text;
	
	
	$dir = WP_CONTENT_DIR.'/uploads/'.$folder;
	
	if (!file_exists($dir)) {
		wp_mkdir_p($dir);
	}
	
	$file = WP_CONTENT_DIR.'/uploads/'.$folder.'/'.$prefix.$post_name.'.jpg';
	$href = WP_CONTENT_URL.'/uploads/'.$folder.'/'.$prefix.$post_name.'.jpg';
	
	if(!file_exists($file))
		$post2image->save( $file , 'jpg');
	
	return '<a href="'.$href.'" target="_blank"><img src="'.$href.'" alt="'.$attr_alt.'" title="'.$attr_title.'" width="100%" /></a>';
}
