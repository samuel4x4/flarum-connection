<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 19/07/18
 * Time: 15:00
 */

namespace FlarumConnection\Features;

use FlarumConnection\Exceptions\InvalidTagException;
use FlarumConnection\Exceptions\InvalidUserCreationException;
use FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumTag;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

/**
 * Handle tags management
 * @package FlarumConnection\Features
 */
class FlarumTagsManager extends AbstractFeature
{
    /**
     * Path for Discussions
     */
    const TAG_PATH = '/api/tags';

    /**
     * FlarumTagsManager constructor.
     * @param FlarumConnectorConfig $config The configuration of the lib
     * @param LoggerInterface $logger The logger
     */
    public function __construct(FlarumConnectorConfig $config, LoggerInterface $logger)
    {
        $this->init($config, $logger);
    }


    /**
     * Add a new tag
     * @param string $name Name of the tag
     * @param string $slug Name of the slug (must be unique)
     * @param string $description Description of the category
     * @param string $color Color of the category
     * @param bool $isHidden Should the tag be hidden from the general feed
     * @param bool $isRestricted Should the tag be restricted
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function addTag(string $name, string $slug, string $description, string $color, bool $isHidden, bool $isRestricted): \GuzzleHttp\Promise\promiseinterface
    {

        $tag = new FlarumTag($name, $slug, $color, $description, $isHidden, $isRestricted, null);

        $body = json_encode($tag->getTagCreationBody());
          $headers = [
              'Content-Type' => 'application/json',
              'Content-Length' => strlen($body),
              'Authorization' => 'Token ' . $this->config->flarumAPIKey . '; userId=1'
          ];


        $request = new Request('POST', $this->config->flarumUrl . self::TAG_PATH, $headers, $body);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (Response $res) {
                if ($res->getStatusCode() === 201) {
                    try {
                        $content = json_decode($res->getBody(), true);
                        $tag = FlarumTag::fromJSON($content);
                        $listTags = $this->getTags()->wait();
                        foreach($listTags as $tagF){
                            if($tagF->slug === $tag->slug){
                                $tag->id = $tagF->id;
                            }
                        }
                        return $tag;
                    } catch (\Exception $e) {
                        return new InvalidUserCreationException($e->getMessage());
                    }
                }

                $this->logger->debug('Invalid tag creation ' . $res->getStatusCode() . ' returned');
                return new InvalidUserCreationException('Invalid tag creation ' . $res->getStatusCode() . ' returned');

            },
            function (\Exception $e) {
                $this->logger->debug('Exception trigerred on tag creation' . $e->getMessage());
                return new InvalidUserCreationException($e->getMessage());
            }
        );
    }

    /**
     * Update a tag
     * @param string $name Name of the tag
     * @param string $slug Slug associated to the tag
     * @param string $description Description of the taf
     * @param string $color Color in which to display the taf
     * @param bool $isHidden Indicate if the tag needs to be hidden
     * @param bool $isRestricted Indicate if the tag is restricted
     * @param int $id The id of the tag
     * @return \GuzzleHttp\Promise\promiseinterface     A promise of a FlarumTag or an exception
     */
    public function UpdateTag(string $name, string $slug, string $description, string $color, bool $isHidden, bool $isRestricted, int $id) :\GuzzleHttp\Promise\promiseinterface{
        $tag = new FlarumTag($name, $slug, $color, $description, $isHidden, $isRestricted, $id);

        $body = json_encode($tag->getTagCreationBody());
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($body),
            'Authorization' => 'Token ' . $this->config->flarumAPIKey . '; userId=1'
        ];


        $request = new Request('PATCH', $this->config->flarumUrl . self::TAG_PATH.'/'.$id, $headers, $body);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (Response  $res) {
                if ($res->getStatusCode() === 201) {
                    try {
                        $content = json_decode($res->getBody(), true);
                        $tag = FlarumTag::fromJSON($content);
                        $listTags = $this->getTags()->wait();
                        foreach($listTags as $tagF){
                            if($tagF->slug === $tag->slug){
                                $tag->id = $tagF->id;
                            }
                        }
                        return $tag;
                    } catch (\Exception $e) {
                        return new InvalidUserCreationException($e->getMessage());
                    }
                }

                $this->logger->debug('Invalid tag creation ' . $res->getStatusCode() . ' returned');
                return new InvalidUserCreationException('Invalid tag creation ' . $res->getStatusCode() . ' returned');

            },
            function (\Exception $e) {
                $this->logger->debug('Exception trigerred on tag update' . $e->getMessage());
                return new InvalidUserCreationException($e->getMessage());
            }
        );
    }

    /**
     * Return the list of tags
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getTags(): \GuzzleHttp\Promise\promiseinterface{
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Token ' . $this->config->flarumAPIKey . '; userId=1'
        ];


        $request = new Request('GET', $this->config->flarumUrl . self::TAG_PATH, $headers);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (Response $res) {
                if ($res->getStatusCode() === 200) {
                    try {

                        $content = json_decode($res->getBody(), true);

                        return FlarumTag::fromJSONList($content);
                    } catch (\Exception $e) {
                        $this->logger->debug('Invalid get tags  ' . $e->getTraceAsString() . ' returned');
                        return new InvalidTagException($e->getMessage());
                    }
                }

                $this->logger->debug('Invalid get tags  ' . $res->getStatusCode() . ' returned');
                return new InvalidTagException('Invalid tag creation ' . $res->getStatusCode() . ' returned');

            },
            function (\Exception $e) {
                $this->logger->debug('Exception trigerred on tag creation' . $e->getMessage());
                return new InvalidTagException($e->getMessage());
            }
        );
    }

}