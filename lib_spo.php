<?php // $Id: lib_culture.php,v 1.21 2012/04/06 07:16:29 shtifanov Exp $

/*
По каждому показателю первично выстраивается рейтинг ПОО с учетом веса количественных/долевых данных каждого ПОО. 
В рейтинге порядок ПОО определяется распределением количественных/долевых данных по возрастанию (от меньшего количества/доли к большему) 
или по убыванию (от большего количества/доли к меньшему).
Для формирования итогового рейтинга значения показателей, сформированных по убыванию, суммируются, 
значения показателей по возрастанию вычитаются. 
Итоговый рейтинг ПОО выстраивается по убыванию (от большего количества баллов, набранных ПОО, к меньшему).

ordering = 0 - убывание
ordering = 1 - возрастание
ordering = -1 - сортировка отсутствует
*/


function listbox_college_role($scriptname, &$rid, &$typeou, &$oid, $yid)
{
	global $CFG, $USER;

	$ret = false;
  
 	if ($rid == 0)  return false;
   
  	$listous = '';
   	
  	// $outype = get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
    // $outype = get_config_typeou($typeou);
    $outype = new stdClass(); 
    $outype->context = CONTEXT_COLLEGE;
    $outype->contextqueue = CONTEXT_COLLEGE;
	$outype->strselect = get_string('selectacollege','block_monitoring').' ...';
	$outype->strtitle = get_string('college', 'block_monitoring');
    $outype->idname = 'collegeid';
    $outype->tblname = 'monit_college';
   	$outype->where = ' and israting=1'; 		
    
	  
	$strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path 
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
    // echo $strsql . '<hr>';
	if ($ctxs = get_records_sql($strsql))	{
	 		// echo '<pre>'; print_r($ctxs);  echo '</pre><hr>';
			foreach($ctxs as $ctx1)	{
				switch ($ctx1->contextlevel)	{
					case CONTEXT_SYSTEM: if ($ctx1->roleid == 1)	{ 
											$listous = -1;
										 }
										 break;	
										 			
					case CONTEXT_REGION_ATT:					 					 	
										 if ($ctx1->roleid == 8 || $ctx1->roleid == 10)	{
										 	$listous = -1;
										 }
										 break;
					case CONTEXT_RAYON_COLLEGE:
					case CONTEXT_RAYON_UDOD:
					case CONTEXT_RAYON_DOU:					 		
    				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10 || $ctx1->roleid == 18) {
    										$listous = -1;
										 }
								 		 break;
					case CONTEXT_QUEUE_SCHOOL: 
                                         $contexts = explode('/', $ctx1->path);
										 $ctxou = get_record('context', 'id', $contexts[4]);
                                         $listous .= $ctxou->instanceid . ',';
										 break;                    
					case CONTEXT_QUEUE_UDOD: 
                                         $contexts = explode('/', $ctx1->path);
										 $ctxou = get_record('context', 'id', $contexts[4]);
                                         $listous .= $ctxou->instanceid . ',';
					case CONTEXT_QUEUE_DOU:   
                                         $contexts = explode('/', $ctx1->path);
										 $ctxou = get_record('context', 'id', $contexts[4]);
                                         $listous .= $ctxou->instanceid . ',';
                    default:
                        // notify( get_string('notdefinedcontext', 'block_mou_att', $ctx1->contextlevel)); 
	 			}
	 			
	 			if 	($listous == -1) break;
	 			
	 			if ($ctx1->contextlevel == $outype->context) {
	 				$listous .= $ctx1->instanceid . ',';
	 			}	
			}
	 }		 
	
	 // echo $listous . '<hr>';
	 if ($listous == '') 	{
	 	return false;
	 } else if 	($listous == -1) 	{
	 	$strsql = "SELECT id, rayonid, name  FROM {$CFG->prefix}{$outype->tblname}
					WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid {$outype->where} 
 					ORDER BY number";
	 } else {	
	 	$listous .= '0';
	 	$strsql = "SELECT id, rayonid, name FROM {$CFG->prefix}{$outype->tblname}
		 			 WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid AND id in ($listous) {$outype->where}
   					 ORDER BY number";
	 }
 
 

	$schoolmenu = array();
	// echo $strsql . '<hr>';
    if ($arr_schools =  get_records_sql($strsql))	{
    	if (count($arr_schools) > 1) {
	   		$schoolmenu[0] = $outype->strselect;
	  		foreach ($arr_schools as $school) {
				$len = strlen ($school->name);
				if ($len > 200)  {
					// $school->name = substr($school->name, 0, 200) . ' ...';
					$school->name = substr($school->name,0,strrpos(substr($school->name,0, 210),' ')) . ' ...';
				}
				$schoolmenu[$school->id] =$school->name;
			}
		 	$ret =  '<tr><td>'.$outype->strtitle.':</td><td>';
  			$ret .=  popup_form($scriptname, $schoolmenu, 'switchou', $oid, '', '', '', true);
  			$ret .= '</td></tr>';
		} else {
  			$school = current($arr_schools);
  			// $schoolmenu[$school->id] = $school->name;
  			$oid = $school->id;
  			$rid = $school->rayonid;
		 	$ret =  '<tr><td>'.$outype->strtitle.':</td><td>';
  			$ret .=  "<b>$school->name</b>";
  			$ret .= '</td></tr>';
		} 
  	} else {
  	    $schoolmenu[0] = get_string('selectou', 'block_mou_att').' ...';;
	 	$ret =  '<tr><td>'.$outype->strtitle.':</td><td>';
		$ret .=  popup_form($scriptname, $schoolmenu, 'switchou', $oid, '', '', '', true);
		$ret .= '</td></tr>';
  		// $ret = false;
  	}
	
	  
  return $ret;
}


