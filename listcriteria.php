<?php // $Id: listcriteria.php,v 1.18 2012/12/06 12:30:25 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../mou_ege/lib_ege.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('lib_spo.php');

    $rid = required_param('rid', PARAM_INT);            // Rayon id
    // $sid = optional_param('sid', 0, PARAM_INT);            // School id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $yid = optional_param('yid', 0, PARAM_INT);       		// Year id
    $fid = optional_param('fid', 0, PARAM_INT);       // Form id
    $level = optional_param('level', 'school');       // Form id
    // $nm  = optional_param('nm', 9, PARAM_INT);  // Month number
    $typeou = optional_param('typeou', '09');       // Type OU
    $action   = optional_param('action', '');
    $shortname = optional_param('sn', 'rating_spo');
    $nm = 9;
	$itogmark = 0;
	
    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

   	$strtitle = get_string('title','block_mou_spo');    
    $strscript = get_string('listcriteria', 'block_monitoring');
    $strrayon = get_string('rayon', 'block_monitoring');
    $strrayons = get_string('rayons', 'block_monitoring');
    $strschools = get_string('college', 'block_monitoring');
    $strreports = get_string('reportschool', 'block_monitoring');


    $scriptname = 'listcriteria.php';

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&yid=$yid&rid=", $rid);
	// $strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons) { //  && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
    
    get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
    
    if ($oid > 0) {
    	$context = get_context_instance($CONTEXT_OU, $oid);
        $view_capability = has_capability('block/mou_att2:viewou', $context);
        $edit_capability = has_capability('block/mou_att2:editou', $context);
    }        

    init_rating_parameters($yid, $shortname, $select, $order);

    if ($oid != 0)	{
    	$college = get_record('monit_college', 'id', $oid);
   	    $strschool = $college->name;
    }	else  {
   	    $strschool = get_string('college', 'block_monitoring');
    }
	
    if ($action == 'excel') 	{
    	// init_region_criteria($yid);
	    $table = table_listcriteria($rid, $oid, $yid, $nm, $shortname, $action);
        // print_object($table);
  		print_table_to_excel($table);
        exit();
	}

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_spo/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navlinks[] = array('name' => $strschool, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)
    

    // $currenttab = 'listcriteria';
    // include('tabs.php');

/*
    $toprow2  = array();
    $toprow2[] = new tabobject('school', "listcriteria.php?level=school&rid=$rid&sid=$sid&nm=$nm&yid=$yid", 'По школе');
    $toprow2[] = new tabobject('rayon', "listcriteria.php?level=rayon&rid=$rid&nm=$nm&yid=$yid", 'По району');
    $tabs2 = array($toprow2);
    print_tabs($tabs2, $level, NULL, NULL);
*/
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;

    $ret =  '<tr> <td>Тип образовательной организации: </td><td>';
	$ret .= "профессиональная образовательная организация";
	$ret .= '</td></tr>';
    echo $ret;
    
	// echo $strlisttypeou;
	// echo $typeou;
	// if ($typeou != '-')	{
	    if ($strlistou = listbox_college_role("$scriptname?rid=$rid&amp;yid=$yid&amp;typeou=$typeou&amp;oid=", $rid, $typeou, $oid, $yid))	{ 
			echo $strlistou;
		} else {
			echo '</table>';
			notice(get_string('ounotfound', 'block_mou_att'), "../index.php?rid=$rid&amp;yid=$yid");
		}	
	// } 
	echo '</table>';
	
	if ($rid != 0 && $oid != 0)   {
        // init_region_criteria($yid);
        print_tabs_years_rating_spo("$scriptname?nm=$nm", $rid, $oid, $yid);
	   
       /*
        echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
        listbox_rating_level("listcriteria.php?level=$level&rid=$rid&amp;sid=$sid&amp;nm=$nm&amp;yid=$yid&amp;sn=", $shortname, $yid, false, $level);
        echo '</table>';
        echo '<p>';
        */

	    $totalsum = $itogmark = $totalsumweight = 0;
 
        $table = table_listcriteria($rid, $oid, $yid, $nm, $shortname);    
      	$strtotlamark = get_string('total_mark', 'block_monitoring') . ': ' . $totalsum;
        $strtotlamarkweigth = 'Сумма баллов с учетом поправочного коэффициента' . ': ' . $totalsumweight;
       	print_heading($strtotlamark, 'center', 4);
        print_heading($strtotlamarkweigth, 'center', 4);
      	// print_table($table);
       	print_color_table($table);
	    print_heading($strtotlamark, 'center', 4);
        print_heading($strtotlamarkweigth, 'center', 4);

    	$options = array('action'=> 'excel', 'rid' => $rid, 'oid' => $oid, 'yid' => $yid, 
    					 'fid' => $fid,  'nm' => $nm,  'sn' => $shortname,  'sesskey' => $USER->sesskey);
    	
    
       	echo '<div style="text-align: center;">';
        print_single_button("listcriteria.php", $options, get_string('downloadexcel'));
        echo '</div>';
    }
    
	// print_string('remarkyear', 'block_monitoring');
    print_footer();



