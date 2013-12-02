<?php

namespace Oro\Bundle\EntityConfigBundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class OptionSetRepository extends EntityRepository
{
    /**
     * @param $fieldConfigId
     * @return object
     */
    public function findOptionsByField($fieldConfigId)
    {
        return $this->findBy(
            ['field' => $fieldConfigId],
            ['priority' => Criteria::ASC]
        );
    }
}
