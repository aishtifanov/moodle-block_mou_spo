<?php // $Id: block_mou_school.php,v 1.22 2011/10/25 05:43:33 shtifanov Exp $

require_once('lib_spo_menu.php');

class block_mou_spo extends block_list {

    function init() {
        $this->title = get_string('title','block_mou_spo');
        $this->version = 2010210400;
    }

    function get_content() {
        global $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
            $this->content = '';
        } else {
            $this->load_content();
        }

        return $this->content;
        }

    function load_content() {
        global $CFG, $yearmonit, $USER, $rid, $sid, $yid;

        $rid = 0;
        $sid = 0;
        $yid = 14;// $yearmonit;  !!!!!!!!!!!!!!!!!!!!1
        $items = array();
        $icons = array();
        $index_items = get_items_menu_block_spo ($items, $icons); 
		
		if (!empty($index_items))	{			
			foreach ($index_items as $index_item)	{
				$this->content->items[] = $items[$index_item];
				$this->content->icons[] = $icons[$index_item];
			}

        	$this->content->footer = '<a href="'.$CFG->wwwroot.'/blocks/mou_spo/index.php">'.get_string('title','block_mou_spo').'</a>'.' ...';
 		}
   
	}	    

  }
