<?php // $Id: listforms.php,v 1.16 2012/11/14 10:58:53 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
	require_once('../monitoring/lib_excel.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('lib_spo.php');
    
    $rid = required_param('rid', PARAM_INT);            // Rayon id
    // $sid = optional_param('sid', 0, PARAM_INT);            // School id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $yid = optional_param('yid', 0, PARAM_INT);       		// Year id
    $fid = optional_param('fid', 0, PARAM_INT);       // Form id
    $typeou = optional_param('typeou', '-');       // Type OU
    // $nm  = optional_param('nm', 9, PARAM_INT);  // Month number
    $action   = optional_param('action', '');
    $nm = 9;
    
    $scriptname = 'listforms.php';

    $curryearid = get_current_edu_year_id();
    if ($yid != 0)	{
    	$eduyear = get_record('monit_years', 'id', $yid);
    } else {
    	$yid = $curryearid;
    	$eduyear = get_record('monit_years', 'id', $yid);
    }

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&amp;yid=$yid&amp;rid=", $rid);
	// $strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons)   {  // && !$strlisttypeou 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

		
	
    if ($action == 'excel') 	{
        $rkps = array('rating_spo');
        $datefrom = get_date_from_month_year($nm, $yid);
        print_excel_header('rating_'.$oid.'_'.$nm.'_all');
		create_excel_workbook();
	    foreach($rkps as $rkp)	{
			// print_excel_form('rkp_prr_ro', $datefrom);
            print_excel_form($rkp, $datefrom, 'rating', $rid, $oid, $yid, '', false, true);
		}
		close_excel_workbook();
        exit();
	}
/*
    $strrayon = get_string('rayon', 'block_monitoring');
    $strrayons = get_string('rayons', 'block_monitoring');

    $strschools = get_string('schools', 'block_monitoring');
    $strreports = get_string('reportschool', 'block_monitoring');
*/
   	$strtitle = get_string('title','block_mou_spo');
	$strscript = get_string('begindata', 'block_monitoring');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_spo/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;
    $ret =  '<tr> <td>Тип образовательной организации: </td><td>';
	$ret .= "профессиональная образовательная организация";
	$ret .= '</td></tr>';
    echo $ret;

    if ($rid > 0)   {
	// echo $strlisttypeou;
	// echo $typeou;
	// if ($typeou != '-')	{
		if ( $strlistou = listbox_college_role("$scriptname?rid=$rid&amp;yid=$yid&amp;typeou=$typeou&amp;oid=", $rid, $typeou, $oid, $yid))	{
			echo $strlistou;
		} else {
			echo '</table>';
			notice(get_string('ounotfound', 'block_mou_att'), "../index.php?rid=$rid&amp;yid=$yid");
		}	
	// }
    } 
	echo '</table>';
       
	
	if ($rid != 0 && $oid != 0)   {
        /*
    	$REGIONCRITERIA = new stdClass();
    	init_region_criteria($yid);
    	$timedenied = time();
    	if (!$admin_is && !$region_operator_is)	{ 
    		if ($timedenied > $REGIONCRITERIA->timeaccessdenied)	{  
    			//    $str1 = $strreports.': '.$school->name . get_string('zauchyear', 'block_monitoring', $eduyear->name);
    			notice(get_string('accessdenied', 'block_monitoring'), $CFG->wwwroot.'/blocks/monitoring/index.php');
    		}
    	}
        */	
            print_tabs_years_rating_spo("listforms.php?nm=$nm", $rid, $oid, $yid);  
       
            $sql = "SELECT st.cod FROM mdl_monit_college c
                    inner join mdl_monit_school_type st on st.id=c.typeinstitution
                    where c.id=$oid";    
            $typeou = get_field_sql($sql);
            // echo $typeou;
       
            get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
            
        	$context = get_context_instance($CONTEXT_OU, $oid);
            $view_capability = has_capability('block/mou_att2:viewou', $context);
            $edit_capability = has_capability('block/mou_att2:editou', $context);
        	if ($view_capability || $edit_capability_region || $edit_capability_rayon)	{
        	
           	    $table = table_begindata($rid, $oid, $yid, $nm, $typeou);
               	// print_table($table);
               	print_color_table($table);
        
            	$options = array('action'=> 'excel', 'rid' => $rid, 'oid' => $oid, 'yid' => $yid, 
            					 'fid' => $fid,  'nm' => $nm,  'sesskey' => $USER->sesskey);
               	echo '<center>';
                print_single_button("listforms.php", $options, get_string('downloadexcel'));
                echo '</center>';
        	} else {
        		error(get_string('permission', 'block_mou_school'), '../index.php');
        	}  

 
        /*
        if ($yid >= NEW_CRITERIA_YEARID)  {
            $strtimeclose = date('d.m.Y\г\. \в H:i', $REGIONCRITERIA->timeaccessdenied);
        	notify("<b><i>Внимание! $strtimeclose доступ к исходным данным системы рейтингования ОУ будет закрыт!</i></b>");
        }     
        */
    }
    print_footer();


function table_begindata($rid, $oid, $yid, $nm, $typeou)	
{
	global $CFG, $view_capability, $edit_capability_region, $edit_capability_rayon;

    $rkps = array('rating_spo');
    	
	$datefrom = get_date_from_month_year($nm, $yid);
	
    $strstatus = get_string('status', 'block_monitoring');
    // $strname = get_string('territory', 'block_monitoring');
	$strtable = get_string('table','block_monitoring');
 	$strperiod = get_string('period','block_monitoring');
	$straction = get_string("action","block_monitoring");

    $table = new stdClass();
    $table->head  = array ($strstatus, $strtable, $straction);
    $table->align = array ("center", "center", "center");
	$table->width = '60%';
    $table->size = array ('10%', '30%', '5%');
	$table->class = 'moutable';

    foreach($rkps as $rkp)	{
		$links = array();
        
        $razdel = get_record_select('monit_razdel', "shortname = '$rkp'", 'id, name');

	    $strsql = "SELECT * FROM {$CFG->prefix}monit_rating_listforms
 		   		   WHERE (collegeid=$oid) and (shortname='$rkp') and (datemodified=$datefrom)";

 		if ($recsss = get_records_sql($strsql)) 	{
 			// print_r($recsss);
 		    if (count($recsss) > 1) {
 		    	notify (get_string('errorinduplicatedform', "block_monitoring"));
	 		    print_r($recsss);
                echo '<hr>';
	 		}
	 		unset ($recsss);
 		}

	    if ($rec = get_record_sql($strsql))	{
	    	$fid = $rec->id;
			$strformrkpu_status = get_string('status'.$rec->status, "block_monitoring");
			$strcolor = get_string('status'.$rec->status.'color',"block_monitoring");
			//$strformrkpu = $rec->shortrusname;
			$strformrkpu = $razdel->name;// get_string('name_'.$rkp, "block_monitoring");
			$currstatus = $rec->status;
		} else {
	      	$fid = 0;
	    	$strformrkpu_status = get_string("status1","block_monitoring");
	    	$strcolor = get_string("status1color","block_monitoring");
			$strformrkpu = $razdel->name;// get_string('name_'.$rkp, "block_monitoring");
			$currstatus = 1;
	    }

        $alink = "typeou=$typeou&rid=$rid&amp;oid=$oid&amp;nm=$nm&amp;yid=$yid&amp;sn=$rkp";
		if ($currstatus < 4 || ( $edit_capability_region || $edit_capability_rayon))  {       //
           // if ($curryearid == $yid || $admin_is)	{
                $links['edit'] = new stdClass();
		 		$links['edit']->url = "htmlforms.php?$alink&fid=";
	 			$links['edit']->title = get_string('editschool','block_monitoring');
		 		$links['edit']->pixpath = "{$CFG->pixpath}/i/edit.gif";
	 		// }
	 	}

		if ($currstatus != 1 && $currstatus < 4)  {
		    $links['status4'] = new stdClass();
	 		$links['status4']->url = "changestatus.php?$alink&status=4&fid=";
	 		$links['status4']->title = get_string('sendtocoordination', 'block_monitoring');
	 		$links['status4']->pixpath = "{$CFG->pixpath}/s/yes.gif";
        }

		if ($currstatus > 1 && ($edit_capability_region || $edit_capability_rayon)) { //  || $rayon_operator_is)) {
		    $links['status6'] = new stdClass();
	 		$links['status6']->url = "changestatus.php?$alink&status6=6&amp;fid=";
	 		$links['status6']->title = get_string('status6', 'block_monitoring');
	 		$links['status6']->pixpath = "{$CFG->pixpath}/i/tick_green_big.gif";
            
            $links['status3'] = new stdClass();
	 		$links['status3']->url = "changestatus.php?$alink&status3=3&amp;fid=";
	 		$links['status3']->title = get_string('status3', 'block_monitoring');
	 		$links['status3']->pixpath = "{$CFG->pixpath}/i/return.gif";
	 	}

		if ($currstatus >= 6 && ( $edit_capability_region || $edit_capability_rayon)) {
		    $links['status5'] = new stdClass();
	 		$links['status5']->url = "changestatus.php?$alink&status5=5&amp;fid=";
	 		$links['status5']->title = get_string('status5', 'block_monitoring');
	 		$links['status5']->pixpath = "{$CFG->wwwroot}/blocks/monitoring/i/archive.gif";
	 	}
        $links['excel'] = new stdClass();
 		$links['excel']->url = "to_excel.php?level=rating&$alink&action=excel&amp;fid=";
 		$links['excel']->title = get_string('downloadexcel');
 		$links['excel']->pixpath = "{$CFG->pixpath}/f/xlsx.gif";

        if ($currstatus >= 6)  {
        	unset($links);
	 		$links['excel']->url = "to_excel.php?level=rating&$alink&action=excel&fid=";
	 		$links['excel']->title = get_string('downloadexcel');
	 		$links['excel']->pixpath = "{$CFG->pixpath}/f/xlsx.gif";
        }

	    $strlinkupdate = '';
	    foreach ($links as $key => $link)	{

			$strlinkupdate .= "<a title=\"$link->title\" href=\"$link->url$fid\">";
			$strlinkupdate .= "<img src=\"{$link->pixpath}\" alt=\"$link->title\" /></a>&nbsp;";
	    }

		if (isset($links['edit']))  {
			 $link = $links['edit'];
        	 $strformrkpu = "<b><a title=\"$link->title\" href=\"$link->url$fid\">$strformrkpu</a></b>";
        }

	    $table->data[] = array ($strformrkpu_status, $strformrkpu, $strlinkupdate);
		$table->bgcolor[] = array ($strcolor);
		unset($links);
	   // add_rkp_to_table($table, $strsql, , $links, $school_operator_is);
	}
	
	return $table;
}

?>