<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 19/07/18
 * Time: 15:00
 */

namespace FlarumConnection\Features;


use FlarumConnection\Exceptions\InvalidUserException;
use FlarumConnection\Hydrators\FlarumTagsHydrator;
use FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumPermissions;
use FlarumConnection\Models\FlarumTag;


use FlarumConnection\Serializers\FlarumTagsSerializer;

use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use Http\Promise\RejectedPromise;
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
     * Path for permissions
     */
    const PERMISSION_PATH = '/api/permission';

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
     * @param bool $admin Indicate if admin mode should be forced
     * @return \Http\Promise\Promise        The promoise of a TAG or an exception
     * @throws \FlarumConnection\Exceptions\InvalidUserException    Trigerred if no users are associated
     */
    public function addTag(string $name, string $slug, string $description, string $color, bool $isHidden, bool $isRestricted, bool $admin = false): \Http\Promise\Promise
    {
        $tag = new FlarumTag();
        $tag->init($name, $slug, $color, $description, $isHidden, $isRestricted);

        return $this->insert($this->config->flarumUrl . self::TAG_PATH, $tag, 201, $admin);

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
     * @param bool $admin Use admin mode or not
     * @return \Http\Promise\Promise        A promise of a tag or of an exception
     * @throws \FlarumConnection\Exceptions\InvalidUserException    An exception is trigerred if no user is associated
     */
    public function updateTag(string $name, string $slug, string $description, string $color, bool $isHidden, bool $isRestricted, int $id, bool $admin = false): \Http\Promise\Promise
    {
        $tag = new FlarumTag();
        $tag->init($name, $slug, $color, $description, $isHidden, $isRestricted, $id);


        return $this->update($this->config->flarumUrl . self::TAG_PATH . '/' . $id, $tag, 200, $admin);
    }

    /**
     * Return the list of tags
     * @param bool $admin
     * @return \Http\Promise\Promise
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     */
    public function getTags(bool $admin = false): \Http\Promise\Promise
    {
        return $this->getAll($this->config->flarumUrl . self::TAG_PATH, new FlarumTag(), $admin);
    }

    /**
     * Delete a tag
     * @param int $id The id of the tag to delete
     * @param bool $admin Use admin mode or not
     * @return string
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     */
    public function deleteTag(int $id, bool $admin = false): \Http\Promise\Promise
    {
        return $this->delete(
            $this->config->flarumUrl . self::TAG_PATH . '/' . $id,
            new FlarumTag(),
            204,
            $admin);

    }



    /**
     * Set read only permission
     * @param int $tagId
     * @param FlarumPermissions $permissions
     * @param bool $admin
     * @return bool
     * @throws \Throwable
     */
    public function setTagPermission(int $tagId, FlarumPermissions $permissions, bool $admin = false)
    {
        $rights = $permissions->getPermissionSetup();
        $promisesModerate = [];

        $promiseList = [];

        foreach ($rights['VIEW'] as $key => $right) {
            $rightCopy = $rights['VIEW'][$key];
            $promiseList[] = $this->setPermission($tagId, $rightCopy['groups'], $rightCopy['permission'], $admin);

        }

        foreach ($rights['RESPOND'] as $key => $right) {
            $rightCopy = $rights['RESPOND'][$key];
            $promiseList[] = $this->setPermission($tagId, $rightCopy['groups'], $rightCopy['permission'], $admin);

        }

        foreach ($rights['CREATE'] as $key => $right) {
            $rightCopy = $rights['CREATE'][$key];
            $promiseList[] = $this->setPermission($tagId, $rightCopy['groups'], $rightCopy['permission'], $admin);

        }

        foreach ($rights['MODERATE'] as $key => $right) {
            $rightCopy = $rights['MODERATE'][$key];
            $promiseList[] = $this->setPermission($tagId, $rightCopy['groups'], $rightCopy['permission'], $admin);
        }

        Promise\unwrap($promiseList);
        return true;


    }


    /**
     * Set permission on a tag for a group
     * @param int $tagId
     * @param array $groups
     * @param bool $admin
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function setPermission(int $tagId, array $groups, string $permission, bool $admin = false): \GuzzleHttp\Promise\PromiseInterface
    {

        $tagSerializer = new FlarumTagsSerializer();
        $body = json_encode($tagSerializer->getBodyPermission($tagId, $groups, $permission));
        $headers = [
            'Content-Type:' => 'application/json',
            'Authorization' => 'Token ' . $this->config->flarumAPIKey . '; userId=1',
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

}