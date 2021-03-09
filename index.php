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

    $new_page_load = false;
    # TODO: Maybe even verify file data too.
    # Impliment refreshing mechnish ofr the list.
    # Impliment Job Submittion here only.
    if ( empty($_POST['usr_name']) and empty($_POST['job_name']) ) {
      // Means this is a fresh page load.
      $new_page_load = true;
      $usr_name =  "user@". crc32(time() . get_client_ip() . rand(10,100) )%100000;
    }

    else {
      $usr_name   = $_POST['usr_name'];
    }

    // $filename   = $_FILES['sif_file']['name'];
    // $file_type  = $_FILES['sif_file']['type'];
    // $file_size  = $_FILES['sif_file']['size'];

    $usr_ip     = get_client_ip();
    $job_name   = $_POST['job_name'];
    

?>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title> Community Finding Tool - SCIS, JNU </title>
  <style type="text/css" media="screen">
    body {
      display: flex;
      flex-direction: column;
      align-items: center;}
    .header_section {
      border-radius: 5px;
      font-family: "Courier";
      padding:20px;
      padding-left: 200px;
      width:70%;
      background: rgb(27,55,64);
      background-image: url('media/header_art.png'), -moz-linear-gradient(90deg, rgba(27,55,64,1) 13%, rgba(92,116,119,1) 71%, rgba(82,98,117,1) 85%);
      background-image: url('media/header_art.png'), -webkit-linear-gradient(90deg, rgba(27,55,64,1) 13%, rgba(92,116,119,1) 71%, rgba(82,98,117,1) 85%);
      background-image: url('media/header_art.png'), linear-gradient(90deg, rgba(27,55,64,1) 13%, rgba(92,116,119,1) 71%, rgba(82,98,117,1) 85%);
      background-repeat: no-repeat, repeat;
      background-position: left;
      background-size: contain;
      filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#1b3740",endColorstr="#526275",GradientType=1);
      box-shadow: 2px 3px 6px #3b3b3b;}
    .vertical_container {
      display: flex;
      flex-direction: row;}
    .body_section {
      width:80%;
      border: rgb(99, 99, 99) solid 2px;
      padding:20px;
      margin:2%;
      box-shadow: 2px 6px 4px rgb(156, 156, 156);
      border-radius: 4px;
      display: flex;
      align-content: center ;
      align-items: center;
      flex-direction:column;}
    h1 {
      color:rgb(255, 255,255);
      text-shadow: rgb(12,12,12);
      }
    h2 {
      color: rgb(255, 255, 255);
      font-size: 20px;
      }
      .intro {
        font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }
    .form_div {
      padding:15px;
      
      margin:2%;
      margin-top: 0%;
      border: 1px solid rgb(100,100,100);
      box-shadow: 2px 2px 3px inset rgba(156, 156, 156,0.1);
      border-radius: 4px;
      background-color: rgb(238, 237, 205);
      align-content: center;
      width:40%;}
    button {
      color:green; }
    .form-h2 {
      font-weight: 900;
      font-family:'Courier';}
    .submit_button {
      color: black;
      width: 50%;
      border:1px solid #8a8aca;
      padding: 3px;
      border-radius: 5px;}
    .form-heading {
      color: #213d73;
      padding-bottom: 0;
      text-align: center;
      align-self: center;
      margin-bottom: 10px;
      font-size: 127%;
      font-weight: bold;
      font-family: courier;}
    .footer {
      border-top: 1px solid rgb(100,100,100);
      width:80%;
      font-size: 14px;
      font-weight: bold;
      font-family: "courier";}
    .job_list_div {
      padding: 15px;
      margin-bottom: 2%;
      margin-top: 2%;
      margin-top: 0%;
      border: 1px solid rgb(100,100,100);
      box-shadow: 2px 2px 3px inset rgba(156, 156, 156,0.1);
      border-radius: 4px;
      background-color: rgb(238, 237, 205);
      align-content: center;}
    .job_list_table {
      border-radius: 4px;
      border:1px solid black;
      background-color: #fff;
      padding: 6px;
      font-family: 'Courier New', Courier, monospace;
      /* box-shadow: 1px 1px 2px black; */
      table-layout: fixed;
      width: 100%;
      /* margin-top: 2%; */}
    th,td {
      border-bottom: 1px solid black;
      border-right:  1px solid black;
      padding: 6px;
      border-radius: 2px;}
    tr {background-color: inherit;}
    tr:nth-child(2n-1) {background-color: #e2e3e0;}
    tr:first-child {background-color: #eeedcd;}
    th {font-family: 'Courier New', Courier, monospace;font-weight: lighter;border-right: 0px;}
  </style>
</head>

<body>

<div class="header_section">
  <h1 class="title_header">
    Community Finding Tool
  </h1>
  <h2>
  Complex Dynamics Lab,<br/>
  School of Computational and Integrative Sciences,<br/>
  Jawaharlal Nehru University
  </h2>  
</div>

<div class="body_section">
  <div class="vertical_container">

  <div class="job_list_div">
    <span class="form-heading" style="margin-bottom:10px;">
      Control and Monitor your Current Jobs
    </span>
    <span>
      <form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
      <span class="form-h2"> Currently Running Jobs for User: </span> <br/>
      <input type="text" name="usr_id_search" value="<?php echo $usr_name;?>"/>
      <input type="submit" value="Search" name="Search"/>
    </form>
  </span>
  <br/>
    <table class="job_list_table">
      <tr>
        <th> Time Stamp </th> <th> User </th> <th> Job-Id </th> <th> Job Status </th> <th> Action </th> <th> Result </th>
      </tr>
      <?php
        $output = null;
        chdir("scheduler");
        $cmd = "python3 scheduler.py l " . $usr_name;
        exec( $cmd, $output);
        
        for ( $i=0; $i < count($output); $i++ ) {
          echo "<tr>";
          $row = explode ( ',' , substr($output[$i],0,-1) );
          for ($j=0; $j < count($row)+2; $j++ ) {
            $txt = count($row) <= $j ? $row[$j] : "N/A";
            echo "<td>" . "Ho" . "</td>";
          }
          echo "</tr>";
        }

        chdir("../");
      ?>
    </table>
  </div>
      
    <div class="form_div">
        <span class="form-heading"> Submit New Job </span>
        <!-- TODO: Do not let user submit form without valid form data. -->
        <form enctype="multipart/form-data" style="padding-top:10px;" title="Submit New Job" action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
          
          <span class="form-h2"> Enter Job name or leave it default: </span> <br>
          <input id="Name" type="text" name="job_name" value="<?php $job_id = 'Job@' . date('d-m-yh:i:s');echo $job_id;?>"/>
          
          <br/><br/>
          
          <!-- TODO: If username is already present in the post field, then display that. -->
          <span class="form-h2"> Enter user name/alias:  </span> <br>
          <input id="usr_name" type="text" value="<?php
          echo $usr_name;
           ?>" title="Note your user name." name="usr_name" placeholder= "Enter your name" />
          <br/>
          <span style="font-size:12px;"><i> (Note this for future Refrence.) </i></span>
          <br/><br/>
          
          <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
          <span class="form-h2"> Select File: <i> (.sif) </i> </span> <br/>
          <input id="file" type="file" name="sif_file" placeholder= "<?php $date = date('d-m-y h:i:s');echo $date; ?>" />
          
          <br/>
          <br/>
          <!-- Don't let the user submit without proper validation. -->
          <input class="submit_button" type="submit">
          <br/>
        </form>
    </div>
  </div>

  <div class="intro">
        <p>
          This tool lets you find all possible communities in your gene data.
          Please use this tool and don't use any other tool since this tool is the best.
          <br/><br/>
          To use this just select the <i>.sif</i> file from your local drive and wait for 20 minutes.
          <br/>
          Your jobs will be put in queue and will be processed in the next available slot.
          <br/>
          You can view your Job queue using your name and download the finished data.
          
          <br/>
          We use your IP/Credentials to track your jobs.
          After completing your Jobs will stay on our server for 24hours and will be deleted afterwards.
          <br><br>
          We do not store any of your personal info, we just use your IP and a simple name for Job tracking.
          We also don't use any cookies whatsoever.
        </p>
    </div>


  <br/>
  
  <div class="footer">
    <?php
      $IP_IA = get_client_ip();
      echo "<p>Copyright " . date('Y') . " SCIS, JNU | Your IP is: " . $IP_IA . "</p>";
    ?>
    <p> Incase of any error, kindly email to: ubdussamad@gmail.com </p>
  </div>

</div>

</body>
</html>

