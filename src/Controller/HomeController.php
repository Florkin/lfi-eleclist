<?php

namespace App\Controller;

use App\Repository\GroupedAddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private GroupedAddressRepository $groupedAddressRepository;

    public function __construct(GroupedAddressRepository $groupedAddressRepository)
    {
        $this->groupedAddressRepository = $groupedAddressRepository;
    }

    /**
     * @Route("/home", name="app_home")
     */
    public function index(): Response
    {
        $city = "wattrelos";
        $streets = $this->groupedAddressRepository->findStreets($city)->execute();

        return $this->render('pdf.html.twig', [
            'streets' => $streets,
            'city' => strtoupper($city)
        ]);
    }
}
