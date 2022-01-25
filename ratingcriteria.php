<?php // $Id: ratingrayon.php,v 1.8 2012/10/18 10:40:41 shtifanov Exp $

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
    $typeou = optional_param('typeou', '-');       // Type OU
    $shortname = optional_param('sn', 'rating_spo');   
    $action   = optional_param('action', ''); 
    $nm = 9;

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    $scriptname = 'ratingcriteria.php';

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

	$strtitle = get_string('title','block_mou_spo');
    $strscript = get_string('ratingcriteria', 'block_mou_spo');
    
    if ($action == 'excel') 	{
	   	$strsql = "SELECT concat (number, '. ', name) as nname, ordering FROM {$CFG->prefix}monit_rating_criteria
   			   WHERE id=$criteriaid";
        $c = get_record_sql($strsql);
		$table = table_ratingrayon($rid, $oid, $yid, $nm, $shortname, $select, $criteriaid, $c->ordering, $c->nname);
  		print_table_to_excel($table);
        // print_object($table);
        exit();
	}
    

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_spo/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    print_heading($strscript, 'center', 3);
    
    print_tabs_years_rating_spo("$scriptname?nm=$nm&sn=$shortname", $rid, $oid, $yid);
    
    echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_rating_criteria("$scriptname?rid=$rid&oid=$oid&nm=$nm&yid=$yid&sn=$shortname&cid=", $shortname, $select, $criteriaid, $order, 250);
    echo '</table><br />';
	
	if ($criteriaid <> 0)	{
	   
	   	$strsql = "SELECT concat (number, '. ', name) as nname, ordering FROM {$CFG->prefix}monit_rating_criteria
   			   WHERE id=$criteriaid";
	    // $nname = get_field_sql($strsql);
        $c = get_record_sql($strsql);
        // print_object($c);
        
        print_heading($c->nname, 'center', 4);
		$table = table_ratingrayon($rid, $oid, $yid, $nm, $shortname, $select, $criteriaid, $c->ordering, $c->nname);
        print_color_table($table);

		$options = array('action'=> 'excel', 'rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'nm' => $nm,  
                         'shortname'  => $shortname, 'cid' => $criteriaid, 'sesskey' => $USER->sesskey);
	   	echo '<center>';
	    print_single_button("ratingcriteria.php", $options, get_string('downloadexcel'));
	    echo '</center>';
	}

	// print_string('remarkyear', 'block_monitoring');
    print_footer();