// Print tabs years with auto generation link to dou
function print_tabs_years_rating_spo($link = '', $rid = 0, $oid = 0, $yid = 6)
{
	$toprow1 = array();
    $ouids = array();
    
	$uniqueconstcode = 0;
   	if ($rid != 0 && $oid != 0)	{
   		if ($ou = get_record_select('monit_college', "rayonid = $rid AND id = $oid AND yearid = $yid", 'id, uniqueconstcode'))		{
			$uniqueconstcode = $ou->uniqueconstcode;   			
   		}
   	} 

    if ($years = get_records_select('monit_years', 'id>=7', '', 'id, name'))  {
    	foreach ($years as $year)	{
    		$fulllink = $link . "&rid=$rid&oid=$oid&yid=" . $year->id;
	    	if ($uniqueconstcode != 0)	{
				if ($ou = get_record_select('monit_college', "uniqueconstcode=$uniqueconstcode AND yearid = {$year->id}", 'id, rayonid'))	{
					$fulllink = $link . "&rid={$ou->rayonid}&oid={$ou->id}&yid={$year->id}";
                    $ouids[$year->id] = $ou->id;
				}	
	    	}
            
  			$ayears = explode("/", $year->name);
   			$toprow1[] = new tabobject($year->id, $fulllink, get_string('civilyear', 'block_monitoring', $ayears[0]));    			
	    }
  	}
    $tabs1 = array($toprow1);

   //  print_heading(get_string('terms','block_dean'), 'center', 4);
   
	print_tabs($tabs1, $yid, NULL, NULL);
    
    return $ouids;
}


