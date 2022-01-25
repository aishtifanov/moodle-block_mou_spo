<?php // $Id: summaryrating.php,v 1.26 2013/02/25 06:17:19 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../mou_ege/lib_ege.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('lib_spo.php');

    $rid = required_param('rid', PARAM_INT);            // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);            // School id
    $yid = optional_param('yid', 0, PARAM_INT);       		// Year id
    // $nm  = optional_param('nm', 9, PARAM_INT);  // Month number
    $criteriaid = optional_param('cid', 0);       // Shortname form
    $level = optional_param('level', 'school');       // Form id
    $report = optional_param('r', 'rA');       //  Report
    $shortname = optional_param('sn', 'rating_spo');
	$action   = optional_param('action', '');        
    $nm = 9;

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    $scriptname = 'svodrating.php';

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&amp;yid=$yid&amp;rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    if ($yid < 9)  {    
        $select = "yearid = 7 AND gradelevel = 1";
    } else {
        $select = "yearid = $yid AND gradelevel = 1";
    }    
    $select .=  " AND edizm <> 'null'";
    $order = 'sortnumber, id'; 

    if ($action == 'excel') 	{
        $rkps = array('rating_spo');
        $datefrom = get_date_from_month_year($nm, $yid);
        $table = table_svodrating($rid, $oid, $yid, $nm, $shortname, $select, $order);        
        print_table_to_excel($table);        
        exit();
	}

 
	$strtitle = get_string('title','block_mou_spo');
    $strscript = get_string('svodrating', 'block_mou_spo');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_spo/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    print_heading('Сводный рейтинг профессиональных образовательных организаций области', 'center');

    print_tabs_years_rating_spo("$scriptname?nm=$nm&sn=$shortname&cid=$criteriaid", $rid, $oid, $yid);
        
    $table = table_svodrating($rid, $oid, $yid, $nm, $shortname, $select, $order);
            
    if (!empty($table)) {
   	    print_color_table($table);
		$options = array('action'=> 'excel', 'rid' => $rid, 'oid' => $oid, 'yid' => $yid,
                          'level' => $level, 'sn' => $shortname, 'sesskey' => $USER->sesskey);
	   	echo '<center>';
	    print_single_button("svodrating.php", $options, get_string('downloadexcel'));

    	$options = array('action'=> 'recalc', 'rid' => 1, 'yid' => $yid, 'sesskey' => $USER->sesskey);
        
        echo '<br /><br /><br />';
        
        print_single_button("recalcrating.php", $options, get_string('recalcrating', 'block_monitoring'));
        
	    echo '</center>';
    }    

	// print_string('remarkyear', 'block_monitoring');
    print_footer();
    


