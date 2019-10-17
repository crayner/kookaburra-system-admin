<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 7/10/2019
 * Time: 09:48
 */

namespace Kookaburra\SystemAdmin\Controller;

use App\Entity\Module;
use Kookaburra\SystemAdmin\Manager\ModulePagination;
use Kookaburra\SystemAdmin\Manager\ModuleUpdateManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ModuleController
 * @package Kookaburra\SystemAdmin\Controller
 * @Route("/system_admin", name="system_admin__")
 */
class ModuleController extends AbstractController
{
    /**
     * manage
     * @param ModulePagination $pagination
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/module/manage/", name="module_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manage(ModulePagination $pagination, ModuleUpdateManager $manager)
    {
        $content = $manager->getAllModules();
        $pagination->setContent($content)->setPageMax(25)
            ->setPaginationScript();

        return $this->render('@KookaburraSystemAdmin/module_manage.html.twig',
            [
                'content' => $content,
            ]
        );
    }

    /**
     * update
     * @param Module $upgrade
     * @param ModuleUpdateManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/module/{upgrade}/update/", name="module_update")
     * @Security("is_granted('ROLE_ROUTE', ['system_admin__module_manage'])")
     */
    public function update(Module $upgrade, ModuleUpdateManager $manager)
    {
        $manager->setModule($upgrade);
        // Check for update required.
        $manager->upgradeModule();
        foreach($manager->getMessageManager()->getMessages() as $message)
            $this->addFlash($message->getLevel(), $message->getMessage());
        return $this->redirectToRoute('system_admin__module_manage');
    }

    /**
     * delete
     * @param Module $delete
     * @param ModuleUpdateManager $manager
     * @Route("/module/{delete}/delete/", name="module_delete")
     * @Security("is_granted('ROLE_ROUTE', ['system_admin__module_manage'])")
     */
    public function delete(Module $delete, ModuleUpdateManager $manager)
    {
        if ($manager->setModule($delete)->isSymfonyBundle() && $delete->getType() === 'Additional')
        {
            $manager->deleteModule();
            $this->addFlash('success', 'Your request was completed successfully.');
        } else {
            $this->addFlash('error', 'Your request failed because your inputs were invalid.');
        }

        return $this->redirectToRoute('system_admin__module_manage');
    }
}