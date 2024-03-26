<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240326220159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create activity logs table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE log_activity_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE log_activity (id INT NOT NULL, action VARCHAR(8) NOT NULL, logged_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(191) NOT NULL, version INT NOT NULL, data TEXT DEFAULT NULL, username VARCHAR(191) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX log_activity_user_index ON log_activity (username)');
        $this->addSql('CREATE INDEX log_activity_class_index ON log_activity (object_class)');
        $this->addSql('CREATE INDEX log_activity_date_index ON log_activity (logged_at)');
        $this->addSql('CREATE INDEX log_activity_version_index ON log_activity (object_id, object_class, version)');
        $this->addSql('COMMENT ON COLUMN log_activity.data IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE log_activity_id_seq CASCADE');
        $this->addSql('DROP TABLE log_activity');
    }
}
