<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;

class UserHandler extends AbstractUserHandler
{

    /**
     * @var BusinessUnitManager
     */
    protected $businessUnitManager;

    /**
     * {@inheritdoc}
     */
    public function process(User $user)
    {
        $this->form->setData($user);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $businessUnits = $this->request->get('businessUnits', array());
                if ($businessUnits) {
                    $businessUnits = array_keys($businessUnits);
                }
                if ($this->businessUnitManager) {
                    $this->businessUnitManager->assignBusinessUnits($user, $businessUnits);
                }
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(User $user)
    {
        $this->manager->updateUser($user);

        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        $this->manager->reloadUser($user);
    }

    /**
     * @param BusinessUnitManager $businessUnitManager
     */
    public function setBusinessUnitManager(BusinessUnitManager $businessUnitManager)
    {
        $this->businessUnitManager = $businessUnitManager;
    }
}
