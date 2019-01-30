<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 19/07/18
 * Time: 15:00
 */

namespace FlarumConnection\Features;

use FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumGroup;
use FlarumConnection\Models\FlarumTag;

use Psr\Log\LoggerInterface;


/**
 * Handle tgroups  management
 * @package FlarumConnection\Features
 */
class FlarumGroupsManager extends AbstractFeature
{
    /**
     * Path for Discussions
     */
    public const GROUP_PATH = '/api/groups';

    /**
     * FlarumGroupsManager constructor.
     * @param FlarumConnectorConfig $config The configuration of the lib
     * @param LoggerInterface $logger The logger
     */
    public function __construct(FlarumConnectorConfig $config, LoggerInterface $logger)
    {
        $this->init($config, $logger);
    }

    /**
     * Get the  user
     * @param int|null $groupId The id of the group
     * @param int|null $user
     * @return \Http\Promise\Promise
     */
    public function getGroup(int $groupId = null, ?int $user = null): \Http\Promise\Promise
    {
        return $this->getOne($this->config->flarumUrl . self::GROUP_PATH . '/' . $groupId, new FlarumGroup(), $user);
    }


    /**
     * Add a new group
     * @param string $nameSingular Singular name of the group (ex : Admin)
     * @param string $namePlural Plural name of the group (ex : Admin)s
     * @param string $color Color of the group
     * @param string $icon Fontawesome icon name
     * @param int|null $user
     * @return \Http\Promise\Promise        The promoise of a TAG or an exception
     */
    public function addGroup(string $nameSingular, string $namePlural, string $color, string $icon, ?int $user = null): \Http\Promise\Promise
    {
        $group = new FlarumGroup();
        $group->init($nameSingular, $namePlural);
        $group->color = $color;
        $group->icon = $icon;

        return $this->insert($this->config->flarumUrl . self::GROUP_PATH, $group, 201, $user);

    }

    /**
     * Update a group
     * @param string $nameSingular Singular name of the group (ex : Admin)
     * @param string $namePlural Plural name of the group (ex : Admin)s
     * @param string $color Color of the group
     * @param string $icon Fontawesome icon name
     * @param int $id The id of the tag
     * @param int|null $user The id of the user that will call the webservice
     * @return \Http\Promise\Promise        A promise of a tag or of an exception
     */
    public function updateGroup(string $nameSingular, string $namePlural, string $color, string $icon, int $id, int $user = null): \Http\Promise\Promise
    {
        $group = new FlarumGroup();
        $group->init($nameSingular, $namePlural);
        $group->color = $color;
        $group->icon = $icon;


        return $this->update($this->config->flarumUrl . self::GROUP_PATH . '/' . $id, $group, 200, $user);
    }

    /**
     * Return the list of groups
     * @param int|null $user
     * @return \Http\Promise\Promise        A list of group
     */
    public function getGroups(?int $user = null): \Http\Promise\Promise
    {
        return $this->getAll($this->config->flarumUrl . self::GROUP_PATH, new FlarumGroup(), $user);
    }

    /**
     * Delete a tag
     * @param int $id The id of the tag to delete
     * @param int|null $user
     * @return \Http\Promise\Promise
     */
    public function deleteGroup(int $id, int $user = null): \Http\Promise\Promise
    {
        return $this->delete(
            $this->config->flarumUrl . self::GROUP_PATH . '/' . $id,
            new FlarumTag(),
            204,
            $user);

    }

}