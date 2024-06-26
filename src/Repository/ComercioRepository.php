<?php

namespace App\Repository;

use App\Entity\Comercio;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;

/**
 * @extends ServiceEntityRepository<Comercio>
 *
 * @method Comercio|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comercio|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comercio[]    findAll()
 * @method Comercio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Comercio[]    findNombresComercios(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Comercio[]    buscadorComercios(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class ComercioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comercio::class);
    }


    /**
     * @return Comercio[] Returns an array of Comercio objects
     */
    public function findNombresComercios(Usuario $usuario, array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id', 'c.nombre', 'u.nombre as usuario', 'c.email', 'c.estado', 'f.archivo', 'cat.nombre as categoria')
            ->addSelect('CASE c.estado
            WHEN 1 THEN \'pendiente\'
            WHEN 2 THEN \'abierto\'
            WHEN 3 THEN \'cerrado\'
            WHEN 4 THEN \'vacaciones\'
            ELSE \'sin estado\'
            END as nombreEstado ')
            ->join('c.usuario', 'u')
            ->leftJoin('c.fotos', 'f', 'WITH', 'f.destacada = true')
            ->leftJoin('c.categorias', 'cat')
            ->groupBy('c.id');

        if (!is_null($orderBy)) {
            foreach ($orderBy as $campo => $direccion) {
                $qb->orderBy('c.' . $campo, $direccion);
            }
        }

        if (!in_array('ROLE_ADMIN', $usuario->getRoles())) {
            $qb->where('c.usuario = :val')
                ->setParameter('val', $usuario);
        }

        if (!is_null($limit)) {
            $qb->setMaxResults($limit);
        }

        if (!is_null($offset)) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->execute();
    }
    /**
     * @return Comercio[] Returns an array of Comercio objects
     */
    public function buscadorComercios(string $searchTerm, array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id', 'c.nombre', 'c.descripcion', 'c.estado', 'f.archivo', 'cat.nombre as categoria')
            ->addSelect('CASE c.estado
            WHEN 1 THEN \'pendiente\'
            WHEN 2 THEN \'abierto\'
            WHEN 3 THEN \'cerrado\'
            WHEN 4 THEN \'vacaciones\'
            ELSE \'sin estado\'
            END as nombreEstado ')
            ->join('c.usuario', 'u')
            ->leftJoin('c.fotos', 'f', 'WITH', 'f.destacada = true')
            ->leftJoin('c.categorias', 'cat')
            ->where('c.nombre LIKE :searchTerm')
            ->orWhere('c.descripcion LIKE :searchTerm')
            ->orWhere('cat.nombre LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->groupBy('c.id')
            ;

        if (!is_null($orderBy)) {
            foreach ($orderBy as $campo => $direccion) {
                $qb->orderBy('c.' . $campo, $direccion);
            }
        }

        return $qb->getQuery()->getResult();
    }
}
