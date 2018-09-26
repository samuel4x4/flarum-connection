<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 19/07/18
 * Time: 15:00
 */

namespace FlarumConnection\Features;


use FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumPermissions;
use FlarumConnection\Models\FlarumTag;


use FlarumConnection\Models\FlarumTagOrder;
use FlarumConnection\Serializers\FlarumTagsSerializer;

use GuzzleHttp\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;

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
     public const TAG_PATH = '/api/tags';

    /**
     * Path for permissions
     */
     public const PERMISSION_PATH = '/api/permission';

    /**
     * Set the order for tags
     */
     public const ORDER_PATH = '/api/tags/order';

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
     * @param string $name
     * @param string $slug
     * @param string $description
     * @param string $color
     * @param bool $isHidden
     * @param bool $isRestricted
     * @param int|null $user
     * @return FlarumTag|mixed|null
     * @throws \Exception
     */
    public function getOrAddTagByName(string $name, string $slug, string $description, string $color = '#888', bool $isHidden = false, bool $isRestricted = false, ?int $user = null): FlarumTag
    {
        return $this->getTagByName($name) ?? $this->addTag($name, $slug, $description, $color, $isHidden, $isRestricted, $user)->wait();
    }

    /**
     * @param string $name
     * @param string $slug
     * @param string $description
     * @param string $color
     * @param bool $isHidden
     * @param bool $isRestricted
     * @param int|null $user
     * @return FlarumTag|mixed|null
     * @throws \Exception
     */
    public function getOrAddParentTagByName(string $name, string $slug, string $description, string $color = '#888', bool $isHidden = false, bool $isRestricted = false, ?int $user = null): FlarumTag
    {
        $tag = $this->getOrAddTagByName($name, $slug, $description, $color, $isHidden, $isRestricted, $user);

        /** @var FlarumTagOrder $tagOrder */
        $tagOrder = $this->getTagOrder($user)->wait();
        $tagOrder->addParentToEnd($tag->tagId);
        $this->setTagOrder($tagOrder, $user)->wait();

        return $tag;
    }

    /**
     * @param string $name
     * @param int|null $user
     * @return FlarumTag|null
     * @throws \Exception
     */
    public function getTagByName(string $name, ?int $user = null): ?FlarumTag
    {
        /** @var FlarumTag[] $tags */
        $tags = $this->getTags($user)->wait();

        foreach ($tags as $tag) {
            if ($tag->name == $name) {
                return $tag;
            }
        }

        return null;
    }

    /**
     * Add a new tag
     * @param string $name Name of the tag
     * @param string $slug Name of the slug (must be unique)
     * @param string $description Description of the category
     * @param string $color Color of the category
     * @param bool $isHidden Should the tag be hidden from the general feed
     * @param bool $isRestricted Should the tag be restricted
     * @param int|null $user
     * @return \Http\Promise\Promise        The promoise of a TAG or an exception
     */
    public function addTag(string $name, string $slug, string $description, string $color, bool $isHidden, bool $isRestricted, ?int $user = null): \Http\Promise\Promise
    {
        $tag = new FlarumTag();
        $tag->init($name, $slug, $color, $description, $isHidden, $isRestricted);

        return $this->insert($this->config->flarumUrl . self::TAG_PATH, $tag, 201, $user);

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
     * @param int|null $user
     * @return \Http\Promise\Promise        A promise of a tag or of an exception
     */
    public function updateTag(string $name, string $slug, string $description, string $color, bool $isHidden, bool $isRestricted, int $id, ?int $user = null): \Http\Promise\Promise
    {
        $tag = new FlarumTag();
        $tag->init($name, $slug, $color, $description, $isHidden, $isRestricted, $id);


        return $this->update($this->config->flarumUrl . self::TAG_PATH . '/' . $id, $tag, 200, $user);
    }

    /**
     * Return the list of tags
     * @param int|null $user
     * @return \Http\Promise\Promise
     */
    public function getTags(?int $user = null): \Http\Promise\Promise
    {
        return $this->getAll($this->config->flarumUrl . self::TAG_PATH, new FlarumTag(), $user);
    }

    /**
     * Delete a tag
     * @param int $id The id of the tag to delete
     * @param int|null $user            The user to use to call the API
     * @return \Http\Promise\Promise    True or false
     */
    public function deleteTag(int $id, ?int $user = null): \Http\Promise\Promise
    {
        return $this->delete(
            $this->config->flarumUrl . self::TAG_PATH . '/' . $id,
            new FlarumTag(),
            204,
            $user);

    }


    /**
     * Set read only permission
     * @param int $tagId
     * @param FlarumPermissions $permissions
     * @param int|null $user    The user which will be used to call the API
     * @return bool     True if the operation was a success
     * @throws \Throwable
     */
    public function setTagPermission(int $tagId, FlarumPermissions $permissions, ?int $user = null): bool
    {
        $rights = $permissions->getPermissionSetup();
        $promiseList = [];

        foreach ($rights['VIEW'] as $key => $right) {
            $rightCopy = $rights['VIEW'][$key];
            $promiseList[] = $this->setPermission($tagId, $rightCopy['groups'], $rightCopy['permission'], $user);

        }

        foreach ($rights['RESPOND'] as $key => $right) {
            $rightCopy = $rights['RESPOND'][$key];
            $promiseList[] = $this->setPermission($tagId, $rightCopy['groups'], $rightCopy['permission'], $user);

        }

        foreach ($rights['CREATE'] as $key => $right) {
            $rightCopy = $rights['CREATE'][$key];
            $promiseList[] = $this->setPermission($tagId, $rightCopy['groups'], $rightCopy['permission'], $user);

        }

        foreach ($rights['MODERATE'] as $key => $right) {
            $rightCopy = $rights['MODERATE'][$key];
            $promiseList[] = $this->setPermission($tagId, $rightCopy['groups'], $rightCopy['permission'], $user);
        }

        Promise\unwrap($promiseList);
        return true;


    }


    /**
     * Set permission on a tag for a group
     * @param int $tagId
     * @param array $groups
     * @param string $permission
     * @param int|null $user
     * @return PromiseInterface
     */
    public function setPermission(int $tagId, array $groups, string $permission, ?int $user = null): PromiseInterface
    {

        $tagSerializer = new FlarumTagsSerializer();
        $body = json_encode($tagSerializer->getBodyPermission($tagId, $groups, $permission));
        if($user === null){
            $user = $this->config->flarumDefaultUser;
        }
        $headers = [
            'Content-Type:' => 'application/json',
            'Authorization' => 'Token ' . $this->config->flarumAPIKey . '; userId='.$user,
            'Content-Length' => strlen($body)
        ];
        $request = new Request('POST', $this->config->flarumUrl . self::PERMISSION_PATH, $headers, $body);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response $res) use($permission) {
                if($res->getStatusCode() !== 204){
                    $this->logger->error('Exception setting permission'.$permission);
                    return false;
                }
                return true;
            }, function (\Exception $e) {
            $this->logger->error('Exception trigerred on permission set' . $e->getMessage());
            return $e;
        });
    }

    /**
     * Return the tag order
     * @param int|null $user
     * @return \Http\Promise\Promise    A promise of FlarumTagOrder
     */
    public function getTagOrder(?int $user = null): \Http\Promise\Promise
    {
        return $this->getAll($this->config->flarumUrl . self::TAG_PATH, new FlarumTag(), $user)->then(
            function(array $tags){
               return new FlarumTagOrder($tags);
            },
            function(\Exception $e){
                $this->logger->error('Exception trigerred on get tag order' . $e->getMessage());
            }
        );
    }

    /**
     * Set the tag order
     * @param FlarumTagOrder $order The new order to set
     * @param int|null $user
     * @return PromiseInterface
     */
    public function setTagOrder(FlarumTagOrder $order,?int $user = null): PromiseInterface
    {
        $body_array = $order->toOrderArray();
        $body = json_encode($body_array);

        if($user === null){
            $user = $this->config->flarumDefaultUser;
        }
        $headers = [
            'Content-Type:' => 'application/json',
            'Authorization' => 'Token ' . $this->config->flarumAPIKey . '; userId='.$user ,
            'Content-Length' => strlen($body)
        ];

        $request = new Request('POST', $this->config->flarumUrl . self::ORDER_PATH, $headers, $body);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response $res)  {
                if($res->getStatusCode() !== 204){
                    $this->logger->error('Invalid return setting order :'.$res->getStatusCode());
                    return false;
                }
                return true;
            }, function (\Exception $e) {
            $this->logger->error('Exception trigerred on order set' . $e->getMessage());
            return $e;
        });
    }



}