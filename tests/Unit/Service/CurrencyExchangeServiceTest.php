<?php

namespace App\Tests\Unit\Authentication;

use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use App\Entity\CurrencyExchangeRate;
use PHPUnit\Framework\Attributes\Test;
use App\Service\CurrencyExchangeService;
use PHPUnit\Framework\MockObject\MockObject;
use App\Repository\CurrencyExchangeRateRepository;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyExchangeServiceTest extends TestCase
{
    private array $constructorArgs;
    private MockObject&CurrencyExchangeRateRepository $currencyRateRepository;
    private CurrencyExchangeService $currencyExchangeService;

    protected function setUp(): void
    {
        $this->constructorArgs = [
            $this->currencyRateRepository = $this->createMock(CurrencyExchangeRateRepository::class),
            $this->createMock(HttpClientInterface::class),
            $this->createMock(LoggerInterface::class),
        ];

        $this->currencyExchangeService = new CurrencyExchangeService(...$this->constructorArgs);
    }

    #[Test]
    public function failingFetchExchangeRateWillNotStopProcessing(): void
    {
        $currencyCodes = ['USD', 'EUR'];
        $existingCurrencyCodes = ['AUD', 'CHF'];

        $this->currencyRateRepository
            ->method('getSupportedCurrencyCodes')
            ->willReturn($existingCurrencyCodes)
        ;

        $fetchExchangeRateCallCount = 0;
        $currencyExchangePartialMock = $this->getCurrencyExchangeServicePartialMock(['fetchExchangeRate', 'importCurrencyExchangeRate']);
        $currencyExchangePartialMock
            ->expects($this->exactly(count($currencyCodes)))
            ->method('fetchExchangeRate')
            ->willReturnCallback(function () use (&$fetchExchangeRateCallCount) {
                $fetchExchangeRateCallCount++;
                switch ($fetchExchangeRateCallCount) {
                    case 1:
                        throw new \Exception('Failed to fetch exchange rate');
                    default:
                        return ['aud' => ['code' => 'AUD', 'rate' => 1.3, 'inverseRate' => 0.77]];
                }
            })
        ;
        // Expect 1 importCurrencyExchangeRate call because 1 exchange rate failed to be fetched
        $currencyExchangePartialMock
            ->expects($this->exactly(count($currencyCodes) - 1))
            ->method('importCurrencyExchangeRate')
        ;

        $currencyExchangePartialMock->updateExchangeRates($currencyCodes);
    }

    #[Test]
    public function canImportAllKnownExchangeRates(): void
    {
        $currencyCodes = ['USD', 'EUR'];
        $existingCurrencyCodes = ['AUD', 'CHF'];
        $ratesToImport = array_unique(array_merge($existingCurrencyCodes, $currencyCodes, CurrencyExchangeService::DEFAULT_SUPPORTED_CURRENCY_CODES));

        $this->currencyRateRepository
            ->method('getSupportedCurrencyCodes')
            ->willReturn($existingCurrencyCodes)
        ;

        $currencyExchangePartialMock = $this->getCurrencyExchangeServicePartialMock(['fetchExchangeRate', 'importCurrencyExchangeRate']);

        $currencyExchangePartialMock
            ->expects($this->exactly($currencyCodeCount = count($currencyCodes)))
            ->method('fetchExchangeRate')
            // Return an empty array for each currency code ['eur' => [], ....]
            ->willReturn(array_map(fn () => [], array_flip(array_map('strtolower', $ratesToImport))))
        ;

        $currencyExchangePartialMock
            ->expects($this->exactly($currencyCodeCount * count($ratesToImport)))
            ->method('importCurrencyExchangeRate')
        ;

        $currencyExchangePartialMock->updateExchangeRates($currencyCodes);
    }

    #[Test]
    public function canGetCurrencyExchangeRatesForCurrencyCode(): void
    {
        $currencyCode = 'USD';
        $currencyExchangeRate = $this->getCurrencyExchangeRate();

        $this->currencyRateRepository
            ->method('findByCurrencyCode')
            ->willReturn([$currencyExchangeRate]);

        $result = $this->currencyExchangeService->getCurrencyExchangeRatesForCurrencyCode($currencyCode);

        $expected = [
            [
                'code' => 'EUR',
                'rate' => 0.85,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function canGetCurrencyExchangeRatesForCurrencyCodeWithFlippedCurrencyCode(): void
    {
        $currencyCode = 'EUR';
        $currencyExchangeRate = $this->getCurrencyExchangeRate();

        $this->currencyRateRepository
            ->method('findByCurrencyCode')
            ->willReturn([$currencyExchangeRate]);

        $result = $this->currencyExchangeService->getCurrencyExchangeRatesForCurrencyCode($currencyCode);

        $expected = [
            [
                'code' => 'USD',
                'rate' => 1.18,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function importCurrencyExchangeRateFlipsRatesIfCurrencyCodesAreOutOfOrder(): void
    {
        $currencyExchangePartialMock = $this->getCurrencyExchangeServicePartialMock(['fetchExchangeRate', 'importCurrencyExchangeRate']);
        $importCurrencyExchangeRateMethod = $this->getReflectionMethodForClass('importCurrencyExchangeRate', CurrencyExchangeService::class);

        $this->currencyRateRepository
            ->expects($this->once())
            ->method('upsert')
            ->with($this->callback(function ($currencyExchangeRate) {
                $this->assertSame(md5('eurusd'), $currencyExchangeRate->getId());
                $this->assertSame('EUR', $currencyExchangeRate->getFromCurrencyCode());
                $this->assertSame('USD', $currencyExchangeRate->getToCurrencyCode());
                $this->assertSame(1.18, $currencyExchangeRate->getExchangeRate());
                $this->assertSame(0.85, $currencyExchangeRate->getInvertedExchangeRate());
                return true;
            }))
        ;

        $importCurrencyExchangeRateMethod->invokeArgs($currencyExchangePartialMock, ['USD', ['code' => 'EUR', 'rate' => 0.85, 'inverseRate' => 1.18]]);
    }

    #[Test]
    public function canimportCurrencyExchangeRate(): void
    {
        $currencyExchangePartialMock = $this->getCurrencyExchangeServicePartialMock(['fetchExchangeRate', 'importCurrencyExchangeRate']);
        $importCurrencyExchangeRateMethod = $this->getReflectionMethodForClass('importCurrencyExchangeRate', CurrencyExchangeService::class);

        $this->currencyRateRepository
            ->expects($this->once())
            ->method('upsert')
            ->with($this->callback(function ($currencyExchangeRate) {
                $this->assertSame(md5('eurusd'), $currencyExchangeRate->getId());
                $this->assertSame('EUR', $currencyExchangeRate->getFromCurrencyCode());
                $this->assertSame('USD', $currencyExchangeRate->getToCurrencyCode());
                $this->assertSame(1.18, $currencyExchangeRate->getExchangeRate());
                $this->assertSame(0.85, $currencyExchangeRate->getInvertedExchangeRate());
                return true;
            }))
        ;

        $importCurrencyExchangeRateMethod->invokeArgs($currencyExchangePartialMock, ['EUR', ['code' => 'USD', 'rate' => 1.18, 'inverseRate' => 0.85]]);
    }

    protected function getCurrencyExchangeRate(): CurrencyExchangeRate
    {
        $currencyExchangeRate = new CurrencyExchangeRate();
        $currencyExchangeRate->setFromCurrencyCode('USD');
        $currencyExchangeRate->setToCurrencyCode('EUR');
        $currencyExchangeRate->setExchangeRate(0.85);
        $currencyExchangeRate->setInvertedExchangeRate(1.18);
        return $currencyExchangeRate;
    }

    protected function getReflectionMethodForClass(string $method, string $class): \ReflectionMethod
    {
        return (new \ReflectionClass($class))->getMethod($method);
    }
    
    protected function getCurrencyExchangeServicePartialMock(array $methods): CurrencyExchangeService&MockObject
    {
        return $this->getMockBuilder(CurrencyExchangeService::class)
            ->disableOriginalClone()
            ->onlyMethods($methods)
            ->setConstructorArgs($this->constructorArgs)
            ->getMock();
    }
}
