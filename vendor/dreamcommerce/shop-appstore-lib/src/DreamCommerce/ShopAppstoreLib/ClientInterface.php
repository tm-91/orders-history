<?php

namespace DreamCommerce\ShopAppstoreLib;

use Psr\Log\LoggerInterface;

/**
 * Interface ClientInterface
 *
 * @package DreamCommerce
 */
interface ClientInterface
{
    /**
     * Authentication
     *
     * @param boolean $force
     * @return \stdClass
     * Example output:
     * {
     *      access_token:   'xxxxx',
     *      expires_in:     '3600',
     *      token_type:     'bearer'
     * }
     */
    public function authenticate($force = false);

    /**
     * Performs REST request
     *
     * @param Resource $res
     * @param string $method
     * @param null|array|int $objectPath
     * @param array $data
     * @param array $query
     * @return array
     * @throws \DreamCommerce\ShopAppstoreLib\Client\Exception\Exception
     */
    public function request(Resource $res, $method, $objectPath = null, $data = array(), $query = array());

    /**
     * @param HttpInterface $httpClient
     * @return $this
     */
    public function setHttpClient(HttpInterface $httpClient);

    /**
     * @return HttpInterface
     */
    public function getHttpClient();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale);

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * @return LoggerInterface
     */
    public function getLogger();

    /**
     * set callback upon invalid token error received
     * @param \Callable|null $callback
     * @return mixed
     */
    public function setOnTokenInvalidHandler($callback = null);
}