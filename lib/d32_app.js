let params = new URLSearchParams(location.search);


  d3.json(params.get('tree'), function(treeData) {
    JSON.stringify(treeData, null, '  ');
    
    function add_render_links() {
      if ( treeData['output_format'] == 'json') {
        type = 'json';
      } else if (treeData['output_format'] == 'edgelist') {
        type = 'tsv';
      }

      lineage = document.getElementById("current_selection_lineage").innerText;
      if (lineage == "0") {
        return;
      }
      var url =  params.get('tree');
      var svg_new_url = url.slice(0, url.length -10) + '/subgraphs/subgraphs_svg_render/' + lineage + '.' + "svg";

      var sg_render_new_url = "../lib/sg_render_2.html?sg=" + url.slice(0, url.length -10) + '/subgraphs/subgraphs_json/' + lineage + '.' + "json";

      var sg_new_url = url.slice(0, url.length -10) + '/subgraphs/subgraphs_'+ type + '/' + lineage + '.' + type;

      document.getElementById("get_sub_network_render").href = svg_new_url;
      document.getElementById("get_sub_network_sg").href = sg_new_url;
      document.getElementById('get_sub_network_interactive_render').href = sg_render_new_url;

    }


    document.getElementById('method_label').innerHTML = treeData['cf_algo'];
    document.getElementById('selection_vmin').innerHTML = treeData['subgraph_min_vertices'];
    document.getElementById('selection_kr_num').innerHTML = treeData['key_regulator_bin_width'];

    
    function update_details_panel (lineage_str) {

      // Remove any links to graphs from previous selection.
      l1 = document.getElementById('csv_line_plot_render_line');
      l1.href = 'javascript:void(0)';
      l1.style.opacity = 0.5;
      l2 = document.getElementById('csv_line_plot_render_scatter');
      l2.href = 'javascript:void(0)';
      l2.style.opacity = 0.5;
      l3 = document.getElementById('csv_line_plot_download_csv');
      l3.href = 'javascript:void(0)';
      l3.style.opacity = 0.5;

      var csx = document.getElementById("csv_plot_prop_selection_ddm");
      csx.innerText = null;
      var _property_headings_list = [
        "eigen_vector_centrality",
        "betweenness_centrality",
        "closeness_centrality",
        "probability_degree_distribution",
        "clustering_coefficient",
        "neighborhood_connectivity",
        "current_degree",
      ]
      
      _property_headings_list.forEach(function(item) {
        var option = document.createElement("option");
        option.text = item;
        csx.add(option);
      });
      
      l = document.getElementById('current_selection_lineage');
      l.innerText = lineage_str;
      add_render_links();
      l = document.getElementById('lineage_display')
      var parser_str = "> 0 -- ";

      if (lineage_str.length > 1){
        for(let chr of lineage_str) {
          if (chr != '_') {parser_str = parser_str + chr;}
          else {parser_str = parser_str + " -- ";}
        };
      }
      else {
        parser_str = parser_str + lineage_str;
      }
      l.innerText = parser_str;
      node = document.getElementById(lineage_str)
      var node_data = d3.select(node).datum();
      
      document.getElementById('selection_current_depth').innerHTML = node_data.current_depth;

      document.getElementById('selection_vertex_count').innerHTML = node_data.num_vertices;

      document.getElementById('selection_keyreg_count').innerHTML = d3.keys(node_data.key_regs).length;

      iln = document.getElementById('selection_is_leaf_node');
      iln.innerHTML = node_data.is_leaf_node ? "True" : "False";
      iln.style.color =  node_data.is_leaf_node ? "green" : "rgb(158, 39, 39)";

      var key_reg_table = document.getElementById('selection_table_2');
      key_reg_table.replaceChildren();

      var header_row = key_reg_table.insertRow();
      header_row.classList.add('key_reg_th');

      h1 = header_row.insertCell();
      h1.classList.add("key_reg_header_cell");
      h1.innerHTML = "Key Reg";
      h1.title = "Key Regulator's Name";

      h1 = header_row.insertCell();
      h1.classList.add("key_reg_header_cell");
      h1.innerHTML = "Deg<i>d</i>";
      h1.title = "Key Regulator's Degree at current depth d.";
      
      h2 = header_row.insertCell();
      h2.classList.add("key_reg_header_cell");
      h2.innerHTML = "<i>x<sub>v</sub> </i>";
      h2.title = "Eigenvector Centrality / Page Rank of the Key Regulator.";

      h2 = header_row.insertCell();
      h2.classList.add("key_reg_header_cell");
      h2.innerHTML = "<i>g(v)</i>";
      h2.title = "Betweenness Centrality of the Key Regulator.";

      h2 = header_row.insertCell();
      h2.classList.add("key_reg_header_cell");
      h2.innerHTML = "<i>C(v)</i>";
      h2.title = "Closeness Centrality of the Key Regulator.";


      h2 = header_row.insertCell();
      h2.classList.add("key_reg_header_cell");
      h2.innerHTML = "<i>P(v)</i>";
      h2.title = "Probability of degree distribution of the Key Regulator.";

      h2 = header_row.insertCell();
      h2.classList.add("key_reg_header_cell");
      h2.innerHTML = "<i>C<sub>i</sub></i>";
      h2.title = "Clustering Coefficient of the Key Regulator.";

      // To populate graph display's dd menu with selected node's keyregs.
      var kr_sec_ddm = document.getElementById('key_reg_selection_ddm');
      kr_sec_ddm.replaceChildren();

      kr_sec_ddm.options.add(new Option("Selact Key Reg","default" , true));

      d3.keys(node_data.key_regs).forEach(function(key_reg) {
        var row = key_reg_table.insertRow();
        
        // Render lineages of key regs fancily.
        row.onclick = function() {
          expand_lineage(key_reg);
        }

        row.classList.add("key_reg_row");

        kr_sec_ddm.options.add(new Option(key_reg, key_reg));
        
        var cell1 = row.insertCell();
        cell1.classList.add("key_reg_cell");
        cell1.style.textShadow = "#625a5a 1px 1px 4px";
        cell1.innerText = key_reg;

        var cell1 = row.insertCell();
        cell1.classList.add("key_reg_cell");
        cell1.innerText = node_data.key_regs[key_reg]["current_degree"];
        cell1.title = node_data.key_regs[key_reg]["current_degree"];

        var cell1 = row.insertCell();
        cell1.classList.add("key_reg_cell");
        cell1.title = node_data.key_regs[key_reg]["eigen_vector_centrality"];
        cell1.innerText = node_data.key_regs[key_reg]['eigen_vector_centrality'].toExponential(2);

        var cell1 = row.insertCell();
        cell1.classList.add("key_reg_cell");
        cell1.title = node_data.key_regs[key_reg]['betweenness_centrality'];
        cell1.innerText = node_data.key_regs[key_reg]['betweenness_centrality'].toExponential(2);

        var cell1 = row.insertCell();
        cell1.classList.add("key_reg_cell");
        cell1.title = node_data.key_regs[key_reg]['closeness_centrality'];
        cell1.innerText = node_data.key_regs[key_reg]['closeness_centrality'].toExponential(2);

        var cell1 = row.insertCell();
        cell1.classList.add("key_reg_cell");
        cell1.title = node_data.key_regs[key_reg]['probability_degree_distribution'];
        cell1.innerText = node_data.key_regs[key_reg]['probability_degree_distribution'].toExponential(2);

        var cell1 = row.insertCell();
        cell1.classList.add("key_reg_cell");
        cell1.title = node_data.key_regs[key_reg]['clustering_coefficient'];
        cell1.innerText = node_data.key_regs[key_reg]['clustering_coefficient'].toExponential(2);
      });
      };

    function render_graphs () {
      var selection = document.getElementById('key_reg_selection_ddm');
      var selected_key_reg = selection.options[selection.selectedIndex].value;
      
      if (selected_key_reg == "default") {
        document.getElementById('graph_details_cont').innerText = "Please select a key regulator.";
        return;
      }

      expand_lineage(selected_key_reg);
      // var graph_render_cont = document.getElementById('graph_details_cont');
      // graph_render_cont.innerText = selected_key_reg;
      var lineage = treeData['key_reg_trace'][selected_key_reg]

      // Then one by one keep synthetic clicks on the nodes to reach the given keyreg.
      var _property_headings_list = {
        "eigen_vector_centrality" : [],
        "betweenness_centrality" : [],
        "closeness_centrality" : [],
        "probability_degree_distribution" : [],
        "clustering_coefficient" : [],
        "neighborhood_connectivity" : [],
        "current_degree" : [],
      }

      var kr_prop_sec_ddm = document.getElementById('key_reg_property_selection_ddm');
      kr_prop_sec_ddm.replaceChildren();

      kr_prop_sec_ddm.options.add(new Option("Selact a property.","default" , true));

      d3.keys(_property_headings_list).forEach(function(_property) {
        kr_prop_sec_ddm.options.add(new Option(_property, _property));
      });

      var node_id_string = 'ray';
      
      //  Pre fetch the root node data.
      var snode = d3.select(document.getElementById('ray_0')).datum();
      Object.entries(_property_headings_list).map(([k, v]) => {
        _property_headings_list[k].push(
          snode['key_regs'][selected_key_reg][k]
        );
      });

      lineage.split("_").forEach(function(lineage_node) {
        node_id_string += '_' + lineage_node;
        var node = document.getElementById(node_id_string);
        var snode = d3.select(node).datum();
        
        Object.entries(_property_headings_list).map(([k, v]) => {
          _property_headings_list[k].push(
            snode['key_regs'][selected_key_reg][k]
          );
        });
      });


      document.getElementById('key_reg_property_selection_ddm').onchange = function() {
        // Add a nice little table for showing property change at each level.
        prop_list = _property_headings_list[this.value];
        table_ = document.getElementById('selection_keyreg_prop_table');

        len_list = prop_list.length;
        table_.classList.add("selection_keyreg_prop_table");
        
        table_.innerHTML = "";
        header_row = table_.insertRow();
        header_row.classList.add("keyreg_prop_table_header");
        header_row.style.border = "1px solid #ddd";
        c1 = header_row.insertCell()
        c1.style.textAlign = "center";
        c1.classList.add("keyreg_prop_table_header_cell");
        c1.innerText = "Level";
        c2 = header_row.insertCell()
        c2.classList.add("keyreg_prop_table_header_cell");
        c2.style.textAlign = "center";
        c2.innerText = "Value";


        for (var i = 0; i < len_list; i++) {
          var row = table_.insertRow();
          row.classList.add("keyreg_prop_table_row");


          var cell1 = row.insertCell();
          cell1.classList.add("keyreg_prop_table_cell");
          cell1.innerText = i;
          cell1.title = "Level " + i;

          var cell1 = row.insertCell();
          cell1.classList.add("keyreg_prop_table_cell");
          cell1.innerText = prop_list[i].toExponential(2);
          cell1.title = prop_list[i];
        };
      };
    }

    document.getElementById('csv_plot_prop_selection_ddm').onchange = function() {
      // Add links to the page which render's the graphs present in CSV file.
      l = document.getElementById('current_selection_lineage');
      if (l.innerText == "0") {
        return;
      }
      var prop = this.value;
      
      var lineage = document.getElementById('current_selection_lineage').innerText;
      var name_str  = lineage + "-" + prop + ".csv";

      var url =  params.get('tree');

      var csv_url = url.slice(0, url.length -10) + '/subgraphs/prop_plots/' + name_str;

      var link_to_line_plot = "../lib/line_plotter.html?csv=" + csv_url;

      var link_to_scatter_plot = "../lib/line_plotter_2.html?csv=" + csv_url + "&y_title=" + prop;

      

      var link_obj = document.getElementById('csv_line_plot_render_line');
      link_obj.href = link_to_line_plot;
      link_obj.innerText = "Render Line Plot";
      link_obj.style.opacity = 1;

      var link_obj = document.getElementById('csv_line_plot_render_scatter');
      link_obj.href = link_to_scatter_plot;
      link_obj.innerText = "Render Scatter Plot";
      link_obj.style.opacity = 1;

      var link_obj = document.getElementById('csv_line_plot_download_csv');
      link_obj.href = csv_url;
      link_obj.innerText = "Download CSV";
      link_obj.style.opacity = 1;


    }


    var display_graph_container = false;
    document.getElementById('graph_displ_en').onclick = function() {
      
      document.getElementById('selection_table_2').style.display = "none";
      
      var graph_cont = document.getElementById('graph_container');
      display_graph_container = !display_graph_container;
      graph_cont.style.display = display_graph_container ? "block" : "none";
    }

    var display_table = false;
    function toggle_table() {
      document.getElementById('graph_container').style.display = "none";
      var table = document.getElementById('selection_table_2');
      display_table = !display_table;
      table.style.display = display_table ? "none" : "block";
    }
    document.getElementById('tabular_displ_en').onclick = toggle_table;

    document.getElementById('key_reg_selection_ddm').onchange = render_graphs;

    var margin = {top: 20, right: 120, bottom: 20, left: 50},
      width = window.innerWidth * 0.8 - margin.right - margin.left,
      height = window.innerHeight * 0.95- margin.top - margin.bottom;

    var i = 0,duration = 680,root;
    var tree = d3.layout.tree().size([height, width]);

    var diagonal = d3.svg.diagonal().projection(function(d) { return [d.y, d.x]; });

    var svg = d3.select(".container").append("svg")
      .attr("width", width + margin.right + margin.left)
      .attr("height", height + margin.top + margin.bottom)
      .append("g")
      .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    tooltip = d3.select('svg').append('text')
      .attr('class', '.tooltip_heading')
      .attr('x', '80%')
      .attr('y', 40);
    
    var displayDetailsOnHover = true;

    // Control display of details on hover.
    document.getElementById('mouseover_overlay_selector').onclick = function (d) {
        displayDetailsOnHover = displayDetailsOnHover ? false : true;
        if (displayDetailsOnHover) {
          tooltip.style('display', 'block');
        }
        else {
          tooltip.style('display', 'none');
        }
      }

    root = treeData;
    root.x0 = height / 2;
    root.y0 = 0;

    root.children.forEach(collapse);
      
    update(root);

    d3.select(self.frameElement).style("height", "500px");

    function update(source) {

      // Compute the new tree layout.
      var nodes = tree.nodes(root).reverse(),
        links = tree.links(nodes);

      // Normalize for fixed-depth.
      nodes.forEach(function(d) { d.y = d.depth * 130; });

      // Update the nodes…
      var node = svg.selectAll("g.node")
        .data(nodes, function(d) { return d.id || (d.id = ++i); });

      // Enter any new nodes at the parent's previous position.
      var nodeEnter = node.enter().append("g")
        .attr("class", "node")
        .attr("id" , function(d) { return "ray_" + d.lineage; } )
        .attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; })
        .on("click", click);

      nodeEnter.append("circle")
        .attr("r", 1e-6)
        .style("fill", function(d) { return d._children ? "#fff" : "#fff"; })
        .attr("class", function(d) { return d.has_keyreg ? "node_with_keyregs" : "node_without_keyregs" })
        .attr('id', d=> d.lineage)
        .on('mouseover', function(d) {

          if (!displayDetailsOnHover) {return;}
          
          tooltip.selectAll('tspan').remove();

          tooltip.append('tspan').attr('class', 'tooltip_heading').attr('x', '80%').attr('dy', '1.2em').attr('font-size', '12px')
          .text("Node Details: ");
          tooltip.append('tspan').attr('class', 'tooltip_tspan').attr('x', '80%').attr('dy', '1.2em').attr('font-size', '12px')
          .text('SubDiv Name: ' + d.name);
          tooltip.append('tspan').attr('class', 'tooltip_tspan').attr('x', '80%').attr('dy', '1.2em').attr('font-size', '12px')
          .text('Node Lineage: ' + d.lineage);
          tooltip.append('tspan').attr('class', 'tooltip_tspan').attr('x', '80%').attr('dy', '1.2em').attr('font-size', '12px')
          .text('Current Depth: ' + d.current_depth);
          tooltip.append('tspan').attr('class', 'tooltip_tspan').attr('x', '80%').attr('dy', '1.2em').attr('font-size', '12px')
          .text('Vertice Count: ' + d.num_vertices);
          tooltip.append('tspan').attr('class', 'tooltip_tspan').attr('x', '80%').attr('dy', '1.2em').attr('font-size', '12px')
          .text('Key Regs Count: ' + d3.keys(d.key_regs).length);
          tooltip.append('tspan').attr('class', 'tooltip_tspan').attr('x', '80%').attr('dy', '1.4em').attr('font-size', '12px')
          .text('Key Regulators Inside:');

          if (d3.keys(d.key_regs).length) {
            
            d3.keys(d.key_regs).forEach(function(key_reg) {
              tooltip.append('tspan')
              .attr('class', 'tooltip_tspan_key_reg')
              .attr('x', '80%')
              .attr('dy', '1.5em')
              .attr('font-size', '12px')
              .text(key_reg);
            });
          }
        else {
          tooltip.append('tspan')
          .attr('class', 'tooltip_tspan_key_reg')
          .attr('x', '80%')
          .attr('dy', '1.5em')
          .attr('font-size', '12px')
          .text('No Key Regulators Inside.');
        }})
        .on('mouseout', function(d) {})
        .on('dblclick', function(d) {
            current_lineage = document.getElementById('current_selection_lineage');
            lineage_str = current_lineage.textContent;
            node = document.getElementById(lineage_str);
            snode = d3.select(node)
            snode.attr('class', d => d.has_keyreg ? "node_with_keyregs" : "node_without_keyregs" );

            thisNode = d3.select(this);
            thisNode.attr('class', "selected_node");
            update_details_panel(d.lineage);
        });

      nodeEnter.append("text").attr("x", function(d) { return d.children || d._children ? -13 : 13; })
        .attr("dy", ".35em")
        .attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
        .text(function(d) { return d.name; })
        .attr('x-axis-rotation', '-45')
        .style("fill-opacity", 1e-6);

      // Transition nodes to their new position.
      var nodeUpdate = node.transition()
        .duration(duration)
        .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

      nodeUpdate.select("circle")
        .attr("r", 10)
        .style("fill", function(d) { return d._children ? d.has_keyreg ? "rgb(191, 255, 172)" : "rgb(255, 214, 214)" : "#fff"; });

      nodeUpdate.select("text")
        .style("fill-opacity", 1);

      // Transition exiting nodes to the parent's new position.
      var nodeExit = node.exit().transition()
        .duration(duration)
        .attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
        .remove();

      nodeExit.select("circle")
        .attr("r", 1e-6);

      nodeExit.select("text")
        .style("fill-opacity", 1e-6);

      // Update the links…
      var link = svg.selectAll("path.link")
        .data(links, function(d) { return d.target.id; });

      // Enter any new links at the parent's previous position.
      link.enter().insert("path", "g")
        .attr("class", "link")
        .attr("id" , function(d) { return "link_" + d.target.lineage; } )
        .attr("d", function(d) {
          var o = {x: source.x0, y: source.y0};
          return diagonal({source: o, target: o});
        });

      // Transition links to their new position.
      link.transition()
        .duration(duration)
        .attr("d", diagonal);

      // Transition exiting nodes to the parent's new position.
      link.exit().transition()
        .duration(duration)
        .attr("d", function(d) {
        var o = {x: source.x, y: source.y};
        return diagonal({source: o, target: o});
        })
        .remove();

      // Stash the old positions for transition.
      nodes.forEach(function(d) {
        d.x0 = d.x;
        d.y0 = d.y;
      });
    }
    // Toggle children on click.
    function click(d) {
      if (d.children) {
      d._children = d.children;
      d.children = null;
      } else {
      d.children = d._children;
      d._children = null;
      }
      update(d);
    }

  // Collapse the node and all it's children
    function collapse(d) {
      if(d.children) {
        d._children = d.children
        d._children.forEach(collapse)
        d.children = null
      }
    }

    function sleep(ms) {
      return new Promise(resolve => setTimeout(resolve, ms));
    }
    var last_lineage = ''

    function expand_lineage(key_reg) {
      var _link_id_string = 'link'
      if (last_lineage != '') {
        last_lineage.split("_").forEach(function(lineage_node) {
          _link_id_string += '_' + lineage_node;
          var node = document.getElementById(_link_id_string);
          node.style.stroke = "#ccc";
        });
      }
      last_lineage = treeData['key_reg_trace'][key_reg];
      // console.log(key_reg);
      // Collapse all the children from root.
      root.children.forEach(collapse);
      collapse(root);

      click(root);

      // Then one by one keep synthetic clicks on the nodes to reach the given keyreg.
      var lineage = treeData['key_reg_trace'][key_reg];
      var node_id_string = 'ray';
      link_id_string = 'link';
      lineage.split("_").forEach(function(lineage_node) {
        node_id_string += '_' + lineage_node;
        var node = document.getElementById(node_id_string);

        var snode = d3.select(node);
        click(snode.datum());

      });

      lineage.split("_").forEach(function(lineage_node) {
        link_id_string += '_' + lineage_node;
        var node = document.getElementById(link_id_string);
        node.style.stroke = "gold";
        // var snode = d3.select(node);
        // click(snode.datum());

      });
    }
  });