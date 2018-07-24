<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 23/07/18
 * Time: 13:50
 */

namespace FlarumConnection\Models;


use FlarumConnection\Hydrators\AbstractHydrator;
use FlarumConnection\Hydrators\FlarumGroupsHydrator;
use FlarumConnection\Serializers\AbstractSerializer;
use FlarumConnection\Serializers\FlarumGroupsSerializer;

class FlarumGroup extends AbstractModel
{
    /**
     * Id of the group
     * @var int
     */
    public $groupId;

    /**
     * Name of the group at the singular (ex : Admin)
     * @var string
     */
    public $nameSingular;

    /**
     * Name of the group at the plural (ex : Admins)
     * @var string
     */
    public $namePlural;

    /**
     * Name of the icon
     * @var    string
     */
    public $color;

    /**
     * Name of the icon
     * @var    string
     */
    public $icon;

    /** Initialize a group
     * @param string $nameSingular      The name of the group at the singular
     * @param string $namePlural        The name of the group at the plural
     */
    public function init(string $nameSingular,string $namePlural){
        $this->namePlural = $namePlural;
        $this->nameSingular = $nameSingular;
    }

    /**
     * Return the model name
     * @return string
     */
    public function getModelName(): string
    {
        return 'groups';
    }

    /**
     * Retrieve the Serializer of the object
     * @return AbstractSerializer
     */
    public function getSerializer(): AbstractSerializer
    {
        return new FlarumGroupsSerializer();
    }

    /**
     * Retrieve the hydrator of the object
     * @return AbstractHydrator
     */
    public function getHydrator(): AbstractHydrator
    {
        return new FlarumGroupsHydrator();
    }
}