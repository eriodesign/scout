<?php

namespace Eriodesign\Scout;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient as Algolia;
use Algolia\AlgoliaSearch\Support\UserAgent;
use Exception;
use Eriodesign\Scout\Engines\AlgoliaEngine;
use Eriodesign\Scout\Engines\CollectionEngine;
use Eriodesign\Scout\Engines\DatabaseEngine;
use Eriodesign\Scout\Engines\ElasticSearchEngine;
use Eriodesign\Scout\Engines\MeiliSearchEngine;
use Eriodesign\Scout\Engines\NullEngine;
use MeiliSearch\Client as MeiliSearch;
use Elastic\Elasticsearch\Client as ElasticSearch;
use Elastic\Elasticsearch\ClientBuilder;
use Eriodesign\Scout\Engines\XunSearchEngine;

class EngineManager extends Manager
{
    /**
     * Get a driver instance.
     *
     * @param  string|null  $name
     * @return \Eriodesign\Scout\Engines\Engine
     */
    public function engine($name = null)
    {
        return $this->driver($name);
    }

    /**
     * Create an Algolia engine instance.
     *
     * @return \Eriodesign\Scout\Engines\AlgoliaEngine
     */
    public function createAlgoliaDriver()
    {
        $this->ensureAlgoliaClientIsInstalled();

        UserAgent::addCustomUserAgent('Laravel Scout', '9.4.9');

        $config = SearchConfig::create(
            config('plugin.eriodesign.scout.app.algolia.id'),
            config('plugin.eriodesign.scout.app.algolia.secret')
        )->setDefaultHeaders(
            $this->defaultAlgoliaHeaders()
        );

        if (is_int($connectTimeout = config('plugin.eriodesign.scout.app.algolia.connect_timeout'))) {
            $config->setConnectTimeout($connectTimeout);
        }

        if (is_int($readTimeout = config('plugin.eriodesign.scout.app.algolia.read_timeout'))) {
            $config->setReadTimeout($readTimeout);
        }

        if (is_int($writeTimeout = config('plugin.eriodesign.scout.app.algolia.write_timeout'))) {
            $config->setWriteTimeout($writeTimeout);
        }

        return new AlgoliaEngine(Algolia::createWithConfig($config), config('plugin.eriodesign.scout.app.soft_delete'));
    }

    /**
     * Ensure the Algolia API client is installed.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function ensureAlgoliaClientIsInstalled()
    {
        if (class_exists(Algolia::class)) {
            return;
        }

        if (class_exists('AlgoliaSearch\Client')) {
            throw new Exception('Please upgrade your Algolia client to version: ^2.2.');
        }

        throw new Exception('Please install the Algolia client: algolia/algoliasearch-client-php.');
    }

    /**
     * Set the default Algolia configuration headers.
     *
     * @return array
     */
    protected function defaultAlgoliaHeaders()
    {
        if (! config('plugin.eriodesign.scout.app.identify')) {
            return [];
        }

        $headers = [];

        if (! config('app.debug') &&
            filter_var($ip = request()->ip(), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
        ) {
            $headers['X-Forwarded-For'] = $ip;
        }

        if (($user = request()->user()) && method_exists($user, 'getKey')) {
            $headers['X-Algolia-UserToken'] = $user->getKey();
        }

        return $headers;
    }

    /**
     * Create an MeiliSearch engine instance.
     *
     * @return \Eriodesign\Scout\Engines\MeiliSearchEngine
     */
    public function createMeilisearchDriver()
    {

        $this->ensureMeiliSearchClientIsInstalled();
        $client = $this->container->make(MeiliSearch::class);
        return new MeiliSearchEngine(
            $client,
            config('plugin.eriodesign.scout.app.soft_delete', false)
        );
    }

    /**
     * Ensure the MeiliSearch client is installed.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function ensureMeiliSearchClientIsInstalled()
    {
        if (class_exists(MeiliSearch::class)) {
            return;
        }

        throw new Exception('Please install the MeiliSearch client: meilisearch/meilisearch-php.');
    }

    /**
     * Create an ElasticSearch engine instance.
     *
     * @return \Eriodesign\Scout\Engines\ElasticSearchEngine
     */
    public function createElasticsearchDriver()
    {
        $config = config('plugin.eriodesign.scout.app.elasticsearch');
        $this->ensureElasticSearchClientIsInstalled();
        $clientBuilder = ClientBuilder::create()->setHosts($config['hosts']);

        if(!empty($config['auth'])){
            if(!empty($config['auth']['user']) && $config['auth']['user'] !== null &&
                !empty($config['auth']['pass']) && $config['auth']['pass'] !== null
            ){
                $clientBuilder->setBasicAuthentication($config['auth']['user'], $config['auth']['pass']);
            }
            if (!empty($config['auth']['api_key']) && $config['auth']['api_key'] !== null) {
                $clientBuilder->setApiKey( $config['auth']['api_key'],$config['auth']['api_id'] ?? null);
            }
            if (!empty($config['auth']['cloud_id']) && $config['auth']['cloud_id'] !== null) {
                $clientBuilder->setElasticCloudId( $config['auth']['cloud_id']);
            }
        }
        return new ElasticSearchEngine(
            $clientBuilder->build(),
            config('plugin.eriodesign.scout.app.soft_delete', false)
        );
    }

    /**
     * Ensure the MeiliSearch client is installed.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function ensureElasticSearchClientIsInstalled()
    {
        if (class_exists(ElasticSearch::class)) {
            return;
        }

        throw new Exception('Please install the ElasticSearch client: elasticsearch/elasticsearch.');
    }

    /**
     * Create an ElasticSearch engine instance.
     *
     * @return \Eriodesign\Scout\Engines\XunSearchEngine
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     */
    public function createXunsearchDriver()
    {

        $this->ensureXunSearchClientIsInstalled();
        return new XunSearchEngine(
            new XunSearchClient(),
            config('plugin.eriodesign.scout.app.soft_delete', false)
        );
    }

    /**
     * Ensure the MeiliSearch client is installed.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function ensureXunSearchClientIsInstalled()
    {
        if (class_exists(\XS::class)) {
            return;
        }

        throw new Exception('Please install the ElasticSearch client: elasticsearch/elasticsearch.');
    }
    /**
     * Create a database engine instance.
     *
     * @return \Eriodesign\Scout\Engines\DatabaseEngine
     */
    public function createDatabaseDriver()
    {
        return new DatabaseEngine;
    }

    /**
     * Create a collection engine instance.
     *
     * @return \Eriodesign\Scout\Engines\CollectionEngine
     */
    public function createCollectionDriver()
    {
        return new CollectionEngine;
    }

    /**
     * Create a null engine instance.
     *
     * @return \Eriodesign\Scout\Engines\NullEngine
     */
    public function createNullDriver()
    {
        return new NullEngine;
    }

    /**
     * Forget all of the resolved engine instances.
     *
     * @return $this
     */
    public function forgetEngines()
    {
        $this->drivers = [];

        return $this;
    }

    /**
     * Get the default Scout driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        if (is_null($driver = config('plugin.eriodesign.scout.app.driver'))) {
            return 'null';
        }

        return $driver;
    }
}
