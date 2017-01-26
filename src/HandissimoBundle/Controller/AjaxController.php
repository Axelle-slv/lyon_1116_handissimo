<?php

namespace HandissimoBundle\Controller;


use HandissimoBundle\Entity\Organizations;
use HandissimoBundle\Repository\DisabilityTypesRepository;
use HandissimoBundle\Repository\NeedsRepository;
use HandissimoBundle\Repository\OrganizationsRepository;
use HandissimoBundle\Repository\StaffRepository;
use HandissimoBundle\Repository\StructuresTypesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use HandissimoBundle\Form\AdvancedSearchType;

class AjaxController extends Controller
{
    public function researchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $step1SearchForm = $this->createForm('HandissimoBundle\Form\ResearchType');
        $step1SearchForm->handleRequest($request);

        $step2SearchForm = $this->createForm(AdvancedSearchType::class/*, $searchAdvanced, array('organizationsRepository' => ($em->getRepository('HandissimoBundle:Organizations')))  */);
        $step2SearchForm->handleRequest($request);

        if ($step1SearchForm->isSubmitted() && $step1SearchForm->isValid()){

            $data = $step1SearchForm->getData();
            $age = $step1SearchForm->getData()['age'];

            /**
             * @var $repository OrganizationsRepository
             */
            $result = $em->getRepository('HandissimoBundle:Organizations')->getByOrganizationName($data, $age);
            return $this->render('front/search.html.twig', array(
                'result' => $result,
                'keyword' => $data,
                'age' => $age,
                'form' => $step2SearchForm->createView(),
            ));

        } elseif ($step2SearchForm->isSubmitted() && $step2SearchForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $step2SearchForm->getData();
            $age = $data['age'];
            $disabilityChoice = "";
            $needChoice = "";
            $structureChoice = "";
            if (!is_null($data['disabilitytypes'])) {
                $disabilityChoice = $data['disabilitytypes']->getDisabilityName();
            }
            if (!is_null($data['needs'])) {
                $needChoice = $data['needs']->getNeedName();
            }
            if (!is_null($data['structurestypes'])) {
                $structureChoice = $data['structurestypes']->getStructuresType();
            }

            /**
             * @var $repository OrganizationsRepository
             */
            $result = $em->getRepository('HandissimoBundle:Organizations')->getByMultipleCriterias($data, $age);
            return $this->render('front/search.html.twig', array(
                'result' => $result,
                'keyword' => $data,
                'age' => $age,
                'form' => $step2SearchForm->createView(),
            ));
        }
        return $this->render('front/research.html.twig', array(
            'form' => $step1SearchForm->createView(),
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

            /**
             * @var $repository StaffRepository
             */
            $repository = $this->getDoctrine()->getRepository('HandissimoBundle:Staff');
            $staff = $repository->getByStaff($keyword);

            $data =  array_merge($organization, $needs, $disability, $structure, $staff);

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
        } else {
            throw new HttpException('500', 'Invalid call');
        }

    }

}