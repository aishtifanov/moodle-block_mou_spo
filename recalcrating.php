<?php // $Id: recalcrating.php,v 1.8 2012/12/06 12:30:26 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('lib_spo.php');

    $rid = required_param('rid', PARAM_INT);       // Rayon id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $action = optional_param('action', '');       // Action
    
    
    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
    $scriptname = 'recalcrating.php';
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&amp;yid=$yid&amp;rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title','block_mou_dou');
    $strscript = get_string('recalcrating', 'block_monitoring');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_spo/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    print_heading('Перерасчет показателей рейтинга профессиональных образовательных организаций области.');

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("512M");
 	if (function_exists('apache_child_terminate')) {
	    @apache_child_terminate();
	}    


    $exclude =  '';
    /*
    if ($action == 'exclude')   {
        $exclude = "AND number not in ('П12')";
    } else {
        $exclude =  '';
    }    
    */
    recalculate_rating_update($rid, $yid, $exclude);
    
    redirect("svodrating.php?rid=1&sid=0&yid=7", 'Перерасчет выполнен.', 30);

    print_footer();
    


function recalculate_rating_update($rid, $yid, $exclude='')
{
    global $CFG;
    
	$nm = 9;
	$datemodified = get_date_from_month_year($nm, $yid);
    $shortnames = array('rating_spo');

	$strsql =  "SELECT id, name  FROM {$CFG->prefix}monit_college
				WHERE  isclosing=0 AND yearid=$yid  and israting=1
				ORDER BY number";	
                	
	if ($schools = get_records_sql($strsql))	{
	   foreach ($schools as $school)  {
            $oid = $school->id;
            foreach ($shortnames as $i => $shortname) {

               $strsql = "SELECT f.id FROM mdl_monit_rating_listforms l 
                          inner join mdl_monit_form_{$shortname} f on l.id=f.listformid
	   		              WHERE (collegeid=$oid) and (shortname='$shortname') and (datemodified=$datemodified)";
                           
               // echo  $strsql . '<br />';
               if ($idform = get_field_sql($strsql)) 	{
                    $totalmark = calculate_college_mark($yid, $rid, $oid, $idform, $shortname);
                    echo "$school->name: $totalmark<br />";  
               }
            }
       }
    }                
}    	 


?>