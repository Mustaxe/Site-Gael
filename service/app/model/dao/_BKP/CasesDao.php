<?php

namespace app\model\dao;

use lib\Db\Db;
use \PDO;

/**
 * Cases Dao
 */
class CasesDao
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

    public function getCasesComArquivos($ativo = '')
    {
        $ativo = trim($ativo);
        $sql = "
            SELECT c.id, c.titulo, c.descricao, c.texto, c.status, c.ativo, a1.nome as imagem_thumb, a1.extensao as imagem_thumb_extensao, a2.nome as imagem_thumb_over, a2.extensao as imagem_thumb_over_extensao, a3.nome as imagem_integra1, a3.extensao as imagem_integra_extensao, a4.nome as imagem_integra2, a5.nome as imagem_integra3, a6.nome as imagem_integra4, a7.nome as imagem_integra5
            FROM  tbl_cases AS c
            LEFT OUTER JOIN tbl_arquivo AS a1 ON c.imagem_thumb = a1.id
            LEFT OUTER JOIN tbl_arquivo AS a2 ON c.imagem_thumb_over = a2.id
            LEFT OUTER JOIN tbl_arquivo AS a3 ON c.imagem_integra1 = a3.id
			LEFT OUTER JOIN tbl_arquivo AS a4 ON c.imagem_integra2 = a4.id
			LEFT OUTER JOIN tbl_arquivo AS a5 ON c.imagem_integra3 = a5.id
			LEFT OUTER JOIN tbl_arquivo AS a6 ON c.imagem_integra4 = a6.id
			LEFT OUTER JOIN tbl_arquivo AS a7 ON c.imagem_integra5 = a7.id
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
                $result->res[$key]['imagem_thumb'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_thumb_extensao'].'/'.$result->res[$key]['imagem_thumb'];

                $result->res[$key]['imagem_thumb_over'] = $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_thumb_over_extensao'].'/'.$result->res[$key]['imagem_thumb_over'];

                $result->res[$key]['imagem_integra1'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_integra_extensao'].'/'.$result->res[$key]['imagem_integra1'];
				
				if($result->res[$key]['imagem_integra2'] != '') {
					$result->res[$key]['imagem_integra2'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_integra_extensao'].'/'.$result->res[$key]['imagem_integra2'];
				}
				if($result->res[$key]['imagem_integra3'] != '') {
					$result->res[$key]['imagem_integra3'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_integra_extensao'].'/'.$result->res[$key]['imagem_integra3'];
				}
				if($result->res[$key]['imagem_integra4'] != '') {
					$result->res[$key]['imagem_integra4'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_integra_extensao'].'/'.$result->res[$key]['imagem_integra4'];
				}
				if($result->res[$key]['imagem_integra5'] != '') {
					$result->res[$key]['imagem_integra5'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_integra_extensao'].'/'.$result->res[$key]['imagem_integra5'];
				}	
            }
        }

        return $result;
    }
}
 