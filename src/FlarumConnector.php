<?php

namespace FlarumConnection;

use DateTime;
use FlarumConnection\Features\FlarumDiscussionsManager;
use FlarumConnection\Features\FlarumGroupsManager;
use FlarumConnection\Features\FlarumPostsManager;
use FlarumConnection\Features\FlarumTagsManager;

use Psr\Log\LoggerInterface;

use FlarumConnection\Models\FlarumConnectorConfig;

use FlarumConnection\Features\FlarumSSO;
use FlarumConnection\Features\FlarumUserManagement;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Connector class for Flarum
 */
class FlarumConnector
{

    /**
     * Name of the flarum cookie
     */
    public const FLARUM_COOKIE = 'flarum_remember';

    /**
     * Configuration for Flarum
     *
     * @var FlarumConnectorConfig
     */
    private $config;

    /**
     * Logger (PSR3 interface)
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Flarum SSO feature
     *
     * @var FlarumSSO
     */
    private $flarumSSO;

    /**
     * Flarum User management feature
     * @var FlarumUserManagement
     */
    private $flarumUserManagement;


    /**
     * Flarum discussion feature
     * @var FlarumDiscussionsManager
     */
    private $flarumDiscussionManagement;

    /**
     * Handle tags
     * @var FlarumTagsManager
     */
    private $flarumTagManagement;

    /**
     * Handle groups
     * @var FlarumGroupsManager
     */
    private $flarumGroupManagement;

    /**
     * Handle posts
     * @var FlarumPostsManager
     */
    private $flarumPostManagement;

    /**
     * Initialize the service
     *
     * @param FlarumConnectorConfig $config The configuration for the service
     * @param LoggerInterface $logger PSR-3 Logger interface
     */
    public function __construct(FlarumConnectorConfig $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->flarumSSO = new FlarumSSO($config, $logger);
        $this->flarumUserManagement = new FlarumUserManagement($config, $logger);
        $this->flarumDiscussionManagement = new FlarumDiscussionsManager($config, $logger);
        $this->flarumTagManagement = new FlarumTagsManager($config, $logger);
        $this->flarumGroupManagement = new FlarumGroupsManager($config, $logger);
        $this->flarumPostManagement = new FlarumPostsManager($config, $logger);

    }

    /**
     * Retrieve the SSO feature
     *
     * @return FlarumSSO    The initialized SSO feature
     */
    public function getSSO(): FlarumSSO
    {
        return $this->flarumSSO;
    }

    /**
     * Retrieve the User Management feature
     *
     * @return FlarumUserManagement   The user management feature
     */
    public function getUserManagement(): FlarumUserManagement
    {
        return $this->flarumUserManagement;
    }

    /**
     * Get the discussion management feature
     * @return FlarumDiscussionsManager  The discussion management feature
     */
    public function getDiscussionManagement(): FlarumDiscussionsManager
    {
        return $this->flarumDiscussionManagement;
    }

    /**
     * Get the tags feature
     * @return FlarumTagsManager  The discussion management feature
     */
    public function getTagsManagement(): FlarumTagsManager
    {
        return $this->flarumTagManagement;
    }

    /**
     * Get the groups feature
     * @return FlarumGroupsManager  The groups feature
     */
    public function getGroupsManagement(): FlarumGroupsManager
    {
        return $this->flarumGroupManagement;
    }

    /**
     * Get the posts feature
     * @return FlarumPostsManager  The groups feature
     */
    public function getPostsManagement(): FlarumPostsManager
    {
        return $this->flarumPostManagement;
    }


    /**
     * Return the configuration
     *
     * @return FlarumConnectorConfig    Return the current config
     */
    public function getConfig(): FlarumConnectorConfig
    {
        return $this->config;
    }


    /**
     * Realize a login operation asynchronously
     *
     * @param string $login
     * @param string $password
     * @return \GuzzleHttp\Promise\promiseinterface               A promise of an associative array containing a http foundation cookie (with key cookie) & a Flarum token (with key token)
     */
    public function login(string $login, string $password): \GuzzleHttp\Promise\promiseinterface
    {
        $res = $this->flarumSSO->login($login, $password);
        return $res->then(function ($res) {

            $now = new DateTime();
            $newDate = $now->add(new \DateInterval('P' . $this->config->flarumLifeTime . 'd'));
            $cookie = new Cookie(
                self::FLARUM_COOKIE,
                $res->token,
                $newDate,
                '/',
                $this->config->rootDomain
            );
            return [
                'cookie' => $cookie,
                'token' =>$res
            ];


        }, function (\Exception $e) {
            $this->logger->debug('Error while login :' . $e->getMessage());
            return $e;
        });

    }

    /**
     * Check if the user is connected
     * @param Request $request The source http request
     * @param bool $doubleCheck Check through API if true
     * @return bool True if the user is connected
     */
    public function isConnected(Request $request, bool $doubleCheck): bool
    {
        $hasCookie = $request->cookies->has(self::FLARUM_COOKIE);
        if (!$doubleCheck || $hasCookie === false) {
            return $hasCookie;
        }
        return $this->flarumSSO->isUserConnected($request->cookies->get(self::FLARUM_COOKIE))->wait();
    }


    /**
     * Update a response object to clear the flarum cookie
     *
     * @param Response $resp The httpfoundation response object
     * @return void
     */
    public function logout(Response $resp): void
    {
        $resp->headers->clearCookie(self::FLARUM_COOKIE);
    }

}
