<?php

namespace Oro\Bundle\EntityBundle\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

use Doctrine\ORM\ORMInvalidArgumentException;
use Oro\Bundle\EntityBundle\ORM\Query\FilterCollection;

class OroEntityManager extends EntityManager
{
    /**
     * Collection of query filters.
     *
     * @var FilterCollection
     */
    protected $filterCollection;

    /**
     * Manager for extend and custom entities
     *
     * @var ExtendManager
     */
    protected $extendManager;

    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {
        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        if (is_array($conn)) {
            $conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, ($eventManager ? : new EventManager()));
        } elseif ($conn instanceof Connection) {
            if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                throw ORMException::mismatchedEventManager();
            }
        } else {
            throw new \InvalidArgumentException("Invalid argument: " . $conn);
        }

        return new OroEntityManager($conn, $config, $conn->getEventManager());
    }

    /**
     * @param ExtendManager $extendManager
     * @return $this
     */
    public function setExtendManager($extendManager)
    {
        throw new \LogicException('This will be dropped, EntityExtendBundle has been dropped');

        $this->extendManager = $extendManager;

        return $this;
    }

    /**
     * @return ExtendManager
     */
    public function getExtendManager()
    {
        throw new \LogicException('This will be dropped, EntityExtendBundle has been dropped');

        return $this->extendManager;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function isExtendEntity($entity)
    {
        return $this->extendManager->isExtend($entity);
    }

    /**
     * @param FilterCollection $collection
     */
    public function setFilterCollection(FilterCollection $collection)
    {
        $this->filterCollection = $collection;
    }

    /**
     * Gets the enabled filters.
     *
     * @return FilterCollection The active filter collection.
     */
    public function getFilters()
    {
        if (null === $this->filterCollection) {
            $this->filterCollection = new FilterCollection($this);
        }

        return $this->filterCollection;
    }

    /**
     * Checks whether the state of the filter collection is clean.
     *
     * @return boolean True, if the filter collection is clean.
     */
    public function isFiltersStateClean()
    {
        return null === $this->filterCollection || $this->filterCollection->isClean();
    }

    /**
     * Checks whether the Entity Manager has filters.
     *
     * @return boolean True, if the EM has a filter collection with enabled filters.
     */
    public function hasFilters()
    {
        return null !== $this->filterCollection && $this->filterCollection->getEnabledFilters();
    }
}
