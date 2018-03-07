<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180303200252 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE player_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE campaign_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE campaign_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE campaign_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE player (id INT NOT NULL, campaign_id INT NOT NULL, login VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(15) NOT NULL, sex BOOLEAN NOT NULL, birthday DATE NOT NULL, score INT NOT NULL DEFAULT 0, coins INT NOT NULL DEFAULT 0, system JSON NOT NULL, last_day TIMESTAMP(0) WITHOUT TIME ZONE, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_98197A65F639F774 ON player (campaign_id)');
        $this->addSql('COMMENT ON COLUMN player.system IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE campaign (id INT NOT NULL, member_id INT NOT NULL, campaign_type_id INT NOT NULL, name VARCHAR(255) NOT NULL, custom_setting TEXT NOT NULL, message_end TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1F1512DD9B6B5FBA ON campaign (member_id)');
        $this->addSql('CREATE INDEX IDX_1F1512DD6DF610BF ON campaign (campaign_type_id)');
        $this->addSql('CREATE TABLE campaign_status (id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE campaign_type (id INT NOT NULL, status_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D396CCB6BF700BD ON campaign_type (status_id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DD9B6B5FBA FOREIGN KEY (member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DD6DF610BF FOREIGN KEY (campaign_type_id) REFERENCES campaign_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign_type ADD CONSTRAINT FK_8D396CCB6BF700BD FOREIGN KEY (status_id) REFERENCES campaign_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE player DROP CONSTRAINT FK_98197A65F639F774');
        $this->addSql('ALTER TABLE campaign DROP CONSTRAINT FK_1F1512DD6DF610BF');
        $this->addSql('ALTER TABLE campaign_type DROP CONSTRAINT FK_8D396CCB6BF700BD');
        $this->addSql('DROP SEQUENCE player_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE campaign_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE campaign_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE campaign_type_id_seq CASCADE');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE campaign');
        $this->addSql('DROP TABLE campaign_status');
        $this->addSql('DROP TABLE campaign_type');
    }
}
