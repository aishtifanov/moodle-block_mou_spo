<?php // $Id: changestatus.php,v 1.2 2010/10/29 11:58:25 Oleg Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');

    $rid = required_param('rid', PARAM_INT);       // Rayon id
    $oid = required_param('oid', PARAM_INT);       // OU id
    $fid = required_param('fid', PARAM_INT);       // Form id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $nm = required_param('nm', PARAM_INT);         // Month
    $typeou = optional_param('typeou', '-');       // Type OU
    $shortname = required_param('sn');       // Shortname form

	$confirm = optional_param('confirm', 0, PARAM_INT);
    $status = optional_param('status', 0, PARAM_INT);

    if ($confirm == 0 && $status == 0)  {
	    $status3 = optional_param('status3', '');
        if (!empty($status3)) $status = 3;
        else {
		    $status5 = optional_param('status5', '');
        	if (!empty($status5)) $status = 5;
	        else {
			    $status6 = optional_param('status6', '');
 		       	if (!empty($status6)) $status = 6;
 		    }
        }
	}

	$redirlink = "listforms.php?typeou=$typeou&rid=$rid&oid=$oid&nm=$nm&yid=$yid&fid=$fid";
    
    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&amp;yid=$yid&amp;rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $strrayon = get_string('rayon', 'block_monitoring');
    $strrayons = get_string('rayons', 'block_monitoring');
    $strschool = get_string('school', 'block_monitoring');
    $strschools = get_string('schools', 'block_monitoring');
    $strreports = get_string('reportschool', 'block_monitoring');
    $strrep = get_string('reports', 'block_monitoring');


   	$strtitle = get_string('title','block_mou_spo');
	$strscript = get_string('begindata', 'block_monitoring');
	$strformname = get_string('name_'.$shortname,'block_mou_spo');
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_spo/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => "listforms.php?rid=$rid&oid=$oid&typeou=$typeou", 'type' => 'misc');
    $navlinks[] = array('name' => $strformname, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

	if ($fid == 0) {
		notice(get_string('changestatusnew','block_monitoring'), $redirlink);
	}

	if ($confirm == 1) {
	    $datefrom = get_date_from_month_year($nm, $yid);
	    $strsql = "SELECT * FROM {$CFG->prefix}monit_rating_listforms
 		   		   WHERE (collegeid=$oid) and (shortname='$shortname') and (datemodified=$datefrom)";
                   
	    if ($rec = get_record_sql($strsql))	{
             // print_r($rec);
           $rec->status = $status;
	       if (!update_record('monit_rating_listforms', $rec))	{
				error(get_string('errorinupdatingform','block_monitoring'), $redirlink);
		   }
		   redirect($redirlink, get_string('succesupdatedata','block_monitoring'), 1);
		}
	}


    if ($status == 4)	{
		$s1 = get_string('changestatuscoordination', 'block_monitoring', $strformname);
    } else {
		print_heading(get_string('changestatus', 'block_monitoring') .' :: ' .$strformname);
		//  $s1 = get_string('changestatuscheckfull', 'block_monitoring', ' школе &laquo;'. $school->name.'&raquo;');
		$s1 = get_string('changestatuscheckfull', 'block_monitoring'). ' ' . $strformname . " на '". get_string('status'.$status, 'block_monitoring') . "'?";
	}

	notice_yesno($s1, "changestatus.php?typeou=$typeou&rid=$rid&amp;oid=$oid&amp;fid=$fid&amp;nm=$nm&amp;yid=$yid&amp;sn=$shortname&amp;status=$status&amp;confirm=1", $redirlink);

	print_footer();


?>

