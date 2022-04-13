

  d3.json('knowledge_tree.json', function(treeData) {

    function update_details_panel (lineage_str) {
      // alert(lineage_str);
      l = document.getElementById('current_selection_lineage')
      l.innerText = lineage_str;
      l = document.getElementById('lineage_display')
      var parser_str = "root -> ";

      for(let chr of lineage_str) { //  lineage_str.forEach( function (chr)
        if (chr != '_') {
          parser_str = parser_str + chr;
        }
        else {
          parser_str = parser_str + " -> ";
        }
      };
      l.innerText = parser_str;
      // console.log(parser_str);

    }

    

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
          .text('Key Regs Count: ' + d.key_regs.length);
          tooltip.append('tspan').attr('class', 'tooltip_tspan').attr('x', '80%').attr('dy', '1.4em').attr('font-size', '12px')
          .text('Key Regulators Inside:');

          if (d.key_regs.length) {
          d.key_regs.forEach(function(key_reg) {
            tooltip.append('tspan')
            .attr('class', 'tooltip_tspan_key_reg')
            .attr('x', '80%')
            .attr('dy', '1.5em')
            .attr('font-size', '12px')
            .text(key_reg);
          });}
          

        else {
          tooltip.append('tspan')
          .attr('class', 'tooltip_tspan_key_reg')
          .attr('x', '80%')
          .attr('dy', '1.5em')
          .attr('font-size', '12px')
          .text('No Key Regulators Inside.');
        }

        })
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