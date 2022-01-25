<?php // $Id: authbase.inc.php,v 1.4 2011/04/14 13:02:57 shtifanov Exp $

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = optional_param('sid', 0, PARAM_INT);     // School id
    $yid = optional_param('yid', 0, PARAM_INT);     // Year id
    $action = optional_param('action', '');

	require_login();

    // if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    // }

	$scriptname = basename($_SERVER['PHP_SELF']);	// echo '<hr>'.basename(me());
	$arrscriptname = explode('.', $scriptname);
	
	$strlistrayons  =  listbox_rayons_role("$scriptname?sid=0&amp;yid=$yid&amp;rid=", $rid);
	$strlistschools =  listbox_udods_role("$scriptname?rid=$rid&amp;yid=$yid&amp;sid=", $rid, $sid, $yid);

	if (!$strlistrayons && !$strlistschools)   { 
		error(get_string('permission', 'block_mou_culture'), '../index.php');
	}	
	
 	if ($action != 'excel') 	{
		$strtitle = get_string('title','block_mou_culture');
		$strscript = get_string($arrscriptname[0],'block_mou_culture');
	
	    $navlinks = array();
	    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_culture/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid", 'type' => 'misc');
	    if (isset($breadcrumbs))	{
	    	foreach ($breadcrumbs as $breadcrumb)	{
	    		$navlinks[] = array('name' => $breadcrumb->name, 'link' => $breadcrumb->link, 'type' => 'misc');
	    	}
	    }
	    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
	    $navigation = build_navigation($navlinks);
	
	    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)
	
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		echo $strlistrayons;
		echo $strlistschools;
		echo '</table>';
	
		if ($rid == 0 || $sid == 0) {
		    print_footer();
		 	exit();
		}
	
		if ($strlistrayons == false || $strlistschools == false) {
			error(get_string('permission', 'block_mou_culture'), '../index.php');
		}	
	
		// print_tabs_years_link("$scriptname?", $rid, $sid, $yid);
	}		

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);	

	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	has_capability('moodle/role:assign', $context_rayon);
	
	$context_region = get_context_instance(CONTEXT_REGION, 1);
	has_capability('moodle/role:assign', $context_region);

?>