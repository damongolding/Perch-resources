<?php
    # include the API
    include('../../../../../core/inc/api.php');
    

    /* -------- GET THE RESOURCE BUCKET TO USE ---------- */
    $bucket_name  = 'default';

    if (isset($_POST['bucket'])) {
        $bucket_name = $_POST['bucket'];
    }

    $Perch  = Perch::fetch();
    $Bucket = PerchResourceBuckets::get($bucket_name);

    if ($Bucket) $Bucket->initialise();


    $file       = $_FILES['file']['name'];
    $filesize   = $_FILES['file']['size'];

    //if the file is greater than 0, process it into resources
    if($filesize > 0) {
    	
    	if ($Bucket->ready_to_write() && isset($file)) {

        	$target = $Bucket->write_file($_FILES['file']['tmp_name'], $_FILES['file']['name']);
            
            $urlpath = $Bucket->get_web_path().'/'.$target['name'];
                    

            if(isset($_GET['filetype']) && $_GET['filetype'] == 'image') {

                $width   = 640;
                $height  = false;
                $crop    = false;
                $quality = false;
                $density = false;
                $sharpen = false;


                if (isset($_POST['width']) && is_numeric($_POST['width'])) {
                    $width = (int) $_POST['width'];
                }              

                if (isset($_POST['height']) && is_numeric($_POST['height'])) {
                    $height = (int) $_POST['height'];
                }

                if (isset($_POST['quality']) && is_numeric($_POST['quality'])) {
                    $quality = (int) $_POST['quality'];
                }

                if (isset($_POST['density']) && is_numeric($_POST['density'])) {
                    $density = (int) $_POST['density'];
                }

                if (isset($_POST['sharpen']) && is_numeric($_POST['sharpen'])) {
                    $sharpen = (int) $_POST['sharpen'];
                }

                if (isset($_POST['crop']) && $_POST['crop']=='true') {
                    $crop = true;
                }
                
                $PerchImage = new PerchImage();

                if ($quality) $PerchImage->set_quality($quality);
                if ($sharpen) $PerchImage->set_sharpening($sharpen);
                if ($density) $PerchImage->set_density($density);

                $result = $PerchImage->resize_image($target['path'], $width, $height, $crop);

                if (is_array($result)) {
                    
                    if (isset($result['web_path'])) {
                        $urlpath = $Bucket->get_web_path().'/'.$result['file_name'];
                    }
                }           
            }
            
            echo stripslashes(PerchUtil::json_safe_encode(array(
                    'filelink' => $urlpath
                ))); 

    	} else {
            print_r($Bucket);
    		echo 'Upload failed. Is the bucket writable?';
    	}
    } else {
    	//echo '<p class="message">Upload failed.</p>';
    }

    
