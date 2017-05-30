<?php

namespace Ds\Component\Formio\Service;

use GuzzleHttp\ClientInterface;
use Ds\Component\Formio\Query\Parameters;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Class AbstractService
 */
abstract class AbstractService implements Service
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $host; # region accessors

    /**
     * Set host
     *
     * @param string $host
     * @return \Ds\Component\Formio\Service\Service
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    # endregion

    /**
     * Constructor
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string $host
     */
    public function __construct(ClientInterface $client, $host = null)
    {
        $this->client = $client;
        $this->host = $host;
    }

    /**
     * Execute api request
     *
     * @param string $method
     * @param string $resource
     * @param \Ds\Component\Formio\Query\Parameters $parameters
     * @return mixed
     */
    protected function execute($method, $resource, Parameters $parameters = null)
    {
        $uri = $this->host.$resource;
        $options = ['content-type' => 'application/json'];
        $response = $this->client->request($method, $uri, $options);

        try {
            $data = \GuzzleHttp\json_decode($response->getBody());
        } catch (InvalidArgumentException $exception) {
            throw new UnexpectedValueException('Service response is not valid.', 0, $exception);
        }

        return $data;
    }
}