function table_svodrating($rid, $sid, $yid, $nm, $shortname, $select, $order)	
{
	global $CFG, $edit_capability_region, $edit_capability_rayon;

    $strstatus = get_string('status', 'block_monitoring');
    $numberf = get_string('ratingnum', 'block_monitoring');
    $strname = 'Наименование профессиональной образовательной организации';// get_string('school', 'block_monitoring');
    $valueofpokazatel = get_string('valueofpokazatel', 'block_monitoring');

    $table = new stdClass();
    if ($yid <= 10) {
        $table->head = array($strstatus, $numberf, $strname, 'Всего', 'Всего с учётом п.к.');
        $table->align = array("center", "center", "left", "center", "center");
        $table->width = '90%';
        $table->size = array('5%', '5%', '90%', '5%', '5%');
        $table->columnwidth = array(15, 13, 100, 15, 15);
    } else {
        $table->head = array($strstatus, $numberf, $strname, 'Всего');
        $table->align = array("center", "center", "left", "center");
        $table->width = '90%';
        $table->size = array('5%', '5%', '90%', '5%');
        $table->columnwidth = array(15, 13, 100, 15);

    }
	$table->class = 'moutable';
	
   	$table->titlesrows = array(30, 30);
    $table->titles = array();
    $table->titles[] = 'Сводный рейтинг профессиональных образовательных организаций области'; 
	$table->titles[] = '';
    $table->downloadfilename = "report_{$rid}_{$shortname}";
    $table->worksheetname = $table->downloadfilename;
	
	$datefrom = get_date_from_month_year($nm, $yid);
	// $curryid = get_current_edu_year_id();
    $curryid = $yid;

	$strsql =  "SELECT id, rayonid, name  FROM {$CFG->prefix}monit_college
				WHERE  isclosing=0 AND yearid=$curryid  and israting=1
				ORDER BY number";	

	$color = 'red';
	if ($schools = get_records_sql($strsql))	{
		
        $schoolsarray = array();
        $schoolsname = array();
        $schoolsmark = array();
        $rayonschool = array();
	    foreach ($schools as $sa)  {
	        $schoolsarray[] = $sa->id;
	        $schoolsname[$sa->id] = $sa->name;
	        $schoolsmark[$sa->id] = 0;
            $rayonschool[$sa->id] = $sa->rayonid;
	    }
	    // $schoolslist = implode(',', $schoolsarray);

		$strsql = "SELECT id, number, name FROM {$CFG->prefix}monit_rating_criteria
	   			   WHERE $select 
	 		   	   ORDER BY $order";
        // echo $strsql;                    
		if ($criterias = get_records_sql($strsql)) 	{
  			$criteriaids = array();
	   		foreach($criterias as $criteria)	{
	   			$criteriaids[] = $criteria->id;
		  	}
	    	// $criterialist = implode(',', $criteriaids);
		}  	


//		$strsql = "SELECT id, schoolid, mark FROM {$CFG->prefix}monit_rating_school
//		 		   WHERE (schoolid in ($schoolslist))  AND criteriaid=$criteriaid";

        $schoolslist = implode(',', $schoolsarray);


		$strsql = "SELECT id, collegeid as schoolid, criteriaid, mark 
                   FROM {$CFG->prefix}monit_rating_college
		 		   WHERE collegeid in ($schoolslist) AND yearid=$yid";		

	    if ($ratschools = get_records_sql($strsql)) 	{
	       /*
		    foreach ($ratschools as $rs)  {
		    	if (in_array($rs->criteriaid, $criteriaids))	{ 
		            $schoolsmark[$rs->schoolid] += $rs->mark;
		        }    
		    }
            */
		}

        $sql = "create temporary table temp_college
                SELECT cg.id, cg.collegeid, cg.criteriaid, cg.mark, round(cg.mark*r.weight, 4)  as markweight
                FROM mou.mdl_monit_rating_college cg 
                inner join mdl_monit_rating_criteria r on r.id=cg.criteriaid
                where cg.yearid=$yid and collegeid in ($schoolslist)";
        execute_sql($sql, false); 

        $sql = "select collegeid, round(sum(mark), 4) as summark  from temp_college group by collegeid";
        $schoolsmark = get_records_sql_menu($sql);

        $sql = "select collegeid, sum(markweight)  as  summarkweight  from temp_college  group by collegeid";
        $schoolsmarkweight = get_records_sql_menu($sql);

        		
        foreach ($schools as $sa)  { 
            if (!isset($schoolsmark[$sa->id]))  {
                $schoolsmark[$sa->id] = 0;
                $schoolsmarkweight[$sa->id] = 0;
            }
            if ($yid <= 10) {
                $schoolsmark_i[$sa->id] = $schoolsmarkweight[$sa->id];// $schoolsmark[$sa->id]; //  + $schoolsmark_k[$sa->id];
            } else {
                $schoolsmark_i[$sa->id] = $schoolsmark[$sa->id];
            }
        }    
                            
		arsort($schoolsmark_i);        
		reset($schoolsmark_i);
		$maxmark = current($schoolsmark_i);
		// echo $maxsm; 
		$placerating = array();
		$mesto = 1;
		foreach ($schoolsmark_i as $schoolid => $schoolmark) {
			// if ($schoolmark > 0) {
				if ($schoolmark == $maxmark)	{
					$placerating[$schoolid] = $mesto;
				} else {
					$placerating[$schoolid] = ++$mesto;
					$maxmark = $schoolmark; 
				}	 
			/* } else {
				$placerating[$schoolid] = '-';
			}*/
		}	
			
 	
		foreach ($schoolsmark_i as $schoolid => $schoolmark) {
			$schoolname = $schoolsname[$schoolid];
            $rid0 = $rayonschool[$schoolid]; 
            $link = "listcriteria.php?rid=$rid0&yid=$yid&typeou=09&oid=$schoolid";
			$schoolname = "<strong><a href=\"$link\">$schoolname</a></strong>";
			$mesto = '<b><i>'.$placerating[$schoolid] . '</i></b>';
			// $mesto = $placerating[$schoolid];
			// if ($schoolmark >= 0)	{
			// $strmark = 	 "<b><font color=green>{$schoolsmark[$schoolid]}</font></b>";
            // $strmark_k = "<b><font color=green>{$schoolsmark_k[$schoolid]}</font></b>";
            $strmark_i = "<b><font color=green>$schoolmark</font></b>"; 
			/*} else {
			   $strmark = "<b><font color=red>-</font></b>";	
			}*/
			
	    	$strformrkpu_status = get_string("status1","block_monitoring");			
			$strcolor = get_string("status1color","block_monitoring");
		
			if ($rec = get_record_select('monit_rating_listforms', " (collegeid=$schoolid) and (shortname='$shortname') and (datemodified=$datefrom) ", 'id, status'))	{
				$strformrkpu_status = get_string('status'.$rec->status, "block_monitoring");
				$strcolor = get_string('status'.$rec->status.'color',"block_monitoring");	
			}

            if ($yid <= 10) {
                $table->data[] = array($strformrkpu_status, $mesto, $schoolname, $schoolsmark[$schoolid], $strmark_i); // , $schoolsmarkweight[$schoolid]
            } else {
                $table->data[] = array($strformrkpu_status, $mesto, $schoolname, $schoolsmark[$schoolid]);
            }
		    $table->bgcolor[] = array ($strcolor);
		}    
	}
	
	return $table;
}    

?>