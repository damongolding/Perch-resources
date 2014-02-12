<?php

/* ------------ IMAGE ------------ */

class PerchFieldType_base64blur extends PerchFieldType
{
    public static $file_paths = array();

    public function base64_encode_image($filename=string,$orginalImg) {

    	$filetype = '.' . pathinfo($filename, PATHINFO_EXTENSION);
    	$file_path = dirname(__FILE__) . '/../../../resources/' . $filename;

		if (file_exists($file_path)) {

            $imageBlur = new Imagick($file_path);

            $imageBlurAmount = $this->Tag->bluramount();

            $imageBlur->blurImage(0,$imageBlurAmount);

        	return 'data:image/' . $filetype . ';base64,' . base64_encode($imageBlur);
		}
		else{
			return $file_path;
		}
	}
    

    
    public function render_inputs($details=array())
    {
        $Perch = Perch::fetch();
        $bucket = $Perch->get_resource_bucket($this->Tag->bucket());

        $PerchImage = new PerchImage;
        $s = $this->Form->image($this->Tag->input_id());
        $s .= $this->Form->hidden($this->Tag->input_id().'_field', '1');


        PerchUtil::initialise_resource_bucket($bucket);

        if (!is_writable($bucket['file_path'])) {
            $s .= $this->Form->hint(PerchLang::get('Your resources folder is not writable. Make this folder (') . PerchUtil::html($bucket['web_path']) . PerchLang::get(') writable to upload images.'), 'error');
        }  


        if (isset($details[$this->Tag->input_id()]) && $details[$this->Tag->input_id()]!='') {
            $json = $details[$this->Tag->input_id()];

            $bucket = $Perch->get_resource_bucket($json['bucket']);

            if (isset($json['sizes']['thumb'])) {
                $image_src  = $json['sizes']['thumb']['path'];
                $image_w    = $json['sizes']['thumb']['w'];
                $image_h    = $json['sizes']['thumb']['h'];
            }else{
                // For items imported from previous version
                $image_src = str_replace(PERCH_RESPATH, '', $PerchImage->get_resized_filename($json, 150, 150, 'thumb'));
                $image_w   = '';
                $image_h   = '';
            }
            
            $image_path = PerchUtil::file_path($bucket['file_path'].'/'.$image_src);

            PerchUtil::debug($image_path);
            if (file_exists($image_path)) {
                $s .= '<img class="preview" src="'.PerchUtil::html($bucket['web_path'].'/'.$image_src).'" width="'.$image_w.'" height="'.$image_h.'" alt="Preview" />';
                $s .= '<div class="remove">';
                $s .= $this->Form->checkbox($this->Tag->input_id().'_remove', '1', 0).' '.$this->Form->label($this->Tag->input_id().'_remove', PerchLang::get('Remove image'), 'inline');
                $s .= $this->Form->hidden($this->Tag->input_id().'_populated', '1');
                $s .= '</div>';
            }
        }
        return $s;
    }
    
