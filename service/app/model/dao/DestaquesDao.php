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
            SELECT c.titulo, c.link, a1.nome AS 'texto', a2.nome AS 'imagem', a1.extensao AS 'thumb_extensao', a2.extensao AS 'imagem_extensao', c.caseid
            FROM  tbl_destaques AS c
            LEFT OUTER JOIN tbl_arquivo AS a1 ON c.imagem = a1.id
            LEFT OUTER JOIN tbl_arquivo AS a2 ON c.thumb = a2.id
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

				$result->res[$key]['imagem_640'] = $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/mobile/' . substr($result->res[$key]['texto'], 0, strlen($result->res[$key]['texto'])-4) . '-640.jpg';
				
				$result->res[$key]['imagem_1024'] = $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/mobile/' . substr($result->res[$key]['texto'], 0, strlen($result->res[$key]['texto'])-4) . '-1024.jpg';
				
				$result->res[$key]['imagem_1920'] = $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/mobile/' . substr($result->res[$key]['texto'], 0, strlen($result->res[$key]['texto'])-4) . '-1920.jpg';
			
                $result->res[$key]['texto'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['thumb_extensao'].'/'.$result->res[$key]['texto'];

                $result->res[$key]['imagem'] = $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_extensao'].'/'.$result->res[$key]['imagem'];
				
            }
        }

        return $result;
    }

    public function getDestaquesComArquivosUnico($id)
    {
        $sql = '
            SELECT D.id, D.titulo AS "titulo", D.link AS "link", D.caseid AS "caseid",
            CONCAT("/service/web/uploads/", A1.extensao, "/", A1.nome) AS "thumb",
            CONCAT("/service/web/uploads/", A2.extensao, "/", A2.nome) AS "imagem",
            D.thumb AS "thumb_id",
            D.imagem AS "imagem_id",
            D.ativo AS "ativo"
            FROM tbl_destaques D
            LEFT OUTER JOIN tbl_arquivo A1 ON A1.id = D.thumb
            LEFT OUTER JOIN tbl_arquivo A2 ON A2.id = D.imagem
            WHERE D.status = 1 AND A1.status = 1 AND A2.status = 1 AND D.id = :id
        ';

        $result = $this->db->row($sql, array('id' => $id));

        return $result;
    }

    public function getDestaquesComArquivosListagem()
    {
        $sql = '
            SELECT D.id, D.titulo AS "Título", D.link AS "Link", D.caseid,
            CONCAT("/service/web/uploads/", A2.extensao, "/", A2.nome) AS "Imagem", 
            CONCAT("/service/web/uploads/", A1.extensao, "/", A1.nome) AS "Imagem com Texto", 			
            D.ativo AS "Ativo"
            FROM tbl_destaques D
            LEFT OUTER JOIN tbl_arquivo A1 ON A1.id = D.thumb
            LEFT OUTER JOIN tbl_arquivo A2 ON A2.id = D.imagem
            WHERE D.status = 1 AND A1.status = 1 AND A2.status = 1
        ';

        $result = $this->db->query($sql);

        return $result;
    }
}