// $formula =  func_spo13#((fn_41+ fn_14)/(fn_37+ fn_42))*100%  $yearid=13
function func_spo13($formula, $indicator, &$arr_df, $ordering = 0)
{
    global $itogmark;

    $itogmark = 0;

    $o10 = 'fn_14'; // 'fn_10';
    $o46 = 'fn_41'; // 'fn_46';

    $o11 = 'fn_37'; // 'fn_11';
    $o47 = 'fn_42'; // 'fn_47';

    $sum1 = $arr_df[$o10] + $arr_df[$o46];
    $sum2 = $arr_df[$o11] + $arr_df[$o47];

    /// echo $o1 . '   ' . $o2 . '<br>';
    $color = 'red';// get_string('status1color', 'block_monitoring');
    /// $strmark = "<b><font color=\"$color\">-</font></b>";
    $strmark = '-';
    if ($sum2 <> 0)	{
        $drob = (double)($arr_df[$o10] + $arr_df[$o46])/(double)($arr_df[$o11] + $arr_df[$o47]);
        $rez_proc =  $drob*100.0;

        $color = 'green';// get_string('status7color', 'block_monitoring');

        if ($indicator == 'null') {
            $itogmark = round ($rez_proc, 4);
            if ($ordering == 1) {
                $color = 'red';
                $itogmark *= -1;
            }
            $itogproc = '';
        } else {

            $two = explode ('#', $indicator);
            $procents = explode('~', $two[0]);
            $marks = explode('~', $two[1]);
            // print_r($procents); echo '<hr>';
            // print_r($marks); echo '<hr>';
            $itogmark = $itogproc = 0;
            foreach($procents as $key => $procent)	{
                if ($rez_proc <= $procent)	{
                    $itogmark = $marks[$key];
                    $itogproc = ' <= ' . $procent;
                    break;
                }
            }

            if ($rez_proc > 100)	{
                $itogmark = end($marks);
                $itogproc = ' > ' . 100 . '%';
            }
        }
        $dolja = number_format($rez_proc, 2, ',', '');
        $dolja .= '%';

        $strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>($arr_df[$o10] + $arr_df[$o46])/($arr_df[$o11] + $arr_df[$o47])=$dolja $itogproc</small>";

    }
    return 	$strmark;
}


// $formula = func_spo12#fn_2/(fn_1+fn_2)*100%
// $indicator = null
function func_spo12($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
	/*
	$operands = explode('/', $formula);
	$o1 = $operands[0];
	$formula2 = $operands[1];
	$operands2 = explode('*', $formula2);
	$o2 = $operands2[0];
    */
    $o1 = 'fn_1';
    $o2 = 'fn_2';
	
	/// echo $o1 . '   ' . $o2 . '<br>'; 
	$color = 'red';// get_string('status1color', 'block_monitoring');
	/// $strmark = "<b><font color=\"$color\">-</font></b>";
	$strmark = '-';
	if (!empty($arr_df[$o1]) && !empty($arr_df[$o2]))	{
	    $drob = (double)$arr_df[$o2]/(double)($arr_df[$o1] + $arr_df[$o2]);
		$rez_proc =  $drob*100.0;

        $color = 'green';// get_string('status7color', 'block_monitoring');
                
        if ($indicator == 'null') {
   			$itogmark = round ($drob, 4);
            if ($ordering == 1) {   
                $color = 'red';
                $itogmark *= -1;   
            }
   			$itogproc = '';			
        } else {
            
    		$two = explode ('#', $indicator);
    		$procents = explode('~', $two[0]);
    		$marks = explode('~', $two[1]);
    		// print_r($procents); echo '<hr>';
    		// print_r($marks); echo '<hr>';
    		$itogmark = $itogproc = 0;
    		foreach($procents as $key => $procent)	{
    			if ($rez_proc <= $procent)	{
    				$itogmark = $marks[$key];
    				$itogproc = ' <= ' . $procent; 
    				break;
    			}
    		}
    		
    		if ($rez_proc > 100)	{
    			$itogmark = end($marks);
    			$itogproc = ' > ' . 100 . '%';			
    		}
        }
		$dolja = number_format($rez_proc, 2, ',', '');
		$dolja .= '%';
	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
		$strmark .= "<br><small>($arr_df[$o2]/($arr_df[$o1] + $arr_df[$o2])=$dolja $itogproc)</small>";		
		
	}
	return 	$strmark;
}


