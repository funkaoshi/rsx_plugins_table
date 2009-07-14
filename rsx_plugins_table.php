<?php

// Either copy classTextile.php to your plugin directory, or uncomment the following
// line and edit it to give the location where classTextile.php can be found
#ini_set('include_path', ini_get('include_path') . ':/full/path/to/textile');

// Plugin name is optional.  If unset, it will be extracted from the current file name.
// Uncomment and edit this line to override:
# $plugin['name'] = 'abc_plugin';

$plugin['version'] = '1.2';
$plugin['author'] = 'Ramanan Sivaranjan';
$plugin['author_uri'] = 'http://funkaoshi.com/';
$plugin['description'] = 'Display a list of plugins currently installed.';

compile_plugin();
exit;

?>
# --- BEGIN PLUGIN HELP ---

<h1>RSX Plugins Table</h1>

<p>This plugin will display a table containing the basic information about
the plugins you currently have installed in your textpattern system. The table
can be styled using CSS, as the table has been given the class
<code>plugins-table</code>. There are currently no special options or
switches. To use, simply include the tag <code>&lt;txp:rsx_plugins_table
/&gt;</code> anywhere you want this table to be displayed.</p>

<p>You can use the parameter <code>show_inactive</code> to decide if you want
to list active or inactive plugins. If it is set to 1, which is the default,
inactive plugins will be listed as well as active plugins. If it is set to 0,
then only active plugins are displayed.</p>

<p>You can use the parameter <code>show_description</code> to decide if you want
to include the description of the plugin in your table or not. If it is set to 1, 
which is the default, the description of the plugin will be listed. If it is set 
to 0, then no description will be listed.</p>

# --- END PLUGIN HELP ---
<?php


# --- BEGIN PLUGIN CODE ---

function rsx_plugins_table($atts)
{
	extract(lAtts(array('show_inactive' => 1, 'show_description' => 1),$atts));

	$out = '';
	$even = false;
	$rs = safe_rows("name, author, author_uri, version, description, status"
					, "txp_plugin"
					, "1=1");
	if ($rs) {
		$out .= '<table>';
		$out .= '<tr><th>Name</th><th>Author</th><th>Version</th>';
		if ( $show_description )
		    $out .='<th>Description</th>';
		$out .= $show_inactive ? '<th>Active?</th></tr>' : '</tr>';
		foreach($rs as $var) {
			extract($var);
			if ( $status || !$status && $show_inactive) {	
				$out .= '<tr class="'.($even ? 'even' : 'odd').'">';
				$out .= '<td>'.$name.'</td>';
				$out .= '<td><a href="'.$author_uri.'">'.$author.'</a></td>';
				$out .= '<td>'.$version.'</td>';
				if ( $show_description )
				    $out .= '<td>'.$description.'</td>';
				if ( $show_inactive )
				    $out .= '<td>'.($status ? 'Yes' : 'No').'</td>';
				$out .= '</tr>';
				$even = ($even ? false : true);
			}
		}
		$out .= '</table>';
	}
	return $out;
}

# --- END PLUGIN CODE ---


// -----------------------------------------------------

function extract_section($lines, $section) {
	$result = "";
	
	$start_delim = "# --- BEGIN PLUGIN $section ---";
	$end_delim = "# --- END PLUGIN $section ---";

	$start = array_search($start_delim, $lines) + 1;
	$end = array_search($end_delim, $lines);

	$content = array_slice($lines, $start, $end-$start);

	return join("\n", $content);

}

function compile_plugin() {
	global $plugin;

	if (!isset($plugin['name'])) {
		$plugin['name'] = basename(__FILE__, '.php');
	}

	# Read the contents of this file, and strip line ends
	$content = file(__FILE__);
	for ($i=0; $i < count($content); $i++) {
		$content[$i] = rtrim($content[$i]);
	}

	$plugin['help'] = extract_section($content, 'HELP');
	$plugin['code'] = extract_section($content, 'CODE');

	@include('classTextile.php');
	if (class_exists('Textile')) {
		$textile = new Textile();
		$plugin['help'] = $textile->TextileThis($plugin['help']);
	}

	$plugin['md5'] = md5( $plugin['code'] );

	// to produce a copy of the plugin for distribution, load this file in a browser. 

	echo chr(60)."?php\n\n".'$'."plugin='" . base64_encode(serialize($plugin)) . "'\n?".chr(62);

}

?>
