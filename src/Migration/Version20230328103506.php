<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Migration;

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
        $this->addSql('CREATE TABLE baselinker_statuses_associations (shop_status VARCHAR(255) NOT NULL, baselinker_status VARCHAR(255) NOT NULL, PRIMARY KEY(shop_status)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_order ADD baselinker_id INT DEFAULT 0 NOT NULL, ADD baselinker_update_time INT DEFAULT 0 NOT NULL');
        $this->addSql('INSERT INTO baselinker_settings(name, value) VALUES("order.source", "0")');
        $this->addSql('INSERT INTO baselinker_settings(name, value) VALUES("last.journal.id", "0")');
        $this->addSql('INSERT INTO baselinker_statuses_associations(shop_status, baselinker_status) VALUES("new", "")');
        $this->addSql('INSERT INTO baselinker_statuses_associations(shop_status, baselinker_status) VALUES("fulfilled", "")');
        $this->addSql('INSERT INTO baselinker_statuses_associations(shop_status, baselinker_status) VALUES("cancelled", "")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE baselinker_settings');
        $this->addSql('DROP TABLE baselinker_statuses_associations');
        $this->addSql('ALTER TABLE sylius_order DROP baselinker_id, DROP baselinker_update_time');
    }
}