// $formula =  func_spo05#fn_10+ fn_46/fn_11+ fn_47*100%  $yearid=7
// $formula =  func_spo05#fn_12+ fn_41/fn_37+ fn_42*100%  $yearid=12
function func_spo05($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
	/*
	$operands = explode('/', $formula);
	$o1 = $operands[0];
	$formula2 = $operands[1];
	$operands2 = explode('*', $formula2);
	$o2 = $operands2[0];
    */
    $o10 = 'fn_12'; // 'fn_10';
    $o11 = 'fn_37'; // 'fn_11';
    $o46 = 'fn_41'; // 'fn_46';
    $o47 = 'fn_42'; // 'fn_47';
    
    $sum1 = $arr_df[$o10] + $arr_df[$o46];
    $sum2 = $arr_df[$o11] + $arr_df[$o47];
	
	/// echo $o1 . '   ' . $o2 . '<br>'; 
	$color = 'red';// get_string('status1color', 'block_monitoring');
	/// $strmark = "<b><font color=\"$color\">-</font></b>";
	$strmark = '-';
	if ($sum2 <> 0)	{
	    $drob = (double)($arr_df[$o10] + $arr_df[$o46])/(double)($arr_df[$o11] + $arr_df[$o47]);
		$rez_proc =  $drob*100.0;

        $color = 'green';// get_string('status7color', 'block_monitoring');
                
        if ($indicator == 'null') {
   			$itogmark = round ($rez_proc, 4);
            if ($ordering == 1) {   
                $color = 'red';
                $itogmark *= -1;   
            }
   			$itogproc = '';			
        } else {
            
    		$two = explode ('#', $indicator);
    		$procents = explode('~', $two[0]);
    		$marks = explode('~', $two[1]);
    		// print_r($procents); echo '<hr>';
    		// print_r($marks); echo '<hr>';
    		$itogmark = $itogproc = 0;
    		foreach($procents as $key => $procent)	{
    			if ($rez_proc <= $procent)	{
    				$itogmark = $marks[$key];
    				$itogproc = ' <= ' . $procent; 
    				break;
    			}
    		}
    		
    		if ($rez_proc > 100)	{
    			$itogmark = end($marks);
    			$itogproc = ' > ' . 100 . '%';			
    		}
        }
		$dolja = number_format($rez_proc, 2, ',', '');
		$dolja .= '%';
	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
		$strmark .= "<br><small>($arr_df[$o10] + $arr_df[$o46])/($arr_df[$o11] + $arr_df[$o47])=$dolja $itogproc</small>";
		
	}
	return 	$strmark;
}



// $formula =  func_spo06#fn_12+fn_48/fn_11+fn_47*100%  -- yearid = 7
// $formula =  func_spo06#fn_25+fn_24/fn_27+fn_26*100%  -- yearid = 14 ((fn_25+ fn_24)/(fn_27+ fn_26))*100
function func_spo06($formula, $indicator, &$arr_df, $ordering = 0)
{
	global $itogmark;
	
	$itogmark = 0;
	/*
	$operands = explode('/', $formula);
	$o1 = $operands[0];
	$formula2 = $operands[1];
	$operands2 = explode('*', $formula2);
	$o2 = $operands2[0];
    */
    $o12 = 'fn_25'; // 'fn_12';
    $o11 = 'fn_27'; // 'fn_11';
    $o48 = 'fn_24'; // 'fn_48';
    $o47 = 'fn_26'; // 'fn_47';
    
    $sum1 = $arr_df[$o12] + $arr_df[$o48];
    $sum2 = $arr_df[$o11] + $arr_df[$o47];
	
	/// echo $o1 . '   ' . $o2 . '<br>'; 
	$color = 'red';// get_string('status1color', 'block_monitoring');
	/// $strmark = "<b><font color=\"$color\">-</font></b>";
	$strmark = '-';
	if ($sum2 <> 0)	{
	    $drob = (double)($arr_df[$o12] + $arr_df[$o48])/(double)($arr_df[$o11] + $arr_df[$o47]);
		$rez_proc =  $drob*100.0;

        $color = 'green';// get_string('status7color', 'block_monitoring');
                
        if ($indicator == 'null') {
   			$itogmark = round ($rez_proc, 4);
            if ($ordering == 1) {   
                $color = 'red';
                $itogmark *= -1;   
            }
   			$itogproc = '';			
        } else {
            
    		$two = explode ('#', $indicator);
    		$procents = explode('~', $two[0]);
    		$marks = explode('~', $two[1]);
    		// print_r($procents); echo '<hr>';
    		// print_r($marks); echo '<hr>';
    		$itogmark = $itogproc = 0;
    		foreach($procents as $key => $procent)	{
    			if ($rez_proc <= $procent)	{
    				$itogmark = $marks[$key];
    				$itogproc = ' <= ' . $procent; 
    				break;
    			}
    		}
    		
    		if ($rez_proc > 100)	{
    			$itogmark = end($marks);
    			$itogproc = ' > ' . 100 . '%';			
    		}
        }
		$dolja = number_format($rez_proc, 2, ',', '');
		$dolja .= '%';
	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
		$strmark .= "<br><small>($arr_df[$o12] + $arr_df[$o48])/($arr_df[$o11] + $arr_df[$o47])=$dolja $itogproc</small>";
		
	}
	return 	$strmark;
}


