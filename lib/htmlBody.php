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
        </span>

        <span style="font-size:12px;"><i> (Note this for future Reference.) </i></span>
        <br /><br />

        <span class="form-h2"> Select File: <i> (.tsv) </i> </span> <br />
        <input id="file_name" type="file" name="sif_file" placeholder="<?php $date = date('d-m-y h:i:s');echo $date; ?>" />

        <br />
        <br />
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
  <br/>
</div>
</body>
