<?php

namespace Oro\Bundle\UserBundle\Controller;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\PersistentCollection;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Autocomplete\UserSearchHandler;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;

class UserController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_user_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("pim_user_user_index")
     */
    public function viewAction(User $user)
    {
        return $this->view($user);
    }

    /**
     * @Route("/profile/view", name="oro_user_profile_view")
     * @Template("OroUserBundle:User:view.html.twig")
     */
    public function viewProfileAction()
    {
        return $this->view($this->getUser(), 'oro_user_profile_update');
    }

    /**
     * @Route("/profile/edit", name="oro_user_profile_update")
     * @Template("OroUserBundle:User:update.html.twig")
     */
    public function updateProfileAction()
    {
        return $this->update(
            $this->getUser(),
            'oro_user_profile_update',
            array('route' => 'oro_user_profile_view')
        );
    }

    /**
     * @Route("/apigen/{id}", name="oro_user_apigen", requirements={"id"="\d+"})
     * @AclAncestor("pim_user_user_edit")
     */
    public function apigenAction(User $user)
    {
        if (!$api = $user->getApi()) {
            $api = new UserApi();
        }

        $api->setApiKey($api->generateKey())
            ->setUser($user);

        $em = $this->getDoctrine()->getManager();

        $em->persist($api);
        $em->flush();

        return $this->getRequest()->isXmlHttpRequest()
            ? new JsonResponse($api->getApiKey())
            : $this->forward('OroUserBundle:User:view', array('user' => $user));
    }

    /**
     * Create user form
     *
     * @Route("/create", name="oro_user_create")
     * @Template("OroUserBundle:User:update.html.twig")
     * @AclAncestor("pim_user_user_create")
     */
    public function createAction()
    {
        $user = $this->get('oro_user.manager')->createUser();

        return $this->update($user);
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="oro_user_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @AclAncestor("pim_user_user_edit")
     */
    public function updateAction(User $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("pim_user_user_index")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Delete user
     *
     * @Route(
     *      "/delete/{id}",
     *      name="oro_user_user_delete",
     *      requirements={"id"="\d+"},
     *      methods="DELETE"
     * )
     * @AclAncestor("pim_user_user_remove")
     */
    public function deleteAction($id)
    {
        $securityToken = $this->get('security.context')->getToken();
        $currentUser = $securityToken ? $securityToken->getUser() : null;
        if (is_object($currentUser) && $currentUser->getId() != $id) {
            $em = $this->get('doctrine.orm.entity_manager');
            $userClass = $this->container->getParameter('oro_user.entity.class');
            $user = $em->getRepository($userClass)->find($id);

            if (!$user) {
                throw $this->createNotFoundException(sprintf('User with id %d could not be found.', $id));
            }

            $em->remove($user);
            $em->flush();

            return new JsonResponse('', 204);
        } else {

            return new JsonResponse('', 403);
        }
    }

    /**
     * @param User $entity
     * @param string $updateRoute
     * @param array $viewRoute
     * @return array
     */
    protected function update(User $entity, $updateRoute = '', $viewRoute = array())
    {
        if ($this->get('oro_user.form.handler.user')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.user.controller.user.message.saved')
            );

            if (count($viewRoute)) {
                $closeButtonRoute = $viewRoute;
            } else {
                $closeButtonRoute = array(
                    'route' => 'oro_user_view',
                    'parameters' => array('id' => $entity->getId())
                );
            }
            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'oro_user_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                $closeButtonRoute
            );
        }

        return array(
            'form' => $this->get('oro_user.form.user')->createView(),
            'editRoute' => $updateRoute
        );
    }

    /**
     * @param User $user
     * @param string $editRoute
     * @return array
     */
    protected function view(User $user, $editRoute = '')
    {
        $output = array(
            'entity'   => $user,
            'dynamic'  => []
        );

        if ($editRoute) {
            $output = array_merge($output, array('editRoute' => $editRoute));
        }

        return $output;
    }
}