// func_spo08#fn_15/35 
// indicator empty
function func_spo08_old($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
	
	// $color = 'red'; 
	// $strmark = "<b><font color=\"$color\">0</font></b>";
	$strmark = '-';

	$operands = explode ('/', $formula);
	$field_name = trim($operands[0]);
	$koeff = trim($operands[1]);
	 
	if (empty($arr_df[$field_name]))  return $strmark;
	
	$dolja = $arr_df[$field_name];
	if ($dolja < 0) $dolja = 0;
	$itogmark = round ($dolja / $koeff, 4);
	
	$color = 'green';// get_string('status7color', 'block_monitoring');
	$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
	

	$strmark .= "<br><small>($dolja/$koeff)</small>";		
		
	return 	$strmark;
}



// func_spo08#45/fn_15 
// indicator empty
function func_spo08($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
	
	// $color = 'red'; 
	// $strmark = "<b><font color=\"$color\">0</font></b>";
	$strmark = '-';

	$operands = explode ('/', $formula);
    $koeff = trim($operands[0]);
	$field_name = trim($operands[1]);
	 
	if (empty($arr_df[$field_name]))  return $strmark;
	
	$dolja = $arr_df[$field_name];
	if ($dolja < 0) $dolja = 0;
	// $itogmark = round ( $koeff /$dolja , 4);
    $itogmark = round ($koeff/$dolja, 4);
	
    if ($ordering == 1) {   
        $color = 'red';
        $itogmark *= -1;   
    } else {
	   $color = 'green';// get_string('status7color', 'block_monitoring');
    }
       
	$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
	$strmark .= "<br><small>($koeff/$dolja)</small>";		
		
	return 	$strmark;
}


// func_spo_zp#fn_26/21000*100% 
// indicator empty
function func_spo_zp($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
	
	// $color = 'red'; 
	// $strmark = "<b><font color=\"$color\">0</font></b>";
	$strmark = '-';

	$operands = explode ('/', $formula);
	$field_name = trim($operands[0]);
	$koeff = trim($operands[1]);
	 
	if (empty($arr_df[$field_name]))  return $strmark;
	
	$dolja = $arr_df[$field_name];
	if ($dolja < 0) $dolja = 0;
	$itogmark = round ($dolja / $koeff, 4)*100.0;
	
	$dolja = number_format($rez_proc, 2, ',', '');
	$dolja .= '%';
	
    $color = 'green';
	$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
	$strmark .= "<br><small>($arr_df[$field_name]/$koeff = $itogmark)</small>";		
    
	return 	$strmark;
}

