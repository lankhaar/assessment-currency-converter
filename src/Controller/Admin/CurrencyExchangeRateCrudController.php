<?php

namespace App\Controller\Admin;

use App\Entity\CurrencyExchangeRate;
use App\Message\FetchExchangeRatesMessage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Repository\CurrencyExchangeRateRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Messenger\MessageBusInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[Route('/admin')]
class CurrencyExchangeRateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CurrencyExchangeRate::class;
    }

    #[Route('/refresh-exchange-rates', name: 'admin.refresh_exchange_rates')]
    public function refreshExchangeRates(
        CurrencyExchangeRateRepository $currencyExchangeRateRepository,
        MessageBusInterface $messageBus,
        AdminUrlGenerator $adminUrlGenerator
    ): Response
    {
        $messageBus->dispatch(new FetchExchangeRatesMessage($currencyExchangeRateRepository->getSupportedCurrencyCodes()));

        return $this->redirect($adminUrlGenerator->setController(self::class)->generateUrl());
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('fromCurrencyCode'),
            TextField::new('toCurrencyCode'),
            NumberField::new('exchangeRate'),
            DateTimeField::new('updatedAt'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Currency Exchange Rate')
            ->setEntityLabelInPlural('Currency Exchange Rates')
            ->setTimezone('Europe/Amsterdam')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $refreshExchangeRates = Action::new('refreshExchangeRates', 'Refresh Exchange Rates', 'fa fa-cash-register')
            ->createAsGlobalAction()
            ->linkToRoute('admin.refresh_exchange_rates')
        ;

        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, $refreshExchangeRates)
        ;
    }
}
