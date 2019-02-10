<?php

use Doctrine\DBAL\Connection;
use Oc\Repository\Exception\RecordAlreadyExistsException;
use Oc\Repository\Exception\RecordNotFoundException;
use Oc\Repository\Exception\RecordNotPersistedException;
use Oc\Repository\Exception\RecordsNotFoundException;

class XmlsessionRepository
{
    const TABLE = 'xmlsession';

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return XmlsessionEntity[]
     */
    public function fetchAll()
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->execute();

        $result = $statement->fetchAll();

        if ($statement->rowCount() === 0) {
            throw new RecordsNotFoundException('No records found');
        }

        $records = [];

        foreach ($result as $item) {
            $records[] = $this->getEntityFromDatabaseArray($item);
        }

        return $records;
    }

    /**
     * @return XmlsessionEntity
     */
    public function fetchOneBy(array $where = [])
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->setMaxResults(1);

        if (count($where) > 0) {
            foreach ($where as $column => $value) {
                $queryBuilder->andWhere($column . ' = ' . $queryBuilder->createNamedParameter($value));
            }
        }

        $statement = $queryBuilder->execute();

        $result = $statement->fetch();

        if ($statement->rowCount() === 0) {
            throw new RecordNotFoundException('Record with given where clause not found');
        }

        return $this->getEntityFromDatabaseArray($result);
    }

    /**
     * @return XmlsessionEntity[]
     */
    public function fetchBy(array $where = [])
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE);

        if (count($where) > 0) {
            foreach ($where as $column => $value) {
                $queryBuilder->andWhere($column . ' = ' . $queryBuilder->createNamedParameter($value));
            }
        }

        $statement = $queryBuilder->execute();

        $result = $statement->fetchAll();

        if ($statement->rowCount() === 0) {
            throw new RecordsNotFoundException('No records with given where clause found');
        }

        $entities = [];

        foreach ($result as $item) {
            $entities[] = $this->getEntityFromDatabaseArray($item);
        }

        return $entities;
    }

    /**
     * @return XmlsessionEntity
     */
    public function create(XmlsessionEntity $entity)
    {
        if (!$entity->isNew()) {
            throw new RecordAlreadyExistsException('The entity does already exist.');
        }

        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->insert(
            self::TABLE,
            $databaseArray
        );

        $entity->id = (int) $this->connection->lastInsertId();

        return $entity;
    }

    /**
     * @return XmlsessionEntity
     */
    public function update(XmlsessionEntity $entity)
    {
        if ($entity->isNew()) {
            throw new RecordNotPersistedException('The entity does not exist.');
        }

        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->update(
            self::TABLE,
            $databaseArray,
            ['id' => $entity->id]
        );

        return $entity;
    }

    /**
     * @return XmlsessionEntity
     */
    public function remove(XmlsessionEntity $entity)
    {
        if ($entity->isNew()) {
            throw new RecordNotPersistedException('The entity does not exist.');
        }

        $this->connection->delete(
            self::TABLE,
            ['id' => $entity->id]
        );

        $entity->cacheId = null;

        return $entity;
    }

    /**
     * @return []
     */
    public function getDatabaseArrayFromEntity(XmlsessionEntity $entity)
    {
        return [
            'id' => $entity->id,
            'date_created' => $entity->dateCreated,
            'last_use' => $entity->lastUse,
            'users' => $entity->users,
            'caches' => $entity->caches,
            'cachedescs' => $entity->cachedescs,
            'cachelogs' => $entity->cachelogs,
            'pictures' => $entity->pictures,
            'removedobjects' => $entity->removedobjects,
            'modified_since' => $entity->modifiedSince,
            'cleaned' => $entity->cleaned,
            'agent' => $entity->agent,
        ];
    }

    /**
     * @return XmlsessionEntity
     */
    public function getEntityFromDatabaseArray(array $data)
    {
        $entity = new XmlsessionEntity();
        $entity->id = (int) $data['id'];
        $entity->dateCreated = new DateTime($data['date_created']);
        $entity->lastUse = new DateTime($data['last_use']);
        $entity->users = (int) $data['users'];
        $entity->caches = (int) $data['caches'];
        $entity->cachedescs = (int) $data['cachedescs'];
        $entity->cachelogs = (int) $data['cachelogs'];
        $entity->pictures = (int) $data['pictures'];
        $entity->removedobjects = (int) $data['removedobjects'];
        $entity->modifiedSince = new DateTime($data['modified_since']);
        $entity->cleaned = (int) $data['cleaned'];
        $entity->agent = (string) $data['agent'];

        return $entity;
    }
}
