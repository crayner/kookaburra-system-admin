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
use App\Provider\ProviderFactory;
use Kookaburra\SystemAdmin\Manager\ModulePagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
    public function manageModules(ModulePagination $pagination)
    {
        $provider = ProviderFactory::create(Module::class);
        $content = $provider->getRepository()->findBy([],['name' => 'ASC']);
        $pagination->setContent($content)->setPageMax(25)
            ->setPaginationScript();

        return $this->render('@KookaburraSystemAdmin/module_manage.html.twig',
            [
                'content' => $content,
            ]
        );
    }
}