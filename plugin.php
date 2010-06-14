<?php
/*
Plugin Name: Genesis Style Select
Plugin URI: http://designsby.nickgeek.com/2010/06/13/genesis-style-select/
Description: Genesis Style Select is allows users to select which style sheet they will use in any Genesis Child theme by StudioPress.
Version: 0.6.1b
Author: Nick Croft
Author URI: http://DesignsBy.NickGeek.com/
*/

/*
 * To do:
 * 		Setup Plugin URI
 * 		
 * 		
 * 
 * This is an early beta and I would love any continuing advice on how to streamline this.
 * 
 */

// require Genesis upon activation 
register_activation_hook(__FILE__, 'styleselect_activation_check');
function styleselect_activation_check() {
		
		$theme_info = get_theme_data(TEMPLATEPATH.'/style.css');
	
        if( basename(TEMPLATEPATH) != 'genesis' ) {
	        deactivate_plugins(plugin_basename(__FILE__)); // Deactivate ourself
            wp_die('Sorry, you can\'t activate unless you have installed <a href="http://www.studiopress.com/themes/genesis">Genesis</a>');
		}

}

// Add new box to the Genesis -> Theme Settings page
add_action('genesis_init', 'ntg_add_style_settings_init');
function NtG_add_style_settings_init() {
    add_action('admin_menu', 'ntg_add_style_settings_box', 20);
}
function ntg_add_style_settings_box() {
    global $_genesis_theme_settings_pagehook;
    add_meta_box('genesis-theme-settings-style', __('Style Select', 'genesis'), 'ntg_theme_settings_style_box', $_genesis_theme_settings_pagehook, 'column2', 'high');
}

function ntg_theme_settings_style_box() {
    // set the default selection (if empty)
    $style = genesis_get_option('style_selection') ? genesis_get_option('style_selection') : 'style.css';
?>

    <p><label><?php _e('Style Sheet', 'genesis'); ?>: 
        <select name="<?php echo GENESIS_SETTINGS_FIELD; ?>[style_selection]">
            <?php
            foreach ( glob(CHILD_DIR . "/*.css") as $file ) :
            $file = str_replace( CHILD_DIR . '/', '', $file );
            
            if(!genesis_style_check($file, 'genesis')){
            continue;
            }
            
            ?>
                
            <option style="padding-right:10px;" value="<?php echo esc_attr( $file ); ?>" <?php selected($file, $style); ?>><?php echo esc_html( $file ); ?></option>
            
            <?php 
            
            endforeach; ?>
        </select>
    </label></p>
    <p><span class="description">Please select your desired <b>Style Sheet</b> from the drop down list and save your settings.</span></p>
    <p><span class="description"><b>Note:</b> Only Genesis style sheets in the Child theme directory will be included in the list.</span></p>
<?php
}

// Checks if the style sheet is a Genesis style sheet
function genesis_style_check($fileText, $char_list) {

	$fh = fopen(CHILD_DIR . '/' . $fileText, 'r');
	$theData = fread($fh, 500);
	fclose($fh);
	
	$search = strpos($theData, $char_list);
	if($search === false){
	        return false;
	    }
	    return true;
}

// Changes the style sheet per the selection in the theme settings and loads style.css if selected style sheet is not available
add_filter('stylesheet_uri', 'child_stylesheet_uri', 10, 2);
function child_stylesheet_uri($stylesheet, $dir) {
    $style = genesis_get_option('style_selection');
    if ( !$style ) return $stylesheet;
    if (!file_exists(CHILD_DIR . '/' . $style)) return $stylesheet;
    
    return $dir . '/' . $style;
}