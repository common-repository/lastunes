<?php
/*
Plugin Name: lasTunes
Plugin URI: http://infinity.calenfretts.com/category/geek/wordpress/lastunes/
Description: Adds iTunes links to Last.fm user's Recently Played Tracks feed and displays them.
Version: 3.6.1
Author: Calen Fretts
Author URI: http://infiniteschema.com
*/

/*  Copyright 2013  Calen Fretts  (email : calen@calenfretts.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$plugin = plugin_basename(__FILE__);
$settings_link_url = 'themes.php?page=lasTunes_settings'; 

function lasTunes( $args = null ) {
	$returned = '';

	/*if ( $args && is_array($args) )
		extract($args); // currently does nothing
	*/

	$lastfmUsername = get_option( 'lasTunes_lastfmUsername' );
	if ( !$lastfmUsername || $lastfmUsername == "" )
		$lastfmUsername = "cfretts";

	$lastfmApikey = get_option( 'lasTunes_lastfmApikey' );
	if ( !$lastfmApikey || $lastfmApikey == "" )
		$lastfmApikey = "1fa5df8fa019e72b26fbc9fc8aa8229f";

	$lastfmLimit = get_option( 'lasTunes_lastfmLimit' );
	if ( !$lastfmLimit || !is_numeric($lastfmLimit) || $lastfmLimit < 1 )// anything > 100 will return 100
		$lastfmLimit = 10;

	$row_evn_style = get_option( 'lasTunes_row_evn_style' );
	if ( !$row_evn_style )
		$row_evn_style = "background-color:#CCCCCC;";

	$row_odd_style = get_option( 'lasTunes_row_odd_style' );
	if ( !$row_odd_style )
		$row_odd_style = "background-color:#999999;";

	$img_src_album = get_option( 'lasTunes_img_src_album' );
	if ( !$img_src_album || $img_src_album == "" )
		$img_src_album = "/wp-content/plugins/lastunes/images/album-16x16.png";

	$show_username = get_option( 'lasTunes_show_username' );
	$show_timestamp = get_option( 'lasTunes_show_timestamp' );
	$link_to_lastfm = get_option( 'lasTunes_link_to_lastfm' );
	$link_to_myspace = get_option( 'lasTunes_link_to_myspace' );
	$links_in_new = get_option( 'lasTunes_links_in_new' );
	$give_credit = get_option( 'lasTunes_give_credit' );

	if ($show_username) {
		$returned .= '<em>recently listened tracks of <a href="http://www.last.fm/user/' . $lastfmUsername . '"' . ( $links_in_new ? ' target="_blank"' : '' ) . '>' . $lastfmUsername . '</a></em>';
	}
	
	$returned .= '<table>';

	$lastfmURL = "http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=" . $lastfmUsername . "&api_key=" . $lastfmApikey . "&limit=" . $lastfmLimit;

	if($xml = simplexml_load_file($lastfmURL)) {
		$baseSearchURL = "http://phobos.apple.com/WebObjects/MZSearch.woa/wa/advancedSearchResults?";
		$baseAffURL = "http://click.linksynergy.com/fs-bin/stat?id=9k1rkt9wFm4&amp;offerid=146261&amp;type=3&amp;subid=1&amp;tmpid=1826&amp;RD_PARM1=";
		$baseLastfmURL = "http://www.last.fm/music/";
		$baseMyspaceURL = "http://searchservice.myspace.com/index.cfm?fuseaction=sitesearch.results&amp;type=Music&amp;qry=";
		
		$row_even = true;
		foreach($xml->recenttracks->track as $track) {
			$artist = $track->artist;
			$album = $track->album;
			$song = $track->name;

			//search vars are: term, artistTerm, albumTerm, songTerm. but, only one var can be used.
			$artistSearchURL = $baseSearchURL . "artistTerm=" . $artist;
			$albumSearchURL = $baseSearchURL . "term=" . $artist . " " . $album;
			$songSearchURL = $baseSearchURL . "term=" . $artist . " " . $song;

			$artistAffURL = $baseAffURL . urlencode($artistSearchURL);
			$albumAffURL = $baseAffURL . urlencode($albumSearchURL);
			$songAffURL = $baseAffURL . urlencode($songSearchURL);
			
			$row_style = ($row_even ? $row_evn_style : $row_odd_style);
			$returned .= '<tr>';
			$returned .= '<td style="' . $row_style . '"><a href="' . $artistAffURL . '" title="search iTunes">' . $artist . '</a></td>';
			$returned .= '<td style="' . $row_style . '"><a href="' . $songAffURL . '" title="search iTunes">' . $song . '</a></td>';
			$returned .= '<td style="' . $row_style . '">' . (($album && $album != "") ? '<a href="' . $albumAffURL . '" title="search iTunes"><img src="' . $img_src_album . '" alt="' . $album . '" /></a>' : '&nbsp;') . '</td>';
			if ($show_timestamp) {
				$returned .= '<td style="' . $row_style . '"><a href="' . $track->url . '">' . $track->date . '</a></td>';
			}
			if ($link_to_lastfm) {
				$returned .= '<td style="' . $row_style . '"><a href="' . $baseLastfmURL . str_replace("%20", "+", urlencode($artist)) . '"' . ( $links_in_new ? ' target="_blank"' : '' ) . ' title="Last.fm">L</a></td>';
			}
			if ($link_to_myspace) {
				$returned .= '<td style="' . $row_style . '"><a href="' . $baseMyspaceURL . $artist . '"' . ( $links_in_new ? ' target="_blank"' : '' ) . ' title="MySpace">M</a></td>';
			}
			$returned .= '</tr>';
			
			$row_even = !$row_even;
		}
	} else {
	}

	$returned .= '</table><p style="' . ( $give_credit ? '' : 'display:none;' ) . '"><a href="http://infinity.calenfretts.com/category/geek/wordpress/lastunes/">lasTunes</a> by <a href="http://infiniteschema.com">Infinite Schema</a></p>';

	return $returned;
}

