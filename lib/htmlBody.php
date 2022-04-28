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
      <span class="form-heading" style="margin-bottom:10px;" title="This section will contain a list of jobs you have running or have ran recently.">
        Control and Monitor your Current Jobs
      </span>
      <hr />
      <span>
        <form onsubmit="return validate_job_search()" name="search" id="search" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
          <span class="form-h2"> Currently Running Jobs for User: </span> <br />
          <input type="hidden" name="search_only" value="1" />
          <input id="sch_txt" title="Enter the username whose jobs you wish to access."   type="text" id="usr_name" name="usr_name" value="<?php echo $usr_name;?>" />
          <input class="sch_submit" type="submit" value="Search" name="Search" />
        </form>
      </span>
      <br />
      <table title="Table containing the list of currently running or recently finished jobs." class="job_list_table sortable">
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
          
          $render_link = "<a target=\"blank\" rel=\"noopener noreferrer\" href=\"../lib/d32.html?tree=../upload/output_" . crc32( $row[1] . "salt" . $row[2] ) . "/tree.json\">Render </a>";

          $cancel_job = "<form method=\"POST\"> <input type=\"hidden\" value=\"1\" name=\"cancel_job\"/>"
          ."<input type=\"hidden\" name=\"search_only\" value=\"0\" />"
          ."<input type=\"hidden\" name=\"usr_name\" value=\"" . $row[1] ."\"/>".
          "<input type=\"hidden\" name=\"job_name\" value=\"" . $row[2] ."\"/>".
          "<input type=\"submit\" value=\"Cancel\"> </form>";

          $txt = count($row) <= $j ? ( $j == 4 ? ( $row[3] == 4 ? ( $link . $render_link ) : "N/A" ) : ($row[0]=="N/A" ? "N/A" : $cancel_job) ) : ($j==3 ? $job_states[ (int)$row[$j] ] : $row[$j]);
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
        <span class="form-h2" title="Enter the job's name or leave it default.">
          Enter Job name or leave it default:
        </span> <br>
        
        <input id="job_name" title="Since one user can add multiple jobs, you are given the ability to name jobs so you get know which job is which. The current value is auto genrated but you could also set a custom jobname like: sam's_gene_network." placeholder="Enter the Job's name here" type="text" name="job_name"
          value="<?php $job_id = 'Job@' . date('d-m-yh:i:s');echo $job_id;?>" />

        <br /><br />
        <span class="form-h2" title="Enter and Note your username for future reference. Hover at the input box for more info.">
          Enter user name/alias:
        </span>
        <br/>

        <span style="display:flex;flex-direction:row;">
        
        <input id="usr_name" type="text" value="<?php echo $usr_name;?>" title="Enter your username, this username will be needed if you want to look at your last jobs or look at your jobs from another system. Usually this is automatically genrated but you could set it to whatever you could remember." name="usr_name" placeholder="Enter your name" />
        </span>

        <span style="font-size:12px;"><i> (Note this for future Reference.) </i></span>
        <br /><br />

        <span class="form-h2" title="Select the algorithm which'll be used for finding the communities in your network.">
          Select Community finding algorithm.
        </span>
        <br/>
        
        <select id="cf-algo" name="cf_algo" title="Select the algorithm using which you would like to find the communities in your network.">
          <option value="leading_eigenvector" title="M. E. J. Newman's leading eigenvector method for detecting community structure.">
            Leading Eigen Vector Method
          </option>
          <option value="louvain" title="Community structure based on the multilevel algorithm of Blondel et al.">
            Louvian's Method
          </option>
          <!-- <option value="cfa-leiden" title="Finds the community structure of the graph using the Leiden algorithm of Traag, van Eck & Waltman.">
            Leiden's Method
          </option> -->
        </select>

        <br/>
        <br/>
        <span class="form-h2" name="v_min" title="Set the |V|min for leaf nodes. The minimum number of nodes to stop at.">
          Set the |V|<sub>min</sub> for leaf nodes.
        </span>
        <input name="v_min" type="number" min="1" value=3 required>
        <br/>


        <br/>
        <span class="form-h2" name="kreg_nums" title="Set the number of top-most degree nodes to trace. Upper limit is the total number of nodes in your network.">
          Set the number of top-most degree nodes to trace.
        </span>
        <input name="keyreg_num" type="number" min="1" value=30 required>
        <br/>
        <br/>

        <span class="form-h2" title="Select an edgelist file. The file should be a Tab-separated value edge-list. https://en.wikipedia.org/wiki/Tab-separated_values">
          Select Edgelist File: <i> (.tsv) </i>
        </span>
        <br/>
        
        <input id="file_name" type="file" name="sif_file" placeholder="<?php $date = date('d-m-y h:i:s');echo $date; ?>" />

        <br />
        <br/>

        <span class="form-h2" title="Select the type of output subgraphs you want.">
          Select the output file format.
        </span>
        <br/>
        
        <select id="output-type" name="output_type" title="Select the output file type for the generated leaf communities.">
          <option value="output-type-edgelist-tsv" title="The output will be collection of edgelists which will be in .tsv format.">
            Edgelist (TSV format)
          </option>
          <option value="output-type-json" title="The output will be a collection of json files. These are suitable for visulising grpahs in d3.js etc.">
            JSON Format
          </option>
        </select>

        <br/>
        <br/>
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
    </p>
  </div>
  <br/>