function table_listcriteria($rid, $oid, $yid, $nm, $shortname, $action='')
{
    global $CFG, $edit_capability_region, $edit_capability_rayon, $totalsum, $itogmark, $level, $strschool, $totalsumweight;

    $symbolnumber = get_string('symbolnumber', 'block_monitoring'); 
    $nameofpokazatel = get_string('nameofpokazatel', 'block_monitoring');
    $valueofpokazatel = get_string('mark', 'block_monitoring');
	$formula = get_string('formula','block_monitoring');
	$straction = get_string("action","block_monitoring");

    $table = new stdClass();

    if (($edit_capability_region || $edit_capability_rayon) && ($yid >= 9 && $yid <= 10))   {
        $table->head  = array ($symbolnumber, $nameofpokazatel, $formula, $valueofpokazatel, 'Поправочный коэффициент', "Балл*п.к.");
        $table->align = array ("left", "left", "center", "center", "center", "center");
    	$table->width = '90%';
        $table->size = array ('5%', '65%', '10%', '10%', '10%', '10%');
        $table->columnwidth = array (7, 100, 15, 15, 15, 15);
    	$table->class = 'moutable';
    } else {
        $table->head  = array ($symbolnumber, $nameofpokazatel, $valueofpokazatel);
        $table->align = array ("left", "left", "center");
        $table->width = '90%';
        $table->size = array ('5%', '65%', '15%');
        $table->columnwidth = array (7, 100, 15);
        $table->class = 'moutable';
    }

    $yearname = get_field_select('monit_years', 'name', "id = $yid");
    $godi = explode('/', $yearname); 
   	$table->titlesrows = array(30, 30);
    $table->titles = array();
    $table->titles[] = get_string('listcriteria', 'block_monitoring') . ' ' . $strschool . ' (' . $godi[0] . ' год)'; 
	// $table->titles[] = get_string('name_'.$shortname, 'block_monitoring');
    $table->downloadfilename = "criteria_{$rid}_{$oid}_{$shortname}";
    $table->worksheetname = 'criteria';

    $a = $b = 0;
	get_name_otchet_year ($yid, $a, $b);
	// echo $a . $b;	

    // init_rating_parameters($yid, $shortname, $select, $order, $level);	
 
    $datefrom = get_date_from_month_year($nm, $yid);

    $strsql = "SELECT * FROM {$CFG->prefix}monit_rating_listforms
    	   		   WHERE (collegeid=$oid) and (shortname='$shortname') and (datemodified=$datefrom)";
  	// print $strsql . '<br><hr>';
	$arr_df = array();
	if ($rec = get_record_sql($strsql))	{
 		$fid = $rec->id;
   		if ($df = get_record_sql("SELECT * FROM {$CFG->prefix}monit_form_$shortname WHERE listformid=$fid"))	{
   			$arr_df = (array)$df;
			// print_object($arr_df);
   		}
   	}	
  
    if ($yid < 9)  {    
        $select = "yearid = 7 AND gradelevel = 1";
    } else {
        $select = "yearid = $yid AND gradelevel = 1";
    }    
    $order = 'sortnumber, id'; 

    $strsql = "SELECT id, number, name, formula, edizm, indicator, ordering, weight 
			   FROM {$CFG->prefix}monit_rating_criteria
    		   WHERE  $select 
			   ORDER BY $order";
    // echo $strsql .' <br />';
               
	if ($criterias = get_records_sql($strsql)) 	{
	
   		foreach($criterias as $criteria)	{
			$color = 'red';// get_string('status1color', 'block_monitoring');
			$strmark = "<b><font color=\"$color\">0</font></b>";
            $criterianame = '';
   			if ($criteria->formula == 'null')	{
				$criterianumber = '<b>'. $criteria->number . '</b>';
				eval("\$criterianame = \"$criteria->name\";");
   				$criterianame = '<b>'.$criterianame.'</b>';   			
   				$criteriaformula = '';
				$strmark = '';
                $weight = '';
                $mark_with_pk = '';
   			} else {
   				// $criterianame = $criteria->name;
   				eval("\$criterianame = \"$criteria->name\";");
   				$criterianumber = $criteria->number;
				$operands = explode('#', $criteria->formula);
				// print_object($operands); echo '<br>';
				$o1 = trim($operands[0]);
				$o2 = trim($operands[1]);
				$criteriaformula = '<i>'.translitfield('f'.$operands[1]) . '</i>';

				if (!empty($arr_df))	{
	               if (function_exists($o1)) {
	               		$namefunc = $o1;
	               		$strmark = $namefunc($o2, $criteria->indicator, $arr_df, $criteria->ordering);
	               		// echo "$totalsum += $itogmark;<br>" . $strmark . '<br>';
	               		$totalsum += $itogmark;
	               		// echo "$totalsum<hr>";
					}
				} else {
					$strmark = '-';
				}	
                $weight = $criteria->weight;
                $mark_with_pk = '<b>' . $criteria->weight*$itogmark . '</b>';
                $totalsumweight += $criteria->weight*$itogmark;
				
			}	   
    		if ($action == 'excel') 	{
    			$criterianumber = " " . $criterianumber; 
    		}

            if (($edit_capability_region || $edit_capability_rayon) && ($yid >= 9 && $yid <= 10))   {
                $table->data[] = array ($criterianumber, $criterianame, $criteriaformula, $strmark, $weight, $mark_with_pk); //
            } else {
                $table->data[] = array ($criterianumber, $criterianame, $strmark); //
            }

		}    
	}
	
	return $table;
}


?>