<?php
    # Debug Opts
    $EN_DEBUG_ALERTS = true;


    require_once $_SERVER['PHP_ROOT'].'lib/debug.php';
    require_once $_SERVER['PHP_ROOT'].'lib/netUtil.php';
    require_once $_SERVER['PHP_ROOT'].'lib/badRequest.php';

    // TODO: Maybe even verify file data too.
    // Implement auto periodic refreshing mechanism for the list.

    $usr_ip         = get_client_ip();
    $new_page_load  = false;

    # Server side post data verification block.
    $injection_vectors = array('job_name', 'usr_name');
    for ($x = 0; $x < count($injection_vectors) ; $x++) {
      if (isset( $_POST[ $injection_vectors[$x] ] )){

        // This prevents user from inserting other types of data to confuse the server
        if (gettype($_POST[ $injection_vectors[$x]]) != "string") {
          badRequestHandler();
        }

        // Check for illegal chars on server side.
        if (preg_match( '/[;&|`^]/' , $_POST[ $injection_vectors[$x]])) {
          badRequestHandler();
        }
      }
    }


    
    # Request Handler Logic

    // If all three of these are not set then it means it's a fresh page load.
    if ( !isset($_POST['usr_name']) and !isset($_POST['job_name']) and !isset($_POST['search_only']) ) {
      $new_page_load = true;
      $usr_name =  "user@". crc32(time() . get_client_ip() . rand(10,100) )%100000;
    }

    // This is the case where the user is only searching.
    else if ($_POST["search_only"] == 1 and !isset($_POST["cancel_job"])) {
      // When searching this username itself becomes the default job submission username.
      // And the job_name is automatically generated every time anyways.
      if ($_POST['usr_name'] == '') {
        echo "<script>alert(\"Please enter username to search.!\")</script>";
        $new_page_load = true;
        $usr_name =  "user@". crc32(time() . get_client_ip() . rand(10,100) )%100000;
      }
      else {
        $usr_name   = htmlspecialchars($_POST['usr_name']); // Check this var for Injection
      }
    }

    // This is the case where user is submitting a job.
    else if ($_POST["search_only"] == 0 and !isset($_POST["cancel_job"]) and !empty($_POST['usr_name']) and !empty($_POST['job_name']) ) {
      $usr_name   = htmlspecialchars($_POST['usr_name']); // Check this var for Injection
      $job_name   = htmlspecialchars($_POST['job_name']); // Check this var for Injection

      $filename   = htmlspecialchars($_FILES['sif_file']['name']); // Check this var for Injection (Even this, you never know)
      $file_type  = $_FILES['sif_file']['type'];
      $file_size  = $_FILES['sif_file']['size'];

      # Create a special folder for every upload in upload folder.
      # Upload the input file.
      # Check the file for errors and the file's size too.
      # Run jobber on that specific folder.

      $target_dir = "upload/"; // NOTE: This is very Specific to linux, because of the forward slash.
      $target_dir = $target_dir . 'output_' .  crc32( $usr_name . "salt" . $job_name ) . "/";
      
      mkdir($target_dir  , $mode=0777 );


      $file_tmp =$_FILES['sif_file']['tmp_name'];
      $target_file = $target_dir . "input.tsv";
      move_uploaded_file($file_tmp, $target_file);

      // Check the file extension and data too.
      $file_type = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
      $file = fopen($target_file, "r") or badRequestHandler();

      $file_data = fread($file,filesize($target_file));

      $tsv_count =  preg_match_all("/.+\t.+[\r|\n]/", $file_data);
      $newline_count = preg_match_all("[\r|\n]", $file_data);

      if ($tsv_count != $newline_count) {
        echo "<script> alert(\"Invalid file type\") </script>" ;
      }

      fclose($file);


      chdir("scheduler");
      $cmd = "python3 scheduler.py a \"" . $usr_name . "\" \"" .  $job_name . "\"";
      exec( $cmd, $output);
      chdir("../");
    }

    // User Decides to cancel the job.
    else if (isset($_POST["cancel_job"]) and $_POST["cancel_job"] == 1) {
      $usr_name   = htmlspecialchars($_POST['usr_name']); // Check this var for Injection
      $job_name_to_delete   = htmlspecialchars($_POST['job_name']); // Check this var for Injection
      chdir("scheduler");
      $cmd = "python3 scheduler.py u \"" . $usr_name . "\" \"" .  $job_name_to_delete . "\" 3";
      exec( $cmd, $output);
      chdir("../");
    }

    else {
      echo "<script>alert(\"Invalid POST data!\")</script>";
      $new_page_load = true;
      $usr_name =  "user@". crc32(time() . get_client_ip() . rand(10,100) )%100000;
    }


    # Document Generation Logic

    require_once $_SERVER['PHP_ROOT'].'lib/htmlHeader.php';
    require_once $_SERVER['PHP_ROOT'].'lib/htmlBody.php';
    include_once $_SERVER['PHP_ROOT'].'lib/htmlFooter.php';
?>