<style>

  .links line {
    stroke: #999;
    stroke-opacity: 0.6;
  }

  .nodes circle {
    stroke: #fff;
    stroke-width: 4px;
  }

  text {
    font-family: sans-serif;
    font-size: 10px;
    padding-left: 5px;
    color:green;
  }

</style>
<!-- 
<div style="border:1px solid black;border-radius:6px;">
<svg width="180" height="180">
</svg>
</div>

<script src="https://d3js.org/d3.v4.min.js"></script>
<script>

  var svg = d3.select("svg"),
      width = +svg.attr("width"),
      height = +svg.attr("height");

  var color = d3.scaleOrdinal(d3.schemeCategory20);

  var simulation = d3.forceSimulation()
      .force("link", d3.forceLink().id(function(d) { return d.id; }))
      .force("charge", d3.forceManyBody().strength(-400))
      .force("center", d3.forceCenter(width / 2, height / 2));

  d3.json("test.json", function(error, graph) {
    if (error) throw error;

    var link = svg.append("g")
      .attr("class", "links")
      .selectAll("line")
      .data(graph.links)
      .enter().append("line")
      .attr("stroke-width", function(d) { return Math.sqrt(d.value); });

    var node = svg.append("g")
        .attr("class", "nodes")
      .selectAll("g")
      .attr("title", "Something something.")
      .data(graph.nodes)
      .enter().append("g")

    node.append("svg:title").text(function(d) { return "Still DRE"; });

    node.append("image")
      .attr("xlink:href", "media/gene.png")
      // .attr("title", function (d) { return "Something something.";} )
      .attr("x", -8)
      .attr("y", -8)
      .attr("width", 36)
      .attr("height", 36);

    // var circles = node.append("circle")
    //   .attr("r", 10)
    //   .attr("fill", function(d) { return color(d.group); });

    // Create a drag handler and append it to the node object instead
    
    var drag_handler = d3.drag()
        .on("start", dragstarted)
        .on("drag", dragged)
        .on("end", dragended);

    drag_handler(node);
    
    var lables = node.append("text")
        .text(function(d) {
          return d.id;
        })
        .attr('x', 10)
        .attr('y', 6);
    
    // var titles = node

    node.append("title")
        .text(function(d) { return d.id; });

    simulation
        .nodes(graph.nodes)
        .on("tick", ticked);

    simulation.force("link")
        .links(graph.links);

    function ticked() {
      link
          .attr("x1", function(d) { return d.source.x; })
          .attr("y1", function(d) { return d.source.y; })
          .attr("x2", function(d) { return d.target.x; })
          .attr("y2", function(d) { return d.target.y; });

      node
          .attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
          })
    }
  });

  function dragstarted(d) {
    if (!d3.event.active) simulation.alphaTarget(0.3).restart();
    d.fx = d.x;
    d.fy = d.y;
  }

  function dragged(d) {
    d.fx = d3.event.x;
    d.fy = d3.event.y;
  }

  function dragended(d) {
    if (!d3.event.active) simulation.alphaTarget(0);
    d.fx = null;
    d.fy = null;
  }

</script> -->

</div>
</body>
