<?php

namespace App\Controller;

use App\Service\CurrencyExchangeService;
use App\Message\FetchExchangeRatesMessage;
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
        foreach (CurrencyExchangeService::DEFAULT_SUPPORTED_CURRENCY_CODES as $currencyCode) {
            $this->messageBus->dispatch(new FetchExchangeRatesMessage([$currencyCode]));
        }

        $currencyCodes = $this->currencyExchangeRateRepository->getSupportedCurrencyCodes();

        return $this->render('home/index.html.twig', [
            'currencyCodes' => $currencyCodes,
        ]);
    }
}
