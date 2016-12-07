<?php
/**
 * Created by PhpStorm.
 * User: tangui
 * Date: 05/12/16
 * Time: 14:40
 */

namespace HandissimoBundle\Controller;


use HandissimoBundle\Repository\DisabilityTypesRepository;
use HandissimoBundle\Repository\NeedsRepository;
use HandissimoBundle\Repository\OrganizationsRepository;
use HandissimoBundle\Repository\StructuresTypesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AjaxController extends Controller
{
    public function researchAction(Request $request, $keyword/*, $postaldata*/)
    {
    $form = $this->createForm('HandissimoBundle\Form\ResearchType');
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()){
        /**
         * @var $repository OrganizationsRepository
         */
        $repository = $this->getDoctrine()->getRepository('HandissimoBundle:Organizations');
        $result = $repository->getByOrganizationsName($keyword/*, $postaldata*/);
        var_dump($result);

        return $result->$this->redirectToRoute('result_home', array(
            'result' => $result,
        ));
    }
    return $this->render('front/research.html.twig', array(
        'form' => $form->createView(),
    ));

    }

    public function autoCompleteAction(Request $request, $keyword)
    {
        if ($request->isXmlHttpRequest())
        {
            /**
             * @var $repository OrganizationsRepository
             */
            $repository = $this->getDoctrine()->getRepository('HandissimoBundle:Organizations');
            $organization = $repository->getByOrganizations($keyword);

            /**
             * @var $repository NeedsRepository
             */
            $repository = $this->getDoctrine()->getRepository('HandissimoBundle:Needs');
            $needs = $repository->getByNeeds($keyword);

            /**
             * @var $repository DisabilityTypesRepository
             */
            $repository = $this->getDoctrine()->getRepository('HandissimoBundle:DisabilityTypes');
            $disability = $repository->getByDisability($keyword);

            /**
             * @var $repository StructuresTypesRepository
             */
            $repository = $this->getDoctrine()->getRepository('HandissimoBundle:StructuresTypes');
            $structure = $repository->getByStructure($keyword);

            $data =  array_merge($organization,$needs, $disability, $structure);

            return new JsonResponse(array("data" => json_encode($data)));
        } else {
            throw new HttpException("500", "Invalid Call");
        }
    }
    public function postalAction(Request $request, $postalcode)
    {
        /**
         * @var $repository OrganizationsRepository
         */
        if ($request->isXmlHttpRequest()) {
            $repository = $this->getDoctrine()->getRepository('HandissimoBundle:Organizations');
            $data = $repository->getByCity($postalcode);
            return new JsonResponse(array("data" => json_encode($data)));
        }else {
            throw new HttpException('500', 'Invalid call');
        }
    }
}