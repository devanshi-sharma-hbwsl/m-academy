<?php

declare(strict_types=1);

/**
 * Copyright OpenSearch Contributors
 * SPDX-License-Identifier: Apache-2.0
 *
 * OpenSearch PHP client
 *
 * @link      https://github.com/opensearch-project/opensearch-php/
 * @copyright Copyright (c) Elasticsearch B.V (https://www.elastic.co)
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license   https://www.gnu.org/licenses/lgpl-2.1.html GNU Lesser General Public License, Version 2.1
 *
 * Licensed to Elasticsearch B.V under one or more agreements.
 * Elasticsearch B.V licenses this file to you under the Apache 2.0 License or
 * the GNU Lesser General Public License, Version 2.1, at your option.
 * See the LICENSE file in the project root for more information.
 */

namespace OpenSearch\Namespaces;

/**
 * Class NodesNamespace
 *
 * NOTE: This file is autogenerated using util/GenerateEndpoints.php
 */
class NodesNamespace extends AbstractNamespace
{
    /**
     * Returns information about hot threads on each node in the cluster.
     *
     * $params['node_id']             = (array) Comma-separated list of node IDs or names to limit the returned information; use `_local` to return information from the node you're connecting to, leave empty to get information from all nodes.
     * $params['ignore_idle_threads'] = (boolean) Don't show threads that are in known-idle places, such as waiting on a socket select or pulling from an empty task queue. (Default = true)
     * $params['interval']            = (string) The interval for the second sampling of threads.
     * $params['snapshots']           = (integer) Number of samples of thread stack trace. (Default = 10)
     * $params['threads']             = (integer) Specify the number of threads to provide information for. (Default = 3)
     * $params['timeout']             = (string) Operation timeout.
     * $params['type']                = (enum) The type to sample. (Options = block,cpu,wait)
     * $params['pretty']              = (boolean) Whether to pretty format the returned JSON response. (Default = false)
     * $params['human']               = (boolean) Whether to return human readable values for statistics. (Default = true)
     * $params['error_trace']         = (boolean) Whether to include the stack trace of returned errors. (Default = false)
     * $params['source']              = (string) The URL-encoded request definition. Useful for libraries that do not accept a request body for non-POST requests.
     * $params['filter_path']         = (any) Used to reduce the response. This parameter takes a comma-separated list of filters. It supports using wildcards to match any field or part of a field’s name. You can also exclude fields with "-".
     *
     * @param array $params Associative array of parameters
     * @return array
     */
    public function hotThreads(array $params = [])
    {
        $node_id = $this->extractArgument($params, 'node_id');

        $endpoint = $this->endpointFactory->getEndpoint(\OpenSearch\Endpoints\Nodes\HotThreads::class);
        $endpoint->setParams($params);
        $endpoint->setNodeId($node_id);

        return $this->performRequest($endpoint);
    }

    /**
     * Returns information about nodes in the cluster.
     *
     * $params['node_id_or_metric'] = (any) Limits the information returned to a list of node IDs or specific metrics. Supports a comma-separated list, such as `node1,node2` or `http,ingest`.
     * $params['metric']            = (array) Limits the information returned to the specific metrics. Supports a comma-separated list, such as http,ingest.
     * $params['node_id']           = (array) Comma-separated list of node IDs or names used to limit returned information.
     * $params['flat_settings']     = (boolean) If `true`, returns settings in flat format. (Default = false)
     * $params['timeout']           = (string) Period to wait for a response. If no response is received before the timeout expires, the request fails and returns an error.
     * $params['pretty']            = (boolean) Whether to pretty format the returned JSON response. (Default = false)
     * $params['human']             = (boolean) Whether to return human readable values for statistics. (Default = true)
     * $params['error_trace']       = (boolean) Whether to include the stack trace of returned errors. (Default = false)
     * $params['source']            = (string) The URL-encoded request definition. Useful for libraries that do not accept a request body for non-POST requests.
     * $params['filter_path']       = (any) Used to reduce the response. This parameter takes a comma-separated list of filters. It supports using wildcards to match any field or part of a field’s name. You can also exclude fields with "-".
     *
     * @param array $params Associative array of parameters
     * @return array
     */
    public function info(array $params = [])
    {
        $node_id_or_metric = $this->extractArgument($params, 'node_id_or_metric');
        $metric = $this->extractArgument($params, 'metric');
        $node_id = $this->extractArgument($params, 'node_id');

        $endpoint = $this->endpointFactory->getEndpoint(\OpenSearch\Endpoints\Nodes\Info::class);
        $endpoint->setParams($params);
        $endpoint->setNodeIdOrMetric($node_id_or_metric);
        $endpoint->setMetric($metric);
        $endpoint->setNodeId($node_id);

        return $this->performRequest($endpoint);
    }

