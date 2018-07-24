<?php
namespace FlarumConnection\Models;
use FlarumConnection\Exceptions\InvalidTagException;
use FlarumConnection\Hydrators\AbstractHydrator;
use FlarumConnection\Hydrators\FlarumTagsHydrator;
use FlarumConnection\Serializers\AbstractSerializer;
use FlarumConnection\Serializers\FlarumTagsSerializer;


/**
 * Model class for Flarum tags
 */
class FlarumTag extends AbstractModel
{

    /**
     * Name of the tag
     *
     * @var string
     */
    public $name;

    /**
     * Slug of the tag
     *
     * @var string
     */
    public $slug;

    /**
     * Hexa code associated with the tag
     *
     * @var string
     */
    public $color;

    /**
     * Description of the tags
     *
     * @var string
     */
    public $description;

    /**
     * Is the tag visible
     *
     * @var bool
     */
    public $isHidden;

    /**
     * Has the tag specific rights
     * @var bool
     */
    public $isRestricted;

    /**
     * The id of the tag
     * @var int
     */
    public $id;

    /**
     * Numbers of discussions
     * @var int
     */
    private $discussions_count;

    /**
     * Position of the tag
     * @var int
     */
    private $position;



    /**
     * Initialize the tag
     * @param string $name      Name of the tag
     * @param string $slug      Slug of the tag (url) must be unique
     * @param string $color     Color associated with the
     * @param string|null $description   Description of the tag
     * @param bool $isHidden        Is the tag hidden in all discussions
     * @param bool $isrestricted    Is the tag restricted with specific rights
     * @param int|null $id               The id of the tag
     */
    public function init(string $name, string $slug, string $color, ?string $description, bool $isHidden, bool $isRestricted, ?int $id = null){
        $this->name = $name;
        $this->slug = $slug;
        $this->color = $color;
        if($description !== null){
            $this->description = $description;
        }
        $this->isHidden = $isHidden;
        $this->isRestricted = $isRestricted;
        $this->id = $id;
    }

    /**
     * Return the name of the model
     * @return string
     */
    public function getModelName():string{
        return 'Tag';
    }

    /**
     * Retrieve the Serializer of the object
     * @return AbstractSerializer
     */
    public function getSerializer(): AbstractSerializer
    {
        return new FlarumTagsSerializer();
    }

    /**
     * Retrieve the hydrator of the object
     * @return AbstractHydrator
     */
    public function getHydrator(): AbstractHydrator
    {
        return new FlarumTagsHydrator();
    }




}
