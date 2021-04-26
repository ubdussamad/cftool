<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">


<?php
    $EN_ALERT_BASED_LOGIN = false;

    function debugLog($alertMessage) {
      global $EN_ALERT_BASED_LOGIN;
      if ($EN_ALERT_BASED_LOGIN) {
        echo "<script> alert(\"Debug Alert: <br/> " . $alertMessage . "\");</script>";
      }
    }
    function get_client_ip() {
      $ipaddress = '';
      if (getenv('HTTP_CLIENT_IP'))
          $ipaddress = getenv('HTTP_CLIENT_IP');
      else if(getenv('HTTP_X_FORWARDED_FOR'))
          $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
      else if(getenv('HTTP_X_FORWARDED'))
          $ipaddress = getenv('HTTP_X_FORWARDED');
      else if(getenv('HTTP_FORWARDED_FOR'))
          $ipaddress = getenv('HTTP_FORWARDED_FOR');
      else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
      else if(getenv('REMOTE_ADDR'))
          $ipaddress = getenv('REMOTE_ADDR');
      else
          $ipaddress = 'UNKNOWN';
      return $ipaddress;
    }

    function badRequestHandler() {
      header("HTTP/1.1 400 Bad Request");
      echo "<h1>Bad input, Please go back!</h1>";
      echo "<hr><footer> Apache/2.4.46 (Ubuntu) </footer>";
      die();
    }

    $usr_ip     = get_client_ip();
    $new_page_load = false;

    # Server side post data verification block.
    $injection_vectors = array('job_name', 'usr_name');
    for ($x = 0; $x < count($injection_vectors) ; $x++) {
      if (isset( $_POST[ $injection_vectors[$x] ] )){
        if (preg_match( '/[;&|`^]/' , $_POST[ $injection_vectors[$x]])) {
          badRequestHandler();
        }
      }
    }

    # TODO: Maybe even verify file data too.
    # Implement auto periodic refreshing mechanism for the list.

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
?>

<head>

  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title> Community Finding Tool - SCIS, JNU </title>

  <script type="text/javascript">

    function copy_to_clipboard() {
      var copyText = document.getElementById("usr_name");
      // copyText.select();
      // copyText.setSelectionRange(0, 99999); /* For mobile devices */
      // document.execCommand("copy");
      navigator.clipboard.writeText(copyText);
    }
    // TODO:
    // Min file size and max file size validation is done here
    // Also, file type check could be done here.
    // Don't allow job submission if there are spaces in the username or job id.
    // Don't abuse this, the server itself wont accept a larger or smaller file size anyways.
    function validate_job_submission() {
      var a = document.getElementById('job_name');
      var b = document.getElementById('usr_name');
      
      if ( a.value == '' || b.value == '' ) {
        alert("Empty Input fields.");
        return(false);
      }

      const regex = new RegExp('[;&|`^]');
      if ( regex.test( a.value ) ||  regex.test( b.value ) ) {
        alert("Only allowed characters are: [A-Z],[a-z],@,_ \n Please edit the input.");
        return(false);
      }

      var file = document.getElementById('file_name').files[0];

      if(file) {// perform the size check only if a file is present.
        if(file.size > 100 && file.size < 26214400  ) { // 50 MB (this size is in bytes)
            if (a != '' & b != '') {
              return(true);
            }       
        }
        else {
          alert("Inappropriate file size! (1MB-50MB)");
          return false;
        }
      }
      else {
        alert("Please select a file first!");
        return false;
      }
    }

    function validate_job_search() {
      var sch_str = document.getElementById('sch_txt');
      const regex = new RegExp('[;&|`^]');

      if ( regex.test( sch_str.value ) ) {
        alert("Invalid characters in input. \nOnly allowed characters are: [A-Z],[a-z],@,_ \n Please edit the input.");
        return(false);
      }

      if (sch_str.value != '') {
        return (true);
      }

      else {
        alert("Empty Search query.");
        return(false);
      }
    }
  </script>
  <script src="https://www.kryogenix.org/code/browser/sorttable/sorttable.js"></script>
  <link rel="stylesheet" href="style.css">

</head>

