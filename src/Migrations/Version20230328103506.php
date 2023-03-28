<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230328103506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Baselinker Plugin initial';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE baselinker_settings (name VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE baselinker_statuses_associations (shopStatus VARCHAR(255) NOT NULL, baselinkerStatus VARCHAR(255) NOT NULL, PRIMARY KEY(shopStatus)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_order ADD baselinkerId INT DEFAULT 0 NOT NULL, ADD baselinkerUpdateTime INT DEFAULT 0 NOT NULL');
        $this->addSql('INSERT INTO baselinker_settings(name, value) VALUES("orderSource", "")');
        $this->addSql('INSERT INTO baselinker_statuses_associations(shopStatus, baselinkerStatus) VALUES("new", "")');
        $this->addSql('INSERT INTO baselinker_statuses_associations(shopStatus, baselinkerStatus) VALUES("fulfilled", "")');
        $this->addSql('INSERT INTO baselinker_statuses_associations(shopStatus, baselinkerStatus) VALUES("cancelled", "")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE baselinker_settings');
        $this->addSql('DROP TABLE baselinker_statuses_associations');
        $this->addSql('ALTER TABLE sylius_order DROP baselinkerId, DROP baselinkerUpdateTime');
    }
}
