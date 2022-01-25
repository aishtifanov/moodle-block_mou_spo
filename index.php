<?php // $Id: index.php,v 1.40 2011/09/21 06:39:10 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('lib_spo_menu.php');    

    require_login();
    
    $rid = optional_param('rid', 0, PARAM_INT);       // Rayon id
    $sid = optional_param('sid', 0, PARAM_INT);       // School id
    $yid = optional_param('yid', 0, PARAM_INT);       // School id


    $yid = get_current_edu_year_id();
    
    $strmonit = get_string('indextitle','block_mou_spo');
    print_header_mou("$SITE->shortname: $strmonit", $SITE->fullname, $strmonit);
    
    print_heading($strmonit);

    $table = new stdClass();
    $table->align = array ('left', 'left');
    $table->size = array ('20%', '80%');

    $items = array();
    $icons = array();
    $index_items = get_items_menu_block_spo ($items, $icons); 

	if (!empty($index_items))	{			
		foreach ($index_items as $index_item)	{
		    $table->data[] = array("<strong>{$items[$index_item]}</strong>" , 
                                    get_string ('description_'.$index_item, 'block_mou_spo'));
		}
	}

    print_table($table);

    print_footer();

?>