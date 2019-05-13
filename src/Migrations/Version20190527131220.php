<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190527131220 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create currencies';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('INSERT IGNORE INTO `currency` VALUES (\'BTC\',\'Bitcoin\',\'chain-so\'),(\'ETH\',\'Ethereum\',\'ether-scan\'),(\'LTC\',\'Litecoin\',\'chain-so\');');

    }

    public function down(Schema $schema) : void
    {
        die('Pls make separate migration for roll-back');
    }
}
