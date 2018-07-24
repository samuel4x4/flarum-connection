<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 20/07/18
 * Time: 14:55
 */

namespace FlarumConnection\Serializers;


/**
 * Serializer for tags
 * Class FlarumTagsSerializer
 * @package FlarumConnection\Serializers
 */
class FlarumTokenSerializer extends AbstractSerializer
{
    /**
     * Get the body for the creation of a tag
     * @return array
     */
    public function getBodyInsert($token): array
    {

        return [];
    }

    /**
     * Get the body for the update of a tag
     * @return array
     */
    public function getBodyUpdate($token): array
    {

        return [];
    }
}