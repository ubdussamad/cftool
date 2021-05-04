<?php
    # Debug Opts
    $EN_DEBUG_ALERTS  = false;
    $EN_SANITY_JS     = false;
    $EN_AUTO_SORT_JS  = true;
    $EN_EXTERN_FONTS  = true;
    $EN_INTERNAL_STYLE= true;


    require_once $_SERVER['PHP_ROOT'].'lib/debug.php';
    require_once $_SERVER['PHP_ROOT'].'lib/netUtil.php';
    require_once $_SERVER['PHP_ROOT'].'lib/badRequest.php';

    // TODO:
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


    
    # Request Handler Logic [TODO: Needs Optimization]

    // Case: No Post Data or New Page Load.
    if ( !isset($_POST['usr_name']) and !isset($_POST['job_name']) and !isset($_POST['search_only']) ) {
      $new_page_load = true;
      $usr_name =  "user@". crc32(time() . get_client_ip() . rand(10,100) )%100000;
    }

    // Case: Searching.
    else if ($_POST["search_only"] == 1 and !isset($_POST["cancel_job"])) {
      // When searching this username itself becomes the default job submission username.
      // And the job_name is automatically generated every time.

      if (empty($_POST['usr_name'])) {
        badRequestHandler();
      }

      else {
        $usr_name   = htmlspecialchars($_POST['usr_name']);
      }

    }

    // Case: Job Submission.
    else if ($_POST["search_only"] == 0 and !isset($_POST["cancel_job"]) and !empty($_POST['usr_name']) and !empty($_POST['job_name']) ) {
      
      $usr_name   = htmlspecialchars($_POST['usr_name']);
      $job_name   = htmlspecialchars($_POST['job_name']);

      $filename   = htmlspecialchars($_FILES['sif_file']['name']);
      $file_type  = $_FILES['sif_file']['type'];
      $file_size  = $_FILES['sif_file']['size'];

      # Create a special folder for every upload in upload folder.
      # Upload the input file.
      # Check the file for errors (regexp) and the file's size too.
      # Run Bash Job on that specific folder.

      $target_dir = "upload/"; // PhP does not cares for Posix or NT directory separators.

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

      // TODO: When Finalizing the Project, this should either be an error or warning,
      if ($tsv_count != $newline_count) {
        echo "<script> alert(\"Invalid file type\") </script>" ;
      }
      fclose($file);

      chdir("scheduler");
      $cmd = "python3 scheduler.py a \"" . $usr_name . "\" \"" .  $job_name . "\"";
      exec( $cmd, $output);
      chdir("../");
    }

    // Case: Job Cancellation.
    else if (isset($_POST["cancel_job"]) and $_POST["cancel_job"] == 1) {
      $usr_name =           htmlspecialchars($_POST['usr_name']);
      $job_name_to_delete = htmlspecialchars($_POST['job_name']);

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


    # Document Generation Suite

    require_once $_SERVER['PHP_ROOT'].'lib/htmlHeader.php';
    require_once $_SERVER['PHP_ROOT'].'lib/htmlBody.php';
    require_once $_SERVER['PHP_ROOT'].'lib/htmlFooter.php';
?>
