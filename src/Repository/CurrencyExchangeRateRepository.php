<?php

namespace App\Repository;

use App\Entity\CurrencyExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CurrencyExchangeRate>
 *
 * @method CurrencyExchangeRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method CurrencyExchangeRatendOneBy(array $criteria, array $orderBy = null)
 * @method CurrencyExchangeRatendAll()
 * @method CurrencyExchangeRatendBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyExchangeRate::class);
    }

    /**
     * @return string[]
     */
    public function getSupportedCurrencyCodes(): array
    {
        $queryBuilder = $this
            ->createQueryBuilder('currencyExchangeRate')
            ->distinct()
        ;

        $distinctFromCurrencyCodes = $queryBuilder
            ->select('currencyExchangeRate.fromCurrencyCode')
            ->getQuery()
            ->getSingleColumnResult()
        ;

        $distinctToCurrencyCodes = $queryBuilder
            ->select('currencyExchangeRate.toCurrencyCode')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    
        return array_unique(array_merge($distinctFromCurrencyCodes, $distinctToCurrencyCodes));
    }

    public function findByCurrencyCode(string $currencyCode): array
    {
        return $this->createQueryBuilder('currencyExchangeRate')
            ->where('currencyExchangeRate.fromCurrencyCode = :currencyCode')
            ->orWhere('currencyExchangeRate.toCurrencyCode = :currencyCode')
            ->setParameter('currencyCode', $currencyCode)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Write currency exchange rate to database, updating existing records or creating new ones.
     *
     * @param CurrencyExchangeRate $entity
     * @return void
     */
    public function upsert(CurrencyExchangeRate $entity): void
    {
        $sql = 'INSERT INTO currency_exchange_rate (id, from_currency_code, to_currency_code, exchange_rate, inverted_exchange_rate, created_at, updated_at)
        VALUES (:id, :fromCurrencyCode, :toCurrencyCode, :exchangeRate, :invertedExchangeRate, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            from_currency_code = VALUES(from_currency_code),
            to_currency_code = VALUES(to_currency_code),
            exchange_rate = VALUES(exchange_rate),
            inverted_exchange_rate = VALUES(inverted_exchange_rate),
            updated_at = NOW()';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->executeQuery([
            'id' => $entity->getId(),
            'fromCurrencyCode' => $entity->getFromCurrencyCode(),
            'toCurrencyCode' => $entity->getToCurrencyCode(),
            'exchangeRate' => $entity->getExchangeRate(),
            'invertedExchangeRate' => $entity->getInvertedExchangeRate(),
        ]);
    }
}
