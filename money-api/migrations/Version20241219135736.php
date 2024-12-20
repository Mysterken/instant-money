<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219135736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coin (id VARCHAR(255) NOT NULL, symbol VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE crypto (id VARCHAR(255) NOT NULL, symbol VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE historical_coin_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, base_currency_id VARCHAR(255) NOT NULL, currency_id VARCHAR(255) NOT NULL, date DATE NOT NULL, CONSTRAINT FK_6373B87E3101778E FOREIGN KEY (base_currency_id) REFERENCES coin (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6373B87E38248176 FOREIGN KEY (currency_id) REFERENCES coin (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6373B87E3101778E ON historical_coin_data (base_currency_id)');
        $this->addSql('CREATE INDEX IDX_6373B87E38248176 ON historical_coin_data (currency_id)');
        $this->addSql('CREATE TABLE historical_crypto_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date DATE NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE coin');
        $this->addSql('DROP TABLE crypto');
        $this->addSql('DROP TABLE historical_coin_data');
        $this->addSql('DROP TABLE historical_crypto_data');
    }
}
