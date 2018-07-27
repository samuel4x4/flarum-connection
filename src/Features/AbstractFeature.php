<?php

namespace FlarumConnection\Features;

use FlarumConnection\Exceptions\InvalidCreationException;
use FlarumConnection\Exceptions\InvalidDeleteException;
use FlarumConnection\Exceptions\InvalidObjectException;

use FlarumConnection\Exceptions\InvalidUpdateException;


use \FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\AbstractModel;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Adapter\Guzzle6\Client;
use Http\Promise\RejectedPromise;
use \Psr\Log\LoggerInterface;

use WoohooLabs\Yang\JsonApi\Client\JsonApiAsyncClient;
use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;

abstract class AbstractFeature
{

    /**
     * Configuration of the library
     *
     * @var FlarumConnectorConfig
     */
    protected $config;

    /**
     * Logger (PSR3 interface)
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Http client
     *
     * @var \GuzzleHttp\Client
     */
    protected $http;



    /**
     * Initialize the feature with the config
     *
     * @param FlarumConnectorConfig $config Configuration for flarum connector
     * @param LoggerInterface $logger Logger interface
     */
    protected function init(FlarumConnectorConfig $config, LoggerInterface $logger): void
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->http = new \GuzzleHttp\Client();
    }

    /**
     * Insert the model in flarum
     * @param string $uri The
     * @param AbstractModel $model
     * @param int $validResponseCode
     * @param int|null $user
     * @return \Http\Promise\Promise
     */
    protected function insert(string $uri, AbstractModel $model, int $validResponseCode, ?int $user = null): \Http\Promise\Promise
    {
        return $this->alter($uri, 'INSERT', $model, $validResponseCode, $user);
    }

    /**
     * Insert the model in flarum
     * @param string $uri The
     * @param AbstractModel $model
     * @param int $validResponseCode
     * @param int|null $user
     * @return \Http\Promise\Promise
     */
    protected function update(string $uri, AbstractModel $model, int $validResponseCode, ?int $user = null): \Http\Promise\Promise
    {
        return $this->alter($uri, 'UPDATE', $model, $validResponseCode, $user);
    }


    /**
     * Insert or Update a new object within Flarum
     * @param string $uri The URI of the WS
     * @param string $method INSERT or UPDATE
     * @param AbstractModel $model he data model to insert, id must not be empty in order to updtate
     * @param int $validResponseCode The expected http response code
     * @param int|null $user  The user who should call the API
     * @return \Http\Promise\Promise        A promise of a Rootmodel
     */
    protected function alter(string $uri, string $method, AbstractModel $model, int $validResponseCode, ?int $user = null): \Http\Promise\Promise
    {

        $verb = 'POST';
        if ($method === 'UPDATE') {
            $verb = 'PATCH';
        }

        $objectName = $model->getModelName();
        $hydrator = $model->getHydrator();
        $serializer = $model->getSerializer();

        //Create body
        if ($method === 'UPDATE') {
            $body = $serializer->getBodyUpdate($model);
        } else {
            $body = $serializer->getBodyInsert($model);

        }


        // Instantiate an empty PSR-7 request
        $request = new Request('', '');

        // Instantiate the request builder
        $requestBuilder = new JsonApiRequestBuilder($request);

        $requestBuilder
            ->setProtocolVersion('1.1')
            ->setMethod($verb)
            ->setUri($uri)
            ->setHeader('Accept-Charset', 'utf-8')
            ->setJsonApiBody($body);

        //set the auth
        $this->setAuth($requestBuilder,$user);

        $request = $requestBuilder->getRequest();


        $client = new JsonApiAsyncClient(Client::createWithConfig([]));
        return $client->sendAsyncRequest($request)->then(
            function (Response $res) use ($hydrator, $objectName, $validResponseCode, $method) {
                try {
                    $resp = new JsonApiResponse($res);
                    if ($resp->isSuccessful([$validResponseCode]) && $resp->isSuccessfulDocument()) {
                        return $hydrator->hydrate($resp->document());
                    }
                    $this->logger->error('Invalid response on ' . $objectName . ' ' . $method . ' ' . $res->getStatusCode() . ' returned\n');
                    $this->logger->debug('Invalid response ' . $res->getBody()->getContents());
                    return new RejectedPromise(new InvalidObjectException('Error during ' . $objectName . ' ' . $method . ' '));

                } catch (\Exception $e) {
                    $this->logger->error('Exception triggered on ' . $objectName . ' ' . $method . ' ' . $res->getStatusCode() . $e->getMessage() . ' returned');
                    return new RejectedPromise($this->getException($e->getMessage(), $method));
                }
            },
            function (\Exception $e) use ($objectName, $method) {
                $this->logger->error('Exception trigerred on ' . $objectName . ' ' . $method . ' ' . $e->getMessage());
                return new RejectedPromise($this->getException('Exception trigerred on on ' . $objectName . ' ' . $method . ' ', $method));
            }
        );

    }

    /**
     * Retrieve a list of items from Flarum
     * @param string $uri The URI of the WS
     * @param AbstractModel $model he data model to insert, id must not be empty in order to updtate
     * @param int|null $user
     * @return \Http\Promise\Promise        A promise of an array of models of an exception
     */
    protected function getAll(string $uri, AbstractModel $model,?int $user = null): \Http\Promise\Promise
    {

        return $this->get($uri, $model, 'ALL', $user);
    }

    /**
     * Return one object from Flarum
     * @param string $uri The URI of the WS
     * @param AbstractModel $model he data model to insert, id must not be empty in order to updtate
     * @param int|null $user
     * @return \Http\Promise\Promise        A promise of a model of an exception
     */
    protected function getOne(string $uri, AbstractModel $model, ?int $user = null): \Http\Promise\Promise
    {
        return $this->get($uri, $model, 'ONE', $user);
    }


    /**
     * Retrieve a list or an object
     * @param string $uri The URI to request
     * @param AbstractModel $model The model to be used
     * @param string $mod ALL for a collection, ONE for an object
     * @param int|null $user    The user to use for retrievam
     * @return \Http\Promise\Promise    A promise of a list, an object or an exception
     */
    private function get(string $uri, AbstractModel $model, string $mod, ?int $user = null): \Http\Promise\Promise
    {
        $objectName = $model->getModelName();
        $hydrator = $model->getHydrator();

        // Instantiate an empty PSR-7 request
        $request = new Request('', '');

        // Instantiate the request builder
        $requestBuilder = new JsonApiRequestBuilder($request);

        //Create request
        $requestBuilder
            ->setProtocolVersion('1.1')
            ->setMethod('GET')
            ->setUri($uri)
            ->setHeader('Accept-Charset', 'utf-8');

        //Set the auth
        $this->setAuth($requestBuilder,$user);

        $request = $requestBuilder->getRequest();
        $client = new JsonApiAsyncClient(Client::createWithConfig([]));
        return $client->sendAsyncRequest($request)->then(
            function (Response $res) use ($hydrator, $objectName, $mod) {
                try {
                    $resp = new JsonApiResponse($res);
                    if ($resp->isSuccessful([200]) && $resp->isSuccessfulDocument()) {

                        if ($mod === 'ALL') {
                            return $hydrator->hydrateCollection($resp->document());
                        }

                        return $hydrator->hydrate($resp->document());

                    }
                    $this->logger->error('Invalid ' . $objectName . ' result :' . $resp->isSuccessful() . '/' . $resp->isSuccessfulDocument() . ' returned');
                    return new RejectedPromise(new InvalidObjectException('Invalid result retrieving ' . $objectName));
                } catch (\Exception $e) {
                    $this->logger->error('Exception trigerred on ' . $objectName . ' retrieval ' . $e->getMessage() . $e->getTraceAsString() . ' returned');
                    return new RejectedPromise(new InvalidObjectException($e->getMessage()));
                }
            },
            function (\Exception $e) use ($objectName) {
                $this->logger->error('Exception trigerred on ' . $objectName . ' retrieval' . $e->getMessage());
                return new RejectedPromise(new InvalidObjectException('Exception trigerred on ' . $objectName . ' retrieval'));
            }

        );
    }

    /**
     * Delete an object
     * @param string $uri The URI of the WS
     * @param AbstractModel $model he data model to insert, id must not be empty in order to updtate
     * @param int $validResponseCode The right response code
     * @param int|null $user    The user that should call the WS
     * @return \Http\Promise\Promise        A promise of a
     */
    protected function delete(string $uri, AbstractModel $model, int $validResponseCode, ?int $user = null): \Http\Promise\Promise
    {
        $objectName = $model->getModelName();



        // Instantiate an empty PSR-7 request
        $request = new Request('', '');

        // Instantiate the request builder
        $requestBuilder = new JsonApiRequestBuilder($request);

        //Create request
        $requestBuilder
            ->setProtocolVersion('1.1')
            ->setMethod('DELETE')
            ->setUri($uri)
            ->setHeader('Accept-Charset', 'utf-8');

        $this->setAuth($requestBuilder,$user);


        $request = $requestBuilder->getRequest();
        $client = new JsonApiAsyncClient(Client::createWithConfig([]));
        return $client->sendAsyncRequest($request)->then(
            function (Response $res) use ($objectName, $validResponseCode) {
                try {
                    $resp = new JsonApiResponse($res);
                    if ($resp->isSuccessful([$validResponseCode])) {
                        return true;
                    }
                    $this->logger->error('Invalid ' . $objectName . ' delete :' . $res->getStatusCode() . ' returned');
                    return new RejectedPromise(new InvalidDeleteException('Invalid result deleting ' . $objectName));
                } catch (\Exception $e) {
                    $this->logger->error('Exception trigerred on ' . $objectName . ' delete ' . $e->getMessage() . ' returned');
                    return new RejectedPromise(new InvalidDeleteException($e->getMessage()));
                }
            },
            function (\Exception $e) use ($objectName) {
                $this->logger->error('Exception trigerred on ' . $objectName . ' delete' . $e->getMessage());
                return new RejectedPromise(new InvalidDeleteException('Exception trigerred on ' . $objectName . ' delete'));
            }

        );
    }


    /**
     * Return the good exception according to the method
     * @param string $message The message to be displayed
     * @param $method string               INSERT or UPDATE
     * @return \Exception           The created exception
     */
    private function getException(string $message, $method): \Exception
    {
        if ($method === 'UPDATE') {
            return new InvalidUpdateException($message);
        }
        return new InvalidCreationException($message);
    }


    /**
     * Set the auth for Flarum request
     * @param JsonApiRequestBuilder $requestBuilder The request builder
     * @param int|null $userId The user id, if null take the default admin user
     */
    private function setAuth(JsonApiRequestBuilder $requestBuilder, ?int $userId = null): void
    {
        if ($userId === null) {
            $requestBuilder->setHeader('Authorization', 'Token ' . $this->config->flarumAPIKey . '; userId=' . $this->config->flarumDefaultUser);
        } else {
            $requestBuilder->setHeader('Authorization', 'Token ' . $this->config->flarumAPIKey . '; userId=' . $userId);
        }

    }
}
