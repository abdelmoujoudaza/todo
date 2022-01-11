<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminCRUDController extends CRUDController
{
    public function deleteAction(Request $request): Response
    {
        $id = $request->get($this->admin->getIdParameter());
        \assert(null !== $id);
        $object = $this->admin->getObject($id);
        \assert(null !== $object);

        $currentAdminId = $this->getUser()->getId();

        if ($currentAdminId == $id) {
            $this->addFlash('sonata_flash_error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectTo($request, $object);
        }

        parent::deleteAction($request);
    }

    public function batchActionDelete(ProxyQueryInterface $query): Response
    {
        $currentAdminId = $this->getUser()->getId();
        $selectedAdmins = $query->execute();

        foreach ($selectedAdmins as $user) {
            if ($user->getId() == $currentAdminId) {
                $this->addFlash(
                    'sonata_flash_error',
                    'Vous ne pouvez pas supprimer votre propre compte.'
                );

                return $this->redirect(
                    $this->admin->generateUrl('list', ['filter' => $this->admin->getFilterParameters()])
                );
            }
        }

        return parent::batchActionDelete($query);
    }
}