    public function get_raw($post=false, $Item=false) 
    {
        $store = array();

        $Perch = Perch::fetch();
        $bucket = $Perch->get_resource_bucket($this->Tag->bucket());
        
        $image_folder_writable = is_writable($bucket['file_path']);
        
        $item_id = $this->Tag->input_id();

        if ($image_folder_writable && isset($_FILES[$item_id]) && (int) $_FILES[$item_id]['size'] > 0) {
                       
            if (!isset(self::$file_paths[$this->Tag->id()])) {
            
                $filename = PerchUtil::tidy_file_name($_FILES[$item_id]['name']);
                if (strpos($filename, '.php')!==false) $filename .= '.txt'; // diffuse PHP files              

                $target = PerchUtil::file_path($bucket['file_path'].DIRECTORY_SEPARATOR.$filename);
                if (file_exists($target)) {                                        
                    $dot = strrpos($filename, '.');
                    $filename_a = substr($filename, 0, $dot);
                    $filename_b = substr($filename, $dot);

                    $count = 1;
                    while (file_exists($bucket['file_path'].DIRECTORY_SEPARATOR.PerchUtil::tidy_file_name($filename_a.'-'.$count.$filename_b))) {
                        $count++;
                    }

                    $filename = PerchUtil::tidy_file_name($filename_a . '-' . $count . $filename_b);
                    $target = $bucket['file_path'].DIRECTORY_SEPARATOR.$filename;
            
                }
                                    
                PerchUtil::move_uploaded_file($_FILES[$item_id]['tmp_name'], $target);
                self::$file_paths[$this->Tag->id()] = $target;     
                    
                $store['_default'] = rtrim($bucket['web_path'], '/').'/'.$filename;
                $store['path'] = $filename;
                $store['size'] = filesize($target);
                $store['bucket'] = $bucket['name'];

                $size = getimagesize($target);
                if (PerchUtil::count($size)) {
                    $store['w'] = $size[0];
                    $store['h'] = $size[1];
                }
                       
        
                // thumbnail
                if ($this->Tag->type()=='base64blur') {
                    $PerchImage = new PerchImage;
                    $PerchImage->set_density(2);
                    $result = $PerchImage->resize_image($target, 150, 150, false, 'thumb');
                    if (is_array($result)) {
                        if (!isset($store['sizes'])) $store['sizes'] = array();
                    
                        $variant_key = 'thumb';
                        $tmp = array();
                        $tmp['w'] = $result['w'];
                        $tmp['h'] = $result['h'];
                        $tmp['path'] = $result['file_name'];
                        $tmp['size'] = filesize($result['file_path']);
                        $tmp['mime'] = (isset($result['mime']) ? $result['mime'] : '');   
                        
                        $store['sizes'][$variant_key] = $tmp;
                    }
                    unset($result);
                    unset($PerchImage);
                }
                
                
            }
        }else{
            PerchUtil::debug('Error: '.$item_id, 'error');
        }

        // Loop through all tags with this ID, get their dimensions and resize the images.
        $all_tags = $this->get_sibling_tags();
        
        if (PerchUtil::count($all_tags)) {
            foreach($all_tags as $Tag) {
                if ($Tag->id()==$this->Tag->id()) {
                    // This is either this tag, or another tag in the template with the same ID.
                    
                    if ($Tag->type()=='image' && ($Tag->width() || $Tag->height()) && isset(self::$file_paths[$Tag->id()])) {

                        $variant_key = 'w'.$Tag->width().'h'.$Tag->height().'c'.($Tag->crop() ? '1' : '0').($Tag->density() ? '@'.$Tag->density().'x': '');

                        if (!isset($store['sizes'][$variant_key])) {

                            $PerchImage = new PerchImage;
                            if ($Tag->quality()) $PerchImage->set_quality($Tag->quality());
                            if ($Tag->sharpen()) $PerchImage->set_sharpening($Tag->sharpen());
                            if ($Tag->density()) $PerchImage->set_density($Tag->density());
                            $result = $PerchImage->resize_image(self::$file_paths[$Tag->id()], $Tag->width(), $Tag->height(), $Tag->crop());
                            
                            if (is_array($result)) {
                                if (!isset($store['sizes'])) $store['sizes'] = array();
                                                            
                                $tmp = array();
                                $tmp['w'] = $result['w'];
                                $tmp['h'] = $result['h'];
                                $tmp['density'] = ($Tag->density() ? $Tag->density() : '1');
                                $tmp['path'] = $result['file_name'];
                                $tmp['size'] = filesize($result['file_path']);
                                $tmp['mime'] = (isset($result['mime']) ? $result['mime'] : '');    

                                $store['sizes'][$variant_key] = $tmp;

                                unset($tmp);
                            }

                            unset($result);
                            unset($PerchImage);
                        }
                    }
                }
            }
        }
        

        // If a file isn't uploaded...
        if (!isset($_FILES[$item_id]) || (int) $_FILES[$item_id]['size'] == 0) {
            // If remove is checked, remove it.
            if (isset($_POST[$item_id.'_remove'])) {
                $store = array();
            }else{
                // Else get the previous data and reuse it.
                if (is_object($Item)){
                    
                    $json = PerchUtil::json_safe_decode($Item->itemJSON(), true);
                    
                    /*
                    PerchUtil::debug('Item: '. $item_id);
                    PerchUtil::debug($json);
                    PerchUtil::debug($this->Tag);
                    */

                    if (PerchUtil::count($json) && $this->Tag->in_repeater() && $this->Tag->tag_context()) {
                        $waypoints = preg_split('/_([0-9]+)_/', $this->Tag->tag_context(), null, PREG_SPLIT_DELIM_CAPTURE);
                        if (PerchUtil::count($waypoints) > 0) {
                            $subject = $json;
                            foreach($waypoints as $waypoint) {
                                if (isset($subject[$waypoint])) {
                                    $subject = $subject[$waypoint];
                                }else{
                                    $subject = false;
                                }
                                $store = $subject;
                            }
                        } 
                    }

                    if (PerchUtil::count($json) && isset($json[$this->Tag->id()])) {
                        $store = $json[$this->Tag->id()];
                    }
                }else if (is_array($Item)) {
                    $json = $Item;
                    if (PerchUtil::count($json) && isset($json[$this->Tag->id()])) {
                        $store = $json[$this->Tag->id()];
                    }
                }
            }                                
        }

        // log resources
        if (PerchUtil::count($store)) {
            $Resources = new PerchResources;

            // Main image
            $parentID = $Resources->log($this->app_id, $store['bucket'], $store['path'], 0, 'orig');

            // variants
            if (isset($store['sizes']) && PerchUtil::count($store['sizes'])) {
                foreach($store['sizes'] as $key=>$size) {
                    if ($key == 'thumb') {
                        $Resources->log($this->app_id, $store['bucket'], $size['path'], $parentID, 'thumb');
                    }else{
                        $Resources->log($this->app_id, $store['bucket'], $size['path'], $parentID);
                    }
                    
                }
            }
        }
        
        self::$file_paths = array();
        
        return $store;
    }
    