function lasTunes_register() {
function lasTunes_sidebar_widget( $args ) {
	extract($args); // extracts before_widget,before_title,after_title,after_widget
	echo $before_widget . $before_title . 'lasTunes' . $after_title;
	echo lasTunes();
	echo $after_widget;
}

register_sidebar_widget('lasTunes', 'lasTunes_sidebar_widget');
}

add_action('init', lasTunes_register);

function lasTunes_add_pages() {
	add_theme_page( 'lasTunes settings', 'lasTunes', 10, 'lasTunes_settings', 'lasTunes_settings_page');
}

function lasTunes_settings_page() {
	global $settings_link_url;
	$hidden_field_name = 'field_submit_hidden';
	$page_options = '';

	$opt_names[] = 'lasTunes_lastfmUsername';
	$opt_label[] = 'last.fm Username';

	$opt_names[] = 'lasTunes_lastfmApikey';
	$opt_label[] = 'last.fm Apikey';

	$opt_names[] = 'lasTunes_lastfmLimit';
	$opt_label[] = 'last.fm Tracks Shown';

	$opt_names[] = 'lasTunes_row_evn_style';
	$opt_label[] = 'Even Row Style';

	$opt_names[] = 'lasTunes_row_odd_style';
	$opt_label[] = 'Odd Row Style';

	$opt_names[] = 'lasTunes_img_src_album';
	$opt_label[] = 'Album Img Src';

	$opt_yn_names[] = 'lasTunes_show_username';
	$opt_yn_label[] = 'Show Username?';

	$opt_yn_names[] = 'lasTunes_show_timestamp';
	$opt_yn_label[] = 'Show Timestamp?';

	$opt_yn_names[] = 'lasTunes_link_to_lastfm';
	$opt_yn_label[] = 'Link to Artist Last.fm Page?';

	$opt_yn_names[] = 'lasTunes_link_to_myspace';
	$opt_yn_label[] = 'Link to Artist MySpace Page?';

	$opt_yn_names[] = 'lasTunes_links_in_new';
	$opt_yn_label[] = 'Open Links in New Page?';

	$opt_yn_names[] = 'lasTunes_give_credit';
	$opt_yn_label[] = 'Give lasTunes Credit?';

	$opt_num = count($opt_names);
	$opt_yn_num = count($opt_yn_names);
	
	if( $_POST[ $hidden_field_name ] == 'Y' ) {
		for ($i=0; $i<$opt_num; $i++) {
			$opt_value[$i] = $_POST[ $opt_names[$i] ];
			update_option( $opt_names[$i], $opt_value[$i] );
		}
		for ($i=0; $i<$opt_yn_num; $i++) {
			$opt_yn_value[$i] = isset( $_POST[ $opt_yn_names[$i] ] );
			update_option( $opt_yn_names[$i], $opt_yn_value[$i] );
		}
		echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
	} else {
		for ($i=0; $i<$opt_num; $i++) {
			$opt_value[$i] = get_option( $opt_names[$i] );
		}
		for ($i=0; $i<$opt_yn_num; $i++) {
			$opt_yn_value[$i] = get_option( $opt_yn_names[$i] );
		}
	}
?>
<div class="wrap">
<h2>lasTunes</h2>

<form method="post" action="<?php echo $settings_link_url; ?>">
<?php wp_nonce_field('update-options'); ?>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<table class="form-table">

<?php
	for ($i=0; $i<$opt_num; $i++) {
		$page_options .= $opt_names[$i] . ',';
		echo '<tr valign="top">';
		echo '<th scope="row">' . $opt_label[$i] . '</th>';
		echo '<td><input type="text" id="' . $opt_names[$i] . '" name="' . $opt_names[$i] . '" value="' . $opt_value[$i] . '" size="50" /></td>';
		echo '</tr>';
	}
	for ($i=0; $i<$opt_yn_num; $i++) {
		$page_options .= $opt_yn_names[$i] . ',';
		echo '<tr valign="top">';
		echo '<th scope="row">' . $opt_yn_label[$i] . '</th>';
		echo '<td><input type="checkbox" id="' . $opt_yn_names[$i] . '" name="' . $opt_yn_names[$i] . '" ' . ( $opt_yn_value[$i] ? 'checked="checked"' : '' ) . '/></td>';
		echo '</tr>';
	}
?>

</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="<?php echo $page_options; ?>" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

</form>
</div>
<?php
}

