	<?php
	
    include('simpleimage.php');
    extract( $_POST );


    //////////////////////////////////////////////////////////////
	////////////////////// DATABASE STUFF ////////////////////////
	//////////////////////////////////////////////////////////////
   
	require_once 'dbConnection.php';

    try {  
      $connection = new PDO("mysql:host=$host;dbname=$db", $user, $pass);  
    }  
    catch(PDOException $e) {  
        echo $e->getMessage();  
    } 

			$sql = "";
			$count = 0;
            
			foreach ($_FILES['file']['name'] as $filename){
            $tempname = $_FILES['file']['tmp_name'][$count];

				if($filename && ($filename != ''))
				{
                    $exif_ifd0 = exif_read_data ( $tempname ,'IFD0' ,0 ) ? exif_read_data ( $tempname ,'IFD0' ,0 ) : [ "dummy" => "dumb"];
                    $exif_exif = exif_read_data ( $tempname ,'EXIF' ,0 ) ? exif_read_data ( $tempname ,'EXIF' ,0 ) : [ "dummy" => "dumb"];

                    $exif_make = array_key_exists('Make', $exif_ifd0) ? $exif_ifd0['Make'] : ''; 
                    $exif_model = array_key_exists('Model', $exif_ifd0) ? $exif_ifd0['Model'] : ''; 
                    $exif_exposuretime = array_key_exists('ExposureTime', $exif_exif) ? $exif_exif['ExposureTime'] : ''; 
                    $exif_fnumber = array_key_exists('FNumber', $exif_exif) ? $exif_exif['FNumber'] : ''; 
                    $exif_iso = array_key_exists('ISOSpeedRatings', $exif_exif) ? $exif_exif['ISOSpeedRatings'] : ''; 
                    $exif_date = array_key_exists('DateTime', $exif_ifd0) ? $exif_ifd0['DateTime'] : ''; 
                    $focal_length_raw = array_key_exists('FocalLength', $exif_exif) ? $exif_exif['FocalLength'] : ''; 

                    $fone = explode('/',$exif_fnumber);
                    $ftwo = explode('/',$exif_fnumber);
                    $aperature = array_key_exists('FNumber', $exif_exif) ? (int)$fone[0]/(int)$ftwo[1] : '';
                    $focal_length = array_key_exists('FocalLength', $exif_exif) ? substr($focal_length_raw, 0, strrpos($focal_length_raw,'/')) : '';

                    $filename = str_replace(" ", "_", $filename);  

					if($count==0)
					$sql.= "INSERT INTO phototable(filename, category, date_added, sort_id, camera_make, camera_model, exposure_time, f_number, iso, focal_length, date_taken) VALUES ('".$filename."','".$category."',NOW(),0,'". $exif_make."','".$exif_model."','".$exif_exposuretime."','".$aperature."','".$exif_iso."','".$focal_length."','".$exif_date."')";
					else
					$sql.=",('".$filename."','".$category."',NOW(),0,'". $exif_make."','".$exif_model."','".$exif_exposuretime."','".$aperature."','".$exif_iso."','".$focal_length."','".$exif_date."')";
				
				$count++;
				}
				
			}
			$sql.=";";
							
			$statement = $connection->prepare($sql);

         $statement->execute();
			
			$statement = NULL;
			

	//////////////////////////////////////////////////////////////
	////////////////////// FILE UPLOAD ///////////////////////////
	//////////////////////////////////////////////////////////////
	
		$count = 0;
		$skippedcount = 0;
		foreach($_FILES["file"]["name"] as $filename)
		{
			if($filename && $filename != '')
			{
				               
				$thumb = new SimpleImage();
                $photo = new SimpleImage();

                $thumb->load($_FILES["file"]["tmp_name"][$count]);
                $photo->load($_FILES["file"]["tmp_name"][$count]);

                //get dimensions
                $thumbsize = getimagesize($_FILES["file"]["tmp_name"][$count]);
                $photosize = getimagesize($_FILES["file"]["tmp_name"][$count]);

                $thumbwidth = $thumbsize[0];
                $thumbheight = $thumbsize[1];

                $photowidth = $photosize[0];
                $photoheight = $photosize[1];

                //size image 200px on shortest edge
                if($thumbwidth < $thumbheight) $thumb->resizeToWidth(200);              
                else $thumb->resizeToHeight(200);

                //size image 2000px on shortest edge
                if($photowidth < $photoheight) $photo->resizeToWidth(2000);              
                else $photo->resizeToHeight(2000);
                
                //add an underscore in replacement of space
                $filename = str_replace(" ", "_", $filename);    

                //save em
                $thumb->save("../photothumbs/" . $filename);
			    $photo->save("../photos/" . $filename);
				
				
			}
			$count++;
			
		}

		//future pass back of $skippedcount to display how many files were skipped 
		//due to being in other categories

		header('Location: ../index.htm');
	
	   
	
	?>
