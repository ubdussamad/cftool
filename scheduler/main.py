# /usr/bin/ python3.8
from curses import has_key
from random import randrange
import sys, os
from typing import BinaryIO, Dict, List, NoReturn, Optional, Union

import igraph
import json
import networkx as nx
# from networkx.classes.function import degree
from networkx.readwrite import json_graph

class CommunityFinder:
    """
    Class for finding Communities in a given graph.
    It uses the leading eigenvector methods recursively
    to find subcommunities unless we get subcommunities
    with vertices lesser than 3.
    The minimum number of vertices can be specified through
    the constructor or `parse` method.
    """

    def __init__(
        self,
        edge_list_file: Optional[Union[str, BinaryIO]] = None,
        subgraph_min_vertices: int = 3,
    ) -> None:
        self._graph: igraph.Graph = None
        self.subgraph_min_vertices = subgraph_min_vertices
        if edge_list_file is not None:
            self.loadFile(edge_list_file)

        self._leaf_communities_edgelist: Dict[str, List[ List[str, str] ] ] = {}
        self._root_communities_edgelist: Dict[str, List[ List[str, str] ] ] = {}
        self.communities_subgraph_inclusive: Dict[str, igraph.Graph] = {}
        self.tree: Dict[str, List[ Dict[str, List[str] ]]] = dict()

    def set_graph(self, graph: Union[igraph.Graph, nx.Graph]) -> None:
        if not isinstance(graph, igraph.Graph):
            if isinstance(graph, nx.Graph):
                self._graph = igraph.Graph.from_networkx(graph)
                return
            else:
                raise (
                    TypeError,
                    "Graph should be of following types:"
                    " igraph.Graph or networkx.Graph.",
                )
        self._graph = graph

    def get_graph(self) -> igraph.Graph:
        return self._graph

    def parse(self, subgraph_min_vertices=None, key_regulator_bin_width : int = 50) -> Dict[str, igraph.Graph]:

        if isinstance(subgraph_min_vertices, int):
            self.subgraph_min_vertices = subgraph_min_vertices
        
        if self._graph is None:
            raise TypeError(
                "Either initialize the class with a edgelist file"
                " or set self.graph explicitly using `.set_graph`."
            )
        elif not isinstance(self._graph, igraph.Graph):
            raise TypeError (
                "Invalid graph type, use `igraph.Graph()`."
            )

        if not isinstance(key_regulator_bin_width,int):
            raise TypeError (
                "Key Regulator bin width should be an integer."
            )

        self._leaf_communities_edgelist.clear()

        _degrees = self._graph.degree()
        _vertex_ids = sorted( range( len(_degrees) ) , key = lambda sub: _degrees[sub])[-key_regulator_bin_width:]
        self._max_degree_nodes = {self._graph.vs[i]["_nx_name"]:[self._graph.vs[i].degree()] for i in _vertex_ids}

        self.tree['root'] = {
            'name' : 'root',
            'lineage' : '0',
            'current_depth': 0,
            'has_keyreg' : False,
            'num_vertices' : 0,
            'is_leaf_node' : False,
            'children': [],
            'key_regs' : []
        }

        self.key_regulators = self._max_degree_nodes.copy()

        self.algo_switch_node_count = 10000
        if len(self._graph.vs) > self.algo_switch_node_count:
            sys.stdout.write(f"\n\nUsing the faster Louvain's method (a.k.a community_multilevel) instead of leading eigenvector one since |V| > {self.algo_switch_node_count}.\n")
            self.find_communities_recursive(self._graph, tree = self.tree['root'], method=1)
        else:
            sys.stdout.write(f"\n\nUsing the leading eigenvector method since |V| < {self.algo_switch_node_count}.")
            self.find_communities_recursive(self._graph, tree = self.tree['root'], method=1)

        
        # Edgelist not required.
        _merged_dict = self._root_communities_edgelist.copy()
        _merged_dict.update(self._leaf_communities_edgelist)
        return self._leaf_communities_edgelist

    def has_cycles(self, graph: igraph.Graph):
        if isinstance(graph, igraph.Graph):
            graph = nx.Graph(graph.get_edgelist())
        try:
            nx.find_cycle(graph)
            return True
        except nx.NetworkXNoCycle:
            return False
    
    def check_star_topology(self, graph: igraph.Graph):

        _vs_count = len(graph.vs)
        _es_count = len(graph.vs)

        # Number of edges should be equal
        # to (Number of vertices - 1)
        if _es_count != ( _vs_count - 1):
            return False
    
        # a single node is termed as a bus topology
        if (_vs_count == 1):
            return True
    
        vertex_degrees = graph.degree()
    
        # countCentralNodes stores the count of nodes
        # with degree V - 1, which should be equal to 1
        # in case of star topology
        # countCentralNodes = 0
        # centralNode = 0
    
        central_nodes = [ 1 if degree == _vs_count-1 else 0 for degree in vertex_degrees]

        # there should be only one central node
        # in the star topology
        if (sum(central_nodes) != 1):
            return False
    
        # for i in range(1, V + 1):
        #     # except for the central node
        #     # check if all other nodes have
        #     # degree 1, if not return false
        #     if (i == centralNode):
        #         continue
        #     if (vertexDegree[i] != 1):
        #         return False
    
        # if all three necessary
        # conditions as discussed,
        # satisfy return true
        return True

    def find_communities_recursive(self, graph: igraph.Graph, depth: Union[str, None] = None, tree = None, method : int = 0) -> None:

        if depth is not None:
            self.communities_subgraph_inclusive[depth] = [graph.copy(),]
        
        # Tree setting spot.
        key_regs = [i["_nx_name"] for i in graph.vs if i["_nx_name"] in self.key_regulators.keys()]
        has_keyreg = True if len(key_regs) > 0 else False

        tree['lineage'] = '0' if depth is None else depth
        tree['current_depth'] = tree['lineage'].count('_') + 1
        tree['key_regs'] = key_regs
        tree['num_vertices'] = len(graph.vs)
        tree['has_keyreg'] = has_keyreg
        tree['name'] = 'root' if depth is None else depth.split('_')[-1]
        

        # Implement motif discovery here.
        if not self.has_cycles(graph):
            tree['is_leaf_node'] = True
            return

        if len(graph.vs) <= self.subgraph_min_vertices:
            _edg_list = [ [ graph.vs[edge]["_nx_name"] for edge in line] for line in graph.get_edgelist() ]
            if len(_edg_list) > 1:
                self._leaf_communities_edgelist[depth] = _edg_list
                tree['is_leaf_node'] = True
            return

        if depth is not None:
            _edg_list = [ [ graph.vs[edge]["_nx_name"] for edge in line] for line in graph.get_edgelist() ]
            self._root_communities_edgelist[f"0_{depth}"] = _edg_list

        if method == 0:communities = list(graph.community_leading_eigenvector( ))
        elif method == 1:communities = list(graph.community_multilevel())

        for vertices in communities:
            if len(vertices) == len(graph.vs):communities.remove(vertices)

        for cg_index, community_vertices in enumerate(communities,1):
            # Tree Stuff
            _c_tree = {
                'name' : None,
                'lineage' : None,
                'current_depth' : None,
                'children': [],
                'key_regs' : []
            }

            sub_graph = graph.subgraph( graph.vs.select(community_vertices) )

            if depth is None:
                _depth = str(cg_index)
            else:
                _depth = f"{depth}_{str(cg_index)}"

            self.find_communities_recursive(sub_graph, tree = _c_tree,  depth = _depth, method = method)
            # Tree Stuff.
            tree['children'].append(_c_tree)

    def find_topological_and_centrality_properties(self, graph: igraph.Graph , vertex_index: int = None ):
        
        # See: https://igraph.org/python/doc/api/igraph._igraph.GraphBase.html#eigenvector_centrality
        l = len(graph.vs)
        _eigen_vector_centrality = graph.evcent()[vertex_index]/( ( (l-1)*(l-2) ) / 2 ) 

        # See: https://igraph.org/python/doc/api/igraph._igraph.GraphBase.html#betweenness
        # Also see: https://en.wikipedia.org/wiki/Betweenness_centrality#Definition
        # The value is scaled by dividing the centrality value by total number of vertex pairs in the graph.
        _bc_arr = graph.betweenness()
        # l = len(_bc_arr)
        _betweenness_centrality = _bc_arr[vertex_index] / ( ( (l-1)*(l-2) ) / 2 ) # Good 


        # See: https://igraph.org/python/doc/api/igraph._igraph.GraphBase.html#closeness
        _closeness_centrality = graph.closeness()[vertex_index] ## Good

        # We find the degree of current node then find the number
        # of occurrences of that degree in the list of degrees of all nodes.
        # Then we divide the above number with the length of all distinct degrees
        # present in the graph.
        _degrees = graph.degree()
        _deg = _degrees[vertex_index]
        _p_degree_distribution = _degrees.count(_deg) / len(_degrees) # Seems good but verification would be great.

        # See: https://igraph.org/python/doc/api/igraph._igraph.GraphBase.html#transitivity_local_undirected
        _clustering_coeff = graph.transitivity_local_undirected()[vertex_index] # Good

        # See: https://www.centiserver.org/centrality/Neighborhood_Connectivity/
        _neighborhood = graph.neighbors(vertex_index)
        _neighborhood_connectivity = sum([graph.vs[vid].degree() for vid in _neighborhood ])/len(_neighborhood) ## Good

        return (
            _eigen_vector_centrality,
            _betweenness_centrality,
            _closeness_centrality,
            _p_degree_distribution,
            _clustering_coeff,
            _neighborhood_connectivity
        )

    def loadFile(self, filePath: Union[str, BinaryIO]) -> None:
        try:
            self._graph = nx.read_edgelist(filePath, comments="#", delimiter="\t")
            # _json_data = json.dumps(json_graph.node_link_data(self._graph))
            # _json_file_obj = open( os.path.join("", f"parent_network.json"), "w")
            # _json_file_obj.write(_json_data)
            # _json_file_obj.flush()
            # _json_file_obj.close()
            self._graph = igraph.Graph.from_networkx(self._graph)

        except IOError:
            sys.stderr.write(
                "Error while reading the file," " no such file or directory."
            )
            raise

    def genrate_edgelist(self, v_name_type: str = '_nx_name'):
        if self._leaf_communities_edgelist == {}:
            self.parse()
        for order, subgraph in self._leaf_communities_edgelist.items():
            l = self._graph.vs[0]
            yield order, [ [self._graph.vs[edge][v_name_type] for edge in line] for line in subgraph.get_edgelist() ]

    def write_edgelist_to_files(self, base_dir : str = "./", delimiter: str = "\t", format : str = "txt" , newline="\n"):
        for filename, sub_graph_edgelist in self._leaf_communities_edgelist.items():
            # self.communities_subgraph_inclusive[filename][0]["shape"] = 3
            # self.communities_subgraph_inclusive[filename][0].write_svg(
            #     os.path.join(base_dir, f"{filename}.svg"),
            #     shapes="shape",
            #     labels="_nx_name",
            #     colors="green"
            # )

            g_sub = nx.Graph(sub_graph_edgelist)
            _json_data = json.dumps(json_graph.node_link_data(g_sub))

            _json_file_obj = open( os.path.join(base_dir, f"{filename}.json"), "w")
            _json_file_obj.write(_json_data)
            _json_file_obj.flush()
            _json_file_obj.close()


if __name__ == "__main__":

    if len(sys.argv) < 3:
        sys.stderr.write("Please provide filepath as first commandline arg and a directory to write the edgelist.")

        cf = CommunityFinder("/var/www/html/scheduler/test_data.tsv")#sys.argv[1])

        leaf_communities = cf.parse()
        
        lc = cf._leaf_communities_edgelist

        tree__ = cf.tree['root']

        key_regs = cf.key_regulators

        with open("lib/knowledge_tree.json", "w") as fp:
            json.dump(tree__,fp)

        community_subgraph_inclusive = cf.communities_subgraph_inclusive

        # cf.write_edgelist_to_files(base_dir="./samples")

        key_regs.clear()
    else:
        cf = CommunityFinder(sys.argv[1])
        
        leaf_communities = cf.parse()
        
        lc = cf._leaf_communities_edgelist

        key_regs = cf.key_regulators

        community_subgraph_inclusive = cf.communities_subgraph_inclusive

        cf.write_edgelist_to_files(base_dir=sys.argv[2])
