<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241211072940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX clave_producto ON producto');
        $this->addSql('ALTER TABLE producto ADD id INT AUTO_INCREMENT NOT NULL, CHANGE Id_producto id_producto INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE producto MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON producto');
        $this->addSql('ALTER TABLE producto DROP id, CHANGE id_producto Id_producto INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX clave_producto ON producto (clave_producto)');
        $this->addSql('ALTER TABLE producto ADD PRIMARY KEY (Id_producto)');
    }
}