    /**
     * Reloads secure settings.
     *
     * $params['node_id']     = (array) The names of particular nodes in the cluster to target.
     * $params['timeout']     = (string) Period to wait for a response.If no response is received before the timeout expires, the request fails and returns an error.
     * $params['pretty']      = (boolean) Whether to pretty format the returned JSON response. (Default = false)
     * $params['human']       = (boolean) Whether to return human readable values for statistics. (Default = true)
     * $params['error_trace'] = (boolean) Whether to include the stack trace of returned errors. (Default = false)
     * $params['source']      = (string) The URL-encoded request definition. Useful for libraries that do not accept a request body for non-POST requests.
     * $params['filter_path'] = (any) Used to reduce the response. This parameter takes a comma-separated list of filters. It supports using wildcards to match any field or part of a field’s name. You can also exclude fields with "-".
     * $params['body']        = (array) An object containing the password for the OpenSearch keystore.
     *
     * @param array $params Associative array of parameters
     * @return array
     */
    public function reloadSecureSettings(array $params = [])
    {
        $node_id = $this->extractArgument($params, 'node_id');
        $body = $this->extractArgument($params, 'body');

        $endpoint = $this->endpointFactory->getEndpoint(\OpenSearch\Endpoints\Nodes\ReloadSecureSettings::class);
        $endpoint->setParams($params);
        $endpoint->setNodeId($node_id);
        $endpoint->setBody($body);

        return $this->performRequest($endpoint);
    }

    /**
     * Returns statistical information about nodes in the cluster.
     *
     * $params['node_id']                    = (array) Comma-separated list of node IDs or names used to limit returned information.
     * $params['metric']                     = (array) Limit the information returned to the specified metrics
     * $params['index_metric']               = (array) Limit the information returned for indexes metric to the specific index metrics. It can be used only if indexes (or all) metric is specified.
     * $params['completion_fields']          = (any) Comma-separated list or wildcard expressions of fields to include in field data and suggest statistics.
     * $params['fielddata_fields']           = (any) Comma-separated list or wildcard expressions of fields to include in field data statistics.
     * $params['fields']                     = (any) Comma-separated list or wildcard expressions of fields to include in the statistics.
     * $params['groups']                     = (array) Comma-separated list of search groups to include in the search statistics.
     * $params['include_segment_file_sizes'] = (boolean) If `true`, the call reports the aggregated disk usage of each one of the Lucene index files (only applies if segment stats are requested). (Default = false)
     * $params['level']                      = (enum) Indicates whether statistics are aggregated at the cluster, index, or shard level. (Options = cluster,indices,shards)
     * $params['timeout']                    = (string) Period to wait for a response. If no response is received before the timeout expires, the request fails and returns an error.
     * $params['types']                      = (array) A comma-separated list of document types for the indexing index metric.
     * $params['pretty']                     = (boolean) Whether to pretty format the returned JSON response. (Default = false)
     * $params['human']                      = (boolean) Whether to return human readable values for statistics. (Default = true)
     * $params['error_trace']                = (boolean) Whether to include the stack trace of returned errors. (Default = false)
     * $params['source']                     = (string) The URL-encoded request definition. Useful for libraries that do not accept a request body for non-POST requests.
     * $params['filter_path']                = (any) Used to reduce the response. This parameter takes a comma-separated list of filters. It supports using wildcards to match any field or part of a field’s name. You can also exclude fields with "-".
     *
     * @param array $params Associative array of parameters
     * @return array
     */
    public function stats(array $params = [])
    {
        $node_id = $this->extractArgument($params, 'node_id');
        $metric = $this->extractArgument($params, 'metric');
        $index_metric = $this->extractArgument($params, 'index_metric');

        $endpoint = $this->endpointFactory->getEndpoint(\OpenSearch\Endpoints\Nodes\Stats::class);
        $endpoint->setParams($params);
        $endpoint->setNodeId($node_id);
        $endpoint->setMetric($metric);
        $endpoint->setIndexMetric($index_metric);

        return $this->performRequest($endpoint);
    }

    /**
     * Returns low-level information about REST actions usage on nodes.
     *
     * $params['node_id']     = (array) A comma-separated list of node IDs or names to limit the returned information; use `_local` to return information from the node you're connecting to, leave empty to get information from all nodes
     * $params['metric']      = (array) Limits the information returned to the specific metrics. A comma-separated list of the following options: `_all`, `rest_actions`.
     * $params['timeout']     = (string) Period to wait for a response.If no response is received before the timeout expires, the request fails and returns an error.
     * $params['pretty']      = (boolean) Whether to pretty format the returned JSON response. (Default = false)
     * $params['human']       = (boolean) Whether to return human readable values for statistics. (Default = true)
     * $params['error_trace'] = (boolean) Whether to include the stack trace of returned errors. (Default = false)
     * $params['source']      = (string) The URL-encoded request definition. Useful for libraries that do not accept a request body for non-POST requests.
     * $params['filter_path'] = (any) Used to reduce the response. This parameter takes a comma-separated list of filters. It supports using wildcards to match any field or part of a field’s name. You can also exclude fields with "-".
     *
     * @param array $params Associative array of parameters
     * @return array
     */
    public function usage(array $params = [])
    {
        $node_id = $this->extractArgument($params, 'node_id');
        $metric = $this->extractArgument($params, 'metric');

        $endpoint = $this->endpointFactory->getEndpoint(\OpenSearch\Endpoints\Nodes\Usage::class);
        $endpoint->setParams($params);
        $endpoint->setNodeId($node_id);
        $endpoint->setMetric($metric);

        return $this->performRequest($endpoint);
    }

}
