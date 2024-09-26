<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240926093622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create allowed_ip table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE allowed_ip (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE allowed_ip');
    }
}