// func_div_4#fn_1/4*100
function func_div_4($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
    
    // echo $formula . '<hr>';
    // print_object($arr_df);

	$operands = explode('/', $formula);
	$o1 = $operands[0]; 
   

	$strmark = '-';
	if (!empty($arr_df[$o1]))	{
	    $itogmark = round ($arr_df[$o1]/4*100, 4);

     	$color = 'green';
    	$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>($arr_df[$o1]/4*100 = $itogmark)</small>";
	}
    	
	return 	$strmark;
}


// func_div_const#fn_28/10
function func_div_const($formula, $indicator, &$arr_df, $ordering = 0)
{
	global $itogmark;
	
	$itogmark = 0;
    
    // echo $formula . '<hr>';
    // print_object($arr_df);

	$operands = explode('/', $formula);
	$o1 = $operands[0];
    $o2 = trim($operands[1]);
   

	$strmark = '-';
	if (!empty($arr_df[$o1]) && $o2 != 0)	{
	    $itogmark = round ($arr_df[$o1]/$o2, 4);
        $color = 'green';
        if ($ordering == 1) {
            $color = 'red';
            $itogmark *= -1;
        }

    	$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>($arr_df[$o1]/$o2 = $itogmark)</small>";
	}
    	
	return 	$strmark;
}


function func_div_div_const($formula, $indicator, &$arr_df, $ordering = 0)
{
    global $itogmark;

    $itogmark = 0;

    // echo $formula . '<hr>';
    // print_object($arr_df);

    $operands = explode('/', $formula);
    $o1 = $operands[0]; // fk_25
    $o2 = $operands[1]; // fk_07
    $o3 = trim($operands[2]);

    $strmark = '-';
    if (!empty($arr_df[$o1]) && !empty($arr_df[$o2]) && $o3 != 0)	{
        $itogmark = round ($arr_df[$o1]/$arr_df[$o2]/$o3, 4);

        if ($ordering == 1)  {
            $color = 'red';
            $itogmark *= -1;
        } else {
            $color = 'green';
        }

        $strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>($arr_df[$o1]/$arr_df[$o2]/$o3 = $itogmark)</small>";
    }

    return 	$strmark;
}



// $formula =  fun_sum_3#fn_51/fn_34+fn_52+ fn_53*100
function fun_sum_3($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;

    $o34 = 'fn_34';
    $o51 = 'fn_51';
    $o52 = 'fn_52';
    $o53 = 'fn_53';
    
    $sum = 0;
    if (isset($arr_df[$o34])) {
        $sum += $arr_df[$o34];
    }
    if (isset($arr_df[$o52])) {
        $sum += $arr_df[$o52];
    }
    if (isset($arr_df[$o53])) {
        $sum += $arr_df[$o53];
    }
    if (isset($arr_df[$o51])) {
        $sum += $arr_df[$o51];
    }
    
    if ($sum > 0)
        $itogmark = round($arr_df[$o51]/$sum*100.0, 4);  

    $color = 'green';
    if ($ordering == 1) {   
        $color = 'red';
        $itogmark *= -1;   
    }
	
	$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
	$strmark .= "<br><small>$arr_df[$o51]/($arr_df[$o34] + $arr_df[$o52] + $arr_df[$o53] + $arr_df[$o51])*100 = $itogmark</small>";		
		
	return 	$strmark;
}


// $formula =  fun_sum_4#fn_61+fn_62/fn_61+fn_62+ fn_63*100
function fun_sum_4($formula, $indicator, &$arr_df, $ordering = 0)
{
    global $itogmark;

    $itogmark = 0;

    // $o34 = 'fn_34';
    $o61 = 'fn_61';
    $o62 = 'fn_62';
    $o63 = 'fn_63';

    $sum = $chisl = $znam = 0;
    
    if (isset($arr_df[$o61])) {
        $chisl += $arr_df[$o61];
        $znam  += $arr_df[$o61];
    }
    if (isset($arr_df[$o62])) {  //  && isset($arr_df[$o61]) && !empty($arr_df[$o61])
        $chisl += $arr_df[$o62];
        $znam += $arr_df[$o62];
    }
    
    if (isset($arr_df[$o63])) {
        $znam += $arr_df[$o63];
    }
    
    if ($znam > 0)  {
        $sum += $chisl / $znam * 100;
    }
    
    $itogmark = round($sum, 4);  
    /*
    if ($sum > 0)
        $itogmark = round($arr_df[$o51]/$sum*100.0, 4);
    */

    $color = 'green';
    if ($ordering == 1) {
        $color = 'red';
        $itogmark *= -1;
    }

    $strmark = "<b><font color=\"$color\">$itogmark</font></b>";
    $strmark .= "<br><small>($arr_df[$o61] + $arr_df[$o62])/($arr_df[$o61] + $arr_df[$o62] + $arr_df[$o63])*100 = $itogmark</small>";

    return 	$strmark;
}



