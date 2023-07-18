<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230602091147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_flavor DROP FOREIGN KEY FK_DE5975F04584665A');
        $this->addSql('ALTER TABLE product_flavor DROP FOREIGN KEY FK_DE5975F0FDDA6450');
        $this->addSql('DROP TABLE flavor');
        $this->addSql('DROP TABLE product_flavor');
        $this->addSql('ALTER TABLE product ADD parfum VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE flavor (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE product_flavor (product_id INT NOT NULL, flavor_id INT NOT NULL, INDEX IDX_DE5975F0FDDA6450 (flavor_id), INDEX IDX_DE5975F04584665A (product_id), PRIMARY KEY(product_id, flavor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_flavor ADD CONSTRAINT FK_DE5975F04584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_flavor ADD CONSTRAINT FK_DE5975F0FDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product DROP parfum');
    }
}
