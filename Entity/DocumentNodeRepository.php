<?php

namespace Erichard\DmsBundle\Entity;

use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class DocumentNodeRepository extends ClosureTreeRepository
{
    public function findOneBySlugWithChildren($slug)
    {
        return $this
            ->createQueryBuilder('n')
            ->addSelect('nodes','d', 'p', 'm')
            ->leftJoin('n.nodes', 'nodes', 'nodes.id')
            ->leftJoin('n.documents', 'd', 'd.id')
            ->leftJoin('n.parent', 'p', 'd.id')
            ->leftJoin('n.metadatas', 'm', 'm.metadata.name')
            ->where('n.slug = :node')
            ->setParameter('node', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getRoots()
    {
        return $this
            ->createQueryBuilder('n')
            ->where('n.parent IS NULL')
            ->orderBy('n.name')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getNodeAuthorizationsByRoles($id, array $roles)
    {
        $queryRoles = array_map(function($role) { return "'$role'"; }, $roles);
        $queryRoles = implode(',', $queryRoles);

        $table = $this
            ->getEntityManager()
            ->getClassMetadata('Erichard\DmsBundle\Entity\DocumentNodeClosure')
            ->getTableName()
        ;

        $authorizationTableName = $this
            ->getEntityManager()
            ->getClassMetadata('Erichard\DmsBundle\Entity\DocumentNodeAuthorization')
            ->getTableName()
        ;

        $query = "SELECT a.role, a.allow, a.deny ".
            "FROM $table c INNER JOIN $authorizationTableName a ON (a.node_id = c.ancestor) ".
            "WHERE c.descendant = :node AND a.role IN ($queryRoles) ORDER BY c.depth ASC "
        ;

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->bindValue("node", $id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getAuthorizationsByRoles(array $roles)
    {
        $queryRoles = array_map(function($role) { return "'$role'"; }, $roles);
        $queryRoles = implode(',', $queryRoles);

        $table = $this
            ->getEntityManager()
            ->getClassMetadata('Erichard\DmsBundle\Entity\DocumentNodeClosure')
            ->getTableName()
        ;

        $authorizationTableName = $this
            ->getEntityManager()
            ->getClassMetadata('Erichard\DmsBundle\Entity\DocumentNodeAuthorization')
            ->getTableName()
        ;

        $query = "SELECT a.* ".
            "FROM $table c INNER JOIN $authorizationTableName a ON (a.node_id = c.ancestor) ".
            "WHERE a.role IN ($queryRoles) ORDER BY c.depth ASC "
        ;

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

}