function calculate_college_mark($yid, $rid, $oid, $id, $shortname)
{
	global $db, $CFG;
	 
 	// echo $yid . '<hr>';
 
	$arr_df = array();
    $sql = "SELECT * FROM {$CFG->prefix}monit_form_$shortname WHERE id=$id";
    // echo  $sql;
	if ($df = get_record_sql($sql))	{
		$arr_df = (array)$df;
		// print_r($arr_df); echo '<hr>';   	
	}
    //print_object($arr_df); echo '<hr>';
    
    $totalmark = 0;
    // init_rating_parameters($yid, $shortname, $select, $order);
    $shortname = optional_param('sn', 'rating_spo');
    
    if ($yid < 9)  {    
        $select = "yearid = 7 AND gradelevel = 1";
    } else {
        $select = "yearid = $yid AND gradelevel = 1";
    }    
    $select .=  " AND edizm <> 'null'";
    $order = 'sortnumber, id'; 

    $strsql = "SELECT id, number, formula, edizm, indicator, ordering FROM {$CFG->prefix}monit_rating_criteria
    		   WHERE $select
			   ORDER BY $order";
    // echo $strsql .' <br />';           
	if ($criterias = get_records_sql($strsql)) 	{
		
		$criteriaids = array();
   		foreach($criterias as $criteria)	{
   			$criteriaids[] = $criteria->id;
	  	}
   		$criterialist = implode(',', $criteriaids);

		$strsql = "UPDATE {$CFG->prefix}monit_rating_college mark=0 WHERE (yearid=$yid) AND (collegeid=$oid) AND (criteriaid in ($criterialist))";
		$db->Execute($strsql);		
		// delete_records('monit_rating_school', 'schoolid', $sid);
		// set_field('monit_rating_school', 'mark', 0, 'schoolid', $sid);
		// print_object($criterias); exit();
        
        $totalmark = calculating_rating_college($yid, $rid, $oid, $shortname, $arr_df, $criterias);
	}

    // update_rating_total($yid, $rid, $sid, $shortname, $totalmark);
	
	return $totalmark;
}



