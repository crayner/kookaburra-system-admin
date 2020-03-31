<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 7/10/2019
 * Time: 09:48
 */

namespace Kookaburra\SystemAdmin\Controller;

use App\Manager\PageManager;
use Kookaburra\SystemAdmin\Entity\Module;
use Kookaburra\SystemAdmin\Pagination\ModulePagination;
use Kookaburra\SystemAdmin\Manager\ModuleUpdateManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ModuleController
 * @package Kookaburra\SystemAdmin\Controller
 * @todo Modules need a lot of work...
 */
class ModuleController extends AbstractController
{
    /**
     * manage
     * @param ModulePagination $pagination
     * @param ModuleUpdateManager $manager
     * @param PageManager $pageManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/module/manage/", name="module_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manage(ModulePagination $pagination, ModuleUpdateManager $manager, PageManager $pageManager)
    {
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $content = $manager->getAllModules();
        $pagination->setContent($content)->setPageMax(25)
            ->setPaginationScript();

        return $pageManager->createBreadcrumbs('Manage Modules')
            ->render(['pagination' => $pagination->toArray()]);
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
            $this->addFlash('success', 'return.success.0');
        } else {
            $this->addFlash('error', 'Your request failed because your inputs were invalid.');
        }

        return $this->redirectToRoute('system_admin__module_manage');
    }

    /**
     * checkInstallation
     * @param KernelInterface $kernel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/module/installation/check/", name="module_installation_check")
     * @Security("is_granted('ROLE_ROUTE', ['system_admin__module_manage'])")
     *
     * DOES NOT WORK
     *
     */
    public function checkInstallation(KernelInterface $kernel)
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'module:install',
            // (optional) define the value of command arguments
            // 'fooArgument' => 'barValue',
            // (optional) pass options to the command
            // '--quiet' => '--quiet',
            // '--no-interaction' => '--no-interaction',
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new NullOutput();
        dd($application->run($input, $output));

        // return the output, don't use if you used NullOutput()
        // $content = $output->fetch();

        //if ('' !== $content)
        //return new Response($content);

        return $this->redirectToRoute('system_admin__module_manage');
    }
}