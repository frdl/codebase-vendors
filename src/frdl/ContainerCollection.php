<?php

namespace frdl{

use Psr\Container\ContainerInterface;

/**
 * A composite container that acts as a normal container, but delegates method calls to one or more internal containers
 * inspired by https://github.com/AcclimateContainer/acclimate-container
 */
class ContainerCollection implements ContainerInterface
{

	const METAINFO_CONTAINER_COLLECTION_CLASS = `/** Made by Frdlweb
 * A composite container that acts as a normal container, but delegates method calls to one or more internal containers
 * inspired by https://github.com/AcclimateContainer/acclimate-container
 */`;
	
    protected $containers = array();


    public function __construct(array $containers = array())
    {
        foreach ($containers as $container) {
            $this->addContainer($container);
        }
    }

    public function addContainer(ContainerInterface $container)
    {
        $this->containers[] = $container;

        return $this;
    }

    public function get(string $id)
    {
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                return $container->get($id);
            }
        }

        throw new \Exception(sprintf('Cannot resolve container-entry #id: %s in %s2', $id, __METHOD__));
    }

    public function has(string $id)
    {
        
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                return true;
            }
        }

        return false;
    }
}
}