<body>

  <div class="header_section">
    <h1 class="title_header">
      Community Finding Tool
    </h1>
    <h2>
      <span class="cdl_heading">Complex Dynamics Lab </span> <br />
      School of Computational and Integrative Sciences,<br />
      Jawaharlal Nehru University
    </h2>
  </div>

  <div class="body_section">

    <div class="vertical_container">

      <div class="job_list_div">
        <span class="form-heading" style="margin-bottom:10px;">
          Control and Monitor your Current Jobs
        </span>
        <hr />
        <span>
          <form onsubmit="return validate_job_search()" name="search" id="search" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
            <span class="form-h2"> Currently Running Jobs for User: </span> <br />
            <input type="hidden" name="search_only" value="1" />
            <input id="sch_txt"    type="text" id="usr_name" name="usr_name" value="<?php echo $usr_name;?>" />
            <input class="sch_submit" type="submit" value="Search" name="Search" />
          </form>
        </span>
        <br />
        <table class="job_list_table sortable">
          <tr class="th">
            <th> Time Stamp </th>
            <th> User </th>
            <th> Job-Id </th>
            <th> Job Status </th>
            <th> Result </th>
            <th> Action </th>
          </tr>
        <?php
        $output = null;
        chdir("scheduler");
        $cmd = "python3 scheduler.py l \"" . $usr_name . "\"";
        exec( $cmd, $output);
        $job_states = array("Queued","Running","Error","Stopped","Finished","N/A");
        for ( $i=0; $i < count($output); $i++ ) {
          echo "<tr>";
          $row = explode ( ',' , substr($output[$i],0,-1) );
          for ($j=0; $j < count($row)+2; $j++ ) {
            $link = "<a target=\"blank\" rel=\"noopener noreferrer\" href=\"../upload/output_" . crc32( $row[1] . "salt" . $row[2] ) . "/\">Download </a>";
            
            $cancel_job = "<form method=\"POST\"> <input type=\"hidden\" value=\"1\" name=\"cancel_job\"/>"
            ."<input type=\"hidden\" name=\"search_only\" value=\"0\" />"
            ."<input type=\"hidden\" name=\"usr_name\" value=\"" . $row[1] ."\"/>".
            "<input type=\"hidden\" name=\"job_name\" value=\"" . $row[2] ."\"/>".
            "<input type=\"submit\" value=\"Cancel\"> </form>";

            $txt = count($row) <= $j ? ( $j == 4 ? ( $row[3] == 4 ? ( $link ) : "N/A" ) : ($row[0]=="N/A" ? "N/A" : $cancel_job) ) : ($j==3 ? $job_states[ (int)$row[$j] ] : $row[$j]);
            echo "<td>" . $txt . "</td>";
          }
          echo "</tr>";
        }

        chdir("../");
      ?>
        </table>
      </div>

      <div class="form_div">
        <span class="form-heading"> Submit New Job </span>
        <hr />
        <form onsubmit="return validate_job_submission()" enctype="multipart/form-data" style="padding-top:10px;" title="Submit New Job"
          action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
          <input type="hidden" name="search_only" value="0" />
          <span class="form-h2"> Enter Job name or leave it default: </span> <br>
          
          <input id="job_name" placeholder="Enter the Job's name here" type="text" name="job_name"
            value="<?php $job_id = 'Job@' . date('d-m-yh:i:s');echo $job_id;?>" />

          <br /><br />
          <span class="form-h2"> Enter user name/alias: </span> <br>
          <span style="display:flex;flex-direction:row;">
          
          <input id="usr_name" type="text" value="<?php echo $usr_name;?>" title="Note your user name." name="usr_name" placeholder="Enter your name" />

          <!-- <button title="Copy User Name to Clipboard." class="cpy-btn" onclick="copy_to_clipboard()">📄</button> -->
          </span>

          <span style="font-size:12px;"><i> (Note this for future Reference.) </i></span>
          <br /><br />

          <!-- <input type="hidden" name="MAX_FILE_SIZE" value="30000" /> -->
          <span class="form-h2"> Select File: <i> (.tsv) </i> </span> <br />
          <input id="file_name" type="file" name="sif_file" placeholder="<?php $date = date('d-m-y h:i:s');echo $date; ?>" />

          <br />
          <br />
          <!-- Don't let the user submit without proper validation. -->
          <input class="submit_button" type="submit" name="Submit" value="Submit" />
          <br />
        </form>
      </div>
    </div>

    <div class="intro">
    <!-- <div class="scis_logo_outer">
      <span class="scis_logo_inner_1"> COMPLEX DYNAMICS LAB </span> <br/>
      <span class="scis_logo_inner_2"> SCIS, JAWAHARLAL NEHRU UNIVERSITY </span> <br/>
    </div> -->
      <p>
        This tool lets you find all possible communities in your gene data.
        Please use this tool and don't use any other tool since this tool is the best.
        <br /><br />
        To use this just select the <i>.sif</i> file from your local drive and wait for 20 minutes.
        <br />
        Your jobs will be put in queue and will be processed in the next available slot.
        <br />
        You can view your Job queue using your name and download the finished data.

        <br />
        We use your IP/Credentials to track your jobs.
        After completing your Jobs will stay on our server for 24hours and will be deleted afterwards.
        <br><br>
        We do not store any of your personal info, we just use your IP and a simple name for Job tracking.
        We also don't use any cookies whatsoever.
      </p>
    </div>


    <br />

    <div class="footer">
      <?php
      $IP_IA = get_client_ip();
      echo "<p>Copyright " . date('Y') . " Complex Dynamics Lab, SCIS, JNU | Your IP is: " . $IP_IA . "</p>";?>
      <p> Incase of any error, kindly email to: ubdussamad@gmail.com </p>
    </div>

  </div>

</body>
</html>

