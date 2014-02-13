<?php

class PerchFieldType_colour extends PerchFieldType
{
	/*
     * This variable is used to ensure that our custom CSS is only inserted once.
     */

    private static $page_resources_added = false;

	public function render_inputs($details=array())
	{

        $id = $this->Tag->id(); 
        $s = $this->Form->text($this->Tag->input_id(), $this->Form->get($details, $id, $this->Tag->default(), $this->Tag->post_prefix()), $this->Tag->size(), $this->Tag->maxlength(),"iris text");
        $s .= '<div class="' .$id. ' colourPreview"></div>';

        return $s;

	}

    public function add_page_resources()
    {
        if ($this::$page_resources_added) {
            return;
        }

        $asset_path = PERCH_LOGINPATH.'/addons/fieldtypes/colour/assets';

        $Perch = Perch::fetch();
        $Perch->add_foot_content('<!-- BEGIN: Include files for the color fieldtype -->');
        $Perch->add_foot_content('<link rel="stylesheet" href="'.$asset_path.'/iris.min.css" />');
        $Perch->add_foot_content('<script src="'.$asset_path.'/jquery-ui.js"></script>');
        $Perch->add_foot_content('<script src="'.$asset_path.'/iris.min.js"></script>');
        $Perch->add_foot_content('<script src="'.$asset_path.'/colour.init.js"></script>');
        $Perch->add_foot_content('<!-- END: Include files for the color fieldtype -->');

        $this::$page_resources_added = true;

    }

}