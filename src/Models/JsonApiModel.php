<?php
namespace FlarumConnection\Models;



/**
 * Base class for JSON API model
 */
abstract class JsonApiModel
{

    /**
     * Validate a json api document
     *
     * @param array $json  The json array to validate
     * @return bool     True if it's a valid json api document
     */
    protected static function validateJSONAPIDocument(array $json):bool{
        return isset($json['data']['id'], $json['data']['attributes']);
    }

    /**
     * Validate that all the fields are present
     *
     * @param array $json        The JSON array to validate
     * @param array $fields        The list of fields to look for
     * @return bool               The status of validation
     */
    protected static function validateRequiredFieldsFromDocument(array $json, array $fields):bool{
        if(!self::validateJSONAPIDocument($json)){
            return false;
        }
        foreach ($fields as $field){
            if(!isset($json['data']['attributes'][$field])){
                return false;
            }
        }
        return true;
    }

    /**
     * Extract an attribute
     *
     * @param array $json          The json array
     * @param string $field         The field to retrieve
     * @return  mixed               The value within the json array
     */
    protected static function extractAttributeFromDocument(array $json, string $field){
        if(isset($json['data']['attributes'][$field])) {
            return $json['data']['attributes'][$field];
        }
        return null;   
    }

    /**
     * Extract the type of the data 
     *
     * @param array $json         Json API data
     * @return string|null              Name of the type
     */
    protected static function extractTypeFromDocument(array $json):?string{
        if(isset($json['data'])){
            return $json['data']['type'];
        }
        return null;
    }


    /**
     * Extract the id 
     *
     * @param array $json         Json API data
     * @return int|null              Id of the item
     */
    protected static function extractIdFromDocument(array $json):?int{
        if(isset($json['data'])){
            return $json['data']['id'];
        }
        return null;
    }


    /**
     * Transform a json list in a list of document
     * @param array $json
     * @return array
     */
    protected static function extractDocumentsFromList(array $json):array{
        if(!isset($json['data'])){
            return null;
        }
        $list = $json['data'];
        $ret = [];
        foreach ($list as $data){
            $ret[] = [
                'data' => $data
            ];
        }
        return $ret;
    }

    /**
     * Parse a Flarum date
     *
     * @param  string $date        The date to parse
     * @return integer      The associated timestamp
     */
    protected static function parseDate(?string $date):int{
        if($date === null){
            return 0;
        }
        return strtotime($date);
    }


}