<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CurrencyExchangeRateRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly CurrencyExchangeRateRepository $currencyExchangeRateRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $currencyCodes = $this->currencyExchangeRateRepository->getSupportedCurrencyCodes();

        return $this->render('home/index.html.twig', [
            'currencyCodes' => $currencyCodes,
        ]);
    }
}
