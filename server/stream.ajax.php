<?php
/*

 Copyright (c) 2001 - 2007 Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License v2
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

/**
 * Sub-Ajax page, requires AJAX_INCLUDE as one
 */
if (AJAX_INCLUDE != '1') { exit; } 

switch ($_REQUEST['action']) { 
	case 'set_play_type': 
		// Make sure they have the rights to do this
		if (!Preference::has_access('play_type')) { 
			$results['rfc3514'] = '0x1'; 
			break;
		} 
	
		// Go ahead and update their preference
		Preference::update('play_type',$GLOBALS['user']->id,$_POST['type']); 
	break;
	case 'basket': 
		// We need to set the basket up!
		$_SESSION['iframe']['target'] = Config::get('web_path') . '/stream.php?action=basket&playlist_method=' . scrub_out($_REQUEST['playlist_method']); 
		$results['rfc3514'] = '<script type="text/javascript">reload_util("'.$_SESSION['iframe']['target'].'")</script>'; 
	break;
	default: 
		$results['rfc3514'] = '0x1'; 
	break;
} // switch on action; 

// We always do this
echo xml_from_array($results); 
?>