    public function get_processed($raw=false)
    {    
        $json = $raw;
        if (is_array($json)) {
            
            $item = $json;
            $orig_item = $item; // item gets overriden by a variant.
            
            if ($this->Tag->width() || $this->Tag->height()) {
                $variant_key = 'w'.$this->Tag->width().'h'.$this->Tag->height().'c'.($this->Tag->crop() ? '1' : '0').($this->Tag->density() ? '@'.$this->Tag->density().'x': '');
                if (isset($json['sizes'][$variant_key])) {
                    $item = $json['sizes'][$variant_key];
                }
            }           
            
            if ($this->Tag->output() && $this->Tag->output()!='path') {
                switch($this->Tag->output()) {        
                    case 'size':
                        return isset($item['size']) ? $item['size'] : 0; 
                        break;
                        
                    case 'h':
                    case 'height':
                        return isset($item['h']) ? $item['h'] : 0;
                        break;

                    case 'w':
                    case 'width':
                        return isset($item['w']) ? $item['w'] : 0;
                        break;
					
					case 'filename':
						return $item['path'];
						break;

                    case 'mime':
                        return $item['mime'];
                        break;

                    case 'tag':
                        $attrs = array(
                            'src'=> $this->_get_image_src($orig_item, $item),
                        );

                        if (!PERCH_RWD) {
                            $attrs['width']  = isset($item['w']) ? $item['w'] : '';
                            $attrs['height'] = isset($item['h']) ? $item['h'] : '';
                        }

                        $tags = array('class', 'title', 'alt');
                        $dont_escape = array();

                        foreach($tags as $tag) {
                            if ($this->Tag->$tag()) {
                                $val = $this->Tag->$tag();
                                if (substr($val, 0, 1)=='{' && substr($val, -1)=='}') {
                                    $attrs[$tag] = '<'.$this->Tag->tag_name().' id="'.str_replace(array('{','}'), '', $val).'" escape="true" />';
                                    $dont_escape[] = $tag;
                                }else{
                                    $attrs[$tag] = PerchUtil::html($val, true);
                                }
                            }
                        }

                        $this->processed_output_is_markup = true;


                        return PerchXMLTag::create('img', 'single', $attrs, $dont_escape);

                        break;
                }
            }

            return $this->_get_image_src($orig_item, $item);
            
        }

        if ($this->Tag->width() || $this->Tag->height()) {
            $PerchImage = new PerchImage;
            return $PerchImage->get_resized_filename($raw, $this->Tag->width(), $this->Tag->height());
        }



        return PERCH_RESPATH.'/'.str_replace(PERCH_RESPATH.'/', '', $raw);
    }
    
    public function get_search_text($raw=false)
    {
        return '';
    }

    public function render_admin_listing($details=false)
    {
        $s = '';

        if (is_array($details)) {

            if ($this->Tag->output()) {
                return $this->get_processed($details);
            }

            $Perch = Perch::fetch();
            $bucket = $Perch->get_resource_bucket($this->Tag->bucket());

            $PerchImage = new PerchImage;          
            
            $json = $details;

            $bucket = $Perch->get_resource_bucket($json['bucket']);

            if (isset($json['sizes']['thumb'])) {
                $image_src  = $json['sizes']['thumb']['path'];
                $image_w    = $json['sizes']['thumb']['w'];
                $image_h    = $json['sizes']['thumb']['h'];
            }
            
            $image_path = PerchUtil::file_path($bucket['file_path'].'/'.$image_src);

            if (file_exists($image_path)) {
                $s .= '<img src="'.PerchUtil::html($bucket['web_path'].'/'.$image_src).'" width="'.($image_w/2).'" height="'.($image_h/2).'" alt="Preview" />';
            }
        }
            
        return $s;
    }

    private function _get_image_src($orig_item, $item)
    {
        $Perch = Perch::fetch();

        if (isset($orig_item['bucket'])) {
            $bucket = $Perch->get_resource_bucket($orig_item['bucket']);
        }else{
            $bucket = $Perch->get_resource_bucket($this->Tag->bucket());
        }

        $orginalImg = $bucket['web_path'].'/'.str_replace($bucket['web_path'].'/', '', $item['path']);               
        
        return $this->base64_encode_image($item['path'], $orginalImg);
    }

}