<?php

namespace LoginControl\src\Services;

use Doctrine\DBAL\Schema\MySQLSchemaManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

class UserSecurityRepository extends EntityRepository
{
    private const TABLE_NAME = 'user_security';
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em,
        ClassMetadata $class
    ) {
        parent::__construct($em, $class);
        $this->em = $em;
        $this->schemaManager = $this->em->getConnection()->createSchemaManager();
    }

    public function createIfNoneExists(): void
    {
        if (!$this->schemaManager->tablesExist(['ocs_user_security'])) {
            $sql = 'CREATE TABLE `ocs_user_security`  (
                      `id` int NOT NULL AUTO_INCREMENT,
                      `user_id` int NULL,
                      `totp_key` varchar(255) NULL,
                      `create_date` datetime NULL,
                      `passwd_expire` datetime NOT NULL,
                      `active` tinyint(1) NULL,
                      PRIMARY KEY (`id`),
                      INDEX `usrIdx`(`user_id`) USING BTREE
            )';
            $this->em->getConnection()->executeQuery($sql);
        }
    }

    public function createIfLogExists(): void
    {
        if (!$this->schemaManager->tablesExist(['ocs_passwd_log'])) {
            $sql = 'CREATE TABLE `ocs_passwd_log`  (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `user_id` int NOT NULL,
                    `passwd_hash` varchar(255) NULL,
                    `log_date` datetime NOT NULL,
                    PRIMARY KEY(`id`),
                    INDEX `usrIdx`(`user_id`) USING BTREE
            )';
            $this->em->getConnection()->executeQuery($sql);
        }
    }

}
