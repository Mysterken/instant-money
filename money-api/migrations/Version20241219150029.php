<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219150029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__historical_coin_data AS SELECT id, base_currency_id, currency_id, date, value FROM historical_coin_data');
        $this->addSql('DROP TABLE historical_coin_data');
        $this->addSql('CREATE TABLE historical_coin_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, base_currency_id VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, date DATE NOT NULL, value DOUBLE PRECISION NOT NULL, CONSTRAINT FK_6373B87E3101778E FOREIGN KEY (base_currency_id) REFERENCES coin (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO historical_coin_data (id, base_currency_id, currency, date, value) SELECT id, base_currency_id, currency_id, date, value FROM __temp__historical_coin_data');
        $this->addSql('DROP TABLE __temp__historical_coin_data');
        $this->addSql('CREATE INDEX IDX_6373B87E3101778E ON historical_coin_data (base_currency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__historical_coin_data AS SELECT id, base_currency_id, date, currency, value FROM historical_coin_data');
        $this->addSql('DROP TABLE historical_coin_data');
        $this->addSql('CREATE TABLE historical_coin_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, base_currency_id VARCHAR(255) NOT NULL, currency_id VARCHAR(255) NOT NULL, date DATE NOT NULL, value DOUBLE PRECISION NOT NULL, CONSTRAINT FK_6373B87E3101778E FOREIGN KEY (base_currency_id) REFERENCES coin (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6373B87E38248176 FOREIGN KEY (currency_id) REFERENCES coin (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO historical_coin_data (id, base_currency_id, date, currency_id, value) SELECT id, base_currency_id, date, currency, value FROM __temp__historical_coin_data');
        $this->addSql('DROP TABLE __temp__historical_coin_data');
        $this->addSql('CREATE INDEX IDX_6373B87E3101778E ON historical_coin_data (base_currency_id)');
        $this->addSql('CREATE INDEX IDX_6373B87E38248176 ON historical_coin_data (currency_id)');
    }
}
