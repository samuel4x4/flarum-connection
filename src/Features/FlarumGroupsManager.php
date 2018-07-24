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
    const GROUP_PATH = '/api/groups';

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
     * Add a new group
     * @param string $nameSingular      Singular name of the group (ex : Admin)
     * @param string $namePlural         Plural name of the group (ex : Admin)s
     * @param string $color             Color of the group
     * @param string $icon              Fontawesome icon name
     * @param bool $admin Indicate if admin mode should be forced
     * @return \Http\Promise\Promise        The promoise of a TAG or an exception
     * @throws \FlarumConnection\Exceptions\InvalidUserException Trigerred if no users are associated
     */
    public function addGroup(string $nameSingular, string $namePlural, string $color, string $icon, bool $admin = false): \Http\Promise\Promise
    {
        $group = new FlarumGroup();
        $group->init($nameSingular, $namePlural);
        $group->color = $color;
        $group->icon = $icon;

        return $this->insert($this->config->flarumUrl . self::GROUP_PATH, $group, 201, $admin);

    }

    /**
     * Update a group
     * @param string $nameSingular      Singular name of the group (ex : Admin)
     * @param string $namePlural         Plural name of the group (ex : Admin)s
     * @param string $color             Color of the group
     * @param string $icon              Fontawesome icon name
     * @param int $id The id of the tag
     * @param bool $admin Use admin mode or not
     * @return \Http\Promise\Promise        A promise of a tag or of an exception
     * @throws \FlarumConnection\Exceptions\InvalidUserException An exception is trigerred if no user is associated
     */
    public function updateGroup(string $nameSingular, string $namePlural, string $color, string $icon, int $id, bool $admin = false): \Http\Promise\Promise
    {
        $group = new FlarumGroup();
        $group->init($nameSingular, $namePlural);
        $group->color = $color;
        $group->icon = $icon;


        return $this->update($this->config->flarumUrl . self::GROUP_PATH . '/' . $id, $group, 200, $admin);
    }

    /**
     * Return the list of groups
     * @param bool $admin                   Use the current user or use admin
     * @return \Http\Promise\Promise        A list of group
     * @throws \FlarumConnection\Exceptions\InvalidUserException If no users are associated
     */
    public function getGroups(bool $admin = false): \Http\Promise\Promise
    {
        return $this->getAll($this->config->flarumUrl . self::GROUP_PATH, new FlarumGroup(), $admin);
    }

    /**
     * Delete a tag
     * @param int $id           The id of the tag to delete
     * @param bool $admin       Use admin mode or not
     * @return string
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     */
    public function deleteGroup(int $id, bool $admin = false):\Http\Promise\Promise{
        return $this->delete(
            $this->config->flarumUrl . self::GROUP_PATH.'/'.$id,
        new FlarumTag(),
        204,
        $admin);

    }

}