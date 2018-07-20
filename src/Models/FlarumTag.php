<?php
namespace FlarumConnection\Models;
use FlarumConnection\Exceptions\InvalidTagException;


/**
 * Model class for Flarum tags
 */
class FlarumTag extends JsonApiModel
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
     * Initialize the tag
     * @param string $name      Name of the tag
     * @param string $slug      Slug of the tag (url) must be unique
     * @param string $color     Color associated with the
     * @param string|null $description   Description of the tag
     * @param bool $isHidden        Is the tag hidden in all discussions
     * @param bool $isrestricted    Is the tag restricted with specific rights
     * @param int|null $id               The id of the tag
     */
    public function __construct(string $name, string $slug, string $color, ?string $description, bool $isHidden, bool $isRestricted, ?int $id = null)
    {
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
     * Get the body for the creation of a tag
     * @return array
     */
    public function getTagCreationBody(): array
    {
        return [
            'data' => [
                'type' => 'tags',
                'attributes' => [
                    'name' => $this->name,
                    'slug' =>$this->slug,
                    'description' => $this->description,
                    'color' => $this->color,
                    'isHidden' => $this->isHidden,
                    'isRestricted' => $this->isRestricted

                ]

            ]
        ];
    }

    /**
     * Parse tag from JSON array
     * @param array $json The JSOn array
     * @return FlarumTag
     * @throws InvalidTagException
     */
    public static function fromJSON(array $json):FlarumTag{

        if (!self::validateRequiredFieldsFromDocument($json,['name','slug','color','isHidden']))
        {

            throw new InvalidTagException('Invalid json input for a tag model');
        }
        return new FlarumTag(self::extractAttributeFromDocument($json,'name'),self::extractAttributeFromDocument($json,'slug'),self::extractAttributeFromDocument($json,'color'),self::extractAttributeFromDocument($json,'description'),self::extractAttributeFromDocument($json,'isHidden'),false,self::extractIdFromDocument($json));

    }

    /**
     * Return a list of tags
     * @param array $json The json from the ws
     * @return array            An array of FlarumTag object
     */
    public static function fromJSONList(array $json):array{
        $ret = [];
        foreach($json['data'] as $el){
            $newformed = ['data' => $el];
            try{
                $ret[] =  self::fromJSON($newformed);
            } catch(InvalidTagException $e){
                // We ignore the exception right now
            }
        }
        return $ret;
    }

}
