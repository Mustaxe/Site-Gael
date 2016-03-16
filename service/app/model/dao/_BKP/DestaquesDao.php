<?php

namespace app\model\dao;

use lib\Db\Db;
use \PDO;

/**
 * Cases Dao
 */
class DestaquesDao
{
    /**
     * @var Db
     */
    protected $db;

    /**
     * @var App
     */
    protected $app;

    /**
     * Construtor
     *
     * @param Db
     */
    public function __construct($app)
    {
        $this->db  = $app->db;
        $this->app = $app;
    }

    public function getDestaquesComArquivos($ativo = '')
    {
        $ativo = trim($ativo);
        $sql = "
            SELECT c.titulo, c.link, a1.nome AS 'thumb', a2.nome AS 'imagem', a1.extensao AS 'thumb_extensao', a2.extensao AS 'imagem_extensao'
            FROM  tbl_destaques AS c
            INNER JOIN tbl_arquivo AS a1 ON c.imagem = a1.id
            INNER JOIN tbl_arquivo AS a2 ON c.thumb = a2.id
            WHERE c.STATUS = 1
        ";
        if($ativo == '1' || $ativo == '0'){
            $sql .= " AND ativo = :ativo";
            $result = $this->db->query($sql, array('ativo' => $ativo));
        }else{
            $result = $this->db->query($sql);
        }
		
        if ($result->cod == 200) {
            foreach ($result->res as $key => $row) {
                $result->res[$key]['thumb'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['thumb_extensao'].'/'.$result->res[$key]['thumb'];

                $result->res[$key]['imagem'] = $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_extensao'].'/'.$result->res[$key]['imagem'];
            }
        }

        return $result;
    }
}
 