<?php

namespace Anna\Repositories;

use Anna\Config;
use Anna\Databases\Model;
use Anna\Error;
use Anna\Request;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * -----------------------------------------------------------
 * Repository
 * -----------------------------------------------------------.
 *
 * Repositório padrão, fornecido pelo sistema para trabalhar com o adaptador original do Doctrine2 ORM cadastrado
 * nas configurações
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 06, novembro 2015
 */
class Repository extends \Anna\Repositories\Abstracts\Repository
{
    /**
     * Quantidade de items por página.
     *
     * @var int
     */
    public $per_page = 15;

    /**
     * Nome do modelo utilizado por este repositório.
     *
     * @var Model
     */
    protected $model;

    /**
     * Não é necessário esta declaração, é apenas para gerar autocomplete nas IDE's.
     *
     * @var EntityManager
     */
    protected $manager;

    /**
     * Função que persiste os dados da entidade informada no banco de dados.
     *
     * @param mixed $model Qualquer entidade válida do Doctrine
     *
     * @return bool
     */
    public function save($model = null)
    {
        $this->model = $model ? $model : $this->model;

        if ($this->model->id) {
            return $this->edit();
        }

        $this->model->created_at = new \DateTime('now');

        try {
            $this->manager->persist($this->model);
            $this->manager->flush();
        } catch (\Exception $e) {
            Error::log($e);

            return false;
        }

        return true;
    }

    /**
     * Remove o registro no banco de dados referente ao nome do modelo recebido como parâmetro
     * Caso as opções de soft delete estejam configuradas o campo buscará por a data atual no campo informado
     * como delflag nas configurações do aplicativo.
     *
     * @param int $id valor da chave primária do registro
     *
     * @return false|null
     */
    public function remove($id)
    {
        $modelname = get_class($this->model);
        $metadata = $this->manager->getClassMetadata($modelname);
        $table_name = $metadata->getTableName();
        $primary_key = $metadata->getSingleIdentifierFieldName();

        $this->model = $this->search([$primary_key => $id], true);

        if (!$this->model instanceof Model) {
            Error::log(new \Exception('Não foi encontrado registro na tabela: '.$table_name));

            return false;
        }

        if (Config::getInstance()->get('database.softdelete')) {
            $delflag = Config::getInstance()->get('database.delflag');
            $bin_field = $delflag;
            $this->model->$bin_field = new \Datetime('now');

            try {
                $this->manager->merge($this->model);
                $this->manager->flush();
            } catch (\Exception $e) {
                Error::log($e);

                return false;
            }
        } else {
            try {
                $this->manager->remove($this->model);
                $this->manager->flush();
            } catch (\Exception $e) {
                Error::log($e);

                return false;
            }
        }
    }

    /**
     * Função editar dados já persistidos no banco de dados.
     *
     * @param mixed $model Qualquer entidade válida do Doctrine
     *
     * @return bool
     */
    public function edit($model = null)
    {
        $model = $model ? $model : $this->model;

        try {
            $this->manager->merge($model);
            $this->manager->flush();
        } catch (\Exception  $e) {
            Error::log($e);

            return false;
        }

        return true;
    }

    /**
     * Função para buscas simples no banco de dados, para buscas mais complexas
     * utilizar o QueryBuilder do Doctrine.
     *
     * @param array $filters formato do array: ['campo_da_tabela' => 'valor para filtro']
     * @param bool  $one     true retorna apenas 1 registro
     *
     * @return mixed True se os dados foram encontrados e on estiver setado, neste caso a propriedade model
     *               deste repositório irá ser atualizada com os dados encontrados.
     *
     *                          Array se one for false (padrão), neste caso retorna um collecion com as entidades
     *                          encontradas.
     *
     *                          False se houver algum erro
     */
    public function search($filters, $one = false)
    {
        $modelname = get_class($this->model);

        try {
            if ($one) {
                $this->model = $this->manager->getRepository($modelname)->findOneBy($filters);

                return true;
            } else {
                $entities = $this->manager->getRepository($modelname)->findBy($filters);

                return $entities;
            }
        } catch (\Exception  $e) {
            Error::log($e);

            return false;
        }
    }

    /**
     * Efetua a persistencia dos dados em banco.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function persist(Model $model)
    {
        try {
            $this->manager->persist($model);
        } catch (\Exception  $e) {
            Error::log($e);

            return false;
        }

        return true;
    }

    /**
     * Efetua o merge do model atual com o banco de dados persistindo os dados editados.
     *
     * @param Model $model
     */
    public function merge(Model $model)
    {
        try {
            $this->manager->merge($model);
        } catch (\Exception  $e) {
            Error::log($e);

            return false;
        }

        return true;
    }

    public function find($id)
    {
        return $this->manager->find(get_class($this->model), $id);
    }

    /**
     * Comita as alterações/inserções para o banco de dados.
     *
     * Por padrão o Doctrine2 não comita
     *
     * @return bool
     */
    public function flush()
    {
        try {
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->manager->rollback();
            Error::log($e);

            return false;
        }

        return true;
    }

    /**
     * Entrega a ferramenta QueryBuider do Doctrine2 para construção de queries customizadas utilizando DQL.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->manager->createQueryBuilder();
    }

    /**
     * Retorna um objeto PDO para uso de SQL's manuais.
     *
     * @return \Doctrine\DBAL\Driver\Connection
     */
    public function getPdo()
    {
        return $this->manager->getConnection()->getWrappedConnection();
    }

    /**
     * Busca registros nos parametros POST de entrada com o mesmo nome das propriedades do modelo registrado e
     * preenche automaticamente seus valores.
     */
    public function autoFill()
    {
        $r = new Request();
        $fields = $this->manager->getClassMetadata(get_class($this->model))->getFieldNames();

        foreach ($fields as $field) {
            $value = $r->post($field);
            $this->model->$field = $value;
        }
    }

    /**
     * registra o model específico para persistência.
     *
     * @param Model $model
     *
     * @return Repository
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Paginador padrão, efetua a busca com base nos filtros recebidos e retorna componente de paginação.
     *
     * @param int   $page
     * @param array $filters
     *
     * @return Paginator
     */
    public function paginate($page = 1, $filters = null)
    {
        $offset = ($page == 1) ? 0 : $page * $this->per_page;

        $qb = $this->manager->createQueryBuilder();
        $qb->select('a')->from(get_class($this->model), 'a');

        if (is_array($filters)) {
            foreach ($filters as $field => $value) {
                if ($value !== null) {
                    $qb->where("a.$field = :$field")->setParameter($field, $value);
                } else {
                    $qb->where("a.$field IS NULL");
                }
            }
        }

        $query = $qb->getQuery();
        unset($qb);

        $query->setFirstResult($offset);
        $query->setMaxResults($this->per_page);

        try {
            $paginator = new Paginator($query);
        } catch (\Exception  $e) {
            Error::log($e);

            return false;
        }

        $paginator = new \Anna\Paginator($paginator, $this->per_page, $page);

        return $paginator;
    }
}