function table_ratingrayon($rid, $sid, $yid, $nm, $shortname, $select, $criteriaid, $ordering=0, $cnname='')	
{
	global $CFG, $admin_is, $region_operator_is, $rayon_operator_is, $strscript;

    $numberf = get_string('ratingnum', 'block_monitoring');
    $strname = 'Наименование профессиональной образовательной организации';// get_string('college', 'block_monitoring');
    $strnew = "Дата распоряжения Правительства области об изменении типа организационно-правовой формы на автономную";
    $valueofpokazatel = get_string('valueofpokazatel', 'block_monitoring');

    $table = new stdClass();
    //  8 - 2014, 9 - 2015, 10 - 2016, 11 - 2017
    if ($yid <=8 || $yid >=11)   {
        $table->head  = array ($numberf, $strname, $valueofpokazatel); // , $strnew
        $table->align = array ("center", "left", "center", "center");
        $table->columnwidth = array (7, 100, 15, 15);
    } else {
        $table->head  = array ($numberf, $strname, 'Формула', 'Количество баллов ПОО', 'Поправочный коэффициент', 'Итоговое количество баллов');
        $table->align = array ("center", "left", "center", "center", "center", "center");
        $table->columnwidth = array (7, 100, 15, 15, 15, 15);
        
    }    
	$table->width = '100%';
    // $table->size = array ('5%', '90%', '5%');
	$table->class = 'moutable';
    
   	$table->titlesrows = array(30, 30);
    $table->titles = array();
    $table->titles[] = $strscript;
    $table->titles[] = $cnname; 
	// $table->titles[] = get_string('name_'.$shortname, 'block_monitoring');
    $table->downloadfilename = "ratingcriteria_{$yid}_{$criteriaid}";
    $table->worksheetname = $table->downloadfilename;
    
    $weight  = get_field_sql("SELECT weight  FROM mdl_monit_rating_criteria where id=$criteriaid");
    $criteria = get_record_sql("SELECT * FROM mdl_monit_rating_criteria where id=$criteriaid");
    
	$datefrom = get_date_from_month_year($nm, $yid);
	// $curryid = get_current_edu_year_id();
    $curryid = $yid;

	$strsql =  "SELECT id, name  FROM {$CFG->prefix}monit_college
				WHERE  isclosing=0 AND yearid=$curryid and israting=1
				ORDER BY number";	
   
	$color = 'red';
	if ($schools = get_records_sql($strsql))	{
		
        $schoolsarray = array();
        $schoolsname = array();
        $schoolsmark = array();
	    foreach ($schools as $sa)  {
	        $schoolsarray[] = $sa->id;
	        $schoolsname[$sa->id] = $sa->name;
	        $schoolsmark[$sa->id] = -1;
	    }
	    $schoolslist = implode(',', $schoolsarray);

		$strsql = "SELECT id, collegeid as schoolid, mark FROM {$CFG->prefix}monit_rating_college
		 		   WHERE (collegeid in ($schoolslist)) AND criteriaid=$criteriaid AND yearid=$yid";
        // print $strsql .'<br />';                   
	    if ($ratschools = get_records_sql($strsql)) 	{
		    foreach ($ratschools as $rs)  {
		        $schoolsmark[$rs->schoolid] = $rs->mark;
		    }
            
            if ($criteriaid == 551)  {
                asort($schoolsmark);
            } else {
                arsort($schoolsmark);    
            }
            
            // arsort($schoolsmark);
            // print_object($schoolsmark);			
		}
		
		reset($schoolsmark);
		$maxmark = current($schoolsmark);
		// echo $maxsm; 
		$placerating = array();
		$mesto = 1;
		foreach ($schoolsmark as $schoolid => $schoolmark) {
			if ($schoolmark > 0  || $schoolmark < 0) {
				if ($schoolmark == $maxmark)	{
					$placerating[$schoolid] = $mesto;
				} else {
					$placerating[$schoolid] = ++$mesto;
					$maxmark = $schoolmark; 
				}	 
			} else {
				$placerating[$schoolid] = '-';
			}
		}	
			
 	
		foreach ($schoolsmark as $schoolid => $schoolmark) {
		  
			$schoolname = $schoolsname[$schoolid];
			$schoolname = "<strong>$schoolname</strong></a>";
			$mesto = '<b><i>'.$placerating[$schoolid] . '</i></b>';
		
        	if ($schoolmark >= 0)	{
			   $strmark = "<b><font color=green>$schoolmark</font></b>";	
			} else if ($schoolmark < 0)	{
			   $strmark = "<b><font color=red>$schoolmark</font></b>";
			} else {
               $strmark = "<b><font color=red>-</font></b>";
			}

            //  8 - 2014, 9 - 2015, 10 - 2016, 11 - 2017
            if ($yid <=8 || $yid >=11)   {
		      $table->data[] = array ($mesto, $schoolname , $strmark); // , ''
            } else {
                
                $strsql = "SELECT * FROM {$CFG->prefix}monit_rating_listforms
                	   		   WHERE (collegeid=$schoolid) and (shortname='$shortname') and (datemodified=$datefrom)";
              	// print $strsql . '<br>';
            	$arr_df = array();
            	if ($rec = get_record_sql($strsql))	{
             		$fid = $rec->id;
               		if ($df = get_record_sql("SELECT * FROM {$CFG->prefix}monit_form_$shortname WHERE listformid=$fid"))	{
               			$arr_df = (array)$df;
            			// print_object($arr_df);   	
               		}
               	}
                   
       			if ($criteria->formula == 'null')	{
       				$criteriaformula = '';
    				$strmark = '';
       			} else {
    				$operands = explode('#', $criteria->formula);
    				// echo $criteria->formula . '<br>';
    				// print_r($operands); echo '<br>';
    				$o1 = trim($operands[0]);
    				$o2 = trim($operands[1]);
                    $criteriaformula = '<i>'.translitfield('f'.$operands[1]) . '</i>';
    
    				if (!empty($arr_df))	{
    	               if (function_exists($o1)) {
    	               		$namefunc = $o1;
    	               		$strmark = $namefunc($o2, $criteria->indicator, $arr_df, $criteria->ordering);
    	               		// echo "$totalsum += $itogmark;<br>" . $strmark . '<br>';
    	               			               		// echo "$totalsum<hr>";
    					}
    				} else {
    					$strmark = '-';
    				}	
    			}	   
                
                $table->data[] = array ($mesto, $schoolname , $criteriaformula, $strmark, $weight, '<b>' . round($schoolmark*$weight, 2) . '</b>');  
            }  
		}    
	}
	
	return $table;
}
