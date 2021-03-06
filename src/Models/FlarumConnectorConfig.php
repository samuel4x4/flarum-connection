<?php
namespace FlarumConnection\Models;
/**
 * Configuration class for service
 */
class FlarumConnectorConfig{

    /**
     * Url of the Flarum forum
     *
     * @var string
     */
    public $flarumUrl;

    /**
     * Url of the Page where will be located the iframe containing the forum
     *
     * @var string
     */
    public $iframeFlarumUrl;

    /**
     * Url of the login form
     *
     * @var string
     */
    public $loginUrl;

    /**
     * Root domain used (for the cookies)
     *
     * @var string
     */
    public $rootDomain;

    /**
     * API key of Flarum api for administrative tasks
     *
     * @var string
     */
    public $flarumAPIKey;

    /**
     * Life lenght of the cookie set
     *
     * @var int
     */
    public $flarumLifeTime;

    /**
     * Default user for Flarum
     * @var int
     */
    public $flarumDefaultUser;


    /**
     * Initialize the config
     *
     * @param string $flarumUrl Url of the Flarum forum
     * @param string $rootDomain Root domain used (for the cookies)
     * @param string $flarumAPIKey API key of Flarum api for administrative tasks
     * @param int $flarumDefaultUser    The default admin user for Flarum
     * @param int $flarumLifeTime Life lenght of the cookie set
     */
    public function __construct(string $flarumUrl,string $rootDomain,string $flarumAPIKey,int $flarumDefaultUser, int $flarumLifeTime){
        $this->flarumUrl = $flarumUrl;
        $this->flarumDefaultUser = $flarumDefaultUser;
        $this->rootDomain =$rootDomain;
        $this->flarumAPIKey = $flarumAPIKey;
        $this->flarumLifeTime = $flarumLifeTime;
    }

}
