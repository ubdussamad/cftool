

  d3.json('knowledge_tree_1.json', function(treeData) {

    function update_details_panel (lineage_str) {
      l = document.getElementById('current_selection_lineage')
      l.innerText = lineage_str;
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


      d3.keys(node_data.key_regs).forEach(function(key_reg) {
        var row = key_reg_table.insertRow();
        row.classList.add("key_reg_row");

        kr_sec_ddm.options.add(new Option(key_reg, key_reg));
        
        var cell1 = row.insertCell();
        cell1.classList.add("key_reg_cell");
        cell1.style.textShadow = "#625a5a 1px 1px 4px";
        cell1.innerText = key_reg;

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
      var graph_render_cont = document.getElementById('graph_details_cont');
      graph_render_cont.innerText = selected_key_reg;
    }

    var display_graph_container = false;
    document.getElementById('graph_displ_en').onclick = function() {
      var graph_cont = document.getElementById('graph_container');
      display_graph_container = !display_graph_container;
      graph_cont.style.display = display_graph_container ? "block" : "none";
    }

    var display_table = false;
    document.getElementById('tabular_displ_en').onclick = function() {
      var table = document.getElementById('selection_table_2');
      display_table = !display_table;
      table.style.display = display_table ? "none" : "table";
    }

    document.getElementById('key_reg_selection_ddm').onchange = render_graphs;

    var margin = {top: 20, right: 120, bottom: 20, left: 50},
      width = window.innerWidth * 0.8 - margin.right - margin.left,
      height = window.innerHeight * 0.95- margin.top - margin.bottom;
      
    var i = 0,duration = 750,root;

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

      nodeEnter.append("text")
        .attr("x", function(d) { return d.children || d._children ? -13 : 13; })
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

  });