function calculating_rating_college($yid, $rid, $oid, $shortname, $arr_df, $criterias)
{
    global $db, $CFG, $itogmark;
    
	$totalmark = 0;    
    // print_object($criterias);
	foreach($criterias as $criteria)	{
		$itogmark = 0;
		if ($criteria->formula == 'null')	continue;
		$operands = explode('#', $criteria->formula);
		$o1 = trim($operands[0]);
		$o2 = trim($operands[1]);
       	if (function_exists($o1))   {
			if (!empty($arr_df))	{
                if (function_exists($o1)) {
               		$namefunc = $o1;
               		$strmark = $namefunc($o2, $criteria->indicator, $arr_df, $criteria->ordering);
				} 
			} else {
				$strmark = '-';
			}	
            /*
            if ($criteria->ordering == 1)   {
                $itogmark *= -1;    
            }
            */
            
       		$totalmark += $itogmark;
            // echo "$totalsum += $itogmark;<br>" . $strmark . '<br>';
            // echo "$totalsum<hr>";
            // if ($criteria->id == 411) {
            // echo $namefunc . ' = ' . $criteria->id . ' = ' . $itogmark . '<hr>';
            // }     
			if ($markschool = get_record_sql("SELECT id, mark 
											  FROM {$CFG->prefix}monit_rating_college
			 								  WHERE yearid=$yid AND collegeid=$oid AND criteriaid=$criteria->id")) {
			 	set_field('monit_rating_college', 'mark', $itogmark, 'id', $markschool->id);							  	
		   } else {
		        $markschool = new stdClass();
		   		$markschool->yearid = $yid;
		   		$markschool->rayonid = $rid;
		        $markschool->collegeid = $oid;
				$markschool->ratingcategoryid = 1;
				$markschool->criteriaid = $criteria->id;
				$markschool->mark = $itogmark;
				$markschool->rationum = 0;
                // print_object($markschool);
                
				if (!insert_record('monit_rating_college', $markschool))	{
					error('Not insert rating school.', "listforms.php?rid=$rid&amp;yid=$yid&amp;oid=$oid");
				}
                
		   }      
		}  else {
		    notify ("Function $o1 not found.");
		}
	} // foreach criterias	   
    return 	$totalmark;
}
  

// $formula = f_r1_01/f_r1_02*100%
// $indicator = 50~55~59~69~100#0~1~2~3~4
function func_proc_spo($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
	
	$operands = explode('/', $formula);
	$o1 = $operands[0];
	$formula2 = $operands[1];
	$operands2 = explode('*', $formula2);
	$o2 = $operands2[0];
	$sto = (float)$operands2[1];

	/// echo $o1 . '   ' . $o2 . '<br>'; 
	$color = 'red';// get_string('status1color', 'block_monitoring');
	/// $strmark = "<b><font color=\"$color\">-</font></b>";
	$strmark = '-';
	if (!empty($arr_df[$o1]) && !empty($arr_df[$o2]))	{
	    $drob = (double)$arr_df[$o1]/(double)$arr_df[$o2];
		// $rez_proc =  $drob*100.0;
        $rez_proc =  $drob*$sto;

        $color = 'green';// get_string('status7color', 'block_monitoring');
                
        if ($indicator == 'null') {
   			// $itogmark = round ($drob, 4);
            $itogmark = round ($rez_proc, 4);
            if ($ordering == 1) {   
                $color = 'red';
                $itogmark *= -1;   
            }
   			$itogproc = '';			
        } else {
            
    		$two = explode ('#', $indicator);
    		$procents = explode('~', $two[0]);
    		$marks = explode('~', $two[1]);
    		// print_r($procents); echo '<hr>';
    		// print_r($marks); echo '<hr>';
    		$itogmark = $itogproc = 0;
    		foreach($procents as $key => $procent)	{
    			if ($rez_proc <= $procent)	{
    				$itogmark = $marks[$key];
    				$itogproc = ' <= ' . $procent; 
    				break;
    			}
    		}
    		
    		if ($rez_proc > 100)	{
    			$itogmark = end($marks);
    			$itogproc = ' > ' . 100 . '%';			
    		}
        }
		$dolja = number_format($rez_proc, 2, ',', '');
		$dolja .= '%';
	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
		$strmark .= "<br><small>(($arr_df[$o1]/$arr_df[$o2])*$sto=$dolja $itogproc)</small>";
		
	}
	return 	$strmark;
}


// func_mul_const#fn_20*10
function func_mul_const($formula, $indicator, &$arr_df, $ordering = 0)
{
    global $itogmark;

    $itogmark = 0;

    // echo $formula . '<hr>';
    // print_object($arr_df);

    $operands = explode('*', $formula);
    $o1 = $operands[0];
    $o2 = trim($operands[1]);


    $strmark = '-';
    if (!empty($arr_df[$o1]) && $o2 != 0)	{
        $itogmark = round ($arr_df[$o1]*$o2, 4);
        $color = 'green';
        if ($ordering == 1) {
            $color = 'red';
            $itogmark *= -1;
        }

        $strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>($arr_df[$o1]*$o2 = $itogmark)</small>";
    }

    return 	$strmark;
}
