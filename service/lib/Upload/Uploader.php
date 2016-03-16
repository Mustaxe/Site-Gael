<?php

namespace lib\Upload;

use app\model\Arquivo;
use \Upload\Storage\FileSystem;
use \Upload\File;
use \Upload\Validation\Mimetype;
use \Upload\Validation\Size;
use lib\Log\DbWriter;

/**
 * Realiza o upload de arquivos
 */ 
class Uploader
{
    /**
     * @var object Objeto para log de exceções
     */
    private $log;

    /**
     * Formatos aceitos
     */
    public $formatosAceitos = array();

    /**
     * Tamanho máximo do arquivo (usar "B", "K", M" ou "G")
     */
    public $tamanhoMaximo;

    /**
     * Arquivo para upload
     */ 
    protected $arquivo;

    /**
     * Nome original do arquivo
     */
    protected $nomeOriginal;
    
    /**
     * Diretório raiz para o upload
     */
    protected $caminho;

    /**
     * Diretório completo para o upload
     */
    protected $caminhoUpload;

    /**
     * Tipo
     */
    protected $tipo; 

    /**
     * Contrutor
     *
     * @param Arquivo $arquivoCrud
     * @param array   $config
     */
    public function __construct(Arquivo $arquivoCrud, array $config, DbWriter $log)
    {
        $this->arquivoCrud          = $arquivoCrud;
        $this->tamanhoMaximo        = $config['slim']['upload.max_size'];
        $this->formatosAceitos      = $config['slim']['upload.mimetypes'];
        $this->caminho              = $config['slim']['upload.path'];
        $this->log                  = $log;
    }

    /**
     * Seta o arquivo para upload
     * 
     * @param string $arquivo
     */ 
    public function setArquivo($arquivo)
    {

        $this->setNomeOriginal($arquivo);
        $this->setCaminhoUpload($arquivo);
        $c = $this->getCaminhoUpload();
        
        $storage = new FileSystem($c);
        $this->arquivo = new File($arquivo, $storage);
    }

    /**
     * Retorna o arquivo do upload
     * 
     * @return $arquivo
     */ 
    public function getArquivo()
    {
        return $this->arquivo;
    }

    public function setNomeOriginal($key){
        $this->nomeOriginal = $_FILES[$key]['name'];
    }

    public function getNomeOriginal(){
        return $this->nomeOriginal;
    }

    /**
     * Retorna o caminho completo para o upload
     * 
     * @param string $arquivo
     */ 
    public function setCaminhoUpload($arquivo){
        $ext = trim(strtolower(pathinfo($_FILES[$arquivo]['name'], PATHINFO_EXTENSION)));
        if(empty($ext)){
            throw new \Exception('Não é permitido o upload de arquivos sem extensão.');
        }

        $c = $this->caminho . $ext;
        if (!is_dir($c)) {
            mkdir($c);
        }

        $this->caminhoUpload = $c;
    }

    /**
     * Retorna o arquivo do upload
     */ 
    public function getCaminhoUpload()
    {
        return $this->caminhoUpload;
    }

    /**
     * Retorna o arquivo do upload
     */ 
    public function getCaminhoReal()
    {
        return $this->arquivo->getRealPath();
    }

    /**
     * Seta o tipo
     */ 
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }
	
    /**
     * Retorna o tipo
     */ 
    public function getTipo()
    {
        return $this->tipo;
    }

	
    /**
     * Seta a URL
     */ 
    public function setUrl($url)
    {
        $this->url = $url;
    }
	
    /**
     * Retorna a URL
     */ 
    public function getUrl()
    {
        return $this->url;
    }

	
	
    /**
     * Seta os formatos aceitos
     */
    public function setFormatosAceitos($formatos = array())
    {
        $this->formatosAceitos = $formatos;
    }

    /**
     * Retorna os formatos aceitos
     */
    public function getFormatosAceitos()
    {
        return $this->formatosAceitos;
    }

    /**
     * Seta o tamanho máximo do arquivo
     */
    public function setTamanhoMaximo($tamanho)
    {
        $this->tamanhoMaximo = $tamanho;
    }

    /**
     * Retorna o tamanho máximo do arquivo
     */
    public function getTamanhoMaximo()
    {
        return $this->tamanhoMaximo;
    }

    /**
     * Retorna o diretório para upload conforme mimetype
     */
    public function getCaminhoMime()
    {
        return $this->caminho .'/'. $this->getExtensao();
    }

    /**
     * Gera novo nome do arquivo
     * 
     * @return string
     */ 
    public function geraNomeArquivo()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    /**
     * Nome do arquivo com extensão
     * 
     * @return string
     */ 
    public function getNomeArquivo()
    {
        return $this->arquivo->getNameWithExtension();
    }

    /**
     * Extensão do arquivo
     * 
     * @return string
     */ 
    public function  getExtensao()
    {
        return $this->arquivo->getExtension();
    }

    /**
     * Tamanho do arquivo
     * 
     * @return string
     */ 
    public function  getTamanho()
    {
        return $this->arquivo->getSize();
    }

    /**
     * MimeType do arquivo
     * 
     * @return string
     */ 
    public function getMimetype()
    {
        return $this->arquivo->getMimetype();
    }

    /**
     * MD5 do arquivo
     * 
     * @return string
     */ 
    public function  getMd5()
    {
        return $this->arquivo->getMd5();
    }

    /**
     * Dimensoes do arquivo
     * 
     * @return string
     */ 
    public function  getDimensoes()
    {
        return $this->arquivo->getDimensions();
    }

    public function salva()
    {
        // Renomeia o arquivo
        $novoNome = $this->geraNomeArquivo();
        $this->arquivo->setName($novoNome);

        // Valida o arquivo
        // MimeTypes => http://www.webmaster-toolkit.com/mime-types.shtml
        $this->arquivo->addValidations(array(
            // Arquivo deve ser do tipo "image/png"
            new Mimetype($this->getFormatosAceitos()),

            // Pode-se usar vários mimetypes para validar
            // new \Upload\Validation\Mimetype(array('image/png', 'image/gif')));

            // Tamanho máximo do arquivo
            new Size($this->getTamanhoMaximo()),
        ));

        try {
            $res = false;

            // Realiza o upload
            $this->arquivo->upload();

            $caminhoReal = $this->getCaminhoUpload().'/'.$novoNome.'.'.$this->getExtensao();
            $checksum    = sha1_file($caminhoReal);
            $nomeArquivo = $novoNome.'.'.$this->getExtensao();

            $dataAtual = new \DateTime();

            // Salva no banco de dados
            $sArquivo = $this->arquivoCrud;
            $sArquivo->nome         = $nomeArquivo;
            $sArquivo->checksum     = $checksum;
            $sArquivo->modificado   = $dataAtual->format('Y-m-d H:i:s');
            $sArquivo->tamanho      = $this->getTamanho();
            $sArquivo->extensao     = $this->getExtensao();
            $sArquivo->tipo         = $this->getTipo();
			$sArquivo->url			= $this->getUrl();
            $sArquivo->nomeOriginal = $this->getNomeOriginal();
            $resArquivo = $sArquivo->Create();

            if($resArquivo->cod == 200) {
                $res = $this->arquivoCrud->findOne(array(), "nome = '" . $nomeArquivo . "'", array('id'), '');
            }

            return $res;

        } catch (\Exception $e) {
            $errosArquivo = implode('|',$this->arquivo->getErrors());
            $excecao      = $e->getMessage();
            $msg          = 'Erro no upload: ' . $e->getMessage() .' - erros no arquivo: '. $errosArquivo;

            throw new \Exception($msg);
        }
    }

}
