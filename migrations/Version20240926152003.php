<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240926152003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create currency_exchange_rate table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE currency_exchange_rate (id VARCHAR(255) NOT NULL, from_currency_code VARCHAR(3) NOT NULL, to_currency_code VARCHAR(3) NOT NULL, exchange_rate DOUBLE PRECISION NOT NULL, inverted_exchange_rate DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE currency_exchange_rate');
    }
}
