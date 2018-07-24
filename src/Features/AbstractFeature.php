<?php

namespace FlarumConnection\Features;

use FlarumConnection\Exceptions\InvalidCreationException;
use FlarumConnection\Exceptions\InvalidDeleteException;
use FlarumConnection\Exceptions\InvalidObjectException;

use FlarumConnection\Exceptions\InvalidUpdateException;
use FlarumConnection\Exceptions\InvalidUserException;

use FlarumConnection\Models\FlarumPost;
use \FlarumConnection\Models\FlarumToken;
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
     * The currently connected user
     *
     * @var \FlarumConnection\Models\FlarumToken
     */
    protected $token;


    /**
     * Initialize the feature with the config
     *
     * @param FlarumConnectorConfig $config Configuration for flarum connector
     * @param LoggerInterface $logger Logger interface
     */
    protected function init(FlarumConnectorConfig $config, LoggerInterface $logger)
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
     * @param bool|null $admin
     * @return \Http\Promise\Promise
     * @throws InvalidUserException
     */
    protected function insert(string $uri, AbstractModel $model, int $validResponseCode, ?bool $admin = false): \Http\Promise\Promise
    {
        return $this->alter($uri, 'INSERT', $model, $validResponseCode, $admin);
    }

    /**
     * Insert the model in flarum
     * @param string $uri The
     * @param AbstractModel $model
     * @param int $validResponseCode
     * @param bool|null $admin
     * @return \Http\Promise\Promise
     * @throws InvalidUserException
     */
    protected function update(string $uri, AbstractModel $model, int $validResponseCode, ?bool $admin = false): \Http\Promise\Promise
    {
        return $this->alter($uri, 'UPDATE', $model, $validResponseCode, $admin);
    }


    /**
     * Insert or Update a new object within Flarum
     * @param string $uri The URI of the WS
     * @param string $method INSERT or UPDATE
     * @param AbstractModel $model he data model to insert, id must not be empty in order to updtate
     * @param int $validResponseCode The expected http response code
     * @param bool|null $admin True if is admin
     * @return \Http\Promise\Promise        A promise of a Rootmodel
     * @throws InvalidUserException Trigerred if their is no connected user
     */
    protected function alter(string $uri, string $method, AbstractModel $model, int $validResponseCode, ?bool $admin = false): \Http\Promise\Promise
    {

        $verb = 'POST';
        if ($method === 'UPDATE') {
            $verb = 'PATCH';
        }

        $objectName = $model->getModelName();
        $hydrator = $model->getHydrator();
        $serializer = $model->getSerializer();

        $token = null;
        if ($admin) {
            $token = $this->config->flarumAPIKey;
        }

        //retrieve the current user
        if ($token === null) {
            //Retrieve current user
            $token = $this->getToken()->token;
            if ($token === false) {
                throw new InvalidUserException('There is no currently defined user');
            }
        }

        //Create body
        if ($method === 'UPDATE') {
            $body = $serializer->getBodyUpdate($model);
        } else {
            $body = $serializer->getBodyInsert($model);

        }


        // Instantiate an empty PSR-7 request
        $request = new Request("", "");

        // Instantiate the request builder
        $requestBuilder = new JsonApiRequestBuilder($request);

        $requestBuilder
            ->setProtocolVersion("1.1")
            ->setMethod($verb)
            ->setUri($uri)
            ->setHeader("Accept-Charset", "utf-8")
            ->setHeader('cookie', 'flarum_remember=' . $token)
            ->setJsonApiBody($body);

        if ($admin) {
            $requestBuilder->setHeader('Authorization', 'Token ' . $this->config->flarumAPIKey . '; userId=1');
        } else {
            $requestBuilder->setHeader('Authorization', 'Token ' . $token . '');
        }

        $request = $requestBuilder->getRequest();


        $client = new JsonApiAsyncClient(Client::createWithConfig([]));
        return $client->sendAsyncRequest($request)->then(
            function (Response $res) use ($hydrator, $objectName, $validResponseCode, $method,$request) {
                try {
                    $resp = new JsonApiResponse($res);
                    if ($resp->isSuccessful([$validResponseCode]) && $resp->isSuccessfulDocument()) {
                        return $hydrator->hydrate($resp->document());
                    }
                    $this->logger->error('Invalid response on ' . $objectName . ' ' . $method . ' ' . $res->getStatusCode() . ' returned\n');
                    $this->logger->debug('Invalid response '. $res->getBody()->getContents());
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
     * @param bool|null $admin
     * @return \Http\Promise\Promise        A promise of an array of models of an exception
     * @throws InvalidUserException Trigerred if their is no connected user
     */
    protected function getAll(string $uri, AbstractModel $model, ?bool $admin = false): \Http\Promise\Promise
    {

        return $this->get($uri, $model, 'ALL', $admin);
    }

    /**
     * Return one object from Flarum
     * @param string $uri The URI of the WS
     * @param AbstractModel $model he data model to insert, id must not be empty in order to updtate
     * @param bool|null $admin
     * @return \Http\Promise\Promise        A promise of a model of an exception
     * @throws InvalidUserException     Trigerred if their is no connected user
     */
    protected function getOne(string $uri, AbstractModel $model, ?bool $admin = false)
    {
        return $this->get($uri, $model, 'ONE', $admin);
    }


    /**
     * Retrieve a list or an objecy
     * @param string $uri The URI to request
     * @param AbstractModel $model The model to be used
     * @param string $mod ALL for a collection, ONE for an object
     * @param bool|null $admin Should the request been made as admin
     * @return \Http\Promise\Promise    A promise of a list, an object or an exception
     * @throws InvalidUserException     Trigerred if their is no user associated
     */
    private function get(string $uri, AbstractModel $model, string $mod, ?bool $admin = false): \Http\Promise\Promise
    {
        $objectName = $model->getModelName();
        $hydrator = $model->getHydrator();

        $token = null;
        if ($admin) {
            $token = $this->config->flarumAPIKey;
        }
        if ($token === null) {
            //Retrieve current user
            $token = $this->getToken()->token;
            if ($token === false) {
                throw new InvalidUserException('There is no currently defined user');
            }
        }
        // Instantiate an empty PSR-7 request
        $request = new Request('', '');

        // Instantiate the request builder
        $requestBuilder = new JsonApiRequestBuilder($request);

        //Create request
        $requestBuilder
            ->setProtocolVersion('1.1')
            ->setMethod('GET')
            ->setUri($uri)
            ->setHeader('Accept-Charset', 'utf-8')
            ->setHeader('cookie', 'flarum_remember=' . $token);


        if ($admin) {
            $requestBuilder->setHeader('Authorization', 'Token ' . $this->config->flarumAPIKey . '; userId=1');
        } else {
            $requestBuilder->setHeader('Authorization', 'Token ' . $token . '');
        }

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
     * @param bool|null $admin
     * @return \Http\Promise\Promise        A promise of a
     * @throws InvalidUserException Trigerred if their is no connected user
     */
    protected function delete(string $uri, AbstractModel $model, int $validResponseCode, ?bool $admin = false): \Http\Promise\Promise
    {
        $objectName = $model->getModelName();

        $token = null;
        if ($admin) {
            $token = $this->config->flarumAPIKey;
        }
        if ($token === null) {
            //Retrieve current user
            $token = $this->getToken()->token;
            if ($token === false) {
                throw new InvalidUserException('There is no currently defined user');
            }
        }
        // Instantiate an empty PSR-7 request
        $request = new Request('', '');

        // Instantiate the request builder
        $requestBuilder = new JsonApiRequestBuilder($request);

        //Create request
        $requestBuilder
            ->setProtocolVersion('1.1')
            ->setMethod('DELETE')
            ->setUri($uri)
            ->setHeader('Accept-Charset', 'utf-8')
            ->setHeader('cookie', 'flarum_remember=' . $token);

        if ($admin) {
            $requestBuilder->setHeader('Authorization', 'Token ' . $this->config->flarumAPIKey . '; userId=1');
        } else {
            $requestBuilder->setHeader('Authorization', 'Token ' . $token . '');
        }

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
                    $this->logger->error('Exception trigerred on ' . $objectName . ' delete ' . $e->getMessage()  . ' returned');
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
     * Securely get the token
     *
     * @return bool|FlarumToken
     */
    public function getToken()
    {
        if ($this->token === null || $this->token->userId === null || $this->token->token === null) {
            return false;
        }
        return $this->token;
    }


    /**
     * Set the currently connected user token
     *
     * @param FlarumToken $token The token of the currently connected user
     * @return void
     **/
    public function setToken(FlarumToken $token)
    {
        $this->token = $token;
    }
}
