# Developer documentation

Documentation used to describe technical design and thoughts.

## Facetapi api to the external world (hooks and plugins)

For the first version of the drupal 8 module we keep the absolutely necessary hooks
and from the info, only the necessary info items.
Look at the facetapi.api.php file in the drupal 7 module for extra info.

In drupal 8, we can use hooks, or events. 
For now we still use hooks to keep the transition simple.
Later on we will replace the hooks by events.

### Searchers (hook_facetapi_searcher_info)

Searchers are synonymous with search pages, or environments. Multiple
searchers can share the same adapter class, but each searcher will spawn a
separate instance of the adapter. Each searcher must be unique, so it is
common practice to prefix the name with the module implementing the hook,
such as "apachesolr@searcher-x", "search_api@searcher-y", etc.

The searchers are used for the following things: 

- Creating the admin pages for the facets.
- Storing facets fields per searcher (done by a hook)

The info we need per searcher are:

- label
- Adapter
- Url processor
- path
- 

### Facets (hook_facetapi_facet_info)

Define all facets provided by the module.

Facets correspond with fields in the search index and are usually related to
entity properties and fields. However, it is not a requirement that the
source data be stored in Drupal. For example, if you are indexing external
RSS feeds, facets can be defined that filter by the field in the index that
stores the publication dates.

### Adapters

And adapter was a plugin in the drupal 7 module and will be the same in drupal 8.

### Query types

Query types where plugins in the drupal 7 module and will be the same in drupal 8.

## Internal concepts and plugins.

The following internal concepts and plugins can be recognized:

### Processor (helper class)

From the drupal 7 documentation:

Builds base render array used as a starting point for rendering.

The processor constructs the base render array used by widgets across all
realms. It is responsible for mapping the raw data returned by the index to
human readable values, processing hierarchical data, and building the query
strings for each facet item via the adapter's url processor plugin.

The processors are generated per facets in the processFacets method in the adapter.
Dependencies are injected upon generation (in the constructor).

### FacetapiFacet (helper class)

Wrapper around the facet definition with methods that build render arrays.

Thic class contain methods that assist in render array generation and stores
additional context about how and what generated the render arrays for
consumption by the widget plugins. Objects can also be used as if they are
the facet definitions returned by facetapi_facet_load().

### Url processor (plugin)

Url processor plugins provide a pluggable method of retrieving facet data.
Most commonly facet data is retrieved from a query string variable via $_GET,
however custom plugis can be written to retrieve data from the path as well.
In addition to facet data retrieval, the url processor plugin is also
responsible for building facet links and setting breadcrumb trails.

Each adapter instance is associated with a single url processor plugin. The
plugin is associated with the adapter via hook_facetapi_searcher_info()
implementations.

### Adapter (plugin)

Adapters are responsible for abstracting interactions with the Search backend
that are necessary for faceted search. The adapter is also responsible for
retrieving facet information passed by the user via the url processor plugin
taking the appropriate action, whether it is checking dependencies for all
enabled facets or passing the appropriate query type plugin to the backend
so that it can execute the actual facet query.

### Query types (backend dependent plugin)

Facet API does not perform any facet calculations on it's own. The query type
plugin system provides Facet API with a consistent way to tell backends what
type of query to execute in order to return the appropriate data required
for the faceted navigation display.

Query type plugins are implemented by the backends and perform the
alterations of their internal search engine query mechanisms to execute the
filters and retrieve facet data. For example, modules that integrate with
Apache Solr will set the necessary params for faceting, whereas modules that
extend the core Search module will add SQL joins, filter clauses, and COUNT
queries in order to implement faceting.

Although the actual method of querying the search engine is vastly different
per backend, Facet API operates under the assumption that the types of
queries are the same. For example, a "term" query is assumed to be a straight
filter, whereas a "range" query is assumed to be a search between two values.
Although the common query types such as "term" and "date" should be available
to all backends, it is expected that some backends will have additional query
types based on capability. For example, backends integrating with the Apache
Solr engine might have a "geospatial" query type that modules integrating
with the core Search won't have.

### Widgets (plugin)

Widgets are responsible for altering the render arrays to achieve some user
interface component. For example, the render arrays could produce a list of
clickable links or even clickable charts.

# Flow in requests

The following flow was used in search api in drupal 7.

Go to https://www.drupal.org/node/2348781 for the flow in drupal 7.

# Implementation flow

The best way to start developing is to create a  module that should implement
the facetapi. 
A search_api_facets module will be made, which requires the facetapi and implements
the hooks as stated in this document. Start with altering the query by the supplied facets.
There will be no ui to enable facets anywhere. We just start with hardcoded facets on
which a ui may be build later.
The first version aims to show a facet block showing counts for links.