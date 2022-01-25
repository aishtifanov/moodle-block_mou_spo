<?php // $Id: to_excel.php,v 1.5 2008/10/16 10:25:08 Shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib_excel.php');
    require_once('../monitoring/lib.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $oid = required_param('oid', PARAM_INT);          // School id
    $yid = required_param('yid', PARAM_INT);       		// Year id
    $rkp = optional_param('sn', '');       				// Shortname
    $nm  = optional_param('nm', date('n'), PARAM_INT);       // Month number
	$action   = optional_param('action', '');
    $levelmonit  = optional_param('level', 'school');

    if ($action == 'excel') {
        $datefrom = get_date_from_month_year($nm, $yid);
        print_excel_header($levelmonit.'_'.$oid.'_'.$nm.'_'.$rkp);
		create_excel_workbook();
		print_excel_form($rkp, $datefrom, $levelmonit, $rid, $oid, $yid, '', false, true);
		close_excel_workbook();
        exit();
	}
?>