add_action('admin_menu', 'lasTunes_add_pages');

function lasTunes_activation_redirect() {
	global $settings_link_url;
	include($settings_link_url);
	exit;
}

function lasTunes_activation_hook() {
	global $settings_link_url;
	add_option('lasTunes_lastfmUsername', 'cfretts');
	add_option('lasTunes_lastfmApikey', '1fa5df8fa019e72b26fbc9fc8aa8229f');
	add_option('lasTunes_lastfmLimit', '10');
	add_option('lasTunes_row_evn_style', 'background-color:#CCCCCC;');
	add_option('lasTunes_row_odd_style', 'background-color:#999999;');
	add_option('lasTunes_img_src_album', '/wp-content/plugins/lastunes/images/album-16x16.png');
	add_option('lasTunes_show_username', true);
	add_option('lasTunes_show_timestamp', false);
	add_option('lasTunes_link_to_lastfm', false);
	add_option('lasTunes_link_to_myspace', false);
	add_option('lasTunes_links_in_new', true);
	add_option('lasTunes_give_credit', true);

//	add_action('admin_notices', create_function( '', "echo '<div class=\"error\">Please update your <a href=\"".get_bloginfo('wpurl')."/wp-admin/$settings_link_url\">lasTunes settings</a>.</div>';" ) );//not working
//	add_action('template_redirect', 'lasTunes_activation_redirect');//not working
}

if ( function_exists('register_activation_hook') )
	register_activation_hook( __FILE__, 'lasTunes_activation_hook' );

function lasTunes_deactivation_hook() {
}

//if ( function_exists('register_deactivation_hook') )
	//register_deactivation_hook( __FILE__, 'lasTunes_deactivation_hook' );

function lasTunes_install_hook() {
}

//if ( function_exists('register_install_hook') )
	//register_install_hook( __FILE__, 'lasTunes_install_hook' );

function lasTunes_uninstall_hook() {
	delete_option('lasTunes_lastfmUsername');
	delete_option('lasTunes_lastfmApikey');
	delete_option('lasTunes_lastfmLimit');
	delete_option('lasTunes_row_evn_style');
	delete_option('lasTunes_row_odd_style');
	delete_option('lasTunes_img_src_album');
	delete_option('lasTunes_show_username');
	delete_option('lasTunes_show_timestamp');
	delete_option('lasTunes_link_to_lastfm');
	delete_option('lasTunes_link_to_myspace');
	delete_option('lasTunes_links_in_new');
	delete_option('lasTunes_give_credit');
}

if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook( __FILE__, 'lasTunes_uninstall_hook' );

function lasTunes_action_links( $links ) {
	global $settings_link_url;
	$settings_link = '<a href="' . $settings_link_url . '">Settings</a>'; 
	array_unshift( $links, $settings_link ); 
	return $links;
}

add_filter("plugin_action_links_$plugin", 'lasTunes_action_links' );

function lasTunes_shortcode_handler($atts, $content = null) {
	/*extract(shortcode_atts(array(
		'foo' => 'no foo',
		'bar' => 'default bar',
	), $atts));*/

	return lasTunes();
}

add_shortcode('lasTunes', 'lasTunes_shortcode_handler');

?>