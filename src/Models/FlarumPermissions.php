<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 24/07/18
 * Time: 15:06
 */

namespace FlarumConnection\Models;


class FlarumPermissions
{
    const READ_RIGHTS = ['viewDiscussions'];

    const CREATE_RIGHTS = ['startDiscussion'];

    const RESPOND_RIGHTS = ['discussion.reply',
        'discussion.replyWithoutApproval',
        'discussion.likePosts'
    ];

    const MODERATE_RIGHTS = ['discussion.rename',
        'discussion.lock',
        'discussion.sticky',
        'discussion.tag',
        'discussion.hide',
        'discussion.delete',
        'discussion.editPosts',
        'discussion.viewFlags',
        'discussion.flagPosts'
    ];

    /**
     * List of the groups that are allowed to read
     * @var     array
     */
    private $allowedRead = [];

    /**
     * List of the groups that are allowed to create new topic
     * @var     array
     */
    private $allowedCreate =[];

    /**
     * List of the groups that are allowed to respond to topics
     * @var     array
     */
    private $allowedRespond = [];

    /**
     * List of the groups that are allowed to moderate
     * @var     array
     */
    private $allowedModerate = [];


    /**
     * Set the groups for read
     * @param array $groups
     */
    public function setRead(array $groups){
        $this->allowedRead = $groups;
    }

    /**
     * Set the groups for create
     * @param array $groups
     */
    public function setCreate(array $groups){
        $this->allowedCreate = $groups;
    }

    /**
     * Set the groups to respond
     * @param array $groups
     */
    public function setRespond(array $groups){
        $this->allowedRespond = $groups;
    }
    /**
     * Set the groups to moderate
     * @param array $groups
     */
    public function setModerate(array $groups){
        $this->allowedModerate = $groups;
    }

    /**
     * Get the permission setup for a tag
     * @return array
     */
    public function getPermissionSetup(){
        $output = [];
        $readGroups = array_merge($this->allowedCreate,$this->allowedRespond,$this->allowedModerate,$this->allowedRead);
        $output['VIEW'] =$this->setOutputForCategory(self::READ_RIGHTS,$readGroups);

        $respondGroups = array_merge($this->allowedCreate,$this->allowedRespond,$this->allowedModerate);
        $output['RESPOND'] = $this->setOutputForCategory(self::RESPOND_RIGHTS,$respondGroups);

        $createGroups = array_merge($this->allowedCreate,$this->allowedModerate);
        $output['CREATE'] = $this->setOutputForCategory(self::CREATE_RIGHTS,$createGroups);

        $moderateGroups = $this->allowedModerate;
        $output['MODERATE'] = $this->setOutputForCategory(self::MODERATE_RIGHTS,$moderateGroups);

        return $output;

    }

    /**
     * Set the output for a category
     * @param array $arrayPermissions List of permission
     * @param array $groups           Groupŝ to associate
     * @return array            The associated array returned
     */
    private function setOutputForCategory(array $arrayPermissions,array $groups){
        $ret = [];
        foreach($arrayPermissions as $permission){
            $el = [];
            $el['permission'] = $permission;
            $el['groups'] = $groups;
            $ret[] = $el;
        }
        return $ret;
    }

}