<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 25/07/18
 * Time: 09:01
 */

namespace FlarumConnection\Models;

/**
 * Model to handle the tag order
 * @package FlarumConnection\Models
 */
class FlarumTagOrder
{

    /**
     * @var FlarumTagOrderItem
     */
    private $tagOrder;

    /**
     * Init the order from the tag list
     * @param array $tagList The tag list to build the order
     */
    public function __construct(array $tagList)
    {
        $this->tagOrder = new FlarumTagOrderItem(0, true);

        //Add parent tags
        foreach ($tagList as $tag) {
            if ($tag instanceof FlarumTag && $tag->position !== null && $tag->parent === null) {
                $this->tagOrder->addChild(new FlarumTagOrderItem($tag->tagId, true), $tag->position);
            }
        }
        //Add child tags
        foreach ($tagList as $tag) {
            if ($tag instanceof FlarumTag && $tag->position !== null && $tag->parent !== null && $this->tagOrder->containsChild($tag->parent->tagId)) {
                $this->tagOrder->getChild($tag->parent->tagId)->addChild(new FlarumTagOrderItem($tag->tagId, false), $tag->position);
            }
        }
    }

    /**
     * Return the order array
     * @return array
     */
    public function toOrderArray(): array
    {
        return ['order' => $this->tagOrder->toOrderArray()];
    }

    /**
     * Add a parent tag to the end
     * @param int $tagId The id of the tag
     */
    public function addParentToEnd(int $tagId): void
    {
        $this->tagOrder->addChildToEnd(new FlarumTagOrderItem($tagId, true));
    }

    /**
     * Add a parent tag to the start
     * @param int $tagId The if of the tag
     */
    public function addParentToStart(int $tagId): void
    {
        $this->tagOrder->addChildToStart(new FlarumTagOrderItem($tagId, true));
    }

    /**
     * Add a parent tag to it's position
     * @param int $tagId The tag to add
     * @param int $position It's position
     */
    public function addParentToPosition(int $tagId, int $position): void
    {
        $this->tagOrder->addChildToPosition(new FlarumTagOrderItem($tagId, true), $position);
    }


    /**
     * Add a child to the end
     * @param int $tagId The id of the tag
     * @param int $parentId The parent id of the tag
     */
    public function addChildToEnd(int $tagId, int $parentId): void
    {
        $child = $this->tagOrder->getChild($parentId);
        if ($child !== null) {
            $child->addChildToEnd(new FlarumTagOrderItem($tagId, false));
        }

    }

    /**
     * Add a child to the start
     * @param int $tagId The id of the tag
     * @param int $parentId The parent id of the tag
     */
    public function addChildToStart(int $tagId, int $parentId): void
    {
        $child = $this->tagOrder->getChild($parentId);
        if ($child !== null) {
            $child->addChildToStart(new FlarumTagOrderItem($tagId, false));
        }
    }

    /**
     * Add a child to the a position
     * @param int $tagId The id of the tag
     * @param int $parentId The parent id of the tag
     * @param int $position The position where to add the child
     */
    public function addChildToPosition(int $tagId, int $parentId, int $position): void
    {
        $child = $this->tagOrder->getChild($parentId);
        if ($child !== null) {
            $child->addChildToPosition(new FlarumTagOrderItem($tagId, false), $position);
        }
    }

    /**
     * Remove a parent
     * @param int $tagId The id of the tag
     */
    public function removeParent(int $tagId): void
    {
        $this->tagOrder->removeChild($tagId);
    }

    /**
     * Remove a child
     * @param int $tagId The id of the tag
     * @param int $parentId The id of the parent
     */
    public function removeChild(int $tagId, int $parentId): void
    {
        $child = $this->tagOrder->getChild($parentId);
        if ($child !== null) {
            $child->removeChild($tagId);
        }

    }



}

/**
 * Handle the order of one tage
 * @package FlarumConnection\Models
 */
class FlarumTagOrderItem
{
    /**
     * The id of the tag
     * @var int
     */
    private $tagId;

    /**
     * The children list
     * @var array
     */
    private $children;

    /**
     * Children reference position
     * @var array
     */
    private $reference;

    /**
     * Indicate if the item is a child
     * @var bool
     */
    private $isParent;


