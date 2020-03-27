<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 26/03/2020
 * Time: 12:38
 */

namespace Kookaburra\SystemAdmin\Controller;

use App\Container\ContainerManager;
use App\Manager\PageManager;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationsHelper;
use Doctrine\DBAL\Driver\PDOException;
use Kookaburra\SystemAdmin\Entity\StringReplacement;
use Kookaburra\SystemAdmin\Form\StringReplacementType;
use Kookaburra\SystemAdmin\Pagination\StringReplacementPagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StringController
 * @package Kookaburra\SystemAdmin\Controller
 */
class StringController extends AbstractController
{

    /**
     * stringReplacementEdit
     * @param PageManager $pageManager
     * @param ContainerManager $manager
     * @param string|null $stringReplacement
     * @Route("/string/replacement/{stringReplacement}/edit/", name="string_replacement_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse|Response
     */
    public function stringReplacementEdit(PageManager $pageManager, ContainerManager $manager, ?string $stringReplacement = 'Add')
    {
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $request = $pageManager->getRequest();

        $manager->setTranslationDomain('SystemAdmin');

        $stringReplacement = $stringReplacement !== 'Add' ? ProviderFactory::getRepository(StringReplacement::class)->find($stringReplacement) : new StringReplacement();

        $form = $this->createForm(StringReplacementType::class, $stringReplacement, ['action' => $this->generateUrl('system_admin__string_replacement_edit', ['stringReplacement' => $stringReplacement->getId() ?: 'Add'])]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);

            $data = [];
            $form->submit($content);
            if ($form->isValid()) {

                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($stringReplacement);
                    $em->flush();
                    $data = ErrorMessageHelper::getSuccessMessage($data, true);
                    $form = $this->createForm(StringReplacementType::class, $stringReplacement, ['action' => $this->generateUrl('system_admin__string_replacement_edit', ['stringReplacement' => $stringReplacement->getId() ?: 'Add'])]);
                } catch (PDOException $e) {
                    $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
                }
            } else {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return JsonResponse::create($data, 200);
        }

        $manager->setReturnRoute($this->generateUrl('system_admin__string_replacement_manage'))
            ->setAddElementRoute($this->generateUrl('system_admin__string_replacement_edit', ['stringReplacement' => 'Add']))
            ->singlePanel($form->createView());

        return $pageManager->createBreadcrumbs('Edit String',
            [
                ['uri' => 'system_admin__string_replacement_manage', 'name' => 'Manage String Replacements'],
            ]
        )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * stringReplacementManage
     * @param PageManager $pageManager
     * @param StringReplacementPagination $pagination
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/string/replacement/manage/", name="string_replacement_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function stringReplacementManage(PageManager $pageManager, StringReplacementPagination $pagination)
    {
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $request = $pageManager->getRequest();

        $content = [];
        $provider = ProviderFactory::create(StringReplacement::class);
        $content = $provider->getPaginationResults($request->query->get('search'));
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('system_admin__string_replacement_edit', ['stringReplacement' => 'Add']))
            ->setPaginationScript();
        return $pageManager->createBreadcrumbs('Manage String Replacements')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * stringReplacementDelete
     * @param StringReplacement $stringReplacement
     * @Route("/string/replacement/{stringReplacement}/delete/", name="string_replacement_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return RedirectResponse
     */
    public function stringReplacementDelete(StringReplacement $stringReplacement)
    {
        try {
            $em =$this->getDoctrine()->getManager();
            $em->remove($stringReplacement);
            $em->flush();
            $this->addFlash('success', 'return.success.0');
        } catch (PDOException $e) {
            $this->addFlash('error', 'return.error.2');
        }

        return $this->redirectToRoute('system_admin__string_replacement_manage');
    }
}