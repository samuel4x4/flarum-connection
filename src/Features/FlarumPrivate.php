<?php
namespace FlarumConnection\Features;

use FlarumConnection\Models\FlarumConnectorConfig;
use \Psr\Log\LoggerInterface;

/**
 * Handle private categories management
 */
class FlarumPrivate extends AbstractFeature{

    /**
     * Initialize the feature with the config
     *
     * @param FlarumConnectorConfig $config     Configuration for flarum connector
     * @param LoggerInterface $logger           Logger interface
     */
    public function __construct(FlarumConnectorConfig $config,LoggerInterface $logger){
        $this->init($config,$logger);
    }

}