    /**
     * FlarumTagOrderParent constructor.
     * @param int $tagId The id of the tag
     * @param bool $isParent Is the tag a parent
     */
    public function __construct(int $tagId, bool $isParent)
    {
        $this->tagId = $tagId;
        $this->children = [];
        $this->isParent = $isParent;
    }

    /** Add a child
     * @param FlarumTagOrderItem $child
     * @param int $position
     */
    public function addChild(FlarumTagOrderItem $child, int $position): void
    {
        $this->children[$position] = $child;
        $this->reference[$child->tagId] = $position;
    }

    /**
     * Get the position of a child
     * @param int $childId The id of the child
     * @return int      The position of the child or -1
     */
    public function getPosition(int $childId): int
    {
        if (array_key_exists($childId, $this->reference)) {
            return $this->reference[$childId];
        }
        return -1;
    }

    /**
     * Check if the child exist
     * @param int $childId The id of the child
     * @return bool         Does the child exist within the object
     */
    public function containsChild(int $childId): bool
    {
        return $this->getPosition($childId) !== -1;
    }

    /**
     * Return a child by id
     * @param int $childId The id of the child
     * @return FlarumTagOrderItem|null   The item associated
     */
    public function getChild(int $childId): ?FlarumTagOrderItem
    {
        $pos = $this->getPosition($childId);
        if ($pos !== -1) {
            return $this->children[$pos];
        }
        return null;
    }

    /**
     * Transform the object to order array
     * @return array The order array
     */
    public function toOrderArray(): array
    {
        $ret = [];
        foreach ($this->children as $position=>$child) {
            if ($child->isParent) {
                $ret[$position] = [
                    'id' => $child->tagId,
                    'children' => $child->toOrderArray()
                ];
            } else {
                $ret[$position] = $child->tagId;
            }
        }
        return $ret;
    }

    /**
     * Add a child to the end
     * @param FlarumTagOrderItem $child
     */
    public function addChildToEnd(FlarumTagOrderItem $child): void
    {
        $last_position = key(\array_slice($this->children, -1, 1, TRUE));
        $newPosition = $last_position + 1;
        $this->children[$newPosition] = $child;
        $this->reference[$child->tagId] = $newPosition;
    }

    /**
     * Add a child to start
     * @param FlarumTagOrderItem $child
     */
    public function addChildToStart(FlarumTagOrderItem $child): void
    {
        $newChildrenOrder = [];
        $newChildrenOrder[] = $child;
        $newReference = [];
        $newReference[$child->tagId] = 0;

        foreach ($this->children as $position => $childValue) {
            $newChildrenOrder[$position + 1] = $childValue;
            $newReference[$childValue->tagId] = $position + 1;
        }


        $this->reference = $newReference;
        $this->children = $newChildrenOrder;
    }

    /**
     * Add a child to a specific position (and reorder everything)
     * @param FlarumTagOrderItem $child The child to add
     * @param int $position The position to add
     */
    public function addChildToPosition(FlarumTagOrderItem $child, int $position): void
    {
        $newChildrenOrder = [];
        $newReference = [];
        $last_position = key(\array_slice($this->children, -1, 1, TRUE));
        if ($position > $last_position) {
            $newChildrenOrder[$position] = $child;
            $newReference[$child->tagId] = $position;
        } else {
            foreach ($this->children as $exPosition => $childValue) {
                if ($exPosition === $position) {
                    $newChildrenOrder[$exPosition] = $child;
                    $newReference[$child->tagId] = $exPosition;
                    $newChildrenOrder[$exPosition + 1] = $childValue;
                    $newReference[$child->tagId] = $exPosition + 1;

                } else if ($exPosition < $position) {
                    $newChildrenOrder[$exPosition] = $childValue;
                    $newReference[$childValue->tagId] = $exPosition;
                } else {
                    $newChildrenOrder[$exPosition + 1] = $childValue;
                    $newReference[$childValue->tagId] = $exPosition + 1;
                }
            }
        }

        $this->reference = $newReference;
        $this->children = $newChildrenOrder;
    }

    /**
     * Remove a child from the list
     * @param int $tagId The id of the child to add
     */
    public function removeChild(int $tagId): void
    {
        $position = $this->getPosition($tagId);
        if ($position !== -1) {
            unset($this->children[$position]);
            $newRef = [];
            foreach ($this->children as $position => $child) {
                $newRef[$child->tagId] = $position;
            }
            $this->reference = $newRef;
        }
    }
}