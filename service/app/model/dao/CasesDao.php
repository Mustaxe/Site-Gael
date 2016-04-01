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

    public function getCasesComArquivos($ativo = '', $lang = 'pt')
    {
        $ativo = trim($ativo);

        $sql = "
            SELECT
                c.id,
                c.titulo,
                c.descricao,
                c.texto,
                c.status,
                c.ativo,
                c.categorias,
                a1.nome as imagem_thumb,
                a1.extensao as imagem_thumb_extensao,
                c.imagens,
                c.ordem,
                c.lang,
                a2.nome as imagem_thumb_over,
                a2.extensao as imagem_thumb_over_extensao
			FROM
                tbl_cases AS c
			LEFT OUTER JOIN
                tbl_arquivo AS a1 ON c.imagem_thumb = a1.id
			LEFT OUTER JOIN
                tbl_arquivo AS a2 ON c.imagem_thumb_over = a2.id
			WHERE
                c.STATUS = 1 AND c.lang = '" . $lang . "'";

        if($ativo == '1' || $ativo == '0')
        {
            $sql .= " AND ativo = :ativo";
			$sql .= " ORDER BY c.ordem ASC";
            $result = $this->db->query($sql, array('ativo' => $ativo));
        }
        else
        {
            $result = $this->db->query($sql);
        }

        if ($result->cod == 200) {
            foreach ($result->res as $key => $row) {
                $result->res[$key]['imagem_thumb'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_thumb_extensao'].'/'.$result->res[$key]['imagem_thumb'];
                $result->res[$key]['imagem_thumb_over'] = $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_thumb_over_extensao'].'/'.$result->res[$key]['imagem_thumb_over'];
			
				//$imgs = explode(',', $result->res[$key]['imagens']);
				if($result->res[$key]['imagens'] != '') {
					$S = "SELECT CONCAT('".$this->app->request->getUrl().$this->app->request->getRootUri()."', '/web/uploads/', extensao, '/', nome) AS img, 
								 CONCAT('".$this->app->request->getUrl().$this->app->request->getRootUri()."', '/web/uploads/', extensao, '/', SUBSTRING(nome, 1, 40), '-640.jpg') AS img_640,
								 CONCAT('".$this->app->request->getUrl().$this->app->request->getRootUri()."', '/web/uploads/', extensao, '/', SUBSTRING(nome, 1, 40), '-1024.jpg') AS img_1024,
								 url
							FROM tbl_arquivo 
							WHERE id IN (" . $result->res[$key]['imagens'] . ") AND status = 1";
					$R = $this->db->query($S);
					
					$result->res[$key]['assets'] = $R->res;
				} else {
					$result->res[$key]['assets'] = '';
					
				}
				
				//$result->res[$key]['assets'] = $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/' . ;

                /* $result->res[$key]['imagem_integra1'] =  $this->app->request->getUrl().$this->app->request->getRootUri().'/web/uploads/'.$result->res[$key]['imagem_integra_extensao'].'/'.$result->res[$key]['imagem_integra1'];

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
                } */
            }
        }

        return $result;
    }

    public function getCasesComArquivosUnico($id)
    {
        $sql = '
            SELECT 
                C.id,
                C.titulo AS "titulo",
                C.descricao AS "descricao",
                C.texto AS "texto",
                CONCAT("/service/web/uploads/", A1.extensao, "/", A1.nome) AS "thumb",
                CONCAT("/service/web/uploads/", A2.extensao, "/", A2.nome) AS "thumbHover",
                C.imagem_thumb AS "thumb_id",
                C.imagem_thumb_over AS "thumbHover_id",
                C.ativo AS "ativo",
                C.categorias AS "categorias",
                C.ordem AS "ordem",
                C.lang,
                C.imagens AS "imagens"
            FROM
                tbl_cases C
            LEFT OUTER JOIN
                tbl_arquivo A1 ON A1.id = C.imagem_thumb
            LEFT OUTER JOIN
                tbl_arquivo A2 ON A2.id = C.imagem_thumb_over
            WHERE
                C.status = 1 AND A1.status = 1 AND A2.status = 1 AND C.id = :id';

        $result = $this->db->row($sql, array('id' => $id));

		$sql = '
			SELECT
                A.id,
				CONCAT("/service/web/uploads/", A.extensao, "/", A.nome) AS image,
				A.url AS url_video
			FROM
                tbl_cases C
			LEFT OUTER JOIN
                tbl_arquivo A ON FIND_IN_SET(A.id, C.imagens)
			WHERE
				C.id = :id AND A.status = 1';

		$assets = $this->db->Query($sql, array('id' => $id));
		$result->res['assets'] = $assets->res;

        return $result;
    }

    public function getCasesComArquivosListagem()
    {
        $sql = '
            SELECT
                C.id,
                C.titulo AS "Título",
                C.descricao AS "Descrição",
                C.texto AS "Texto",
                IF(lang = \'pt\', \'Português\', \'Inglês\') AS "Idioma",
                C.ordem AS "Ordem",                
                CONCAT("/service/web/uploads/", A1.extensao, "/", A1.nome) AS "Thumb",
                CONCAT("/service/web/uploads/", A2.extensao, "/", A2.nome) AS "ThumbHover",
                C.ativo AS "Ativo"
            FROM
                tbl_cases C
            LEFT OUTER JOIN 
                tbl_arquivo A1 ON A1.id = C.imagem_thumb
            LEFT OUTER JOIN
                tbl_arquivo A2 ON A2.id = C.imagem_thumb_over
            WHERE
                C.status = 1 AND A1.status = 1 AND A2.status = 1
			ORDER BY
                C.ordem ASC';

        $result = $this->db->query($sql);

        return $result;
    }
}
