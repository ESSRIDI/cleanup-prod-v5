<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230621090720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item CHANGE volume_id selected_volume_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09D7A71D01 FOREIGN KEY (selected_volume_id) REFERENCES volume (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09D614C7E7 FOREIGN KEY (price_id) REFERENCES price (id)');
        $this->addSql('CREATE INDEX IDX_52EA1F09D7A71D01 ON order_item (selected_volume_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_52EA1F09D614C7E7 ON order_item (price_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09D7A71D01');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09D614C7E7');
        $this->addSql('DROP INDEX IDX_52EA1F09D7A71D01 ON order_item');
        $this->addSql('DROP INDEX UNIQ_52EA1F09D614C7E7 ON order_item');
        $this->addSql('ALTER TABLE order_item CHANGE selected_volume_id volume_id INT NOT NULL');
    }
}
