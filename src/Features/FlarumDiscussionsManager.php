<?php

namespace FlarumConnection\Features;

use FlarumConnection\Exceptions\InvalidDiscussionCreationException;
use FlarumConnection\Exceptions\InvalidDiscussionException;
use FlarumConnection\Exceptions\InvalidDiscussionUpdateException;
use FlarumConnection\Exceptions\InvalidUserException;
use FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumDiscussion;
use \GuzzleHttp\Psr7\Request;
use \Psr\Log\LoggerInterface;

/**
 * Handle topics & posts features of Flarum
 */
class FlarumDiscussionsManager extends AbstractFeature
{

    /**
     * Path for Discussions
     */
    const DISCUSSIONS_PATH = '/api/discussions';

    /**
     * Initialize the feature with the config
     *
     * @param FlarumConnectorConfig $config Configuration for flarum connector
     * @param LoggerInterface $logger Logger interface
     */
    public function __construct(FlarumConnectorConfig $config, LoggerInterface $logger)
    {
        $this->init($config, $logger);
    }

    /**
     * Post a new topic
     *
     * @param string $title Title of the topic
     * @param string $content Content of the topic
     * @param array $tags Tags associated (array of int)
     * @return \GuzzleHttp\Promise\promiseinterface     A promise of an exception or of a topic
     * @throws InvalidUserException                 When the user is not logged in
     */
    public function postTopic(string $title, string $content, array $tags): \GuzzleHttp\Promise\promiseinterface
    {

        $token = $this->getToken();
        if ($token === false) {
            throw new InvalidUserException('There is no currently defined user');
        }


        $disc = new FlarumDiscussion($title, $content, $tags);
        $body = json_encode((object)$disc->getCreateDiscussionBody());

        $headers = [
            'Content-Type:' => 'application/json',
            'cookie' => 'flarum_remember=' . $token->token,
            'Authorization' => 'Token ' . $token->token . ';',
            'Content-Length' => strlen($body),
        ];

        $request = new Request('POST', $this->config->flarumUrl . self::DISCUSSIONS_PATH, $headers, $body);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response $res) {
                if ($res->getStatusCode() === 201) {
                    try {

                        $content = json_decode($res->getBody(), true);
                        return FlarumDiscussion::fromJSON($content);
                    } catch (\Exception $e) {
                        return new InvalidDiscussionException($e->getMessage());
                    }
                }
                $this->logger->debug('Exception trigerred on discussion creation ' . $res->getStatusCode() . ' returned');
                return new InvalidDiscussionCreationException('Error during discussion creation');

            },
            function (\Exception $e) {
                $this->logger->debug('Exception trigerred on discussion creation' . $e->getMessage());
                return new InvalidDiscussionCreationException('Exception trigerred on discussion creation');
            }
        );

    }

    /**
     * Update a topic
     * @param int $id The id of the topic to update
     * @param string $title The title of the topic
     * @param string $content The content of the topic
     * @param array $tags Tags to set
     * @return \GuzzleHttp\Promise\promiseinterface     The discussion object
     * @throws InvalidUserException     Trigerred if the user is not connected
     */
    public function updateTopic(int $id, string $title, string $content, array $tags): \GuzzleHttp\Promise\promiseinterface
    {
        $token = $this->getToken();
        if ($token === false) {
            throw new InvalidUserException('There is no currently defined user');
        }

        $disc = new FlarumDiscussion($title, $content, $tags,$id);
        $body = json_encode((object)$disc->getUpdateDiscussionBody());


        $headers = [
            'Content-Type:' => 'application/json',
            'cookie' => 'flarum_remember=' . $token->token,
            'Authorization' => 'Token ' . $token->token . ';',
            'Content-Length' => strlen($body),
        ];

        $disc = new FlarumDiscussion($title, $content, $tags);
        $body = json_encode((object)$disc->getCreateDiscussionBody());
        $request = new Request('PATCH', $this->config->flarumUrl . self::DISCUSSIONS_PATH . '/' . $id, $headers, $body);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response $res) {
                if ($res->getStatusCode() === 200) {
                    try {

                        $content = json_decode($res->getBody(), true);
                        return FlarumDiscussion::fromJSON($content);
                    } catch (\Exception $e) {
                        return new InvalidDiscussionException($e->getMessage());
                    }
                }
                $this->logger->debug('Exception trigerred on discussion update ' . $res->getStatusCode() . ' returned');
                return new InvalidDiscussionUpdateException('Error during discussion update');

            },
            function (\Exception $e) {
                $this->logger->debug('Exception trigerred on discussion creation' . $e->getMessage());
                return new InvalidDiscussionUpdateException('Exception trigerred on discussion update');
            }
        );
    }

}
