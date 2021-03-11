<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php
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
    $usr_ip     = get_client_ip();
    $new_page_load = false;
    # TODO: Maybe even verify file data too.
    # Impliment auto periodic refreshing mechanism for the list.
    # Impliment Job Submittion here only.
    if ( !isset($_POST['usr_name']) and !isset($_POST['job_name']) and !isset($_POST['search_only']) ) {
      // If all three of these are not set then it means it's a fresh page load.
      // echo "<script>alert(\"New Page load.\")</script>";
      $new_page_load = true;
      $usr_name =  "user@". crc32(time() . get_client_ip() . rand(10,100) )%100000;
    }

    else if ($_POST["search_only"] == 1) {
      // This is the case where the user is only searching.
      // When searching this username itself becomes the default job submittion username.
      // And the job_name is automatically genrated everytime anyways.
      // echo "<script>alert(\"Only Searching\")</script>";
      $usr_name   = $_POST['usr_name'];
    }

    else if ($_POST["search_only"] == 0 and !empty($_POST['usr_name']) and !empty($_POST['job_name']) ) {
      // This is the case where user is submitting a job.
      // echo "<script>alert(\"Only Submitting job!\")</script>";
      $usr_name   = $_POST['usr_name'];
      $job_name   = $_POST['job_name'];

      $filename   = $_FILES['sif_file']['name'];
      $file_type  = $_FILES['sif_file']['type'];
      $file_size  = $_FILES['sif_file']['size'];

      # Create a special folder for every upload in upload folder.
      # Upload the input file.
      # Check the file for errors.
      # Run jobber on that specific folder.

      $target_dir = "upload/"; // NOTE: This is very Specific to linux, because of the forward slash.
      $target_dir = $target_dir . 'output_' .  crc32( $usr_name . "salt" . $job_name ) . "/";
      mkdir($target_dir  , $mode=0777 , $recursive=true);
      $file_tmp =$_FILES['sif_file']['tmp_name'];
      $target_file = $target_dir . basename($_FILES["sif_file"]["name"]);
      // # Check the file extension and data too.
      // $FileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
      move_uploaded_file($file_tmp, $target_file);  


      chdir("scheduler");
      $cmd = "python3 scheduler.py a " . $usr_name . " " .  $job_name;
      exec( $cmd, $output);
      chdir("../");
      // echo "<script>alert(\"Job Submitted!\")</script>";
    }
?>

<head>

  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title> Community Finding Tool - SCIS, JNU </title>

  <script type="text/javascript">
    function copy_to_clipboard() {
      var copyText = document.getElementById("usr_name");
      copyText.select();
      copyText.setSelectionRange(0, 99999); /* For mobile devices */
      document.execCommand("copy");
    }
  </script>

  <link rel="stylesheet" href="style.css">

</head>

<body>

  <div class="header_section">
    <h1 class="title_header">
      Community Finding Tool
    </h1>
    <h2>
      Complex Dynamics Lab,<br />
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
          <form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
            <span class="form-h2"> Currently Running Jobs for User: </span> <br />
            <input type="text" id="usr_name" name="usr_name" value="<?php echo $usr_name;?>" />
            <input type="hidden" name="search_only" value="1" />
            <input
              style="height: 24pt;margin-left: -7px;background-color: #567880;color: #fff;border-top-right-radius: 8px;border-bottom-right-radius: 8px;border: 1px solid grey;"
              type="submit" value="Search" name="Search" />
          </form>
        </span>
        <br />
        <table class="job_list_table">
          <tr>
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
        $cmd = "python3 scheduler.py l " . $usr_name;
        exec( $cmd, $output);
        $job_states = array("Queued","Running","Error","Stoppped","Finished","N/A");
        for ( $i=0; $i < count($output); $i++ ) {
          echo "<tr>";
          $row = explode ( ',' , substr($output[$i],0,-1) );
          for ($j=0; $j < count($row)+2; $j++ ) {
            $link = "<a target=\"blank\" rel=\"noopener noreferrer\" href=\"../upload/output_" . crc32( $row[1] . "salt" . $row[2] ) . "/\">Download </a>";
            $txt = count($row) <= $j ? ( $j == 4 ? ( $row[3] == 4 ? ( $link ) : "N/A" ) : "X Cancel Job" ) : ($j==3 ? $job_states[ (int)$row[$j] ] : $row[$j]);
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
        <!-- TODO: Do not let user submit form without valid form data. -->
        <form enctype="multipart/form-data" style="padding-top:10px;" title="Submit New Job"
          action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
          <input type="hidden" name="search_only" value="0" />
          <span class="form-h2"> Enter Job name or leave it default: </span> <br>
          
          <input id="Name" type="text" name="job_name"
            value="<?php $job_id = 'Job@' . date('d-m-yh:i:s');echo $job_id;?>" />

          <br /><br />

          <!-- TODO: If username is already present in the post field, then display that. -->
          <span class="form-h2"> Enter user name/alias: </span> <br>
          <span style="display:flex;flex-direction:row;">
          
          <input id="usr_name" type="text" value="<?php
          echo $usr_name;
           ?>" title="Note your user name." name="usr_name" placeholder="Enter your name" />

          <button title="Copy User Name to Clipboard." class="cpy-btn" onclick="copy_to_clipboard()">📄</button>
          </span>

          <span style="font-size:12px;"><i> (Note this for future Refrence.) </i></span>
          <br /><br />

          <!-- <input type="hidden" name="MAX_FILE_SIZE" value="30000" /> -->
          <span class="form-h2"> Select File: <i> (.sif) </i> </span> <br />
          <input id="file" type="file" name="sif_file" placeholder="<?php $date = date('d-m-y h:i:s');echo $date; ?>" />

          <br />
          <br />
          <!-- Don't let the user submit without proper validation. -->
          <input class="submit_button" type="submit" name="Submit" value="Submit" />
          <br />
        </form>
      </div>
    </div>

    <div class="intro">
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
      echo "<p>Copyright " . date('Y') . " SCIS, JNU | Your IP is: " . $IP_IA . "</p>";?>
      <p> Incase of any error, kindly email to: ubdussamad@gmail.com </p>
    </div>

  </div>

</body>
</html>

